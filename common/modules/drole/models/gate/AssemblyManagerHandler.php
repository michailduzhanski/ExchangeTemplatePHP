<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 6/6/2018
 * Time: 4:32 PM
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;
use yii\data\SqlDataProvider;


class AssemblyManagerHandler
{
    public static function getRolesArrayValuesForObjectAndAssembly($objectID, $assemblyID, $companyID, $serviceID)
    {
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
        if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin'] || $dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superuserglobal']) {
            $roleSubquery = "";
        } else if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['admin'] || $dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superuserlocal']) {
            $roleSubquery = " and registry_drole_base.role_id in (SELECT role_data_use.id FROM role_data_use WHERE role_data_use.level > '1')";
        } else {
            return '';
        }
        $sql = "select registry_drole_base.role_id, registry_drole_assembly.active, (SELECT name FROM role_data_use where 
role_data_use.id = registry_drole_base.role_id) as name from registry_drole_base inner join 
registry_drole_assembly on registry_drole_base.id = registry_drole_assembly.drole_id where registry_drole_assembly.object_id = '$objectID' 
and registry_drole_assembly.assembly_id = '$assemblyID' and registry_drole_base.company_id = '$companyID' and registry_drole_base.service_id = '$serviceID'" . $roleSubquery;
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray) {
            return false;
        }
        return $objectsArray;

        /*$resultString = '';
        foreach ($objectsArray as $roleRecord) {
            $mainIcon = '';

            if ($roleRecord['active'] == 1) {
                $mainIcon = '<i class="fa fa-cogs bordered-icon" data-toggle="tooltip" data-placement="top" title="This is Main assembly." aria-hidden="true"></i>';
            }
            $resultString .= '<div id="' . $roleRecord['role_id'] . '" class="inline-btns">' .
                $mainIcon .
                '<button type="button" class="btn-icon btn-delete">-</button>
                    <a href="/en/dataobject-edit?id=97086af0-956b-4380-a385-ea823cff377a&record=' .
                $roleRecord['role_id'] . '"><h5>' . $roleRecord['name'] . '</h5></a>
                </div>';
        }
        return $resultString;*/
    }

    public static function checkElementInArray($idRole, $arrayPresentRoles)
    {
        foreach ($arrayPresentRoles as $record) {
            if ($record[0] == $idRole) {
                return $record[1];
            }
        }
        return -1;
    }

}