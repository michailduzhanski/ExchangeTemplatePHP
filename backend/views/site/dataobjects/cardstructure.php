<?php
/* @var $signature string */
/* @var $this \yii\web\View */
/* @var $ctime mixed */
/* @var $login string */

/* @var $object bool */

use common\modules\drole\models\webtools\JSONRegistryFactory;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;

$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$fieldsTemplate = '<div class="module-field-box" data-id="{field}">
    <h5 class="field-name">
  <div class="inline-block">{name}</div>
  <div class="inline-block relative">
   <div id="{field}" class="grey-text">{{field}}</div>
  </div>
 </h5>
    <p class="classname field-class">Class: <span>{type}</span></p>
    <p class="fieldclass" hidden>{class}</p>
    <p class="field-description">{description}</p>
    {actions}
</div>';
Yii::$app->params['templates']['fields'] = $fieldsTemplate;

$fieldsJson = JSONRegistryFactory::getLocalJSONRegistryForStructure(true, $object['id']);
Yii::$app->params['json']['fields'] = $fieldsJson;

$js = <<<JS
        var fieldsJson = $fieldsJson
$('#search-fields-structure').keyup(function() {
	//console.log($(this).val());
	
	console.log($(this).val());
	fieldsJson['filters'][0]['common']=$(this).val();
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
$currentURL = 'objects-operations';
$currentId = $object['id'];
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
<div class="card">
    <div class="header">
        <h4 class="title">Fields list</h4>
        <p class="category">Choice current field from the list</p>
    </div>
    <div class="content">
        <div class="search-and-filter-row">
            <?php
            $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
            if($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin']){
                echo '<div class="row">
                    <div class="col-md-7 col-sm-6 col-xs-12">
                        <input id="search-fields-structure" type="text" placeholder="Quick search" class="form-control">
                    </div>
                    <div class="col-md-5 col-sm-6 col-xs-12">
                        <a href="" data-toggle="modal" data-target=".modal-create-field" class="btn btn-primary btn-block"
                           onclick="modalEdit(this)"><i class="pe-7s-plus"></i>
                            Create field</a>
                    </div>
                </div>';
            }else if($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['admin'] ||
                $dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superuserglobal'] ||
                $dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superuserlocal']){
                echo '<input id="search-fields-structure" type="text" placeholder="Quick search" class="form-control">';
            }
            ?>
        </div>
        <div id="table-fields" class="box-whis-scroll full-height ps-container ps-theme-default ps-active-y">
        </div>
    </div>
</div>