<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SiteLangCategory */

$this->title = Yii::t('backend', 'Update dictionary category: {nameAttribute}', [
    'nameAttribute' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Dictionary categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->code]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="site-lang-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
