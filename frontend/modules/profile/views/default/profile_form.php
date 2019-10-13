<?php
use \common\modules\drole\models\webtools\JSONRegistryFactory;

$objectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';

$imgTemplate = '<div class="d-inline-block align-middle">
        <div id="table-photo"></div>
    </div>
    <div class="d-inline-block align-middle">
        <h3 class="username">{060f16c7-7573-413f-8f38-fe8d4bf177aa}</h3>
        <p class="user_mail">{a8654798-0aac-4d06-a409-eeb6fae2ed79}</p>
        <p class="last-auth"><?=Yii::t(\'frontend\', \'Last login:\')?> 2018-03-07 18:44:19</p>
    </div>';


Yii::$app->params['templates']['card'] = $imgTemplate;
Yii::$app->params['templateMaps']['card'] = [
    "9b576f7e-842f-4810-a2d5-0cc5e97d0cc1", "060f16c7-7573-413f-8f38-fe8d4bf177aa", "a8654798-0aac-4d06-a409-eeb6fae2ed79"
];

$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectId, '');
$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$js = <<<JS
var response = null;
arrayOfFunctions = [function(nextfunction){
    responseProfileForm = getListObjectContentComplex('$apiRequestURL/drole/default/get-info', $json, 'card', '', nextfunction, 1);    
},function(){    
    var structure = responseProfileForm.responseJSON.data.structure.data;
    var data = responseProfileForm.responseJSON.data.data[0];
    getImage(data, structure, 'table-photo', '$objectId', '9b576f7e-842f-4810-a2d5-0cc5e97d0cc1', 'preview', 'class="rounded-circle user-avatar"');    
}];
globalQueue(arrayOfFunctions);
JS;
$this->registerJs($js);
?>
<div id="table-card"></div>
