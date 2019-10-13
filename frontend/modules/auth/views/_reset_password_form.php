<?php
/**
 * Форма Новый пароль
 */

use yii\bootstrap\ActiveForm;
use \common\widgets\PrettyLabelField;
use yii\widgets\Pjax;
use \yii\helpers\Url;

/**
 * @var $this \yii\web\View;
 * @var $model \frontend\modules\auth\models\ResetPasswordForm
 * @var $status string
 */
?>

<?php Pjax::begin(['id' => 'form-registration-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'reg-form',
    'action' => ['/auth/default/reset-password'],
    'options' => ['data-pjax' => true]
]) ?>
<div class="form-head">
    <h3 class="head-title">Hysiope</h3>
    <p><?=Yii::t('frontend', 'Reset password')?></p>
</div>
<?php if($status == 'success'): ?>
<div class="form-body">
    <?php if($message = Yii::$app->session->getFlash('message')):  ?>
        <div class="error-area alert alert-danger" role="alert"><?=$message?></div>
    <?php endif ?>
    <?=$form->field($model, 'password', [
        'class' => PrettyLabelField::class
    ])->passwordInput() ?>

    <?=$form->field($model, 'confirm_password', [
        'class' => PrettyLabelField::class
    ])->passwordInput() ?>

    <?=$form->field($model, 'token')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <button type="submit" class="btn btn-orange btn-lg btn-block">
            <?=Yii::t('frontend', 'Reset password')?>
        </button>
    </div>
</div>
<?php endif; ?>

<?php if($status == 'error'): ?>
<div class="form-body">
    <div class="alert alert-danger">
    <?=Yii::t('frontend', 'Invalid reset password link. Please {link}try again{endlink}', [
        'link' => '<a href="' . \yii\helpers\Url::to(['/auth/default/forgot-password']) .'">',
        'endlink' => '</a>'
    ]) ?>
    </div>
</div>
<?php endif; ?>

<?php if($status == 'success-update'): ?>
<div class="form-body">
    <div class="alert alert-success">
        <?=Yii::t('frontend', 'Your password has been reset.')?>
    </div>
    <div class="form-group">
        <a href="<?=Url::to(['/auth/default/login'])?>" class="btn btn-orange btn-lg btn-block">
            <?=Yii::t('frontend', 'Sign in')?>
        </a>
    </div>
</div>
<?php endif; ?>

<?php ActiveForm::end() ?>
<?php Pjax::end(); ?>
