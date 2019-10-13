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

<?php $form = ActiveForm::begin([
    'id' => 'auth-form',
    'action' => ['/auth/default/login'],
    'options' => ['data-pjax' => true]
]) ?>
    <div class="form-head">
        <h3 class="head-title">Hysiope</h3>
        <p><?=Yii::t('login_page', 'Authorization to the exchange')?></p>
    </div>
    <div class="form-body">

        <div class="error-area alert alert-success" role="alert">
            <?=Yii::t('login_page', 'An email has been sent to your registered email address with a pin code.')?>
        </div>
        <?=$form->field($model, 'pincode', [
            'template' => '
                                {beginLabel}
                                    {labelTitle}
                                {endLabel}
                                {input}
                                {error}
                            '
        ])->textInput([
            'placeholder' => Yii::t('login_page', 'Pin Code')
        ]) ?>
        <div class="form-group">
            <button type="submit" class="btn btn-orange btn-lg btn-block">
                <?=Yii::t('login_page', 'Login')?>
            </button>
        </div>
    </div>

<?php ActiveForm::end() ?>