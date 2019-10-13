<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 6/11/2018
 * Time: 12:18 PM
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\implemented\RecordUpdate;
use common\modules\drole\models\object\LogObjectHandler;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\RegistryObjects;

class UpdateDataObjectHandler
{
    public static function updateRecordValuesByID($jsonIncomingObject)
    {
        if (!isset($jsonIncomingObject['work']['operation']) || $jsonIncomingObject['work']['operation'] != 2)
            return APIHandler::getErrorArray(404, "Operation is not for update.");
        $recordValues = null;
        if (isset($jsonIncomingObject['work']['value']['record'])) {
            $recordValues = $jsonIncomingObject['work']['value']['record'];
        } else {
            return APIHandler::getErrorArray(404, "Not found values for update.");
        }
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole($jsonIncomingObject['permission']['drole_id']);
        //check permission, and add right value to result array
        $currentAssemblyStructureArray = StructureOperationHandler::getFastStructureWithCheck(
            $jsonIncomingObject['permission']['object_id'], $jsonIncomingObject['permission']['drole_id']);
        if (!$currentAssemblyStructureArray) {
            return APIHandler::getErrorArray(404, "Structure not found.");
        }
        if (strpos($currentAssemblyStructureArray, '"') === 0) {
            $currentAssemblyStructureArray = substr($currentAssemblyStructureArray, 1, strlen($currentAssemblyStructureArray) - 2);
        }
        $objectStructure = json_decode($currentAssemblyStructureArray, true);
        $isInsert = false;
        $objectName = RegistryObjects::getObjectNameByID($jsonIncomingObject['permission']['object_id'])->name;

        $sql = "select " . $objectName . "_data_use.*, " . $objectName . "_record_own.contact_id from " . $objectName . "_data_use join " . $objectName .
            "_record_own on " . $objectName . "_data_use.id = " . $objectName . "_record_own.id where " . $objectName . "_data_use.id = '" . $jsonIncomingObject['work']['value']['record'][0]['value'] . "'";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$objectsArray || $objectsArray == '') {
            $isInsert = true;
        }

