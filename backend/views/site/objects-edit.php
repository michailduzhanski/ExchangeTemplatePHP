<?php
/* @var $this yii\web\View */

use backend\models\dataobjects\ClassesList;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;

$js = <<<JS
        function getContent(url,request,element)
	{
			//console.log(request);
		$.post(url,{json:JSON.stringify(request)}).done(function(data){
		    //console.log(data);
			var html='';
			if(element==='registry')
			{
				var structure=JSON.parse(data['data']['structure'][0]['json_structure']);
				//console.log(structure);
				$.each(data['data']['data'],function(){
					var htmlRow='<a href="" class="dark-blue-text btn btn-link btn-block btn-list btn-whis-angle">';
					var item=JSON.parse(this['json_field']);
					$.each(structure,function(index,value){
						if(parseInt(value['perm'])>1)
						{
							htmlRow+=item[index]+' ';
						}
					});
					
					htmlRow+='<i class="fa fa-angle-right"></i></a>';
					
					html+=htmlRow;
				});
			}
			else if(data != null && data['data'] != null)
			{                    //console.log(data['data'])
				$.each(data['data']['data'],function(){
					var htmlRow=templates[element];
					var actions='';
                    var rowValue = this;
                    if(element == 'company'){
                        rowValue = JSON.parse(this['json_field']);
                        //console.log(rowValue)
                    }
        
					$.each(rowValue,function(index,value){
						
						if(value)
						{
							if((index==='edit'||index==='delete')&&actions==='')
							{
								actions+='<div class="abs-block">';
							}
							
							if(index==='edit')
							{
								actions+='<a href="" data-toggle="modal" class="btn-icon btn-edit" onclick="modalEdit(this)"><i class="pe-7s-pen"></i></a>';
							}
							if(index==='delete')
							{
								actions+='<a href="" data-toggle="modal" class="btn-icon btn-delete" onclick="modalDelete(this)"><i class="pe-7s-trash"></i></a>';
							}
						}
						htmlRow=htmlRow.split(`{\${index}}`).join(value);
					});
					
					if(actions!=='')
					{
						actions+='</div>';
					}
					htmlRow=htmlRow.replace(`{actions}`,actions);
					html+=htmlRow;
				});
			}
			$('#table-'+element).html(html);
		});
	}

JS;
$this->registerJs($js);
$currentId = $object['id'];
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$currentURL = 'objects-operations';
/*$js = <<<JS
    $('#button-save-modal').click(function() {
        var postid = document.getElementById("dataobjectstructure-id").value;
        console.log("start save: " + postid)
        var postname = document.getElementById("dataobjectstructure-name").value;
        var postdescription = document.getElementById("dataobjectstructure-description").value;
        var postvalue = { '$csrfParam':'$csrfToken', StructureFieldValues:{object:'$currentId', id:postid, name:postname, description:postdescription, class:''}};
        $.post('$currentURL',postvalue).done(function(data){
                        console.log(data);
        });
    });

    $('#table-classes').change(function() {
        document.getElementById("radio-classes").checked = true;
        document.getElementById("radio-objects").checked = false;
    });

    $('#table-objects').change(function() {
        document.getElementById("radio-classes").checked = false;
        document.getElementById("radio-objects").checked = true;
    });
JS;
$this->registerJs($js);*/
?>
<script>
    function modalEdit(field) {
        $('#modal-edit-field-N').modal('show');
        field = $(field).parent().parent();
        //console.log(field.find('.field-class-id'));
        $('#dataobjectstructure-id').val(field.data('id'));
        $('#dataobjectstructure-name').val(field.find('.field-name>.inline-block:first-child').html());
        $('#dataobjectstructure-description').val(field.find('.field-description').html());
        //clearSelectList(document.getElementById("table-classes"));
        document.getElementById("table-classes").innerHTML = '<?= ClassesList::getClassesListForSelect()?>';
        getFirstElement(document.getElementById("table-classes"), field.find('.fieldclass').html(), "radio-classes");
        getFirstElement(document.getElementById("table-objects"), field.find('.fieldclass').html(), "radio-objects");
    }

    function modalDelete(field) {
        $('#modal-delete-field-N').modal('show');
        field = $(field).parent().parent();
        //console.log(field.find('.field-class-id'));
        $('#dataobjectdelete-id').val(field.data('id'));
        document.getElementById("dataobjectdelete-name").innerHTML = field.find('.field-name>.inline-block:first-child').html();
    }

    function modalHide() {
        $('#modal-edit-field-N').modal('hide');
    }

    function modalDeleteHide() {
        $('#modal-delete-field-N').modal('hide');
    }

    function getFirstElement(selectList, id, checkBoxName) {
        var index = 0;
        while (index < selectList.options.length) {
            if (selectList.options[index].value == id) {
                //var currentOption = selectList.options[index];
                //selectList.remove(index);
                //selectList.options._setOption(0, currentOption);
                selectList.options[index].selected = true;
                document.getElementById(checkBoxName).checked = true;
                break;
            }
            index++;
        }
    }
