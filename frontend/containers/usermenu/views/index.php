<?php
use \yii\helpers\Html;
use \yii\helpers\Url;
use \common\modules\drole\models\webtools\JSONRegistryFactory;
?>



<?php
$objectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';


$accountMenuTemplate =  '';
Yii::$app->params['templates']['account-menu-photo'] = $accountMenuTemplate;
Yii::$app->params['templateMaps']['account-menu-photo'] = [
    "9b576f7e-842f-4810-a2d5-0cc5e97d0cc1"
];

$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectId, '');
$js = <<<JS
var response = null;
arrayOfFunctions = [function(nextfunction){
    responseAccount = getListObjectContentComplex('$apiRequestURL/drole/default/get-info', $json, 'account-menu-photo', '', nextfunction, 1);
},function(){  
    var structure = responseAccount.responseJSON.data.structure.data;
    var data = responseAccount.responseJSON.data.data[0];
    getImage(data, structure, 'table-account-menu-photo', '$objectId', '9b576f7e-842f-4810-a2d5-0cc5e97d0cc1', 'small', 'class="rounded-circle"');      
}];
globalQueue(arrayOfFunctions);
JS;
$this->registerJs($js);
?>

<?php if(!Yii::$app->user->isGuest): ?>
        <a class="nav-link profile-pic dropdown-toggle" id="personalDropdown" href="#" data-toggle="dropdown">
            <!--<img class="rounded-circle" src="/images/face.jpg" alt="">-->
            <span id="table-account-menu-photo"></span>
            <span class="username"><?=Yii::$app->user->identity->getContact()->login?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="personalDropdown">
            <!--<a href="<?/*=Url::to(['/auth/default/logout'])*/?>" class="dropdown-item">Logout</a>-->
            <?=Html::a(
                Yii::t('frontend', 'Logout'),
                Url::to(['/auth/default/logout']),
                ['data-method' => 'POST', 'class' => 'dropdown-item'])
            ?>
        </div>

<?php endif; ?>

