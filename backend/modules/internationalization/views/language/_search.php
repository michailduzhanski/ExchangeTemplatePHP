<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\internationalization\models\SiteLangSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="site-lang-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'code') ?>

    <?= $form->field($model, 'locale') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'default_lang')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('backend', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
