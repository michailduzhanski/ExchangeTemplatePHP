<?php
/* @var $this yii\web\View */

use common\modules\drole\models\webtools\JSONRegistryFactory;

$this->title = Yii::t('backend', 'Assemblies list');
Yii::$app->params['templates']['access'] = '<div class="row with-border" data-id="{id}" data-name="{name}"><div class="col-md-2 col-sm-2 col-xs-9">{name}</div><div class="col-md-1 col-sm-1 hidden-xs">{company_id} [{accesslevel}]</div><div class="col-md-2 col-sm-3 hidden-xs">{subjectclass}</div><div class="col-md-2 col-sm-3 hidden-xs">{subjectvalue}</div><div class="col-md-1 col-sm-1 hidden-xs">{accessclass}</div><div class="col-md-1 col-sm-1 hidden-xs">{controlclass}</div><div class="col-md-2 col-sm-3 col-xs-3"><form action="" method="post"><div id="{id}" class="abs-hidden-text">{id}</div><button type="button" data-clipboard-target="#{id}" class="btn btn-icon btn-info btn-clipboard">ID</button><a href="/' . \Yii::$app->language . '/accessrules-edit?id={id}&objectid={objectid}" class="btn btn-icon btn-primary"><i class="pe-7s-pen"></i></a><button type="button" class="btn btn-icon btn-danger" onclick="modalDelete(this)"><i class="pe-7s-trash"></i></button><button type="button" class="btn btn-icon btn-arrow"><i class="pe-7s-angle-up"></i></button><button type="button" class="btn btn-icon btn-arrow"><i class="pe-7s-angle-down"></i></button></form></div></div>';
Yii::$app->params['templates']['objects'] = '<option value="{id}">{name}</option>';
$json = JSONRegistryFactory::getAccessRulesList(true, '0000');

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$currentURL = 'objects-operations';
$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$templates = isset(Yii::$app->params['templates']) ? json_encode(Yii::$app->params['templates']) : '[]';

$objectsJson = JSONRegistryFactory::getObjectsList(true, "objects");

Yii::$app->params['json']['objects'] = $objectsJson;

$js = <<<JS
        
JS;
$this->registerJs($js);

