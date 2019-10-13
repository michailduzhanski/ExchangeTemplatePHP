<?php
/**
 * Created by PhpStorm.
 * User: ENGINEER
 * Date: 6/19/2018
 * Time: 1:08 PM
 */

use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\webtools\JSONRegistryFactory;

\dosamigos\ckeditor\CKEditorWidgetAsset::register($this);

$this->title = 'Object\'s records edit';

$objectID = Yii::$app->request->get('id');
$recordID = Yii::$app->request->get('record');
if (!isset(RegistryObjects::getObjectNameByID($objectID)->name)) {
    header("Location: /objects-list");
    exit;
}

$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectID);
$jsonUpdate = JSONRegistryFactory::updateObject(true, $objectID, '');

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$currentURL = 'objectsrecords-operations';
$renderImageFieldUrl = \yii\helpers\Url::to(['/image-storage/default/render-image-field']);
$renderTextFieldUrl = \yii\helpers\Url::to(['/site/render-text-field']);

$js = <<<JS

    var json = ($json);
	json['filters'][1] = {'special' : [{
            map: 0,
            comp: 6,
            value: "$recordID"
        }]
    };
	//console.log()
	/*console.log(JSON.stringify(json))
	json = {"permission":{"object_id":"9cb00590-997d-43dd-b5b2-a1dabb35f74b","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"7d82bde3-7740-41d7-9610-8d1fc75db803","drole_id":"62900a19-88a9-4655-a7ac-71488070b659"},"work":{"set":1,"operation":0,"ctime":1529928641.035,"value":[]},"filters":[{"common":""},{"special":[{"map":0,"comp":6,"value":"$recordID"}]}]};
	console.log(JSON.stringify(json))*/

	function getContent(url, data)
	{
	    $.post(url,{json:JSON.stringify(data)}).done(function(data){
			 var x = document.getElementById("table-records");
			 var editorsHTML = '';
			 if (!x.tHead) {
                var header = x.createTHead(); 
                var row = header.insertRow(0);
                var body = x.createTBody();
                var blankField = document.createElement('th');
                blankField.innerHTML = 
                    '<p>Delete</p>';
                row.appendChild(blankField);
                $.each(data['data']['structure']['data'],function(){
                    var cell = document.createElement('th');
                    var sorting = '';
                    if(!this.nested || this.nested == "false"){
                        sorting = '<button type="button" class="btn btn-icon btn-default"><i class="fa fa-sort"></i></button>';
                    }
                    if(this.perm == 1 || this.perm == 16){
                        cell.classList.add('hidden')
                    }
                    cell.innerHTML = 
                    //'<input type="text" class="form-control th-searching" id="' + this.id + '" placeholder="Search.." onkeyup="searchKeyUp(this)">
                    '<span>' + this.name + '</span>';
                    row.appendChild(cell);
                });
                var index = 1;
                
                $.each(data['data']['data'],function(r, rowValue){
                    
                    var row = body.insertRow();                    
                    var fieldIndex = 0;
                    var viewIndex = 1;
                    zeroFieldID = data['data']['structure']['data'][fieldIndex].id;
                    $.each(this, function(f, fieldValue){
                        var fieldHeader = data['data']['structure']['data'][f];
                        if(typeof(fieldHeader) != "undefined"){
                            var cell = row.insertCell(fieldIndex);
                            cell.innerHTML = '<p>' + fieldValue + '</p>';
                            if((fieldHeader.perm == 1 || fieldHeader.perm == 16)){
                                cell.classList.add('hidden');
                            }else{
                                if(fieldHeader.nested != "false"){
                                    editorsHTML += createTableInnerObject(zeroFieldID, fieldIndex, fieldHeader.nested, fieldValue, fieldHeader.name, fieldHeader.id, fieldHeader.object)
                                }else{
                                    var stringValue = createAbsoluteInput(fieldIndex, fieldHeader.id, fieldHeader.type, fieldHeader.name, fieldValue, fieldHeader.perm);
                                    editorsHTML += stringValue;
                                }
                                viewIndex++;
                            }
                        }
                        fieldIndex++;
                    });
                    var cell = row.insertCell(0);
                    cell.innerHTML = '<button type="button" onclick="modalDelete(this)" id="' + this[0] + '" class="btn btn-icon btn-danger"><i class="pe-7s-trash"></i></button>';
                    index++;
                });
             }
             $("#values-list").html(editorsHTML)
             //alert(editorsHTML);
             //console.log(editorsHTML)
		});
	}
	
	getContent('/en/drole/default/get-info', json);
	
	function createAbsoluteInput(index, fieldID, type, title, value, isEdited){
	    var result = '';
	    var saveBtn = {};
	    if(isEdited > 2 && isEdited != 16){
	        saveBtn['type'] = type.toLowerCase();
	        saveBtn['id'] = fieldID;
	        saveBtn['index'] = index;
	    }
	    
	    switch(type.toLowerCase()){
	        case 'text':
	            result = createAreaInput(title, value, saveBtn);
	            break;
	        case 'image':
	            result = createImageInput(title, value, saveBtn);
	            break;
	        case 'boolean':
	            result = createBooleanInput(title, value, saveBtn);
	            break;
	        case 'integer':	            
	        case 'character varying':	            
	        case 'double precision':	            
	        case 'float':
            case 'uuid':
	            result = createSimpleInput(title, value, saveBtn);
	            break;
	        default:
	            result = createSimpleLabelInput(title, value);	            
	    }
	    result = '<div id="' + fieldID + '" class="col-md-4 col-sm-6 col-xs-12">' + 
                        '<div class="card">' + 
                            '<div class="content">' + 
                                '<div class="form-inline">' + 
                                    result + 
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
	    return result;
	}
	
	function createSimpleInput(title, value, btnParams){
	    var saveBtn = '';
	    if(btnParams){
	        saveBtn = createSaveBtn(btnParams);
	    }
	    return '<div class="form-group"><label>' + title + '</label></div>' + 
                                    '<div class="form-group"><input type="text" class="form-control" value="' + value + '"></div>' + 
                                    saveBtn;
	}
	
	function createSimpleLabelInput(title, value){
	    return '<div class="form-group"><label>' + title + '</label></div>' + 
                                    '<div class="form-group"><input type="text" class="form-control disabled" disabled value="' + value + '"></div>';
	}
	
	function createAreaInput(title, value, btnParams){	    	    
	    	   
	    var saveBtn = '';	    
	    if(btnParams){
	        saveBtn = createSaveBtn(btnParams);
	    }
                                 
        $.ajax({
            url: "$renderTextFieldUrl",
            method: 'POST',
            data: {
                object_id: '$objectID', 
                record_id: '$recordID', 
                field: title,
                value: value
            },
            success: function(result){       
                result = result + saveBtn;
                result = '<div class="card"><div class="content">' +result+ '</div></div>';                
                $('#'+btnParams.id).html(result);
            }
        });                
	}
	
	function createBooleanInput(title, value, saveBtn){
	    var saveBtn = '';
	    if(btnParams){
	        saveBtn = createSaveBtn(btnParams);
	    }
	    var checkedInput = '';
	    if(value == 1 || value == "true"){
	        checkedInput = " checked";
	    }
	    return '<div class="form-group"><label>' + title + '</label></div>' + 
                                    '<div class="form-group">' + 
                                    '<label><input type="checkbox"' + checkedInput + '></label>' + 
                                    '</div>' + 
                                    '<div class="form-group">' + saveBtn + '</div>';
	}
	
	function createImageInput(title, value, saveBtn){         
        $.ajax({
            url: "$renderImageFieldUrl",
            method: 'POST',
            data: {object_id: '$objectID', record_id: '$recordID', field: title},
            success: function(result){
                $('#'+saveBtn.id).html(result);
            }
        });
	}
	
	function createTableInnerObject(recordFieldID, index, structure, data, title, fieldID, objectID){
	    var table = $("<table/>");
	    var row = $("<tr/>");
	    var column = $("<th/>");
	    row.append(column.text("Edit"));
	    $.each(structure, function(){
	        var column = $("<th/>");
	        if(this.perm == 1 || this.perm == 16){
                column.addClass('hidden')
            }
            row.append(column.text(this.name));
        });
	    table.append(row);
	    var position = 0;
	    $.each(data, function() {
            var row = $("<tr/>");
            var column = $("<td/>");
            var link = $("<a/>");
            link.addClass('btn');
            link.addClass('btn-icon');
            link.addClass('btn-edit');
            link.attr('href', '/objectsrecords-edit?id=' + objectID + '&record=' + this[0]);
            var iconn = $("<i/>");
            iconn.addClass('pe-7s-pen');
            link.append(iconn);
            row.append(column.append(link));
            //delete button
            var deleteBtn = $("<a/>");
            deleteBtn.addClass('btn');
            deleteBtn.addClass('btn-icon');
            deleteBtn.addClass('btn-delete');
            deleteBtn.attr('onclick', 'deleteFromImplemented(\'' + fieldID + '\', \'' + index + '.' + position + '\', \'' + this[0] + '\')');
            var iconn = $("<i/>");
            iconn.addClass('pe-7s-trash');
            deleteBtn.append(iconn);
            row.append(column.append(deleteBtn));
            var colIndex = 0;
            $.each(this, function() {
                var column = $("<td/>");
                if(structure[colIndex].perm == 1 || structure[colIndex].perm == 16){
                    column.addClass('hidden')
                }
                row.append(column.text(this));
                colIndex++;
            });
            table.append(row);
            position++;
        });
	    //alert($(table).html());
	    var innerDataLength = 0;
	    if(data != null && data != undefined){
	        innerDataLength = data.length;
	    }
	    return '<div objectid="' + objectID + '" recordfieldid="' + recordFieldID + '" fieldid="'+fieldID+'" indexfield="'+index+'" datacount="' + innerDataLength + '" class="col-md-12 col-sm-12 col-xs-12">' +
												'<div class="card">' +
													'<div class="content">' +
														'<div class="form-group">' +
															'<label>' + title + '</label><button id="newrecordbtn" type="button" class="btn" onclick="modalAddValueShow(this)">Add new record</button><table id="table-' + fieldID + '" class="table table-striped table-bordered">' + $(table).html() + '</table></div></div></div></div>';
	}
	
	function createSaveBtn(settingsValues){
	    saveBtn = '<div class="form-group"><button type="button" class="btn btn-icon btn-primary" onclick="updateFieldValue(\'' + 
	    settingsValues['type'] + '\', \'' + settingsValues['id'] + '\', ' + settingsValues['index'] + ', this)"><i class="pe-7s-diskette"></i></button></div>';
	    return saveBtn;
	}
	
