
<?php
use yii\helpers\Url;
?>
<div class="content-wrapper btn-mr-t">
    <div class="card">

                <?php if($status == 'success'): ?>
                <div class="card-body">
                    <h3 class="card-title">Add coin</h3>
                    <p>Your coin has been successfully added!</p>
                </div>
                <?php else: ?>
                    <div class="card-body">
                        <h3 class="card-title">Add coin</h3>
                        <p>Something error! Please
                            <a href="<?=Url::to(['/profile/default/add-coin', 'id' => $model->recordId])?>">
                                try again!
                            </a>
                        </p>
                    </div>
                <?php endif; ?>

    </div>
</div>
<?php
$js = <<<JS
$(function(){    
    const container = document.querySelector('.container-scroller');        
    container.scrollTop = 0;            
})
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>