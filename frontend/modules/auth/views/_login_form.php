<?php
/**
 * Форма авторизации
 *
 * @var $this \yii\web\View
 * @var $model \frontend\modules\auth\models\LoginForm
 */

use \yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\Url;
?>
<?php Pjax::begin(['id' => 'form-login-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'auth-form',
    'action' => ['/auth/default/login'],
    'options' => ['data-pjax' => true]
]) ?>
    <div class="form-head">
        <h3 class="head-title">Hysiope</h3>
        <p>
            <?=Yii::t('login_page', 'Authorization to the exchange')?>
        </p>
    </div>
    <div class="form-body">
        <?php if($message = Yii::$app->session->getFlash('errors')):  ?>
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
            'placeholder' => Yii::t('login_page', 'Your login')
        ]) ?>


        <?=$form->field($model, 'password', [
            'template' => '
                                    {beginLabel}
                                        {labelTitle}
                                        <a href="'.Url::to(["/auth/default/forgot-password"]).'" class="pull-right forgot-link">
                                            '.Yii::t('login_page', 'Forgot password?').'
                                        </a>
                                    {endLabel}
                                    {input}                                    
                                '
        ])->passwordInput([
            'placeholder' => Yii::t('login_page', 'Your password')
        ]) ?>

        <?= $form->field($model, 'reCaptcha')
            ->widget(\himiklab\yii2\recaptcha\ReCaptcha::class)->label(false) ?>

        <?= $form->field($model, 'infoData')->hiddenInput()->label(false) ?>

        <div class="form-group">
            <button type="submit" class="btn btn-orange btn-lg btn-block">
                <?=Yii::t('login_page', 'Login')?>
            </button>
        </div>
        <p class="reg-link text-center">
            <?=Yii::t('login_page', 'Not on Haysiope yet?')?>
            <a href="<?=Url::to(['/auth/default/registration'])?>">
                <?=Yii::t('login_page', 'Register')?>
            </a>
        </p>
    </div>
<?php ActiveForm::end() ?>
<?php Pjax::end(); ?>