JS;

$this->registerJs($js);

?>
<script>
    var zeroFieldID = '';

    function updateFieldValue(type, fieldID, mapIndex, viewElement) {
        //alert('start update')
        var updateQuery = <?= $jsonUpdate ?>;
        var newValue = getValueByType(type, viewElement);
        updateQuery['work']['value']['record'] = [{
            field: zeroFieldID,
            map: 0,
            value: "<?= $recordID ?>"
        }, {
            field: fieldID,
            map: mapIndex,
            value: newValue
        }];
        console.log(JSON.stringify(updateQuery))
        $.post('/en/drole/default/get-info', {json: JSON.stringify(updateQuery)}).done(function (data) {
            //console.log(data)
        });
    }

    function deleteFromImplemented(fieldID, mapIndex, value) {
        var updateQuery = <?= $jsonUpdate ?>;
        updateQuery['work']['operation'] = 3;
        updateQuery['work']['value']['record'] = [{
            field: zeroFieldID,
            map: 0,
            value: "<?= $recordID ?>"
        }, {
            field: fieldID,
            map: mapIndex,
            value: value
        }];
        //console.log(JSON.stringify(updateQuery))
        $.post('/en/drole/default/get-info', {json: JSON.stringify(updateQuery)}).done(function (data) {
            location.reload();
        });
    }

    function getValueByType(type, viewElement) {
        switch (type) {
            case 'text':
                return $($(viewElement).parent().parent().children()[1]).find('textarea').val();
            case 'image':
                return $($(viewElement).parent().parent().children()[0]).find('img').attr(src);
            case 'boolean':
                var isChecked = $($(viewElement).parent().parent().children()[1]).find('label').is(':checked');
                return (isChecked ? "true" : "false");
            case 'integer':
            case 'character varying':
            case 'double precision':
            case 'float':
            case 'uuid':
                return $($(viewElement).parent().parent().children()[1]).find('input').val();
            default:
                return null;
        }
    }

    function modalAddValueShow(field) {
        //console.log($(field).parent().parent().parent().parent())
        /*
        return;
        $('#currenttitle').text('object');
        */
        $('#modal-add-value').modal('show');
        //objectid="' + objectID + '" recordfieldid="' + recordFieldID + '" fieldid="'+fieldID+'" indexfield="'+index+'"
        $('#modal-add-value').attr('objectid', $(field).parent().parent().parent().parent().attr('objectid'));
        $('#modal-add-value').attr('recordfieldid', $(field).parent().parent().parent().parent().attr('recordfieldid'));
        $('#modal-add-value').attr('fieldid', $(field).parent().parent().parent().parent().attr('fieldid'));
        $('#modal-add-value').attr('indexfield', $(field).parent().parent().parent().parent().attr('indexfield'));
        $('#modal-add-value').attr('datacount', $(field).parent().parent().parent().parent().attr('datacount'));
        $('#hreftonewinnerrecord').attr('href', '/en/objectsrecords-list?id=' + $(field).parent().parent().parent().parent().attr('objectid'));
        var jsonQuery = <?= $json ?>;
        jsonQuery['permission']['object_id'] = $(field).parent().parent().parent().parent().attr('objectid');
        console.log(jsonQuery)
        getValuesForAddToView('/en/drole/default/get-info', jsonQuery);
    }

    function addValueToInnerObject(buttonrow) {
        //console.log($(buttonrow).attr('id'))
        //console.log($(buttonrow).parent().children()[0].value)
        //console.log($('#modal-add-value').attr('objectid'))
        var updateQuery = <?= json_encode(JSONRegistryFactory::updateObject(false, $objectID, array())) ?>;
        //{"field":"' . $objectsIDField['id'] . '","map":"0","value":"' . $newIDRecord . '"}
        var newIndex = $('#modal-add-value').attr('indexfield') + "." + $(buttonrow).parent().children()[0].value;
        updateQuery['work']['value']['record'].push({
            field: $('#modal-add-value').attr('recordfieldid'),
            map: '0',
            value: '<?= $recordID ?>'
        });
        updateQuery['work']['value']['record'].push({
            field: $('#modal-add-value').attr('fieldid'),
            map: newIndex,
            value: $(buttonrow).attr('id')
        });
        console.log(updateQuery['work']['value']['record'])
        $.post('/en/drole/default/get-info', {json: JSON.stringify(updateQuery)}).done(function (data) {
            //location.reload();
        });
    }

    function openNewPageWithCreate(buttonrow) {
        console.log($(buttonrow).attr('id'))
        console.log($(buttonrow).parent().children()[0].value)
        console.log($('#modal-add-value').attr('objectid'))

    }

    function getValuesForAddToView(url, data) {
        $.post(url, {json: JSON.stringify(data)}).done(function (data) {
            console.log(data)
            var x = document.getElementById("table-adds");
            var editorsHTML = '';
            if (!x.tHead) {
                var recordCount = $('#modal-add-value').attr('datacount');
                var header = x.createTHead();
                var row = header.insertRow(0);
                var body = x.createTBody();
                var blankField = document.createElement('th');
                blankField.innerHTML =
                    '<p>Add</p>';
                row.appendChild(blankField);
                $.each(data['data']['structure']['data'], function () {
                    var cell = document.createElement('th');
                    var sorting = '';
                    if (!this.nested || this.nested == "false") {
                        sorting = '<button type="button" class="btn btn-icon btn-default"><i class="fa fa-sort"></i></button>';
                    }
                    if (this.perm == 1 || this.perm == 16) {
                        cell.classList.add('hidden')
                    }
                    cell.innerHTML =
                        //'<input type="text" class="form-control th-searching" id="' + this.id + '" placeholder="Search.." onkeyup="searchKeyUp(this)">
                        '<span>' + this.name + '</span>';
                    row.appendChild(cell);
                });
                var index = 1;
                $.each(data['data']['data'], function (r, rowValue) {
                    var row = body.insertRow();
                    var fieldIndex = 0;
                    var viewIndex = 1;
                    zeroFieldID = data['data']['structure']['data'][fieldIndex].id;
                    $.each(this, function (f, fieldValue) {
                        var fieldHeader = data['data']['structure']['data'][f];
                        if (typeof(fieldHeader) != "undefined") {
                            var cell = row.insertCell(fieldIndex);
                            cell.innerHTML = '<p>' + fieldValue + '</p>';
                            if ((fieldHeader.perm == 1 || fieldHeader.perm == 16)) {
                                cell.classList.add('hidden');
                            }
                            /*else{
                                                            if(fieldHeader.nested != "false"){
                                                                editorsHTML += createTableInnerObject(fieldHeader.nested, fieldValue, fieldHeader.name, fieldHeader.id, fieldHeader.object)
                                                            }else{
                                                                var stringValue = createAbsoluteInput(fieldIndex, fieldHeader.id, fieldHeader.type, fieldHeader.name, fieldValue, fieldHeader.perm);
                                                                editorsHTML += stringValue;
                                                            }
                                                            viewIndex++;
                                                        }*/
                        }
                        fieldIndex++;
                    });
                    var cell = row.insertCell(0);
                    cell.innerHTML = '<input type="number" placeholder="position" value="' + recordCount + '" min="0" max="' + recordCount + '" class="form-control"><button type="button" onclick="addValueToInnerObject(this)" id="' + this[0] + '" class="btn btn-icon btn-add"><i class="pe-7s-plus"></i></button><button type="button" onclick="addValueToInnerObject(this)" id="' + this[0] + '" class="btn btn-icon btn-add"><i class="pe-7s-pen"></i></button>';
                    index++;
                });
            }
            //$("#values-list").html(editorsHTML)
            //alert(editorsHTML);
            //console.log(editorsHTML)
        });
    }

    function modalAddValueHide(field) {
        $('#modal-add-value').modal('hide');
    }

    function modalDelete(field) {
        $('#modal-delete-field-N').modal('show');
        /*field = $(field).parent().parent();
        //console.log(field.find('.field-class-id'));
        $('#dataobjectdelete-id').val(field.data('id'));
        document.getElementById("dataobjectdelete-name").innerHTML = field.find('.field-name>.inline-block:first-child').html();*/
    }
