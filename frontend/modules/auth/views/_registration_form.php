<?php
use \yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use \common\widgets\PrettyLabelField;
use yii\widgets\Pjax;

/**
 * @var $this \yii\web\View;
 * @var $model \frontend\modules\auth\models\RegisterForm
 */
?>

<?php Pjax::begin(['id' => 'form-registration-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'reg-form',
    'action' => ['/auth/default/registration'],
    'options' => ['data-pjax' => true]
]) ?>
<div class="form-head">
    <h3 class="head-title">Hysiope</h3>
    <p><?=Yii::t('registration_page', 'Registration form')?></p>
</div>
<div class="form-body">
    <?php if($message = Yii::$app->session->getFlash('message')):  ?>
    <div class="error-area alert alert-danger" role="alert"><?=$message?></div>
    <?php endif ?>
    <?=$form->field($model, 'login', [
        'class' => PrettyLabelField::class
    ])->textInput() ?>

    <?=$form->field($model, 'email', [
        'class' => PrettyLabelField::class
    ])->textInput() ?>

    <?=$form->field($model, 'password', [
        'class' => PrettyLabelField::class
    ])->passwordInput() ?>

    <?=$form->field($model, 'confirm_password', [
        'class' => PrettyLabelField::class
    ])->passwordInput() ?>

    <?=$form->field($model, 'sponsor', [
        'class' => PrettyLabelField::class
    ])->textInput() ?>

    <?=$form->field($model, 'agree', [
        'template' => '
                                <div class="checkbox agree-row">
                                {beginLabel}{input}{labelTitle}{endLabel}{error}
                                </div>
                            '
    ])->checkbox([], false)->label(
        Yii::t('registration_page', 'I agree to {link} the Terms & conditions {endlink}', [
            'link' => '<a href="'.Url::to(['/']).'">',
            'endlink' => '</a>'
        ])
    ) ?>

    <?= $form->field($model, 'reCaptcha')
        ->widget(\himiklab\yii2\recaptcha\ReCaptcha::class)->label(false) ?>

    <?= $form->field($model, 'infoData')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <button type="submit" class="btn btn-orange btn-lg btn-block">
            <?=Yii::t('frontend', 'Registration')?>
        </button></div>
    <p class="reg-link text-center">
        <?=Yii::t('frontend', 'Have an account?')?>
        <a href="<?=Url::to(['/auth/default/login'])?>">
            <?=Yii::t('frontend', 'Sign in!')?>
        </a>
    </p>
</div>
<?php ActiveForm::end() ?>
<?php Pjax::end(); ?>
