<?php
/* @var $signature string */
/* @var $this \yii\web\View */
/* @var $ctime mixed */
/* @var $login string */

/* @var $object bool */

use common\modules\drole\models\webtools\JSONRegistryFactory;

$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$fieldsTemplate = '<div class="module-field-box" data-id="{field}">
                        <div class="inline-row arrow-box {visible}">
                            <button type="button" class="btn btn-arrow" onclick="updateStructureFieldIndexPermissionByArrow(this, -1);"><i class="pe-7s-angle-up"></i></button>
                            <button type="button" class="btn btn-arrow" onclick="updateStructureFieldIndexPermissionByArrow(this, 1);"><i class="pe-7s-angle-down"></i></button>
                        </div>
                        <div class="inline-row">
                            <h5 class="field-name">{name}</h5>
				             <p class="classname field-class">Class: <span>{type}</span><button type="button" class="btn-icon btn-edit btn-icon-whis-words mr-left" data-toggle="modal" data-target=".modal-add-assembly"><i class="fa fa-code-fork"></i> Switch assembly</button></p>
				             <p class="fieldclass" hidden>{class}</p>
                            <form class="inline-form">
                                <label><input class="usefparam" type="checkbox" {usef} onclick="updateStructureFieldIndexPermissionByCheckBox(this);"> Use</label>
                                <label><input class="visibleparam" type="checkbox" {visible} onclick="updateStructureFieldIndexPermissionByCheckBox(this);"> Show</label>
                                <label><input class="editparam" type="checkbox" {edit} onclick="updateStructureFieldIndexPermissionByCheckBox(this);"> Edit</label>
                                <label><input class="deleteparam" type="checkbox" {delete} onclick="updateStructureFieldIndexPermissionByCheckBox(this);"> Delete</label>
                                <label><input class="insertparam" type="checkbox" {insert} onclick="updateStructureFieldIndexPermissionByCheckBox(this);"> Insert</label>
                            </form>
                            <p class="field-description">{description}</p>
                        </div>
                    </div>';
Yii::$app->params['templates']['fields'] = $fieldsTemplate;

$fieldsJson = JSONRegistryFactory::getLocalJSONRegistryForStructure(true, $object['id']);
Yii::$app->params['json']['fields'] = $fieldsJson;
$currentAssemblyID = $assembly['id'];
$js = <<<JS
        var fieldsJson = $fieldsJson
$('#search-fields-structure').keyup(function() {
	fieldsJson['filters'][0]['common']=$(this).val();
	fieldsJson['work']['value']['work_id']= "$currentAssemblyID";
	fieldsJson['work']['value']['table']= "assemblydrole";
	getContent('$apiRequestURL/drole/default/get-info',fieldsJson,'fields');
}).keyup();
JS;

$this->registerJs($js);

$objectsTemplate = '<option value="{id}">{name}</option>';
Yii::$app->params['templates']['objects'] = $objectsTemplate;
$objectsJson = JSONRegistryFactory::getObjectsList(true, "objects");

Yii::$app->params['json']['objects'] = $objectsJson;

$js = <<<JS
        var objectsJson = $objectsJson
	    getContent('$apiRequestURL/drole/default/get-info',objectsJson,'objects');
JS;

$this->registerJs($js);
$currentURL = 'assembly-operations';
$currentId = $object['id'];
//$assemblyId = $assembly['id'];
$value = json_encode($object);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;


