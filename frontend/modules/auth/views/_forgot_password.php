<?php
/**
 * Форма Забыли пароль
 * @var $this \yii\web\View
 * @var $status string
 * @var $resetToken string
 */

use \yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use \yii\helpers\Url;
?>

<?php Pjax::begin(['id' => 'forgot-password-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'forgot-form',
    'action' => ['/auth/default/forgot-password'],
    'options' => ['data-pjax' => true]
]) ?>
<div class="form-head">
    <h3 class="head-title">Hysiope</h3>
    <p><?=Yii::t('forgot_password_page', 'Reset password')?></p>
</div>

<?php if($status == 'success'): ?>
    <div class="form-body">
        <div class="alert alert-info">
            <?=Yii::t('forgot_password_page', 'An email has been sent to your registered email address with password reset instructions.')?>
        </div>
    </div>
<?php endif; ?>

<?php if($status == 'error'): ?>
    <div class="form-body">
        <div class="alert alert-info">
            <?=Yii::t('forgot_password_page', 'An email has been sent to your registered email address with password reset instructions.')?>
        </div>
    </div>
<?php endif; ?>

<?php if(!$status): ?>
    <div class="form-body">
        <?php if($message = Yii::$app->session->getFlash('forgot_errors')):  ?>
            <div class="error-area alert alert-danger" role="alert"><?=$message?></div>
        <?php endif ?>

        <?=$form->field($model, 'login', [
            'template' => '
                                {beginLabel}
                                    {labelTitle}
                                {endLabel}
                                {input}
                            '
        ])->textInput([
            'placeholder' => Yii::t('forgot_password_page', 'Your login')
        ]) ?>


        <?= $form->field($model, 'reCaptcha')
            ->widget(\himiklab\yii2\recaptcha\ReCaptcha::class)->label(false) ?>

        <?= $form->field($model, 'infoData')->hiddenInput()->label(false) ?>

        <div class="form-group">
            <button type="submit" class="btn btn-orange btn-lg btn-block">
                <?=Yii::t('forgot_password_page', 'Reset password')?>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php ActiveForm::end() ?>
<?php Pjax::end(); ?>


<?php
$js = <<<JS
$('#forgot-form').on('beforeValidateAttribute', function(event, attribute, message){
    $(this).find('.form-body').find('.error-area').remove();
});

$('#forgot-form').on('afterValidateAttribute', function(event, attribute, message){    
    if(message.length > 0){
        var alert = '<div class="error-area alert alert-danger" role="alert">' + message + '</div>';
        $(this).find('.form-body').prepend(alert);
    }    
});
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