        if (($isInsert && ($objectStructure[0]['perm'] != 16 && $objectStructure[0]['perm'] < 4)) || ($isInsert && $objectStructure[0]['perm'] < 2)) {
            return APIHandler::getErrorArray(404, "You do not have permission for the operation." . $objectStructure[0]['perm']);
        }
        if (!$isInsert && $dynamicRoleArray['role_id'] != '1d021b86-41c6-47c1-a38e-0aa89b98dc28' && $dynamicRoleArray['role_id'] != '65fe5829-ff9a-4b58-aa76-d8a92eaeee7e' &&
            $objectsArray['contact_id'] != $jsonIncomingObject['permission']['contact_id']) {
            return APIHandler::getErrorArray(404, "You do not have permission for the operation. role.");
        }
        if ($isInsert) {
            $sql = "insert into " . $objectName . "_record_own (id, company_id, service_id, contact_id) values 
            ('" . $jsonIncomingObject['work']['value']['record'][0]['value'] . "', 
            '" . $dynamicRoleArray['company_id'] . "', 
            '" . $dynamicRoleArray['service_id'] . "', 
            '" . $jsonIncomingObject['permission']['contact_id'] . "')";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "insert into " . $objectName . "_data_use (id) values ('" . $jsonIncomingObject['work']['value']['record'][0]['value'] . "')";
            \Yii::$app->db->createCommand($sql)->execute();
            LogObjectHandler::updateLogRecordForObject($objectName, "data_use", $jsonIncomingObject['work']['value']['record'][0]['value'],
                $jsonIncomingObject['work']['value']['record'][0]['field'], '', $jsonIncomingObject['work']['value']['record'][0]['value'],
                $jsonIncomingObject['permission']['drole_id'], $jsonIncomingObject['permission']['contact_id']);
        } else {
            if (!APIHandler::checkServicePermissionForUpdate($objectsArray['contact_id'], $jsonIncomingObject['permission']['contact_id'], $jsonIncomingObject['permission']['object_id'], $dynamicRoleArray['role_id'])) {
                return APIHandler::getErrorArray(404, "You have not permission for update.");
            }
            $sql = "update " . $objectName . "_data_use set date_change = date_part('epoch', now()) where id = '" . $jsonIncomingObject['work']['value']['record'][0]['value'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();
        }

        for ($i = 1; $i < count($jsonIncomingObject['work']['value']['record']); $i++) {
            $mapValuesArray = explode('.', $jsonIncomingObject['work']['value']['record'][$i]['map']);
            $valueInField = $jsonIncomingObject['work']['value']['record'][$i]['value'];
            /*if (count($mapValuesArray) > 1 && $objectStructure[$mapValuesArray[0]]['nested'] != "false") {
                //insert into nested object
                $nestedObjectName = RegistryObjects::getObjectNameByID($objectStructure[$mapValuesArray[0]]['object'])->name;
                $currentFieldName = $objectStructure[$mapValuesArray[0]]['name'];
                $currentFieldValue = null;
                $sql = "select $currentFieldName from " . $objectName . "_data_use where id = '" . $jsonIncomingObject['work']['value']['record'][0]['value'] . "'";
                $currentFieldValue = \Yii::$app->db->createCommand($sql)->queryOne();
                if (!$currentFieldValue || count($currentFieldValue) < 1) {
                    //insert
                    $currentFieldValue = null;
                } else {
                    //update
                    $currentFieldValue = $currentFieldValue[$currentFieldName];
                }
                $valueInField = ObjectOperationsHandler::insertIntoImplementedRecord($objectStructure[$mapValuesArray[0]]['object'], $nestedObjectName, $jsonIncomingObject['permission']['object_id'],
                    $valueInField, $jsonIncomingObject['work']['value']['record'][$i]['field'], $mapValuesArray[1], $currentFieldValue);
            }*/
            if (($objectStructure[$mapValuesArray[0]]['perm'] != 36 && $objectStructure[$mapValuesArray[0]]['perm'] != 46 &&
                $objectStructure[$mapValuesArray[0]]['perm'] != 56 && $objectStructure[$mapValuesArray[0]]['perm'] < 3)) {
                continue;
            }
            $sql = "update " . $objectName . "_data_use set " . $objectStructure[$mapValuesArray[0]]['name'] . " = '" . $valueInField . "' where id = '" . $jsonIncomingObject['work']['value']['record'][0]['value'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();

            LogObjectHandler::updateLogRecordForObject($objectName, "data_use", $jsonIncomingObject['work']['value']['record'][0]['value'],
                $jsonIncomingObject['work']['value']['record'][$i]['field'], '', $valueInField,
                $jsonIncomingObject['permission']['drole_id'], $jsonIncomingObject['permission']['contact_id']);
            //self::getTimeMarker("before update all", $startTime);
            RecordUpdate::updateAllImplementedRecords($jsonIncomingObject['permission']['object_id'], $objectName,
                $jsonIncomingObject['work']['value']['record'][0]['value'], $jsonIncomingObject['permission']['drole_id'], true);

        }
        return APIHandler::getErrorArray(200, "success");
    }

    public static function deleteInnerRecord($jsonIncomingObject)
    {
        if (!isset($jsonIncomingObject['work']['operation']) || $jsonIncomingObject['work']['operation'] != 3)
            return APIHandler::getErrorArray(404, "Operation is not for delete inner.");
        $recordValues = null;
        if (isset($jsonIncomingObject['work']['value']['record'])) {
            $recordValues = $jsonIncomingObject['work']['value']['record'];
        } else {
            return APIHandler::getErrorArray(404, "Not found values for update.");
        }
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole($jsonIncomingObject['permission']['drole_id']);
        //check permission, and add right value to result array

        $currentAssemblyStructureArray = StructureOperationHandler::getFastStructureWithCheck(
            $jsonIncomingObject['permission']['object_id'], $jsonIncomingObject['permission']['drole_id']);
        if (!$currentAssemblyStructureArray) {
            return APIHandler::getErrorArray(404, "Structure not found.");
        }
        if (strpos($currentAssemblyStructureArray, '"') === 0) {
            //echo substr($currentAssemblyStructureArray, 1, strlen($currentAssemblyStructureArray) - 2); //exit;
            $currentAssemblyStructureArray = substr($currentAssemblyStructureArray, 1, strlen($currentAssemblyStructureArray) - 2);
        }
        $objectStructure = json_decode($currentAssemblyStructureArray, true);
        $objectName = RegistryObjects::getObjectNameByID($jsonIncomingObject['permission']['object_id'])->name;
        $sql = "select " . $objectName . "_data_use.*, " . $objectName . "_record_own.contact_id from " . $objectName . "_data_use join " . $objectName .
            "_record_own on " . $objectName . "_data_use.id = " . $objectName . "_record_own.id where " . $objectName . "_data_use.id = '" . $jsonIncomingObject['work']['value']['record'][0]['value'] . "'";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        //echo "sql insert: " . $sql;
        //echo "[ $objectsArray ]";
        if (!$objectsArray || $objectsArray == '') {
            return APIHandler::getErrorArray(404, "Record not found.");
        }
        $mapValuesArray = explode('.', $jsonIncomingObject['work']['value']['record'][1]['map']);
        $valueInField = $jsonIncomingObject['work']['value']['record'][1]['value'];
        if (count($mapValuesArray) < 2 || $objectStructure[$mapValuesArray[0]]['nested'] == "false") {
            return APIHandler::getErrorArray(404, "Map is not for nested object.");
        }
        $currentFieldName = $objectStructure[$mapValuesArray[0]]['name'];
        $internalFieldValue = null;
        $sql = "select $currentFieldName from " . $objectName . "_data_use where id = '" . $jsonIncomingObject['work']['value']['record'][0]['value'] . "'";
        $internalFieldValue = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$internalFieldValue || count($internalFieldValue) < 1) {
            //insert
            $internalFieldValue = null;
        } else {
            //update
            $internalFieldValue = $internalFieldValue[$currentFieldName];
        }
        $nestedObjectName = RegistryObjects::getObjectNameByID($objectStructure[$mapValuesArray[0]]['object'])->name;
        ObjectOperationsHandler::deleteFromImplementedRecord($objectStructure[$mapValuesArray[0]]['object'], $nestedObjectName, $jsonIncomingObject['permission']['object_id'],
            $jsonIncomingObject['work']['value']['record'][0]['value'], $jsonIncomingObject['work']['value']['record'][1]['field'],
            $internalFieldValue, $jsonIncomingObject['work']['value']['record'][1]['map'], $jsonIncomingObject['work']['value']['record'][1]['value']);
        //ObjectOperationsHandler::updateAllRecordsEnterPoint($jsonIncomingObject['permission']['object_id'], $objectName,
        //    $objectStructure[$mapValuesArray[0]]['id'], null, $jsonIncomingObject['work']['value']['record'][0]['value'], null);
        RecordUpdate::updateAllImplementedRecords($jsonIncomingObject['permission']['object_id'], $objectName,
            $jsonIncomingObject['work']['value']['record'][0]['value'], $jsonIncomingObject['permission']['drole_id'], true);
        return APIHandler::getErrorArray(200, "success");
    }

    public static function getTimeMarker($title, $startTime)
    {
        //$t = microtime(true);
        //$micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        //return "[" . $title . " : " . date_format(new DateTime(date('Y-m-d H:i:s.' . $micro, $t)), 'hh:mi:ss.mmm') . "]";
        echo "<div>[" . $title . " : " . (microtime(true) - $startTime) . "]</div>";
    }

    public static function checkServicePermission($ownerID, $contactID, $objectID, $roleID)
    {
        if (PrivateObjectsDataHandler::getLevelOfAccess($objectID) == 0 && $roleID != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            return false;
        }
        if (PrivateObjectsDataHandler::getLevelOfAccess($objectID) < 2 && $roleID != RegistryDescriptionRolesModel::$rolesArray['superadmin'] &&
            $roleID != RegistryDescriptionRolesModel::$rolesArray['admin']) {
            return false;
        }
        if (PrivateObjectsDataHandler::getLevelOfAccess($objectID) == 2 && $roleID != RegistryDescriptionRolesModel::$rolesArray['superadmin'] &&
            $roleID != RegistryDescriptionRolesModel::$rolesArray['admin'] && $ownerID != $contactID) {
            return false;
        }
        return true;
    }

}