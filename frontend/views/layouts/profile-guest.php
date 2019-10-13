<?php
/**
 *  Основной шаблон
 */

/* @var $this \yii\web\View */

/* @var $content string */


use yii\helpers\Html;
use \frontend\assets\ProfileGuestAsset;
use \yii\helpers\Url;

ProfileGuestAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="sidebar-icon-only">
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
                <?=$this->render('parts/main_menu') ?>
            </ul>
            <ul class="navbar-nav ml-lg-auto d-flex align-items-center flex-row top-nav">
                <li class="nav-item dropdown hidden-sm hidden-xs">
                      <a class="nav-link profile-pic" href="<?=Url::to(['/auth/default/login'])?>">
                        <span class="username"><?=Yii::t('frontend', 'Login')?></span>
                    </a>
                </li>
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

    <footer class="footer">
        <div class="container-fluid clearfix">
            <div class="row">
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <button type="button" class="btn btn-orange btn-toggle-chat"><i class="fa fa-weixin"
                                                                                    aria-hidden="true"></i>
                        <span><?= Yii::t('frontend', 'Live chat') ?></span>
                    </button>
                    <button type="button" class="btn btn-transparent btn-toggle-modal">
                        <i class="fa fa-headphones" aria-hidden="true"></i>
                        <span><?= Yii::t('frontend', 'Support') ?></span>
                    </button>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="footer-menu-block">
                        <h3><?= Yii::t('frontend', 'Information') ?></h3>
                        <a href=""><?= Yii::t('frontend', 'Hysiope IRC') ?></a>
                        <a href=""><?= Yii::t('frontend', 'Privacy & Security') ?></a>
                        <a href=""><?= Yii::t('frontend', 'Terms & Conditions') ?></a>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="footer-menu-block">
                        <h3><?= Yii::t('frontend', 'Exchange') ?></h3>
                        <a href=""><?= Yii::t('frontend', 'Instruments') ?></a>
                        <a href=""><?= Yii::t('frontend', 'Marketplace') ?></a>
                        <a href=""><?= Yii::t('frontend', 'CoinInfo') ?></a>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="footer-menu-block">
                        <h3><?= Yii::t('frontend', 'Social') ?></h3>
                        <a href=""><?= Yii::t('frontend', 'Twitter') ?></a>
                        <a href=""><?= Yii::t('frontend', 'Facebook') ?></a>
                        <a href=""><?= Yii::t('frontend', 'LinkedIn') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
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
