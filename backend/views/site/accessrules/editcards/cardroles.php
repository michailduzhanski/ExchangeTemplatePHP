<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 5/18/2018
 * Time: 3:43 PM
 */

use common\modules\drole\models\webtools\JSONRegistryFactory;

$apiRequestURL = Yii::$app->urlManager->createAbsoluteUrl(['/']);
$pageTitle = "assemblyedit";
$currentURL = 'assembly-operations';
$currentId = $assembly['id'];
$objectId = $object['id'];
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$listTemplate = '<option value="{id}">{name}</option>';
$serviceTemplate = '<option value="{b5afac44-2df9-42b5-88c3-694e63d3dd0a}">{d76789d5-3812-46a1-9a63-2125802b632f} - "{0174803b-dd3b-4dea-ada0-0b68b9d31355}"</option>';
$presentRolesTemplate = '<div class="inline-btns">
                    <button type="button" class="btn-icon btn-delete">-</button>
                    <a href=""><h5>Dynamic role 1</h5></a>
                </div>';
Yii::$app->params['templates']['companies'] = $listTemplate;
Yii::$app->params['templates']['services'] = $serviceTemplate;
Yii::$app->params['templateMaps']['roles'] = ['ea4d5b30-1c60-4bce-a2cd-452e9b075434', 'f5aa4922-2a94-464b-b658-d8893fb8e614'];
Yii::$app->params['templateMaps']['services'] = ['b5afac44-2df9-42b5-88c3-694e63d3dd0a', 'd76789d5-3812-46a1-9a63-2125802b632f', '0174803b-dd3b-4dea-ada0-0b68b9d31355'];
Yii::$app->params['templates']['roles'] = '<div data-id="{ea4d5b30-1c60-4bce-a2cd-452e9b075434}" class="module-field-box">
                <h5>{f5aa4922-2a94-464b-b658-d8893fb8e614}</h5>{buttonaction}<p>{description}</p>
            </div>';
Yii::$app->params['templates']['presentroles'] = '<div data-id="{role_id}" class="inline-btns">
                    {mainicon}
                    <button type="button" {actionclick} class="btn-icon btn-delete">-</button>
                    <a href="/en/dataobject-edit?id=97086af0-956b-4380-a385-ea823cff377a&record={role_id}"><h5>{name}</h5></a>
                </div>';
$companiesJson = JSONRegistryFactory::getRecordsListFromObject(true, '2ed029b6-d745-4f85-8d9f-2dccd2a7da37', '');
$serviceJson = JSONRegistryFactory::getRecordsListFromObject(true, '3db2f640-e01a-42ac-904e-87a46e0373fd', '');
$roleJson = JSONRegistryFactory::getRecordsListFromObject(true, '97086af0-956b-4380-a385-ea823cff377a', '');

$js = <<<JS

/*$.waterfall(
    getListObjectContentSimple('$apiRequestURL/drole/default/get-info', $companiesJson, 'companies', '$pageTitle'),
    getListObjectContentComplex('$apiRequestURL/drole/default/get-info', $serviceJson, 'services', '$pageTitle'),
    getListObjectContentPresentRoles('$apiRequestURL/assembly-operations')
    ).fail(function(error) {
	console.log('fail');
	console.log(error)
})
.done(function() {
	console.log('success');
	console.log(arguments)
})*/
/*getListObjectContentSimple('$apiRequestURL/drole/default/get-info', $companiesJson, 'companies', '$pageTitle', function(){
    getListObjectContentPresentRoles('$apiRequestURL/assembly-operations');
});*/
arrayOfFunctions = [function(nextfunction){
    getListObjectContentSimple('$apiRequestURL/drole/default/get-info', $companiesJson, 'companies', '$pageTitle', nextfunction, 1);
},function(nextfunction){    
    getListObjectContentComplex('$apiRequestURL/drole/default/get-info', $serviceJson, 'services', '$pageTitle', nextfunction, 2);
},function(){
    getListObjectContentPresentRoles('$apiRequestURL/assembly-operations');
}];
globalQueue(arrayOfFunctions);

$('#quick-search-roles').keyup(function() {
    updatePossibilitiesRoles();
}).keyup();

function getListObjectContentPresentRoles(url)
{   
    var elementCompanies = document.getElementById("table-companies");
    var elementServices = document.getElementById("table-services");
    var postvalue = {
        '$csrfParam': '$csrfToken',
        AssemblyRolesListByServiceCompany: {
            objectid: "$objectId",
            assemblyid: "$currentId",
            companyid: elementCompanies.options[elementCompanies.selectedIndex].value,
            serviceid: elementServices.options[elementServices.selectedIndex].value
        }
    }
    
    $.post(url, postvalue).done(function(data){
        var html = '';
        var element = 'presentroles';
        if(data != '{}'){
            $.each(JSON.parse(data), function () {
                var item = this;
                var htmlRow = templates[element];
                htmlRow = htmlRow.split(`{role_id}`).join(item['role_id']);
                htmlRow = htmlRow.split(`{name}`).join(item['name']);
                var mainicon = '';
                var actionclick = 'onclick="addRoleToAssembly(this, \'true\')"';
                if(item['active'] == 1){
                    mainicon = '<i class="fa fa-cogs bordered-icon" data-toggle="tooltip" data-placement="top" title="This is Main assembly." aria-hidden="true"></i>';
                    actionclick = 'onclick="setThisAssemblyAsMain(this, \'true\')"';
                }
                htmlRow = htmlRow.split(`{mainicon}`).join(mainicon);
                htmlRow = htmlRow.split(`{actionclick}`).join(actionclick);
                html += htmlRow;
            });
            $('#table-' + element).html(html);
        }else{
            $('#table-' + element).html('<div class="inline-btns"><h5>Not found by that company and service.</h5></a></div>');
        }
        updatePossibilitiesRoles();
    });
}

