<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SiteLangCategory */

$this->title = Yii::t('backend', 'Create dictionary category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Dictionary categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-lang-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
