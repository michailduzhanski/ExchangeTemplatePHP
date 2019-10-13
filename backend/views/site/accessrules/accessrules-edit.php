<?php
/* @var $this yii\web\View */

use backend\models\dataobjects\ClassesList;

$js = <<<JS
        function getContent(url,request,element)
	{
		$.post(url,{json:JSON.stringify(request)}).done(function(data){
			var html='';
			if(element==='registry')
			{
				var structure=JSON.parse(data['data']['structure'][0]['json_structure']);
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
			{      
			    //var index = 0;
				$.each(data['data']['data'],function(){
					var htmlRow=templates[element];
					var actions='';
                    var rowValue = this;
                    if(element == 'company'){
                        rowValue = JSON.parse(this['json_field']);
                    }
                    if(rowValue['name'] == 'id' || rowValue['name'] == 'date_create' || rowValue['name'] == 'date_change'){
                        htmlRow=htmlRow.replace(`{visible}`,' invisible');
                    }else{
                        htmlRow=htmlRow.replace(`{visible}`,'');
                    }
                    //index++;
					$.each(rowValue,function(index, value){
						if(index == 'usef' || index == 'visible' || index == 'edit' || index == 'delete' || index == 'insert'){
						    if(value == true){
						        htmlRow=htmlRow.replace(`{\${index}}`,' checked');
						    }else{
						        htmlRow=htmlRow.replace(`{\${index}}`,'');
						    }
						}else
						htmlRow=htmlRow.replace(`{\${index}}`,value);
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
$objectId = $object['id'];
$currentId = $assembly['id'];
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
        $('#dataobjectstructure-name').val(field.find('.field-name').html());
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
        document.getElementById("dataobjectdelete-name").innerHTML = field.find('.field-name').html();
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

    function changeUrl(page, url) {
        window.location.href = url;
        /*if (typeof (history.pushState) != "undefined") {
            var obj = { Page: page, Url: url };
            history.pushState(obj, obj.Page, obj.Url);
        } else {
            window.location.href = url;
        }*/
    }
</script>
<div class="card">
    <div class="header">
        <h4 class="title"><?= $object === null ? 'Create' : 'Edit' ?> assembly: <?= $assembly['name'] ?>
            <span>   (<?= $object['name'] ?>)</span></h4>
    </div>
    <div class="content">


        <div class="row">
            <div class="col-md-6 col-xs-12">
                <?=
                $this->render('editcards/cardnamedescription', [
                    'object' => $object,
                    'assembly' => $assembly,
                    'assemblyEditNameDescription' => $assemblyEditNameDescription
                ])
                ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <?=
                $this->render('editcards/cardroles', [
                    'object' => $object,
                    'assembly' => $assembly
                ])
                ?>
            </div>
        </div>
        <div class="card">
        	<div class="content">
		        <?=
		            $this->render('editcards/cardstructure', [
		                'object' => $object,
		                'assembly' => $assembly
		        	])
		        ?>
		    </div>
		</div>

		<div class="card">
            <div class="header">
                <h4 class="title">Functions</h4>
            </div>
            <div class="content">
            	<div class="row">
            		<div class="col-sm-3 col-xs-12">
						<label for="">Type</label>
						<select name="" id="" class="form-control">
							<option value="">Global</option>
							<option value="">Middle</option>
							<option value="">Row</option>
						</select>
					</div>
					<div class="col-sm-3 col-xs-12">
						<label for="">Element</label>
						<select name="" id="" class="form-control">
							<option value="">1</option>
							<option value="">2</option>
							<option value="">3</option>
						</select>
					</div>
					<div class="col-sm-3 col-xs-12">
						<label for="" style="display: block">Priority</label>
						<div class="form-inline">
							<div class="form-group"><button type="button" class="btn btn-primary">-</button></div>
							<div class="form-group" style="width: calc(100% - 95px);"><input type="text" class="form-control"></div>
							<div class="form-group"><button type="button" class="btn btn-primary">+</button></div>
						</div>
					</div>
					<div class="col-sm-3 col-xs-12">
						<label for="" class="invisible">Save</label>
						<button type="button" class="btn btn-primary btn-block">Save</button>
					</div>
				</div>
            </div>
        </div>
        <?php
        $templates = isset(Yii::$app->params['templates']) ? json_encode(Yii::$app->params['templates']) : '[]';
        $templateMaps = isset(Yii::$app->params['templateMaps']) ? json_encode(Yii::$app->params['templateMaps']) : '[]';
        $json = isset(Yii::$app->params['json']) ? json_encode(Yii::$app->params['json']) : '[]';
        ?>
        <script>
            var templates =<?= $templates ?>;
            var json =<?= $json ?>;
            var templateMaps =<?= $templateMaps ?>;
        </script>
    </div>
</div>

<div id="modal-edit-field-N" class="modal fade modal-edit-field" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Edit <?= $assembly['name']; ?> field </h4>
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
                                <?= ClassesList::getClassesListForSelect() ?>
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
                <h4 class="modal-title">Delete <?= $object['name']; ?> field </h4>
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
</div><!-- /.modal -->