$("#table-companies" ).change(function() {
    getListObjectContentPresentRoles('$apiRequestURL/assembly-operations');
});

function getListObjectContentComplexRoles(url, request, element, page = false)
{
    $.post(url, {json:JSON.stringify(request)}).done(function(data){
        var html='';
        var structure = data['data']['structure']['data'];
        var structureMap = getStructureIDMapWithCheck(structure, data['data']['work']['stime'], page + "_" + element);
        var neededElements = templateMaps[element];
        $.each(data['data']['data'],function(){
            var item = this;
            var htmlRow = templates[element];
            var isPresent = getValueFromPresentRoles(item[0]);
            var actionToken = '';
            if(isPresent == 0){
                actionToken = '<button type="button" onclick="setThisAssemblyAsMain(this, \'false\')" class="btn-icon btn-edit btn-icon-whis-words btn-info"><i class="fa fa-podcast"></i> Set as main</button>';
            }
            if(isPresent == -1){
                actionToken = '<button type="button" onclick="addRoleToAssembly(this, \'false\')" class="btn-icon btn-edit btn-icon-whis-words"><i class="pe-7s-plus"></i> Add to assembly</button>';
            }
            htmlRow = htmlRow.split(`{buttonaction}`).join(actionToken);
            $.each(neededElements, function(key){
                var indexItem = neededElements[key];
                var fieldValue = getSingleElementFromDataByIDMap(item, structureMap, indexItem);
                htmlRow = htmlRow.split(`{\${indexItem}}`).join(fieldValue);
            });
            html+=htmlRow;
        });
        $('#table-' + element).html(html);
    });
}

function getValueFromPresentRoles(roleID){
    var list = $('#table-presentroles');
    var presentRoles = document.getElementById("table-presentroles").getElementsByTagName("div");
    var result = -1;
    $.each(presentRoles, function(){
        if(!$(this).data('id')){
            return;
        }
        if($(this).data('id').localeCompare(roleID) == 0){
            if(this.getElementsByTagName("i").length > 0){
                result = 1;
            }else{
                result = 0;
            }
        }
    });
    return result;
}

function updatePossibilitiesRoles(){
    var thisJson = $roleJson;
    thisJson['filters'][0]['common']=$('#quick-search-roles').val();
    getListObjectContentComplexRoles('$apiRequestURL/drole/default/get-info', thisJson, 'roles');
}

JS;
$this->registerJs($js);

?>
<script>
    function addRoleToAssembly(role, deleteAction) {
        var element = $(role).parent().data('id');
        var elementCompanies = document.getElementById("table-companies");
        var elementServices = document.getElementById("table-services");
        var postvalue = {
            '<?= $csrfParam ?>': '<?= $csrfToken ?>',
            AssemblyAddRoleToCurrent: {
                objectid: "<?= $objectId ?>",
                assemblyid: "<?= $currentId ?>",
                roleid: element,
                companyid: elementCompanies.options[elementCompanies.selectedIndex].value,
                serviceid: elementServices.options[elementServices.selectedIndex].value,
                deletevalue: deleteAction
            }
        };
        $.post('<?= $currentURL ?>', postvalue).done(function (data) {
            /*var thisJson = '$roleJson';
            thisJson['filters'][0]['common']=$('#quick-search-roles').val();
            getListObjectContentComplexRoles('$apiRequestURL/drole/default/get-info', thisJson, 'roles');*/
        });
    }

    function setThisAssemblyAsMain(role, deleteAction) {
        var element = $(role).parent().data('id');
        var elementCompanies = document.getElementById("table-companies");
        var elementServices = document.getElementById("table-services");
        var postvalue = {
            '<?= $csrfParam ?>': '<?= $csrfToken ?>',
            AssemblySetRoleAsMain: {
                objectid: "<?= $objectId ?>",
                assemblyid: "<?= $currentId ?>",
                roleid: element,
                companyid: elementCompanies.options[elementCompanies.selectedIndex].value,
                serviceid: elementServices.options[elementServices.selectedIndex].value,
                deletevalue: deleteAction
            }
        };
        $.post('<?= $currentURL ?>', postvalue).done(function (data) {
            console.log(data)
        });
    }
</script>

<div class="card">
    <div class="header">
        <h4 class="title">Subject</h4>
    </div>
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" id="quick-search" placeholder="<?= Yii::t('backend', 'Quick search') ?>" class="form-control">
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <select id="table-services" class="form-control">
                    <option>Not</option>
                </select>
            </div>
        </div>
        <div class="table-responsive over-y-scroll">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>1 th</th>
                        <th>2 th</th>
                        <th>3 th</th>
                        <th>4 th</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                    <tr>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                        <td>123</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-primary"><i class="pe-7s-diskette"></i> Save changes</button>
    </div>
</div>

