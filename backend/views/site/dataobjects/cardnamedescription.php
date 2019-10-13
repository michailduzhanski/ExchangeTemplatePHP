<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;

$currentURL = 'objects-operations';
$currentId = $object['id'];
$value = json_encode($object);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$js = <<<JS
        $('#btn-save-nd').click(function() {
            var postname = document.getElementById("editnamedescription-name").value;
            var postdescription = document.getElementById("editnamedescription-description").value;
            var postvalue = { '$csrfParam':'$csrfToken', EditNameDescription:{id:"$currentId", name:postname, description:postdescription}};
            $.post('$currentURL',postvalue).done(function(data){
                            console.log(data);
            });
        });
JS;
$this->registerJs($js);
?>
<div class="">
    <div class="card">
        <div class="content">
            <?php /*<!--code class="php"><?php Pjax::begin(); ?>
            <?php
            $form = ActiveForm::begin([
                        'fieldConfig' => [
                            'template' => '{label}{input}{error}',
                            'options' => ['class' => 'form-block',
                                'data-pjax' => 0]
                        ]
                    ])
            ?>
            <?= $form->field($editNameDescription, 'id', ['template' => '{input}'])->hiddenInput() ?>
            <?= $form->field($editNameDescription, 'name')->textInput() ?>
            <?= $form->field($editNameDescription, 'description')->textarea() ?>
            <?= Html::submitButton('<i class="pe-7s-diskette"></i> Save changes', ['class' => 'btn btn-primary']) ?>
            <?php ActiveForm::end() ?>
            <?php Pjax::end(); ?></code-->
            */ ?>
            <div>
                <div class="form-block field-editnamedescription-id">
                    <input type="hidden" id="editnamedescription-id" class="form-control" name="id"
                           value=<?= $object['id'] ?>>
                </div>
                <div class="form-block field-editnamedescription-name required">
                    <label class="control-label" for="editnamedescription-name"><?=Yii::t('backend', 'Name')?></label>
                    <input type="text" id="editnamedescription-name" class="form-control" name="name"
                           value="<?= $object['name'] ?>" aria-required="true">
                    <p class="help-block help-block-error"></p>
                </div>
                <div class="form-block field-editnamedescription-description required">
                    <label class="control-label" for="editnamedescription-description"><?=Yii::t('backend', 'Description')?></label>
                    <textarea id="editnamedescription-description" class="form-control" name="description"
                              aria-required="true"><?= $object['description'] ?></textarea>
                    <p class="help-block help-block-error"></p>
                </div>
                <?php
                $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
                if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
                    echo '<button id="btn-save-nd" type="submit" class="btn btn-primary"><i class="pe-7s-diskette"></i> '.
                        Yii::t('backend', 'Save changes').'
                </button>';
                }
                ?>

            </div>
        </div>
    </div>
</div>
