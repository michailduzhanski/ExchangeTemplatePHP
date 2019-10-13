<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SiteLang */

$this->title = Yii::t('backend', 'Add language');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Site Langs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-lang-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