$js = <<<JS
    var objectsJson = $objectsJson;
    var initObjectsList = listObjects('$apiRequestURL/drole/default/get-info', objectsJson, 'objects');
    var currentObjectID = null;
	var json=$json;
	var firstStep = true;
    
	function getContent(url, data, element)
	{
	    $.post(url,{json:JSON.stringify(data)}).done(function(data){
	        console.log(data['data']['data'])
		    var html='';
		    $.each(data['data']['data'],function(){
				var htmlRow = templates[element];
				var lineObject = this;
				$.each(this,function(index, value){
				    if(index == "company_id"){
				        htmlRow=htmlRow.split(`{\${index}}`).join(lineObject['companyname']);
				    }else if(index == "subjectclass"){
				        htmlRow=htmlRow.split(`{\${index}}`).join(lineObject['subjectvalues']['object']);
				    }else
				    htmlRow=htmlRow.split(`{\${index}}`).join(value);
				});
				if(element == 'access'){
                    $.each(this,function(index, value){
                         htmlRow=htmlRow.split(`{objectid}`).join(currentObjectID);
                    });
                }
				html += htmlRow;
			});
            $('#table-'+element).html(html);
            if(!firstStep){
                document.getElementById('autocomplete-objects').select();
            }else{
                firstStep = false;
            }
		});
	}
	
	function listObjects(url, data, element){
	    var newObjectsList = [];
	    $.post(url,{json:JSON.stringify(data)}).done(function(data){
		    if(data != undefined && data['data'] != undefined && data['data']['data'] != undefined){
                currentObjectID = data['data']['data'][0]['id'];
                document.getElementById('autocomplete-objects').value = data['data']['data'][0]['name'];
		    }
            $.each(data['data']['data'],function(){
				newObjectsList.push({"id":this['id'], "value":this['name']});
			});
            console.log(currentObjectID)
            updateAssemblies();
		});
	    return newObjectsList;
	}
	
	$('#quick-search').keyup(function() {
	    updateAssemblies();		
	}).keyup();
	
	function updateAssemblies(){
	    json['filters'][0]['common']=document.getElementById("quick-search").value;
		if(currentObjectID != null){
		    document.getElementById("newassembly").href = "$apiRequestURL/assemblies-edit?objectid=" + currentObjectID;
		    json['work']['value']['object'] = currentObjectID;
		    getContent('/en/drole/default/get-info', json, 'access');
		}
	}
	
	$('#button-delete-modal').click(function () {
        var postid = document.getElementById("dataobjectdelete-id").value;
        var e = document.getElementById("table-objects");
        var postvalue = {
            '$csrfParam': '$csrfToken',
            AssemblyDelete: {
                objectid: e.options[e.selectedIndex].value,
                assemblyid: postid,
                checkusage: true
            }
        };
        $.post('$currentURL', postvalue).done(function (data) {
            console.log(data);
        });
    });
	
	$('#table-objects').change(function() {
        updateAssemblies();
    });
	
	new Clipboard('.btn-clipboard');
	
	$( function() {
        $( "#autocomplete-objects" ).autocomplete({
            source: initObjectsList,
            appendTo: "#autocomplete-box",
            minLength: 0,
            autofocus: true,
            select: function( event, ui ) {currentObjectID = ui.item.id; updateAssemblies();},
            messages: {
                noResults: '',
                results: function() {}
            }
        }).focus(function(){
            $(this).trigger(jQuery.Event("keydown"));
            this.setSelectionRange(0, this.value.length);
        });
	});
	
JS;

$this->registerJs($js);


?>

<script>
    var templates =<?= $templates ?>;

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
        <h4 class="title"><?= Yii::t('backend', 'Assemblies listing') ?></h4>
        <p class="category"><?= Yii::t('backend', 'Choice current assembly from the list') ?></p>
    </div>
    <div class="content">
        <div class="search-and-filter-row">
            <div class="row">
                <div class="col-md-3 col-sm-12 col-xs-12"><a id="newassembly" href="assembly-edit?objectid="
                                                             class="btn btn-info btn-wd btn-big"><i
                                class="pe-7s-plus"></i><?= Yii::t('backend', 'Create assembly') ?></a></div>
                <div class="col-md-4 col-sm-7 col-xs-12"><input type="text" id="quick-search"
                                                                placeholder="<?= Yii::t('backend', 'Quick search') ?>"
                                                                class="form-control"></div>
                <div class="col-md-3 col-sm-5 col-xs-12">
                    <select id="table-objects" class="form-control" style="display: none">

                    </select>
                    <div class="autocomplete" id="autocomplete-box">
                        <input id="autocomplete-objects" type="text" class="form-control" placeholder="objects">
                    </div>
                </div>
            </div>
        </div>
        <div id="table-access">

        </div>
    </div>
</div>

<div id="modal-delete-field-N" class="modal fade modal-edit-field" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= Yii::t('backend', 'Delete assembly') ?></h4>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="">
                        <input type="hidden" id="dataobjectdelete-id"/>
                        <label><?= Yii::t('backend', 'Are you really want to delete assembly?') ?>
                            <div id="dataobjectdelete-name" class="form-control disabled"></div>
                            <label>
                    </div>
                    <div class="pull-right">
                        <button id="button-delete-modal" type="button" class="btn btn-primary"><i
                                    class="pe-7s-trash"></i><?= Yii::t('backend', 'Delete') ?>
                        </button>
                        <button type="button" class="btn btn-danger" onclick="modalDeleteHide()">
                                            <span class="btn-label">
                                                <i class="fa fa-times"></i> <?= Yii::t('backend', 'Cancel') ?>
                                            </span>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->