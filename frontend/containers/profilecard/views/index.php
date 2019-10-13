<?php
use \yii\helpers\Url;
use \common\modules\drole\models\webtools\JSONRegistryFactory;
use yii\widgets\Pjax;
?>

<div class="card" id="main-user-data">
    <div class="card-header white">
        <h5 class="card-title float-left">
            <?=Yii::t('profile_page', 'My profile') ?>
        </h5>
        <button id="edit-profile" type="button" class="btn btn-transparent btn-icon float-right" data-toggle="modal" data-target=".modal-edit-profile"><i class="fa fa-pencil"></i></button>
        <div class="clearfix"></div>
    </div>
    <div class="card-body">
        <div id="table-profile-card"></div>
        <?=$profileCard?>
    </div>
</div>


<?php
$passwordProfileUrl = Url::to(['/profile/default/show-password-form']);
$editProfileForm = Url::to(['/profile/default/edit-profile-form']);
$showProfileInfo = Url::to(['/profile/default/show-profile-info']);

$js = <<<JS


$(document).on('click', '#edit-profile', function(){
    if($(this).hasClass('active')){
        $(this).removeClass('active');
        $.ajax({
            url: '$showProfileInfo',
            method: 'POST',
            success: function(data){
                backHtml = $('#table-profile-card').html();            
                $('#table-profile-card').html(data);
            }
        });        
    } else {
        $(this).addClass('active');
        $.ajax({
            url: '$editProfileForm',
            method: 'POST',
            success: function(data){
                backHtml = $('#table-profile-card').html();            
                $('#table-profile-card').html(data);
            }
        });
    }
});
JS;
$this->registerJs($js);
?>

