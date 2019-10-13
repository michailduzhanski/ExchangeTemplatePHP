<?php
use \yii\bootstrap\ActiveForm;
?>

<?php $form = ActiveForm::begin([
    'id' => 'add-coin-form',
    'action' => $action,
    'options' => ['data-pjax' => true]
    /*'options' => [
        'class' => 'form-pretty-label'
    ]*/
]) ?>

<?php
    $fields = $model->getEditableFields();
    $column = 3;
?>

<div class="row">
<?php for($i=0; $i < $column; $i++):?>
    <div class="col-md-4 col-sm-12 col-12">
        <div class="card">
            <div class="card-body">
                <?php $j=0; ?>
                <?php foreach ($fields as $key => $value): ?>
                    <?php if($j%3 == $i): ?>
                        <?php $field = $form->field($model, $key, [])?>
                        <?=$model->renderWidget($field, $key)?>
                    <?php endif; ?>
                    <?php $j++ ?>
                <?php endforeach; ?>
                <?php if($i==0):?>
                    <?=$form->field($model, 'tariff')
                        ->textInput()
                        ->label(false)
                    ?>
                    <input type="submit" class="btn btn-success" id="add-coin-submit" value="Save" />
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endfor; ?>
</div>
<?php ActiveForm::end() ?>
