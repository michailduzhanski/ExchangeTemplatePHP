<?php
/**
 * Страница входа
 */

/**
 * @var $this \yii\web\View;
 * @var $model \frontend\modules\auth\models\LoginForm
 */
?>

<section id="secondary-work-space">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 col-md-offset-4 col-sm-offset-3">
                <?=$this->render('_login_form', [
                    'model' => $model
                ])?>
            </div>
        </div>
    </div>
</section>


<?php
$js = <<<JS
$('#auth-form').on('beforeValidateAttribute', function(event, attribute, message){
    $(this).find('.form-body').find('.error-area').remove();
});

$('#auth-form').on('afterValidateAttribute', function(event, attribute, message){    
    if(message.length > 0){
        var alert = '<div class="error-area alert alert-danger" role="alert">' + message + '</div>';
        $(this).find('.form-body').prepend(alert);
    }    
});
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>