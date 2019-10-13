<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use \common\models\SiteLangCategory;
use \common\models\SiteLang;

/* @var $this yii\web\View */
/* @var $model common\models\SiteLangDictionary */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="site-lang-dictionary-form">

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'category')->dropDownList(SiteLangCategory::getAllItems())->label(Yii::t('app', 'Category')) ?>

    <?= $form->field($model, 'text')->textInput() ?>

    <?php foreach($model->textItems as $code => $value): ?>
    <?=
        $form->field($model, "textItems[$code]")
            ->textInput(['value' => $value])
        ->label(
            $model->getAttributeLabel('textItems') .
            ' ('.SiteLang::getNameByCode($code).')'
        );
    ?>
    <?php endforeach; ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
