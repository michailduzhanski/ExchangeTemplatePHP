<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SiteLangDictionary */

$this->title = Yii::t('backend', 'Add message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Dictionaries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-lang-dictionary-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
