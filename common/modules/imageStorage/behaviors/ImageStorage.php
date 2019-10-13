<?php
namespace common\modules\imageStorage\behaviors;

use common\modules\imageStorage\components\ResponseData;
use common\modules\imageStorage\helpers\ImageStorageHelper;
use Yii;
use yii\base\Behavior;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;
use yii\web\UploadedFile;

class ImageStorage extends Behavior
{

    /**
     * POST|AJAX
     * @var string
     */
    public $uploadType = 'AJAX';

    /**
     * Имя поля => Название профиля
     * ['field1' => 'ownerName', 'field2' => 'ownerName']
     * @var array
     */
    public $uploadFields;

    /**
     * Имя поля => [Название таблицы => id записи]
     * ['field1' => ['table_name1' => 'id1'], 'field2' => ['table_name2' => 'id2']]
     * @var array
     */
    public $tableFields = [];

    /**
     * id объекта или массив вида ['fieldName1' => 'objectId1', 'fieldName2' => 'objectId2']
     * @var string|array
     */
    public $objects;

    /**
     * Объект компонента загрузки
     * @var \common\modules\imageStorage\components\ImageStorage
     */
    public $storage;

    /**
     * Результат ф-и обработки запроса
     * @var string
     */
    protected $uploadResponse;

    /**
     * Объект картинки ResponseData полученных из БД
     * [['fieldName' => ResponseData object]]
     * @var array
     */
    protected $dataResponses;

    public function init()
    {
        parent::init();
        if(!$this->storage)
            $this->storage = Yii::$app->ImageStorage;
        if($this->objects && is_string($this->objects)){
            $this->storage->setPathTemplateKey('{object_id}', $this->objects);
        }
    }

    public function attach($owner)
    {
        parent::attach($owner);
        foreach ($this->uploadFields as $field => $ownerName ){
            /**
             * Костыль для вложенных объектов
             */
            $validateField = explode('__', $field);
            if(is_array($validateField)){
                $validateField = end($validateField);
            }
            $fieldData = UploadedFile::getInstance($owner, $field);
            if($fieldData)
                $owner->{$field} = $fieldData;
        }
        $this->addValidators($owner);
    }

    public function checkValidators()
    {
        $ownersConfig = $this->storage->owners;
        $validators = $this->owner->validators;
        foreach($this->uploadFields as $field => $ownerName){
            $required = ArrayHelper::getValue($ownersConfig, $ownerName . '.rules.required');
            if($required && $this->owner->{$field} == null){
                $validator = Validator::createValidator('required', $this->owner, [$field]);
                $validators->append($validator);
            }
        }
        $this->owner->validate();
    }

    /**
     * Добавить параметры валидации для полей
     * из конфига НазваниеКомпонента[owners][текущий профиль][rules]
     * @param $owner
     */
    public function addValidators($owner)
    {
        $validators = $owner->validators;
        $ownersConfig = $this->storage->owners;

        foreach ($this->uploadFields as $field => $ownerName) {
            //safe
            $validator = Validator::createValidator('safe', $owner, [$field]);
            $validators->append($validator);
            //required

            $required = ArrayHelper::getValue($ownersConfig, $ownerName . '.rules.required');

            if ($required && !$this->getImage($field) && $this->uploadType!='POST' && Yii::$app->request->isPost) {
                $validator = Validator::createValidator('required', $this->owner, [$field]);
                $validators->append($validator);
            }

            if($ownerConfig = ArrayHelper::getValue($ownersConfig, $ownerName.'.rules')){
                if(isset($this->storage->config['extensions'])){
                    $config['extensions'] = $this->storage->config['extensions'];
                    $ownerConfig = array_merge($config, $ownerConfig);
                }

                if($required){
                    unset($ownerConfig['required']);
                }

                $validator = Validator::createValidator('image', $owner, [$field], $ownerConfig);
                $validators->append($validator);
            } else {
                $validator = Validator::createValidator('image', $owner, [$field], $ownerConfig);
                $validators->append($validator);
            }
        }
    }

