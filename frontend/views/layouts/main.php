<?php
/**
 *  Основной шаблон
 */

/* @var $this \yii\web\View */

/* @var $content string */

use frontend\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?php $skin = 'light-mode';
    $settingsSkin = \common\modules\drole\models\auth\CompaniesContactDataUse::getContactDataByID('nightmode');
    if ($settingsSkin == 1) {
        $skin = 'night-mode';
    } ?>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="sidebar-icon-only <?= $skin ?>">
<?php $this->beginBody() ?>

<div class="container-scroller">
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="bg-grey text-center navbar-brand-wrapper">
            <a class="navbar-brand brand-logo" href="/"><img src="/images/logo_star_black.png"></a>
            <a class="navbar-brand brand-logo-mini" href="/"><img src="/images/logo-mini.png" alt=""></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center">
            <button class="navbar-toggler navbar-toggler d-none d-lg-block navbar-dark align-self-center mr-3"
                    type="button" data-toggle="minimize">
                <span class="navbar-toggler-icon"></span>
            </button>
            <ul class="navbar-nav d-flex align-items-center flex-row top-nav">
                <?= $this->render('parts/main_menu') ?>
            </ul>
            <ul class="navbar-nav ml-lg-auto d-flex align-items-center flex-row top-nav">
                <?= $this->render('parts/main_account_menu') ?>
            </ul>
            <button class="navbar-toggler navbar-dark navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="offcanvas">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row row-offcanvas row-offcanvas-right">
            <?= $this->render('parts/main_sidebar') ?>
            <?= $content ?>
        </div>
    </div>

    <?= $this->render('parts/main_footer') ?> 
    
</div>
<?php $this->endBody() ?>

<?php
$templates = isset(Yii::$app->params['templates']) ? json_encode(Yii::$app->params['templates']) : '[]';
$templateMaps = isset(Yii::$app->params['templateMaps']) ? json_encode(Yii::$app->params['templateMaps']) : '[]';
$json = isset(Yii::$app->params['json']) ? json_encode(Yii::$app->params['json']) : '[]';
?>
<script>
    var templates = <?= $templates ?>;
    var json =<?= $json ?>;
    var templateMaps =<?= $templateMaps ?>;
</script>

</body>
</html>
<?php $this->endPage() ?>
