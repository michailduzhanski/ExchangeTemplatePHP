<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SiteLangDictionary */

$this->title = Yii::t('backend', 'Update message: {nameAttribute}', [
    'nameAttribute' => $model->text,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Dictionaries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="site-lang-dictionary-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