</script>
<div class="card">
    <div class="header">
        <h4 class="title"><?= $object === null ? 'Create' : 'Edit' ?> Data object: <?= $object['name'] ?></h4>
    </div>
    <div class="content">


        <div class="row">
            <div class="col-md-6 col-xs-12">
                <?=
                $this->render('dataobjects/cardnamedescription', [
                    'object' => $object,
                    'editNameDescription' => $editNameDescription
                ])
                ?>
                <?=
                $this->render('dataobjects/cardstructure', [
                    'object' => $object
                ])
                ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <?=
                $this->render('dataobjects/cardassembly', [
                    'object' => $object
                ])
                ?>
                <?=
                $this->render('dataobjects/cardcompanies', [
                    'object' => $object
                ])
                ?>
            </div>
        </div>
        <?php
        $templates = isset(Yii::$app->params['templates']) ? json_encode(Yii::$app->params['templates']) : '[]';
        $json = isset(Yii::$app->params['json']) ? json_encode(Yii::$app->params['json']) : '[]';
        ?>
        <script>
            var templates =<?= $templates ?>;
            var json =<?= $json ?>;
        </script>
    </div>
</div>
<?php
                $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
                if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
                    echo '
<div id="modal-edit-field-N" class="modal fade modal-edit-field" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Edit ' . $object['name'] . ' field </h4>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="hidden" id="dataobjectstructure-id"/>
                            <label>Name <input id="dataobjectstructure-name" type="text" class="form-control"
                                               placeholder="Enter a name"><label>
                                    <label>Description</label>
                                    <textarea id="dataobjectstructure-description" class="form-control"
                                              placeholder="Enter a short description"></textarea>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <h5 class="sub-title">Change class</h5>
                            <label class="inline"><input id="radio-classes" type="radio" name="class_type"
                                                         value="primitives_list"> Primitives</label>
                            <label class="inline"><input id="radio-objects" type="radio" name="class_type"
                                                         value="existing_data_object_list"> Existing Data
                                objects</label>
                            <select id="table-classes" class="form-control">
                                ' . ClassesList::getClassesListForSelect() . '
                            </select>
                            <select id="table-objects" class="form-control">

                            </select>
                        </div>
                    </div>
                    <div class="pull-right">
                        <button id="button-save-modal" type="button" class="btn btn-primary"><i
                                    class="pe-7s-diskette"></i>Save
                        </button>
                        <button type="button" class="btn btn-danger" onclick="modalHide()">
                                            <span class="btn-label">
                                                <i class="fa fa-times"></i> Cancel
                                            </span>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modal-delete-field-N" class="modal fade modal-edit-field" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Delete ' . $object['name'] . ' field </h4>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="">
                        <input type="hidden" id="dataobjectdelete-id"/>
                        <label>Are you really want to delete field?
                            <div id="dataobjectdelete-name" class="form-control disabled"></div>
                            <label>
                    </div>
                    <div class="pull-right">
                        <button id="button-delete-modal" type="button" class="btn btn-primary"><i
                                    class="pe-7s-trash"></i>Delete
                        </button>
                        <button type="button" class="btn btn-danger" onclick="modalDeleteHide()">
                                            <span class="btn-label">
                                                <i class="fa fa-times"></i> Cancel
                                            </span>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';
                }
?>
