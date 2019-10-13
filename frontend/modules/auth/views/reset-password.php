<?php
/**
 * Страница Новый пароль
 * @var $this \yii\web\View
 * @var $status string
 */
?>
<section id="secondary-work-space">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 col-md-offset-4 col-sm-offset-3">
                <?=$this->render('_reset_password_form', [
                    'model' => $model,
                    'status' => $status
                ])?>
            </div>
        </div>
    </div>
</section>