<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use backend\assets\AppAsset;
//<?= common\modules\lng\widgets\ListWidget::widget() ? >
?>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php AppAsset::register($this) ?>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

