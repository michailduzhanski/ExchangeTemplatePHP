<?php


namespace common\modules\imageStorage\components;

use common\modules\imageStorage\helpers\ImageStorageHelper;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class ResponseData
{
    const TYPE_DEFAULT = 'default';

    const TYPE_FILEINPUT = 'file_input_widget';

    const TYPE_FILEINPUT_POST = 'file_input_widget_post';

    public $objectId;

    public $recordId;

    public $attribute;

    public $basename;

    public $fullPath;

    public $webPath;

    public $thumbs;

    /**
     * @var ImageStorage
     */
    public $storage;

    public function __construct()
    {
        $this->storage = Yii::$app->ImageStorage;
    }

    public function ajaxResponse($type = false)
    {
        if(!$type)
            $type = self::TYPE_DEFAULT;
        switch ($type){
            case self::TYPE_DEFAULT : {
                return Json::encode([
                   'url' => $this->webPath
                ]);
                break;
            }
            case self::TYPE_FILEINPUT : {
                return $this->ajaxFileInputResponse();
                break;
            }
            case self::TYPE_FILEINPUT_POST : {
                return $this->ajaxFileInputPostResponse();
                break;
            }
        }
    }

    public function ajaxFileInputPostResponse()
    {
        $json = [];
        if(!$this->fullPath || !$this->objectId || !$this->recordId) {
            $json['error'] = Yii::t('app', 'Something wrong! Please, try again');
        } else {
            $initialPreview[] = Html::img($this->webPath, ['class' => 'file-drag-handle drag-handle-init']);
            $initialPreviewConfig[] = [
                'caption' => "",
                'url' => Url::to(['/image-storage/default/delete']),
                'key' => $this->recordId,
                'extra' => [
                    'data' => ImageStorageHelper::encodeData($this->storage, [
                        'object_id' => $this->objectId,
                        'record_id' => $this->recordId,
                        'attribute' => $this->attribute,
                        'full_path' => $this->fullPath,
                        'name' => $this->basename,
                        'type' => 'POST'
                    ])
                ]
            ];
            $json['initialPreview'] = $initialPreview;
            $json['initialPreviewConfig'] = $initialPreviewConfig;
        }

        return Json::encode($json);
    }

    public function ajaxFileInputResponse()
    {
        $json = [];
        if(!$this->fullPath || !$this->objectId || !$this->recordId) {
            $json['error'] = Yii::t('app', 'Something wrong! Please, try again');
        } else {
            $initialPreview[] = Html::img($this->webPath, ['class' => 'file-drag-handle drag-handle-init']);
            $initialPreviewConfig[] = [
                'caption' => "",
                'url' => Url::to(['/image-storage/default/delete']),
                'key' => $this->recordId,
                'extra' => [
                    'data' => ImageStorageHelper::encodeData($this->storage, [
                        'object_id' => $this->objectId,
                        'record_id' => $this->recordId,
                        'attribute' => $this->attribute,
                        'full_path' => $this->fullPath,
                        'name' => $this->basename
                    ])
                ]
            ];
            $json['initialPreview'] = $initialPreview;
            $json['initialPreviewConfig'] = $initialPreviewConfig;
        }

        return Json::encode($json);
    }

}