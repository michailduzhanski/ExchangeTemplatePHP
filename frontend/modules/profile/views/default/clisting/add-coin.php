<?php
use frontend\modules\droleYii\widgets\FormFields;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;


?>

<?php Pjax::begin([
    'id' => 'form-addcoin-pjax',
    'enablePushState' => false,
]); ?>
<div class="content-wrapper btn-mr-t">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Add coin</h3>
        </div>
    </div>
    <?php
        if($id = Yii::$app->request->get('id'))
            $action = ['/profile/default/add-coin', 'id' => $id];
        else
            $action = ['/profile/default/add-coin'];
    ?>
    <?= FormFields::widget([
        'model' => $model,
        'viewTemplate' => 'add-coin',
        'action' => $action
    ]) ?>
<!--    <div class="row">
        <div class="col-md-4 col-sm-12 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="add-coin-logo-box">
                        <h3 class="card-title">Upload logo</h3>
                        <div class="relative">
                            <img src="/images/add-coin.png" class="img-fluid">
                            <input type="file" class="add-coin-logofield">
                        </div>
                    </div>
                    <label class="d-block">Algorithm</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Network</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Block Time</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Block Reward</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Difficulty Retarget</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Total Coins</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Annual POS Rate</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Minimum Stake Age</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Maximum Stake Age</label>
                    <input type="text" class="form-control">
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 col-12">
            <div class="card">
                <div class="card-body">
                    <label class="d-block">Windows Wallet</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Linux Wallet</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Mac Wallet</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Mobile Wallet</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Web/Paper Wallet</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Premine</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Website</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Block Explorer</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Hysiope Forum</label>
                    <input type="text" class="form-control">
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 col-12">
            <div class="card">
                <div class="card-body">
                    <label class="d-block">Trade Fee</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Pool Fee</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Deposite Conf</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Withdrawal Fee</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Min. Withdrawal</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Max. Withdrawal</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Tipping Expires</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Min. Tip Amount</label>
                    <input type="text" class="form-control">
                    <label class="d-block">Reward Expires</label>
                    <input type="text" class="form-control">
                </div>
            </div>
        </div>
    </div>-->

    <div class="card card-body finished-block" >
        <h6><strong>After clicking on the "Finished" button, your request will be directed to moderation. Check the data carefully. You can not change the data after sending! After checking the information, the moderator will contact you to make payment and placing your coin on the Hysiope exchange.</strong></h6>
        <?php if(!$model->isNewRecord() && $model->isCanPublish()): ?>
            <button id="add-coin-finish" type="submit" class="btn btn-danger btn-lg btn-finish">Finished</button>
        <?php else: ?>
            <button class="btn btn-default btn-lg btn-finish">Finished</button>
        <?php endif; ?>
    </div>
</div>

<?php
$addCoinPublish = \yii\helpers\Url::to(['/profile/default/add-coin-publish']);
$id = Yii::$app->request->get('id');
$js = <<<JS
    $('#add-coin-finish').on('click', function(event){                
        event.preventDefault();                        
        $.pjax({
            type: 'POST',                        
            url: '$addCoinPublish',
            enablePushState: false,
            container: '#form-addcoin-pjax',
            data: {id: '$id', type: 'publish'},     
            push: false,
            replace: false,               
        }) 
    });        
JS;
$this->registerJs($js);
?>
<?php Pjax::end(); ?>