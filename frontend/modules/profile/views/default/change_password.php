<?php
/**
 * Форма Поменять пароль
 */
use \yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use \common\widgets\PrettyLabelField;
?>
<?php Pjax::begin(['id' => 'form-password-change', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'auth-form',
    'action' => ['/profile/default/show-password-form'],
    'options' => [
        'data-pjax' => true,
        'class' => 'form-pretty-label'
    ]
]) ?>

<?=$form->field($model, 'oldPassword', [
    'class' => PrettyLabelField::class
])->passwordInput() ?>


<?=$form->field($model, 'newPassword', [
    'class' => PrettyLabelField::class
])->passwordInput() ?>

<?=$form->field($model, 'confirmPassword', [
    'class' => PrettyLabelField::class
])->passwordInput() ?>


<?=\yii\helpers\Html::submitButton('Change password', ['class' => 'btn btn-warning btn-block']) ?>
<?php ActiveForm::end() ?>
<?php Pjax::end(); ?>
