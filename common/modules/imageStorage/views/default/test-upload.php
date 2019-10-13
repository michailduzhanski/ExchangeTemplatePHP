<?php
use \yii\widgets\ActiveForm;
use \common\modules\imageStorage\widgets\FileInput;
use \common\modules\imageStorage\widgets\FileInputAjax;
/* @var $model \common\modules\imageStorage\models\TestModel */

?>


<div class="container">
<?php $form = ActiveForm::begin([
        'enableClientValidation' => true,
        'options' => ['enctype' => 'multipart/form-data']
]) ?>



<?= $form->field($model, 'photo')->widget(FileInput::class, []); ?>

<?/*= $form->field($model, 'photo2')->widget(FileInput::class, []); */?>

<?/*= $form->field($model, 'photo')->widget(FileInputAjax::class, [
    'table' => 'contact_data_use',
    'owner' => 'user_photo',
    'objectId' => '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24',
    'recordId' => Yii::$app->user->id
]); */?>






<button>Submit</button>

<?php ActiveForm::end() ?>
</div>