    /**
     * Получить id исплолдьзуемый для текущего поля
     * @param $field
     * @return array|mixed|string
     */
    public function getObjectId($field)
    {
        if(is_array($this->objects) && isset($this->objects[$field])){
            return $this->objects[$field];
        } else {
            return $this->objects;
        }
    }

    /**
     * Загрузить изображения
     * @return array|bool
     */
    public function imageStorageUpload()
    {
        if(!$this->owner->validate()){
            return false;
        }

        /*if($this->uploadType == 'AJAX'){
            foreach ($this->uploadFields as $field){
                if(!$this->owner->validateAttribute($field)){
                    return false;
                }
            }
            unset($field);
        } else {
            if(!$this->owner->validate()){
                return false;
            }
        }*/

        $model = $this->owner;
        $responses = [];
        foreach ($this->uploadFields as $field => $owner){
            $file = UploadedFile::getInstance($model, $field);
            if($file) {
                $objectId = $this->getObjectId($field);
                $this->storage->setPathTemplateKey('{object_id}', $objectId);

                $response = $this->storage->upload($file, $owner);
            } else {
                $response = null;
            }
            $responses[$field] = ['owner' => $owner, 'response' => $response];
        }

        $this->uploadResponse = $responses;

        return $responses;
    }


    /**
     * Получить url только что загруженного изображения
     * @param $field название поля
     * @param bool $size размер навазание профиля
     * @return mixed|null
     */
    public function getUploadedSrc($field, $size = false)
    {
        if(!isset($this->uploadResponse[$field]))
            return null;

        if($size){
            return ArrayHelper::getValue($this->uploadResponse[$field], 'paths.thumbs.'.$size.'.web_path');
        }

        return ArrayHelper::getValue($this->uploadResponse[$field], 'paths.web_path');
    }

    /**
     * Получить результать ф-и обработки запроса
     * @return string
     */
    public function getUploadResponse()
    {
        return $this->uploadResponse;
    }


    /**
     * Проверить, существование файла в БД
     * @param $fieldName
     * @return bool|ResponseData
     */
    public function getImage($fieldName)
    {

        if(array_key_exists($fieldName, $this->tableFields)){
            $data = $this->tableFields[$fieldName];
            if(is_array($data)){
                $tableName = key($data);
                $recordId = $data[$tableName];
                $objectId = $this->getObjectId($fieldName);
                /**
                 * Костыль для вложенных обхектов
                 */
                $fieldName = explode('__', $fieldName);
                if(is_array($fieldName)){
                    $fieldName = end($fieldName);
                }
                $this->dataResponses[$fieldName] =  $this->storage->getResponse($tableName, $objectId, $recordId, $fieldName);
                return $this->dataResponses[$fieldName];
            }
        }

        return false;
    }

    /**
     * Загрузить название картинок в поля модели
     * @param $responses
     * @return array
     */
    public function loadDataImage($responses)
    {
        $fields = [];
        if ($responses) {
            foreach ($responses as $key => $field) {
                if($field['response']) {
                    $response = $field['response'];
                    $fields[] = $key;
                    $this->owner->$key = ImageStorageHelper::getFileInfo($response->fullPath, 'basename');
                }
            }
        }

        return $fields;
    }

    /**
     * Получить изображения из БД
     * @param array $fields Массив ResponseData изображений
     * @return array
     */
    public function getImages($fields)
    {
        $images = [];
        foreach($fields as $field){
            if($response = $this->getImage($field)){
                $images[$field] = $response->fullPath;
            }
        }
        return $images;
    }

    /**
     * Удалить изображения
     * @param array $paths [['fieldName' => 'fullPath']]
     */
    public function removeImages($paths)
    {
        foreach ($paths as $path){
            $this->storage->removeFileByFullPath($path);
        }
    }

    public function imageStorageLoad()
    {
        foreach ($this->uploadFields as $field => $owner){
            if($response = $this->getImage($field)) {
                $this->owner->{$field} = ImageStorageHelper::getFileInfo($response->fullPath, 'basename');
            }
        }
    }
}