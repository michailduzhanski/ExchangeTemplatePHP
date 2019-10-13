<?php
namespace common\modules\imageStorage\widgets;

use common\modules\imageStorage\components\ResponseData;
use common\modules\imageStorage\helpers\ImageStorageHelper;
use Yii;
use yii\helpers\BaseHtml;
use yii\helpers\Html;
use yii\web\View;

class FileInput extends \kartik\file\FileInput
{
        public $defaultOptions = [
            'accept' => 'image/*',
        ];

        public $defaultPluginOptions = [
            'showZoom' => false,
            'browseIcon' => '',
            'removeIcon' => '',
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
            ]
        ];

        public function init()
        {
            FileInputAsset::register(Yii::$app->view);
            $this->options = array_merge($this->defaultOptions, $this->options);
            $this->pluginOptions = array_merge($this->pluginOptions, $this->defaultOptions);

            if($image = $this->model->getImage($this->attribute)) {
                if ($response = $image->ajaxResponse(ResponseData::TYPE_FILEINPUT_POST)) {
                    $response = json_decode($response, true);
                    $this->pluginOptions = array_merge($this->pluginOptions, $response);
                }
            }
            $fieldNameID = BaseHtml::getInputId($this->model, $this->attribute);
$js = <<<JS


$('#$fieldNameID').closest('.form-group').find('input[type=hidden]').remove();

$('#$fieldNameID').on('filecleared', function(event) {
    var name = $(this).attr('name');    
    $(this).closest('.form-group').prepend('<input value="" type="hidden" name="'+name+'" />')
});
$('#$fieldNameID').on('filedeleted', function(event) {    
    var name = $(this).attr('name');        
    $(this).closest('.form-group').prepend('<input value="" type="hidden" name="'+name+'" />')
});
JS;

$this->view->registerJs($js, View::POS_END);

/*            if(isset($this->model->storage)){
                $storage = $this->model->storage;
            }*/
            parent::init();
        }
}