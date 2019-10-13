<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;


?>
<div class="content-wrapper">
    <div class="site-error container">
<?php if(
        $exception->statusCode == 403 &&
        Yii::$app->session->getFlash('error') == 'verification'
): ?>
    <?php
        $this->title = Yii::t('frontend', 'Confirmation Email Sent.');
    ?>
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="alert alert-info">
        <?= nl2br(Html::encode($message)) ?>
        <br/>        
    </div>
<?php else: ?>
    <?php
        $this->title = $name;
    ?>
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>
<?php endif; ?>
    </div>
</div>