</script>

<div class="card">
    <div class="header">
        <h4 class="title">Objects Records Edit</h4>
    </div>
    <div class="content">
        <div class="card">
            <div class="content">
                <h5 class="card-title">Total data:</h5>
                <div class="table-responsive" id="table-objects-records">
                    <table id="table-records" class="table table-striped table-bordered">

                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="content">
                <h5 class="card-title">Fields to edit:</h5>
                <div id="values-list" class="row">

                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-add-value" objectid="" class="modal fade modal-edit-field" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 id="currenttitle" class="modal-title">Edit field </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <a id="hreftonewinnerrecord" target="_blank" class="btn btn-primary btn-block no-mr-t" href="">Create
                            new record</a>
                    </div>
                    <div class="col-md-8 col-sm-6 col-xs-12">
                        <input type="text" id="quick-search" placeholder="search" class="form-control">
                    </div>
                </div>
                <div class="table-responsive" id="table-objects-records">
                    <table id="table-adds" class="table table-striped table-bordered">

                    </table>
                </div>
            </div>
            <div class="pull-right">
                <button id="button-delete-modal" type="button" class="btn btn-primary"><i
                            class="pe-7s-trash"></i>Delete
                </button>
                <button type="button" class="btn btn-danger" onclick="modalAddValueHide(this)">
                    <span class="btn-label">
                        <i class="fa fa-times"></i> Cancel
                    </span>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modal-delete-field-N" class="modal fade modal-edit-field" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">Delete objects records</h4>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="">
                        <input type="hidden" id="dataobjectdelete-id" value="">
                        <label>Are you really want to delete objects records?
                            <div id="dataobjectdelete-name" class="form-control disabled"></div>
                            <label>
                            </label></label></div>
                    <div class="pull-right">
                        <button id="button-delete-modal" type="button" class="btn btn-primary"><i
                                    class="pe-7s-trash"></i>Delete
                        </button>
                        <button type="button" class="btn btn-danger" onclick="modalDeleteHide()"><span
                                    class="btn-label"><i class="fa fa-times"></i> Cancel</span></button>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>