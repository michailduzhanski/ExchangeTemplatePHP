<?php
use \yii\widgets\ActiveForm;
use \common\modules\imageStorage\widgets\FileInputAjax;
?>

<?php $form = \yii\widgets\ActiveForm::begin([
    'enableClientValidation' => true,
    'options' => ['enctype' => 'multipart/form-data']
]) ?>

<?= $form->field($model, $field)->widget(FileInputAjax::class, [
    'table' => $table,
    'owner' => $owner,
    'objectId' => $objectId,
    'recordId' => $recordId,
    'dynamicModel' => true
]); ?>

<?php ActiveForm::end() ?>
