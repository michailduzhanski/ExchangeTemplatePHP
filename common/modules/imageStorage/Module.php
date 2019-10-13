<?php
namespace common\modules\imageStorage;

//объект/object_id/img/image_id.png

use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\imageStorage\components\ImageStorage;
use frontend\helpers\DroleHelper;
use Yii;

class Module extends  \yii\base\Module
{

    /*
    * Абсолютный путь к дирректории картинок
    */
    public $absolutePath;

    /*
    * Абсолютный путь к дирректории кеш-картинок
    */
    public $cacheAbsolutePath;

    /**
     * @var ImageStorage
     */
   public $storageComponent;

   public $storageData;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'common\modules\imageStorage\controllers';

    public function init()
    {
        $this->storageComponent = Yii::$app->{$this->storageComponent};
        $this->getAbsolutePath();
        $this->getCacheAbsolutePath();
        $this->getWebPath();
    }

    /**
     * Корневая Веб-директории картинок
     * @return string
     */
    public function getWebPath()
    {
        return $this->storageComponent->webPath;
    }

    public function getCacheWebPath()
    {
        return $this->storageComponent->cacheWebPath;
    }

    /**
     * Абсолютный путь к дирректории картинок
     * @return string
     */
    public function getAbsolutePath()
    {
        if(!$this->absolutePath)
            $this->absolutePath = Yii::getAlias('@root'.'/'.$this->storageComponent->uploadDir);

        return $this->absolutePath;
    }

    public function getCacheAbsolutePath()
    {
        if(!$this->cacheAbsolutePath)
            $this->cacheAbsolutePath = Yii::getAlias('@root'.'/'.$this->storageComponent->cacheDir);

        return $this->cacheAbsolutePath;
    }

    public function getStorageDataParams($objecId, $field)
    {
        $droleID = Yii::$app->user->identity->getUserDroleID();
        $struct = StructureOperationHandler::getFastStructureWithCheck($objecId, $droleID);

        $fieldInfo = DroleHelper::getFieldByName($struct, $field);
        $objectName = RegistryObjects::getObjectNameByID($objecId);
        $table = $objectName->name . '_data_use';

        $objectName = RegistryObjects::getObjectNameByID($objecId);

        if($fieldInfo && $objectName){
            foreach ($this->storageData as $data){
                if($data['object'] == $objecId && $data['field'] == $fieldInfo['id']) {
                    $data['table'] = $table;
                    $data['field'] = $fieldInfo['name'];
                    return $data;
                }
            }
        }

        return [
            'object' => $objecId,
            'field' => $field,
            'owners' => 'default',
            'table' => $table
        ];
    }

}