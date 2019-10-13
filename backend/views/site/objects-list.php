<?php
/* @var $this yii\web\View */

use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use yii\helpers\Url;

$this->title = 'Object list';
$template = '<div class="row with-border" data-id="{id}" data-name="{name}"><div class="col-md-4 col-sm-5 col-xs-9">{name}</div><div class="col-md-6 col-sm-5 hidden-xs">{description}</div><div class="col-md-2 col-sm-2 col-xs-3"><form action="" method="post"><div id="{id}" class="abs-hidden-text">{id}</div><a href="/en/objectsrecords-list?id={id}" class="btn btn-icon btn-info"><i class="fa fa-database"></i></a><a href="/' . \Yii::$app->language . '/objects-edit?id={id}" class="btn btn-icon btn-primary"><i class="pe-7s-pen"></i></a><button type="button" class="btn btn-icon btn-danger" onclick="modalDelete(this)"><i class="pe-7s-trash"></i></button></form></div></div>';

$json = JSONRegistryFactory::getObjectsList(true, "objects");

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$currentURL = 'objects-operations';

$js = <<<JS
	var json=$json;
	var template='$template';
	function getContent(url,data)
	{
		$.post(url,{json:JSON.stringify(data)}).done(function(data){
			var html='';
                        $.each(data['data']['data'],function(){
				var htmlRow=template;
	
				$.each(this,function(index,value){
                    htmlRow=htmlRow.split(`{\${index}}`).join(value);
				});
				html+=htmlRow;
			});
			$('#content-table').html(html);
		});
	}
	
	$('#quick-search').keyup(function() {
		json['filters'][0]['common']=$(this).val();
		getContent('/en/drole/default/get-info',json);
	}).keyup();
	
	$('#button-delete-modal').click(function () {
        var postid = document.getElementById("dataobjectdelete-id").value;
        var postvalue = {
            '$csrfParam': '$csrfToken',
            DataObjectDelete: {
                objectid: postid,
                checkusage: true
            }
        };
        $.post('$currentURL', postvalue).done(function (data) {
            console.log(data);
        });
    });
	
	new Clipboard('.btn-clipboard');
	
JS;

$this->registerJs($js);
?>

<script>
    function modalDelete(field) {
        $('#modal-delete-field-N').modal('show');
        field = $(field).parent().parent().parent();
        $('#dataobjectdelete-id').val(field.data('id'));
        document.getElementById("dataobjectdelete-name").innerHTML = field.data('name');
    }

    function modalDeleteHide() {
        $('#modal-delete-field-N').modal('hide');
    }

</script>
<div class="card">
    <div class="header">
        <h4 class="title">Data objects listing</h4>
        <p class="category">Choice current object from the list</p>
    </div>
    <div class="content">
        <div class="search-and-filter-row">
            <div class="row">
                <?php
                $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
                if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
                    echo '<div class="col-md-3 col-sm-12 col-xs-12">
                    <a href="' . Url::to(['/objects-edit']) . '" class="btn btn-info btn-wd btn-big"><i
                                class="pe-7s-plus"></i>Create
                        Data object</a>
				</div>';
                }
                ?>
                <div class="col-md-4 col-sm-7 col-xs-12">
                    <input type="text" id="quick-search" placeholder="Quick search" class="form-control"></div>
            </div>
        </div>
        <div id="content-table">

        </div>
    </div>
</div>

<div id="modal-delete-field-N" class="modal fade modal-edit-field" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Delete object </h4>
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