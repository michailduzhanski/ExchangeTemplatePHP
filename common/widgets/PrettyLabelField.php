<?php


namespace common\widgets;

use Yii;
use yii\bootstrap\ActiveField;
use yii\helpers\BaseHtml;

class PrettyLabelField extends ActiveField
{

    public function init()
    {
        $this->template =
            '<label for="'.BaseHtml::getInputId($this->model, $this->attribute).'"></label>
            {beginLabel}
            {labelTitle}
                {error}                                
            {endLabel}
            {input}{hint}';

        $this->errorOptions = [
            'tag' => 'span',
            'class' => 'label label-danger pull-right validate-status'
        ];

        $this->registerJs();
    }

    public function registerJs()
    {
        $id = $this->form->id;
        $successText = Yii::t('app', 'Success');
$js = <<<JS

$('#$id').on('beforeValidateAttribute', function(event, attribute, message){
    var id = attribute.id;
    var formGroup = $('#'+id).closest('.form-group');
    var vs = formGroup.find('.validate-status');
    vs.removeClass('label-success');
    vs.addClass('label-danger');
    vs.text('');
});

$('#$id').on('afterValidateAttribute', function(event, attribute, message){
    var id = attribute.id;
    var formGroup = $('#'+id).closest('.form-group');
    var hasError = formGroup.hasClass('has-error');
    var vs = formGroup.find('.validate-status');    
    if(!hasError){        
        vs.removeClass('label-danger');
        vs.addClass('label-success');
        vs.text('$successText');
    }
})
JS;

    Yii::$app->view->registerJs($js, \yii\web\View::POS_READY);
    }

}