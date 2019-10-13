<?php
/**
 * Форма регистрации
 */

/**
 * @var $this \yii\web\View
 * @var $model \frontend\modules\auth\models\RegisterForm
 */
?>

<section id="secondary-work-space">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 col-md-offset-4 col-sm-offset-3">
                <?=$this->render('_registration_form', [
                    'model' => $model
                ])?>
            </div>
        </div>
    </div>
</section>
