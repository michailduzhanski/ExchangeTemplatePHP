<?php

namespace common\modules\imageStorage\widgets;

use common\modules\drole\models\registry\RegistryObjects;
use common\modules\imageStorage\components\ImageStorage;
use common\modules\imageStorage\components\ResponseData;
use common\modules\imageStorage\helpers\ImageStorageHelper;
use Yii;
use yii\base\Exception;

use yii\web\View;

class FileInputAjax extends \kartik\file\FileInput
{

    public $objectId;

    public $objectName;

    public $recordId;

    public $table;
    
    public $owner;

    public $dynamicModel = false;

    /**
     * @var ImageStorage
     */
    public $storage;

    public $options = [
        'accept' => 'image/*',
    ];

    public $pluginOptions = [
        'uploadUrl' => ['/image-storage/default/ajax-upload'],
        'showZoom' => false,
        'browseIcon' => '',
        'removeIcon' => '<i class="fa fa-trash" aria-hidden="true"></i>',
        'dragIcon' => '<i class="fa fa-arrows-alt" aria-hidden="true"></i>',
        'cancelIcon' => '',
        'uploadIcon' => '',
        'indicatorNew' => '',
        'zoomIcon' => '',
        'msgValidationErrorIcon' => '',
        'indicatorError' => '',
        'showUpload' => false,
        'showRemove' => false,
        'showCancel' => false,
        //'allowedFileTypes' => ['image'],
        'fileActionSettings' => [
            'msgValidationErrorIcon' => '',
            'indicatorError' => '',
            'showUpload' => false,
            'showZoom' => false,
            'indicatorNew' => '',
            'removeIcon' => '<i class="fa fa-trash" aria-hidden="true"></i>',
            'dragIcon' => '<i class="fa fa-arrows-alt" aria-hidden="true"></i>',
        ],
/*        'uploadExtraData' => [
            'test' => 'test'
        ]*/
    ];

    public function init()
    {
        if(Yii::$app->user->isGuest)
            return false;

        if(!$this->objectName && !$this->objectId){
            throw new Exception('Object or object id not set');
        }
        if(!$this->recordId)
            throw new Exception('Record id not set');

        if(!$this->table){
            $objectName = RegistryObjects::getObjectNameByID($this->objectId);
            $this->table = $objectName->name . '_data_use';
        }

        FileInputAsset::register(Yii::$app->view);

        if(!$this->storage){
            if(isset($this->model->storage)){
                $this->storage = $this->model->storage;
            } else {
                $this->storage = Yii::$app->ImageStorage;
            }
        }
        
        $this->options = array_merge($this->defaultOptions, $this->options);
        $this->pluginOptions = array_merge($this->defaultOptions, $this->pluginOptions);

        if(!$this->owner) {
            if (isset($this->model->uploadFields)) {
                $uploadFields = $this->model->uploadFields;
                if (array_key_exists($this->attribute, $uploadFields)) {
                    $this->owner = $uploadFields[$this->attribute];
                }
            }
        }

        $this->pluginOptions['uploadExtraData'] = [
            'data' => ImageStorageHelper::encodeData($this->storage, [
                'owner' => $this->owner,
                'attribute' => $this->attribute,
                'class' => get_class($this->model),
                'objectId' => $this->objectId,
                'recordId' => $this->recordId,
                'table' => $this->table,
                'dynamicModel' => $this->dynamicModel
            ])
        ];

        $response = $this->storage->getResponse($this->table, $this->objectId, $this->recordId, $this->attribute);
        if ($response) {
            $json = $response->ajaxResponse(ResponseData::TYPE_FILEINPUT);
            $data = json_decode($json, true);
            $this->pluginOptions = array_merge($this->pluginOptions, $data);
        }

        parent::init();

        $inputId = \yii\helpers\Html::getInputId($this->model, $this->attribute);

$js = <<<JS
    $('#$inputId').on("filebatchselected", function(event, files) {
        $('#$inputId').fileinput("upload");
    });
    $('#$inputId').on('fileuploaded', function(event, data, previewId, index) {
        $('#$inputId').closest('.file-input').find('.kv-upload-progress').hide();
    });
JS;
        $this->registerWidgetJs($js);
    }
}