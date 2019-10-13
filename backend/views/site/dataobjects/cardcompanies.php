<?php
/* @var $signature string */
/* @var $this \yii\web\View */
/* @var $ctime mixed */
/* @var $login string */
/* @var $object bool */

use yii\helpers\Url;
use common\modules\drole\models\webtools\JSONRegistryFactory;

$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$assemblyTemplate = '<a href="/companies/company-edit?id={0}" class="dark-blue-text btn btn-link btn-block btn-list btn-whis-angle">{name}<i class="fa fa-angle-right"></i></a>';
Yii::$app->params['templates']['company'] = $assemblyTemplate;
$assemblyJson = JSONRegistryFactory::getCompaniesList(true, $object['id']);
Yii::$app->params['json']['company'] = $assemblyJson;
$js = <<<JS
        var companyJson = $assemblyJson
$('#search-fields-company').keyup(function() {
	companyJson['filters'][0]['common']=$(this).val();
        getContent('$apiRequestURL/drole/default/get-info',companyJson,'company');
}).keyup();
JS;

$this->registerJs($js);
?>

<div class="card">
    <div class="header">
        <h4 class="title"><?=Yii::t('backend', 'Companies list')?></h4>
        <p class="category"><?=Yii::t('backend', 'The companies list that are used in the object')?>.</p>
    </div>
    <div class="content">
        <div class="search-and-filter-row">
            <input id="search-fields-company" type="text" placeholder="<?=Yii::t('backend', 'Quick search')?>" class="form-control">
        </div>
        <div id="table-company" class="box-whis-scroll ps-container ps-theme-default ps-active-y">

        </div>
    </div>
</div>