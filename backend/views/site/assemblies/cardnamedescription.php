<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model \common\models\LoginForm */

$currentURL = 'assembly-operations';
$currentId = $assembly['id'];
$objectId = Yii::$app->request->get('objectid');
$value = json_encode($assembly);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$js = <<<JS
        $('#btn-save-nd').click(function() {
            var postname = document.getElementById("editnamedescription-name").value;
            var postdescription = document.getElementById("editnamedescription-description").value;
            var posttype = document.getElementById("checkOnlyStructure").checked;
            var postvalue = { '$csrfParam':'$csrfToken', AssemblyEditNameDescription:{id:"$currentId", objectid:"$objectId", name:postname, type:posttype, description:postdescription}};
            //console.log(postvalue);
            $.post('$currentURL',postvalue).done(function(data){
                 var array = JSON.parse(data);
                 if(array['result'] == null){
                    //changeUrl('Assembly edit', "assemblies-edit?id=" + array['id'] + '&objectid=' + array['objectid']);
                 }else{
                     alert(data);
                 }
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
				<div class="row">
					<div class="col-md-6 col-sm-12 col-xs-12">
						<div class="form-block field-editnamedescription-id">
							<input type="hidden" id="editnamedescription-id" class="form-control" name="id"
								   value=<?= $assembly['id'] ?>>
						</div>
						<div class="form-block field-editnamedescription-name required">
							<label class="control-label" for="editnamedescription-name">Name</label>
							<input type="text" id="editnamedescription-name" class="form-control" name="name"
								   value="<?= $assembly['name'] ?>" aria-required="true">
							<p class="help-block help-block-error"></p>
						</div>
					</div>
					<div class="col-md-6 col-sm-12 col-xs-12">
						<label class="control-label vm-form-control" for="checkOnlyStructure"><input type="checkbox" id="checkOnlyStructure" <?= ((!isset($currentId) || !$currentId)) ? 'checked' : ($assembly['type'] == 1 ? 'checked' : '') ?>> Only structure</label>
					</div>
				</div>
                <div class="form-block field-editnamedescription-description required">
                    <label class="control-label" for="editnamedescription-description">Description</label>
                    <textarea id="editnamedescription-description" class="form-control" name="description"
                              aria-required="true"><?= $assembly['description'] ?></textarea>
                    <p class="help-block help-block-error"></p>
                </div>
                <button id="btn-save-nd" type="submit" class="btn btn-primary"><i class="pe-7s-diskette"></i> Save
                    changes
                </button>
            
        </div>
    </div>
</div>
