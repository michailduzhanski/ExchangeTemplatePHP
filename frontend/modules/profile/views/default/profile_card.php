<?php
use \yii\helpers\Url;
use \common\modules\drole\models\webtools\JSONRegistryFactory;
use yii\widgets\Pjax;
use common\modules\drole\models\auth;
?>
<?php 
$droleArray = \common\modules\drole\models\registry\DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
$accountId = common\modules\drole\models\auth\CompaniesContactDataUse::getMD5ForCurrent($droleArray['company_id'], $droleArray['service_id']);
if($accountId){
    $accountField = '<div class="user-data">
                        <p class="grey-text">
                            '.Yii::t("profile_page", "Acount ID").'
                        </p>
                        <p>'.$accountId.'</p>
                    </div>';
} else {
    $accountField = '';
}


$objectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';
$profileTempalte = '
            <div class="profile-card-main">
                <div class="row">
                <div class="col">
                    <div class="user-data">
                        <p class="grey-text">
                            '.Yii::t("profile_page", "Login").'
                        </p>
                        <p>{060f16c7-7573-413f-8f38-fe8d4bf177aa}</p>
                    </div>
                    <div class="user-data">
                        <p class="grey-text">'.Yii::t("profile_page", "E-Mail").'</p>
                        <p>{a8654798-0aac-4d06-a409-eeb6fae2ed79}</p>
                    </div>
                    <div class="user-data">
                        <p class="grey-text">
                            '.Yii::t("profile_page", "Password").'                        
                        </p>
                        <button id="change-password" type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target=".modal-change-password">
                            '.Yii::t("profile_page", "Change password").'
                        </button>
                    </div>
                    <div class="user-data">
                        <div id="social-list"></div>
                    </div>
                </div>
                <div class="col">
                	'.$accountField.'
                    <div class="user-data">
                        <p class="grey-text">
                            '.Yii::t("profile_page", "First name").'
                        </p>
                        <p>{c896b5a6-8640-4103-ba22-70a0bc6c06fe}</p>
                    </div>
                    <div class="user-data">
                        <p class="grey-text">
                        '.Yii::t("profile_page", "Last name").'</p>
                        <p>{d280fa03-48cf-44d7-be2c-bcee54cfe89d}</p>
                    </div>                    
                </div>
                </div>
            </div>
            
            '

;?>

<?php
Yii::$app->params['templates']['profile-card'] = $profileTempalte;
Yii::$app->params['templateMaps']['profile-card'] = [
    "060f16c7-7573-413f-8f38-fe8d4bf177aa", //login
    "a8654798-0aac-4d06-a409-eeb6fae2ed79", //emailconversation
    "c896b5a6-8640-4103-ba22-70a0bc6c06fe", //firstname
    "d280fa03-48cf-44d7-be2c-bcee54cfe89d", //lastname    
];

$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectId, '');
$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);

$passwordProfileUrl = Url::to(['/profile/default/show-password-form']);
$editProfileForm = Url::to(['/profile/default/edit-profile-form']);
$showProfileInfo = Url::to(['/profile/default/show-profile-info']);
$socialLabel = Yii::t("profile_page", "Social");
$js = <<<JS


getListObjectContentSocials('$apiRequestURL/drole/default/get-info', $json);

function getListObjectContentSocials(url, request){
    $.post(url, {json: JSON.stringify(request)}).done(function (data) {
        var structure = data['data']['structure']['data'];
        var contactLinkIndex;
        $.each(structure, function(key, value){
            if(value.name == 'contactlinks'){
                contactLinkIndex = key;
                return key;
            }
        });        
        if(contactLinkIndex){
            var contactLinks = data['data']['data'][0];
            contactLinks = contactLinks[contactLinkIndex];                        
            if(contactLinks && contactLinks[0] != undefined && contactLinks[0] != ""){
                var socialTemplate = '<div class="user-data"><p class="grey-text">$socialLabel</p><nav class="social-network">';
                $.each(contactLinks, function(key, value){                        
                    if(value[3] == 'facebook'){
                        socialTemplate += '<a href="'+value[4]+'" target="_blank"><img src="/images/socials/facebook.png"/> </a>';
                    }
                    if(value[3] == 'linkedin'){
                        socialTemplate += '<a href="'+value[4]+'" target="_blank"><img src="/images/socials/linkedin.png"/> </a>';
                    }                    
                    if(value[3] == 'twitter'){
                        socialTemplate += '<a href="'+value[4]+'" target="_blank"><img src="/images/socials/twitter.png"/> </a>';
                    }  
                });
                socialTemplate += '</nav></div>';       
                   
            } 
            setTimeout(function(){
                $('#social-list').html(socialTemplate);
            }, 400);                                   
        }        
        
    });
}

getListObjectContentComplex('$apiRequestURL/drole/default/get-info', $json, 'profile-card', '', '', 1);

var backHtml = $('#table-profile-card').html();

$('#edit-profile').removeClass('active');

$(document).on('click', '#change-password', function(){
    var el = $(this);
    $.ajax({
        url: '$passwordProfileUrl',
        method: 'POST',
        success: function(data){
            el.closest('.user-data').html(data);
        }
    });
});



$(document).on('click', '#edit-profile', function(){
       
});

$(document).on('click', '#change-profile-back', function(){    
    $('#table-profile-card').html(backHtml);
});



JS;
$this->registerJs($js);
if(isset($profileForm) && $profileForm == true){
$js = <<<JS

JS;
    $this->registerJs($js);
}
?>

