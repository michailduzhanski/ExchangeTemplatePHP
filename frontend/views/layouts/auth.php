<?php
/**
 * Шаблон страниц авторизации
 */

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use \frontend\assets\IndexAsset;
use \yii\helpers\Url;

IndexAsset::register($this);
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
<body class="secondary">
<?php $this->beginBody() ?>
<section id="main-nav">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-10">
                <a href="<?=Url::to(['/site/index'])?>" class="logo-link">
                    <img src="/images/logo-mini.png">ysiope
                </a>
            </div>
            <div class="col-md-8 col-sm-6 col-xs-2">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav pull-right">
                        <?=$this->render('parts/index_menu') ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?=$content?>

<?=$this->render('parts/index_footer')?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>


