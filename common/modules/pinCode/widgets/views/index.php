<?php
/**
 * @var $this \yii\web\View
 */

$successMessage = Yii::t('frontend', 'Send e-mail code');
$sentMessage = Yii::t('frontend', 'Sent') . '(<span class="time-left">'. $timeLeft .'</span>)';
?>

<button data-message="<?=$message?>" type="button" id="<?=$id?>" class="btn btn-warning btn-block btn-lg" <?php  echo ($timeLeft > 0) ? 'disabled="disabled" ' :  ""?> >
    <?php if($timeLeft <= 0): ?>
    <?=$successMessage ?>
    <?php else: ?>
    <?=$sentMessage ?>
    <?php endif; ?>    
</button>

<?php
$js = <<<JS
var timerPin$widgetId = null;

function sendCodeTimer(el, time_left, message_end){    
    var time = time_left;
    clearTimeout(timerPin$widgetId);    
    timerPin$widgetId = setInterval(function(){    
        el.find('.time-left').text(time);
        time--;
        if(time == 0 || time < 0){            
            clearTimeout(timerPin$widgetId);
            $('#$id').removeAttr('disabled');
            $('#$id').html(message_end);
        }
    }, 1000);
    
    return timerPin$widgetId;
}

$('#$id').on('click', function(){
    var message = $(this).data('message');
    if(!message){
        message = '';
    }    
    var timer = null;
    var el = $(this);
    
    $.ajax({
        method: 'POST',
        url: '$url',
        dataType: 'json',
        data: {message: message},        
        success: function(data){
            if(data.status == 'success'){                
                $('#$id').attr('disabled', 'disabled');
                $('#$id').html(data.message);
                timerPin$widgetId  = sendCodeTimer(el, data.time_left, data.message_end);
            }
            if(data.status == 'error' && data.time_left != undefined){
                $('#$id').attr('disabled', 'disabled');
                $('#$id').html(data.message);
                timerPin$widgetId  = sendCodeTimer(el, data.time_left, data.message_end);                                    
            }                
        }
    });

    return false;
});
JS;

$jsTimeLeft = <<<JS

sendCodeTimer($('#$id'), $timeLeft, '$successMessage')
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
$this->registerJs($jsTimeLeft, \yii\web\View::POS_READY);
?>