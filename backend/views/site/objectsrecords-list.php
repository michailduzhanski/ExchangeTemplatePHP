<?php
/**
 * Created by PhpStorm.
 * User: ENGINEER
 * Date: 6/19/2018
 * Time: 1:08 PM
 */

use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\webtools\JSONRegistryFactory;

$this->title = 'Object\'s records list';

$objectID = Yii::$app->request->get('id');
$objectNameArray = RegistryObjects::getObjectNameByID($objectID);
if (!isset($objectNameArray->name)) {
    header("Location: /objects-list");
    exit;
}

$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectID);

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$currentURL = 'objectsrecords-operations';
$newIDRecord = \common\models\UUIDGenerator::v4();
$sql = "select id from " . $objectNameArray->name . "_structure_fields where name = 'id'";
$objectsIDField = \Yii::$app->db->createCommand($sql)->queryOne();
if (!$objectsIDField || count($objectsIDField) < 1) {
    header("Location: /objects-list");
    exit;
}

$jsonUpdate = JSONRegistryFactory::updateObject(true, $objectID, '');
$newRecordBody = JSONRegistryFactory::updateObject(false, $objectID,
    json_decode('[{"field":"' . $objectsIDField['id'] . '","map":"0","value":"' . $newIDRecord . '"}]', true));
/*if ($objectID == '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24') {
    $newRecordBody['permission']['contact_id'] = $newIDRecord;
} else*/
if ((Yii::$app->request->get('parent') != null && Yii::$app->request->get('forrecord') != null && Yii::$app->request->get('parent') == '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24')) {
    $newRecordBody['permission']['contact_id'] = Yii::$app->request->get('forrecord');
}

$newRecordBody = json_encode($newRecordBody);

$js = <<<JS
	var json = $json;
	function getContent(url,data)
	{
	    $.post(url,{json:JSON.stringify(data)}).done(function(data){
			 var x = document.getElementById("table-records");
			 if (!x.tHead) {    
                var header = x.createTHead(); 
                var row = header.insertRow(0);
                var body = x.createTBody();
                var blankField = document.createElement('th');
                blankField.innerHTML = 
                    '<p>EDIT</p>';
                row.appendChild(blankField);
                if(data['data']['structure'] == undefined) return;
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
                    '<input type="text" class="form-control th-searching" id="' + this.id + '" placeholder="Search.." onkeyup="searchKeyUp(this)"><span>' + this.name + sorting + '</span>';
                    row.appendChild(cell);
                });
                var index = 1;
                //console.log(data['data']['data'])
                $.each(data['data']['data'],function(){                    
                    var row = body.insertRow();                    
                    var fieldIndex = 0;
                    $.each(this,function(){   
                        var fieldHeader = data['data']['structure']['data'][fieldIndex];
                        if(typeof(fieldHeader) != "undefined"){
                            var cell = row.insertCell(fieldIndex);
                            cell.innerHTML = '<p>' + this + '</p>';
                            if((fieldHeader.perm == 1 || fieldHeader.perm == 16)){
                                cell.classList.add('hidden')
                            }
                        }
                        fieldIndex++;
                    });
                    var cell = row.insertCell(0);
                    cell.innerHTML = '<a href="/objectsrecords-edit?id=$objectID&record=' + this[0] + '" id="' + this[0] + '" class="btn btn-icon btn-edit"><i class="pe-7s-pen"></i></a>';
                    index++;
                });
             }
		});
	}
	
	getContent('/en/drole/default/get-info', json);
	
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
	
	$('#newrecordbtn').click(function () {
        var newIDRecord = '$newIDRecord';
        var newRecordBody = '$newRecordBody';
        //console.log(newRecordBody)
        $.post('/en/drole/default/get-info', {json: newRecordBody}).done(function (data) {
            if(data.result == 200)
                window.open(window.location.protocol + '//' + window.location.hostname + "/en/objectsrecords-edit?id=$objectID&record=" + newIDRecord, "_blank");
            //console.log(data)
        })
        //window.location.href = window.location.protocol + window.location.hostname;
        
    })
	new Clipboard('.btn-clipboard');
	
	
	
JS;

$this->registerJs($js);

?>
<script>


    function searchKeyUp(searchField) {
        var row = $(searchField).parent().parent().children();
        //console.log(row)
        var result = [];
        $.each(row, function () {
            if (typeof($(this.getElementsByClassName('th-searching')).attr('id')) != "undefined" &&
                typeof($(this.getElementsByClassName('th-searching')).val()) != "undefined" &&
                $.trim($(this.getElementsByClassName('th-searching')).val()) !== '' &&
                $.trim($(this.getElementsByClassName('th-searching')).val()).length > 1) {
                var index = $(this).index() - 1;
                result.push({
                    map: index,
                    comp: 7,
                    value: $(this.getElementsByClassName('th-searching')).val()
                });
            }
        });
        //if (result.length > 0) {
        var jsonRequest = <?= $json ?>;
        jsonRequest['filters'][1] = {'special': result};
        $.post('/en/drole/default/get-info', {json: JSON.stringify(jsonRequest)}).done(function (data) {
            var x = document.getElementById("table-records");
            $("#table-records > tbody").html("");
            var body = x.getElementsByTagName('tbody')[0];
            var row = body.insertRow();
            var index = 1;
            $.each(data['data']['data'], function () {
                var row = body.insertRow();
                var fieldIndex = 0;
                $.each(this, function () {
                    var fieldHeader = data['data']['structure']['data'][fieldIndex];
                    if (typeof(fieldHeader) != "undefined") {
                        var cell = row.insertCell(fieldIndex);
                        cell.innerHTML = '<p>' + this + '</p>';
                        if (fieldHeader.perm == 1 || fieldHeader.perm == 16) {
                            cell.classList.add('hidden')
                        }
                    }
                    fieldIndex++;
                });
                var cell = row.insertCell(0);
                cell.innerHTML = '<a href="/objectsrecords-edit?id=<?= $objectID ?>&record=' + this[0] + '" id="' + this[0] + '" class="btn btn-icon btn-edit"><i class="pe-7s-pen"></i></a>';
                //cell.innerHTML = '<button id="' + this[0] + '" type="button" class="btn btn-icon btn-edit"><i class="pe-7s-pen"></i></button>';
                index++;
            });
        });
        //}
    }
</script>
<div class="card fixed-card">
    <div class="header">
        <h4 class="title pull-left">Object <?= $objectNameArray->name; ?>. Data records list.</h4>
        <button id="newrecordbtn" type="button" class="btn">Add new record</button>
        <div class="btn-group pull-right" role="group" aria-label="">
            <button type="button" class="btn btn-outline-secondary btn-sm">1</button>
            <button type="button" class="btn btn-outline-secondary btn-sm">2</button>
            <button type="button" class="btn btn-outline-secondary btn-sm">3</button>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="content no-pd-t">
        <div class="table-responsive" id="table-objects-records">
            <table class="table table-bordered small-table" id="table-records">

            </table>
        </div>
        <div class="btn-group pull-right" role="group" aria-label="">
            <button type="button" class="btn btn-outline-secondary btn-sm">1</button>
            <button type="button" class="btn btn-outline-secondary btn-sm">2</button>
            <button type="button" class="btn btn-outline-secondary btn-sm">3</button>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
