<?php
namespace common\modules\imageStorage\components;

use Codeception\Exception\ConfigurationException;
use common\modules\imageStorage\helpers\ImageStorageHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ImageStorage extends \yii\base\Component
{

    /**
     * Секретный ключ шифрования
     * Служит для передачи заашифрованных данных в ajax запросах
     * @var string
     */
    public $secretKey = '<r?LF:%J2=JXaGBT';


    /**
     * Путь к папке с оригинальными картинками
     * @var string
     */
    public $uploadRoot = '@root/data/objects/{object_id}/img';

    /**
     * Путь к папке с обработанными картинками
     * @var string
     */
    public $cacheRoot = '@root/cache/objects/{object_id}/img';


    /**
     * Веб путь
     * @var string
     */
    public $webPath = '/data/objects/{object_id}/img';

    /**
     * Кеш веб путь
     * @var string
     */
    public $cacheWebPath  = '/cache/objects/{object_id}/img';

    /**
     * Кеш дирректория
     * @var string
     */
    public $cacheDir = 'cache/objects';

    /**
     * Дирректория
     * @var string
     */
    public $uploadDir = 'data/objects';


    /**
     * Основной конфиг
     * @var array
     */
    public $config;

    /**
     * Кофинг профилей картинок
     * @var array
     */
    public $owners;

    /**
     * Путь к классу парсинга конфигурации профиля
     * @var string
     */
    public $ownerClass = 'common\modules\imageStorage\components\ImageOwner';

    /**
     * Класс изображения
     * @var string
     */
    public $imageItemClass = 'common\modules\imageStorage\components\ImageItem';

    /**
     * Класс работы с БД
     * @var string
     */
    public $dbSaverClass = 'common\modules\imageStorage\components\DbSaver';

    /**
     * Текущие настройки профиля
     * @var ImageOwner
     */
    protected $currentOwner;

    /**
     * Экземпляр для работы с бд
     * @var $dbSaver DbSaver;
     */
    public $dbSaver;

    /**
     * Текущий объект картинки
     * @var ImageItem
     */
    protected $currentImageItem;

    /**
     * Хранилище код => значение для распарсинга
     * шаблонов пути к дирректориям
     * Например [
     *      ['{object_id}' => '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24']
     * ]
     * @var array
     */
    public $pathTemplates =  [];

    /**
     * Полный путь к картинке
     * @var string
     */
    protected $fullPath;

    public $noPhotoUrl = '/data/nophoto/no-photo.jpg';

    public function init()
    {
        parent::init();
        $this->currentOwner = Yii::createObject([
            'class' => $this->ownerClass,
            'config' => $this->config,
            'ownerConfig' => $this->owners,
        ]);
        $this->dbSaver = Yii::createObject([
            'class' => $this->dbSaverClass
        ]);
    }

    /**
     * Загрузить фаил
     * @param UploadedFile $file
     * @param $owner
     * @return ResponseData|bool $response
     * @throws ConfigurationException
     */
    public function upload(UploadedFile $file, $owner)
    {
        if($this->currentOwner->open($owner)) {
            $response = new ResponseData();

            $path = $this->getPath();

            $name = $this->getUniqueName();
            $this->fullPath = $path . '/' . $name . '.' . $file->extension;
            if($file->saveAs($this->fullPath)){
                $this->currentImageItem = Yii::createObject([
                    'class' => $this->imageItemClass,
                    'fullPath' => $this->fullPath,
                    'cachePath' => $this->getCachePath(),
                    'name' => $name,
                ]);

                $this->fullPath = ImageProcess::convert($this->fullPath, $this->currentOwner->getConfig());
                $quality = $this->currentOwner->getValue('imagick.quality');

                ImageProcess::optimize($this->fullPath, $quality);

                $this->currentImageItem->fullPath = $this->fullPath;
                $this->currentImageItem->open($this->fullPath);
                $this->currentImageItem->config = $this->currentOwner->getConfig();
                $thumbPaths = $this->currentImageItem->createThumbs($this->currentOwner->thumbs);

                $thumbData = [];
                foreach ($thumbPaths as $thumbName => $thumbFullPath){
                    $thumbData[$thumbName] = [
                        'full_path' => $thumbFullPath,
                        'web_path' => $this->getWebPathFromCachePath($thumbFullPath)
                    ];
                }

                $response->fullPath = $this->fullPath;
                $response->webPath = $this->getWebPathFromRootPath($this->fullPath);
                $response->thumbs = $thumbData;
                $response->basename = ImageStorageHelper::getFileInfo($this->fullPath, 'basename');

                return $response;
            }

            return false;
        }

        throw new ConfigurationException('Owner not found');
    }

    /**
     * Загрузить файл из ajax запроса
     * @param $owner
     * @return bool
     */
    public function uploadFromAjaxRequest($owner)
    {
        $uploadedFile = $this->getFileFromRequest();
        if($uploadedFile)
            return $this->upload($uploadedFile, $owner);
        else
            return false;
    }

    /**
     * Удалить файл и превью изображения по веб пути
     * @param $webPath
     * @return bool
     */
    public function removeFile($webPath)
    {
        $post = strpos($webPath, $this->uploadDir);
        if($post !== 0){
            $webPath = $this->uploadDir. '/' . $webPath;
        }
        $fullPath = Yii::getAlias('@root') . '/' . $webPath;

        return $this->removeFileByFullPath($fullPath);
    }

    /**
     * Удалить файл и превью изображения по полному пути
     * @param $fullPath
     * @return bool
     */
    public function removeFileByFullPath($fullPath)
    {
        if(!file_exists($fullPath)){
            return true;
        }

        $result = false;
        $img = pathinfo($fullPath);
        $cachePath = str_replace($this->uploadDir, $this->cacheDir, $fullPath);
        $cachePath = str_replace('/'.$img['basename'], '', $cachePath);
        $fileName = $img['filename'];
        $cacheFiles = FileHelper::findFiles($cachePath, ['only' => [$fileName.'*.jpg']]);
        foreach ($cacheFiles as $thumbFile){
            unlink($thumbFile);
        }
        if(file_exists($fullPath))
            $result = unlink($fullPath);

        return $result;
    }

    /**
     * Получить данные в виде ResponseData объекта
     * @param $table
     * @param $objectId
     * @param $recordId
     * @param $field
     * @return ResponseData|null
     */
    public function getResponse($table, $objectId, $recordId, $field)
    {
        if($image = $this->dbSaver->getData($table, $recordId, $field)) {
            $this->setPathTemplateKey('{object_id}', $objectId);

            $response = new ResponseData();
            $response->objectId = $objectId;
            $response->recordId = $recordId;
            $response->attribute = $field;
            $response->basename = $field;
            $response->fullPath = ImageStorageHelper::getFullPathFromObjectRecord($this, $objectId, $image);
            $response->webPath = ImageStorageHelper::getWebPathFromObjectRecord($this, $objectId, $image);

            return $response;
        }

        return null;
    }

    /**
     * Получить файл из ajax запроса
     * @return null|UploadedFile
     */
    public function getFileFromRequest()
    {
        $uploadedFile = null;
        if($_FILES) {
            $files = $_FILES;
            $modelName = key($files);
            $file = array_shift($files);
            if ($file && isset($file['name'])){
                $fieldName = key($file['name']);
                if($modelName) {
                    $fileFieldName = $modelName . '[' . $fieldName . ']';
                } else {
                    $fileFieldName = $fieldName;
                }

                $uploadedFile = UploadedFile::getInstanceByName($fileFieldName);
            }
        }

        return $uploadedFile;
    }

    /**
     * Получить имя файла
     * @return string
     */
    public function getUniqueName()
    {
        //return uniqid(time(), true);
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Полный путь к директории с картинками
     * @return bool|string
     */
    public function getPath()
    {
        $this->uploadRoot = Yii::getAlias($this->uploadRoot);
        $this->uploadRoot = $this->pathTemplateFilter($this->uploadRoot);
        if(!file_exists($this->uploadRoot))
            FileHelper::createDirectory($this->uploadRoot, 0777);

        return Yii::getAlias($this->uploadRoot);
    }

    public function getWebPath()
    {
        $this->webPath = $this->pathTemplateFilter($this->webPath);
        return $this->webPath;
    }

    /**
     * Полный путь к кеш дирректории
     * @return bool|string
     */
    public function getCachePath()
    {
        $this->cacheRoot = Yii::getAlias($this->cacheRoot);
        $this->cacheRoot = $this->pathTemplateFilter($this->cacheRoot);
        if(!file_exists($this->cacheRoot))
            FileHelper::createDirectory($this->cacheRoot, 0777);

        return Yii::getAlias($this->cacheRoot);
    }

    /**
     * Открыть картинку. Если такой нет, будет создана
     * @param $filePath полный путь к картинке
     * @param $owner профиль
     * @param $size размер
     * @return mixed
     * @throws ConfigurationException
     * @throws NotFoundHttpException
     */
    public function getSrc($filePath, $owner = false, $size = false)
    {
        $post = strpos($filePath, '/'.$this->uploadDir);
        if($post !== 0){
            $filePath = $this->uploadDir. '/' . $filePath;
        }

        //original
        if(!$owner && !$size){
            return $filePath;
        }

        $isOwner = array_key_exists($owner, $this->owners);
        $sizeBox = explode('x', $size);
        if(!$isOwner && count($sizeBox) < 2){
            throw new ConfigurationException('Owner not found');
        }

        $pathInfo = pathinfo($filePath);
        if(isset($pathInfo['filename']) && isset($pathInfo['extension']) ){
            $thumbName = $pathInfo['filename'] .'_' . $size . '.' .$pathInfo['extension'];
            $fullPath = Yii::getAlias('@root') . '/' . $filePath;
            $thumbWebPath = str_replace($this->uploadDir, $this->cacheDir, $filePath);
            $thumbWebPath = str_replace($pathInfo['filename'].'.'.$pathInfo['extension'], $thumbName, $thumbWebPath);
            $thumbFullPath = Yii::getAlias('@root') . '/' .$thumbWebPath;

            $cacheRoot = str_replace('/'.$thumbName, '', $thumbFullPath);

            if(file_exists($thumbFullPath)){
                return $thumbWebPath;
            }
        } else {
            return false;
        }

        if(count($sizeBox) < 2){
            $ownerItem = Yii::createObject([
                'class' => $this->ownerClass,
                'config' => $this->config,
                'ownerConfig' => $this->owners,
            ]);

            $ownerItem->open($owner);

            $imageItem = Yii::createObject([
                'class' => $this->imageItemClass,
                'fullPath' => $thumbFullPath,
                'config' => $ownerItem->getConfig(),
                'cachePath' => $thumbFullPath,
                'name' => $owner,
            ]);

            $imageItem->createNewThumb($fullPath, $thumbFullPath, $ownerItem->thumbs, $size);
        }

        if(count($sizeBox) == 2){
            $width = $sizeBox[0];
            $height = $sizeBox[1];

            $imageItem = Yii::createObject([
                'class' => $this->imageItemClass,
                'fullPath' => $thumbFullPath,
                'cachePath' => $this->getCachePath(),
                'name' => $owner,
            ]);

            $imageItem->open($fullPath);
            $process = $imageItem->createThumb($width, $height, []);
            $process->save($cacheRoot, $thumbName);
        }

        if($thumbWebPath)
            return $thumbWebPath;
        else
            throw new NotFoundHttpException();
    }

    public function getWebPathFromRootPath($fullPath)
    {
        $pathInfo = pathinfo($fullPath);
        $this->webPath = $this->pathTemplateFilter($this->webPath);

        return $this->webPath . '/' . $pathInfo['basename'];
    }

    public function getWebPathFromCachePath($fullPath)
    {
        $pathInfo = pathinfo($fullPath);
        $this->cacheWebPath = $this->pathTemplateFilter($this->cacheWebPath);

        return $this->cacheWebPath . '/' . $pathInfo['basename'];
    }

    /**
     * Путь к дирректории
     * @param string $path
     * @return  string
     */
    public function pathTemplateFilter($path)
    {
        return strtr($path, $this->pathTemplates);
    }

    /**
     * Установить код шаблона и значение
     * @param $code
     * @param $value
     * @return mixed
     */
    public function setPathTemplateKey($code, $value)
    {
        return $this->pathTemplates[$code] = $value;
    }

    /**
     * Получить код шаблона и значение
     * @param $code
     * @return mixed|null
     */
    public function getPathTemplateKey($code)
    {
        if(isset($this->pathTemplates[$code]))
            return $this->pathTemplates[$code];

        return null;
    }
}