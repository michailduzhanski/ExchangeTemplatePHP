<?php

namespace common\modules\imageStorage\models;

use Yii;
use yii\base\DynamicModel;
use common\modules\imageStorage\behaviors\ImageStorage;
use yii\web\BadRequestHttpException;

class ImageModel extends DynamicModel
{
    public $objectId;

    public $recordId;

    public $field;

    public $owner;

    public $table;


    public function addImageStorageBehavior($objecId, $field)
    {
        $module = Yii::$app->getModule('image-storage');
        if($data = $module->getStorageDataParams($objecId, $field)){
            $this->objectId = $data['object'];
            $this->field = $data['field'];
            $this->owner = $data['owners'];
            $this->table = $data['table'];

             $this->attachBehavior('imageStorage', [
                'class' => ImageStorage::class,
                 'uploadFields' => [
                     $this->field => $this->owner
                 ],
                 'tableFields' => [
                    $this->field => [$this->table => $this->recordId]
                 ],
                 'objects' => [
                     $this->field => $this->objectId
                 ]
            ]);

        } else {
            throw new BadRequestHttpException("$objecId and $field not found in config");
        }


    }
}