<?php
use \yii\helpers\Url;
use \yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin([
    'id' => 'day-night-form',
    'action' => $url,
]) ?>

<button class="nav-link" type="submit" id="day-night-btn">
    <input type="hidden" name="mode" value="<?=($currentMode) ? 0 : 1 ?>">
    <img src="/images/icons/dn-mode.png" alt="">
    <span class="menu-title">
        <?=$modeText?>
    </span>
</button>
<?php ActiveForm::end() ?>
<?php
$js = <<<JS
$(document).on('submit', '#day-night-form', function(){
    return false;
});

$(document).on('click', '#day-night-btn', function(){  
    var form = $(this).closest('form');
    var mode = form.find('input[name="mode"]').val();          
    $.ajax({
        url: "$url",
        method: "POST",
        data: {mode: mode}
    });
});
JS;
$this->registerJs($js);
?>
