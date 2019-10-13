<?php
use \yii\bootstrap\ActiveForm;
?>

<?php $form = ActiveForm::begin([
    //'id' => 'auth-form',
    //'action' => ['/profile/default/show-password-form'],
    /*'options' => [
        'class' => 'form-pretty-label'
    ]*/
]) ?>

<?php foreach ($model->getEditableFields() as $key => $value): ?>
    <?php $field = $form->field($model, $key, [])?>
    <?=$model->getInputWidget($field, $key) ?>
<?php endforeach; ?>

<?php ActiveForm::end() ?>
