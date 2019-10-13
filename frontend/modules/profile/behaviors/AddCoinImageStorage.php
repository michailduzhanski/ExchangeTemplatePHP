<?php
namespace frontend\modules\profile\behaviors;

use common\modules\imageStorage\behaviors\ImageStorage;
use common\modules\imageStorage\components\ResponseData;
use common\modules\imageStorage\helpers\ImageStorageHelper;
use Yii;
use yii\web\UploadedFile;

class AddCoinImageStorage extends ImageStorage
{

    public function imageStorageLoad()
    {
        foreach ($this->uploadFields as $field => $owner){
            if($response = $this->getImage($field)) {
                $this->owner->{$field} = ImageStorageHelper::getFileInfo($response->fullPath, 'basename');
            }
        }
    }

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
                $fullFieldName = $fieldName;
                $fieldName = explode('__', $fieldName);
                if(is_array($fieldName)){
                    $fieldName = end($fieldName);
                }


                if (!isset($this->owner->$fullFieldName) || !$this->owner->$fullFieldName){
                    if($this->owner->isNewRecord()){
                        return [];
                    } else {
                        return [];
                        /*echo 'load';
                        exit;*/
                    }
                } else {
                    $image = $this->owner->$fullFieldName;
                }


                Yii::$app->ImageStorage->setPathTemplateKey('{object_id}', $objectId);

                $response = new ResponseData();
                $response->objectId = $objectId;
                $response->recordId = $recordId;
                $response->attribute = $fieldName;
                $response->basename = $fieldName;
                $response->fullPath = ImageStorageHelper::getFullPathFromObjectRecord(Yii::$app->ImageStorage, $objectId, $image);
                $response->webPath = ImageStorageHelper::getWebPathFromObjectRecord(Yii::$app->ImageStorage, $objectId, $image);
                $this->dataResponses[$fieldName] = $response;
                return $this->dataResponses[$fieldName];
            }
        }

        return false;
    }

    public function getOldImages($fields)
    {
        $images = [];
        foreach ($fields as $field){
            if(isset($this->owner->loadedData[$field])){
                $image = $this->owner->loadedData[$field];
                $fieldParts = explode('__', $field);
                if(count($fieldParts) > 1) {
                    unset($fieldParts[count($fieldParts) - 1]);
                    $objectName = implode('__', $fieldParts);
                    $objectId = $this->owner->loadedData[$objectName .'__id'];
                } else {
                    $objectName = 'main';
                    $objectId = $this->owner->loadedData['id'];
                }

                $fullPath = ImageStorageHelper::getFullPathFromObjectRecord(Yii::$app->ImageStorage, $objectId, $image);
                $images[$field] = $fullPath;
            }
        }

        return $images;
    }
}