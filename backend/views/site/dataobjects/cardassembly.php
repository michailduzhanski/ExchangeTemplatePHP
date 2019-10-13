<?php
/* @var $signature string */
/* @var $this \yii\web\View */
/* @var $ctime mixed */
/* @var $login string */
/* @var $object bool */

use yii\helpers\Url;
use common\modules\drole\models\webtools\JSONRegistryFactory;

$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$assemblyTemplate = '<div class="module-field-box">
                <a href="/assemblies-edit?id={id}&objectid=' . $object['id'] . '">{name}</a>
                <p class="company_name mr-b-0">'.Yii::t('backend', 'Company').': <span>{company_name}</span></p>
                <p class="company_name">'.Yii::t('backend', 'Dynamic role(s)').': <span>{role_name}</span></p>
                <p>{description}</p>
            </div>';
Yii::$app->params['templates']['assembly'] = $assemblyTemplate;
$assemblyJson = JSONRegistryFactory::getAssembliesList(true, $object['id']);
Yii::$app->params['json']['assembly'] = $assemblyJson;
$js = <<<JS
        var assemblyJson = $assemblyJson
$('#search-fields-assembly').keyup(function() {
	assemblyJson['filters'][0]['common']=$(this).val();
        getContent('$apiRequestURL/drole/default/get-info',assemblyJson,'assembly');
}).keyup();
JS;

$this->registerJs($js);
?>

<div class="card">
    <div class="header">
        <h4 class="title"><?=Yii::t('backend', 'Assemblies list')?></h4>
        <p class="category"><?=Yii::t('backend', 'Choice current assembly from the list')?></p>
    </div>
    <div class="content">
        <div class="search-and-filter-row">
            <input id="search-fields-assembly" type="text" placeholder="<?=Yii::t('backend', 'Quick search')?>" class="form-control">
        </div>
        <div id="table-assembly" class="box-whis-scroll ps-container ps-theme-default ps-active-y">

        </div>
    </div>
</div>