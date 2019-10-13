<?php
use \yii\bootstrap\ActiveForm;
?>

        <div class="form-group">
            <label><?=$post['field']?></label>
        </div>
        <div class="form-group">
            <?php $form = ActiveForm::begin([
                //'id' => 'auth-form',
                //'action' => ['/'],
            ]) ?>
            <?=$form->field($model, $field)->widget(
                \dosamigos\ckeditor\CKEditor::class, [
                'options' => ['rows' => 6],
                'preset' => 'standart'
            ])->label(false)  ?>
            <?php ActiveForm::end() ?>
        </div>