$js = <<<JS
    $('#button-save-modal').click(function () {
        var postname = document.getElementById("dataobjectstructure-name").value;
        var postdescription = document.getElementById("dataobjectstructure-description").value;
        var postid = document.getElementById("dataobjectstructure-id").value;
        var postclassid = getClassID();
        if (postclassid == null) {
            return;
        }
        var postvalue = {
            '$csrfParam': '$csrfToken',
            StructureFieldValues: {
                objectid: "$currentId",
                id: postid,
                classid: postclassid,
                name: postname,
                description: postdescription
            }
        };
        $.post('$currentURL', postvalue).done(function (data) {
            console.log(data);
        });
    });

    $('#button-delete-modal').click(function () {
        var postid = document.getElementById("dataobjectdelete-id").value;
        var postvalue = {
            '$csrfParam': '$csrfToken',
            StructureFieldDelete: {
                objectid: "$currentId",
                id: postid,
                checkusage: false
            }
        };
        $.post('$currentURL', postvalue).done(function (data) {
            //console.log(data);
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

    function getClassID() {
        if (document.getElementById("radio-classes").checked) {
            return document.getElementById("table-classes")[document.getElementById("table-classes").selectedIndex].value;
        }
        if (document.getElementById("radio-objects").checked) {
            return document.getElementById("table-objects")[document.getElementById("table-objects").selectedIndex].value;
        }
        return null;
    }
    
    $('.modal-delete-field').click(function () {
        console.log('test');
    });
    
JS;

$this->registerJs($js);

?>
<script>
    function updateStructureFieldIndexPermissionByArrow(row, direct) {
        //console.log($(row).parent().parent())
        var element = $(row).parent().parent();
        var currentIndex = element.parent().children().index(element);

        generalUpdateElement(element, direct);
    }

    function updateStructureFieldIndexPermissionByCheckBox(row) {
        var element = $(row).parent().parent().parent().parent();
        //var index = element.parent().indexOf(element);
        //console.log(Array.from(element.parent()).indexOf(element))
        //console.log(element.parent().children().index(element));
        generalUpdateElement(element, 0)
    }

    function generalUpdateElement(element, direct) {
        var currentIndex = element.parent().children().index(element);
        /*if (currentIndex > 0 && currentIndex < 2) {
            return;
        }*/
        if ((currentIndex  < 2 && direct == -1)) {
            return;
        }

        var usefparam = element.find('.usefparam').is(":checked");
        if (!usefparam && direct != 0) {
            return;
        }
        var visibleparam = element.find('.visibleparam').is(":checked");
        var editparam = element.find('.editparam').is(":checked");
        var deleteparam = element.find('.deleteparam').is(":checked");
        var insertparam = element.find('.insertparam').is(":checked");
        if ((currentIndex == 1 || currentIndex == 2) && (editparam || deleteparam || insertparam)) {
            return;
        }
        var objectID = "<?= $currentId ?>";
        var assemblyID = "<?= $currentAssemblyID ?>";
        var postvalue = {
            '<?= $csrfParam ?>': '<?= $csrfToken ?>',
            AssemblyStructureFieldValues: {
                objectid: objectID,
                assemblyid: assemblyID,
                id: element.data().id,
                turn: (currentIndex + direct),
                usef: usefparam,
                visible: visibleparam,
                edit: editparam,
                delete: deleteparam,
                insert: insertparam
            }
        };
        $.post('<?= $currentURL ?>', postvalue).done(function (data) {
            simulateKey($('#search-fields-structure'));
        });
    }

    function simulateKey(iElement) {
        //$(iElement).focus();

        var e = jQuery.Event("keyup");
        e.which = 46;
        e.keyCode = 46;
        e.charCode = 0;
        /*
                $(iElement).keyup(function () {
                    console.log("keyup element");
                });
                */
        $(iElement).trigger(e);

    }
</script>
<div class="card">
    <div class="header">
        <h4 class="title">Fields list</h4>
        <p class="category">Choice current field from the list</p>
    </div>
    <div class="content">
        <div class="search-and-filter-row">
            <div class="row">
                <div class="col-md-7 col-sm-6 col-xs-12">
                    <input id="search-fields-structure" type="text" placeholder="Quick search" class="form-control">
                </div>
                <div class="col-md-5 col-sm-6 col-xs-12">
                    <a href="" data-toggle="modal" data-target=".modal-create-field" class="btn btn-primary btn-block"
                       onclick="modalEdit(this)"><i class="pe-7s-plus"></i>
                        Create field</a>
                </div>
            </div>
        </div>
        <div id="table-fields" class="box-whis-scroll full-height ps-container ps-theme-default ps-active-y">
        </div>
    </div>
</div>

<div class="modal fade modal-add-assembly" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Switch assembly</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-3 col-sm-6 col-xs-12">
						<label>Role</label>
						<select class="form-control">
							<option>Role1</option>
							<option>Role2</option>
							<option>Role3</option>
						</select>
					</div>
					<div class="col-md-9 col-sm-6 col-xs-12">
						<label>Assembly</label>
						<select class="form-control">
							<option>Assembly1</option>
							<option>Assembly2</option>
							<option>Assembly3</option>
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>