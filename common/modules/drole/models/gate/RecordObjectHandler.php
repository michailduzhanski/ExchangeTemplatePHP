<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\gate\ObjectOperationsHandler;
use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\UUIDGenerator;
use common\modules\drole\models\object\LogObjectHandler;
use common\modules\drole\models\registry\DynamicRoleModel;

/**
 * Description of RecordObjectHandler
 *
 * @author LILIYA
 */
class RecordObjectHandler {

    //put your code here
    public static function setNullRecord($objectID, $droleID, $contactID) {
        //check permission
        $permission = 4;
        $arrayOfStructureFields = StructureOperationHandler::getFieldAllParamsForAssembly($objectID, $droleID)->getModels();
        //update permission access
        /*if (StructureOperationHandler::getPermissionType($arrayOfStructureFields[0]) < $permission) {
            return false;
        }*/
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        $recordID = UUIDGenerator::v4();
        //$assemblyID = $arrayOfStructureFields[0]['id'];
        $arrayOfDrole = DynamicRoleModel::getArrayOfDynamicRole($droleID);

        if (!$arrayOfDrole) {
            return false;
        }
        $sql = "insert into " . $objectName . "_data_use (id) values ('" . $recordID . "')";
        \Yii::$app->db->createCommand($sql)->execute();
        self::setDataOwner($objectName, $recordID, $arrayOfDrole, $contactID);
        LogObjectHandler::updateLogRecordForObject($objectName, "data_use", $recordID, $arrayOfStructureFields[0]['field'], '', $recordID, $droleID, $contactID);
        $structureArray = StructureOperationHandler::getFastStructureWithCheck($objectID, $droleID);
        //
        $jsonDataRecord = ObjectOperationsHandler::getJsonDataFromJsonStructureArray($objectID, $droleID, $recordID, $structureArray);
        ObjectOperationsHandler::setFastRecord($objectID, $droleID, $recordID, $jsonDataRecord);
        //echo 'jsonDataRecord: ' . print_r($jsonDataRecord, true);
        //exit;
    }

    private static function setDataOwner($objectName, $recordID, $arrayOfDrole, $contactID) {
        $sql = "insert into " . $objectName . "_record_own values ('" . $recordID . "', '" . $arrayOfDrole['company_id'] . "', '" . $arrayOfDrole['service_id'] . "', '" . $contactID . "')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public static function setRecordToObjectJSON($objectID, $droleID, $recordID, $contactID, $jsonRecord) {
        $arrayOfValues = json_decode($jsonRecord, true);
        $arrayOfStructureFields = StructureOperationHandler::getFieldAllParamsForAssembly($objectID, $droleID)->getModels();
        for ($i = 0; $i < count($arrayOfValues); $i++) {
            if (!is_array($arrayOfValues[$i]) && self::compaireValueType($arrayOfValues[$i], strtolower($arrayOfStructureFields[$i]['type']))) {
                self::updateValueInObjectWithMap($objectID, $droleID, $recordID, $contactID, $i, $arrayOfValues[$i]);
            }
        }
    }

    public static function updateValueInObjectWithMap($objectID, $droleID, $recordID, $contactID, $incomingMap, $newValue) {
        return ObjectOperationsHandler::updateFastRecord($objectID, $droleID, $recordID, $contactID, $incomingMap, $newValue);
    }

    public static function updateValueInObjectRecord($objectID, $droleID, $recordID, $contactID, $fieldName, $newValue) {
        $arrayOfStructureFields = StructureOperationHandler::getFieldAllParamsForAssembly($objectID, $droleID)->getModels();
        $index = false;
        for ($i = 1; $i < count($arrayOfStructureFields); $i++) {
            if ($arrayOfStructureFields[$i]['name'] == $fieldName) {
                $index = $i;
                break;
            }
        }
        if (!$index || !self::compaireValueType($newValue, strtolower($arrayOfStructureFields[$i]['type']))) {
            return false;
        }
        return self::updateValueInObjectWithMap($objectID, $droleID, $recordID, $contactID, $index, $newValue);
    }

    private static function compaireValueType($value, $structureType) {
        $valueStringClass = self::getStringNamedTypeOfValue($value);
        echo "compaireValueType($valueStringClass, $structureType)";
        if ($valueStringClass == 'uuid' || $valueStringClass == 'string') {
            switch ($structureType) {
                case 'integer':
                case 'bigint':
                case 'float':
                case 'double precision':
                case 'boolean':
                    return false;
            }
        } else if ($valueStringClass == 'text' || $valueStringClass == 'character varying') {
            switch ($structureType) {
                case 'integer':
                case 'float':
                case 'double precision':
                case 'boolean':
                case 'uuid':
                    return false;
            }
        } else if ($valueStringClass == 'integer' || $valueStringClass == 'bigint') {
            switch ($structureType) {
                case 'float':
                case 'double precision':
                case 'boolean':
                case 'uuid':
                    return false;
            }
        } else if ($valueStringClass == 'float' || $valueStringClass == 'double precision') {
            switch ($structureType) {
                case 'integer':
                case 'bigint':
                case 'boolean':
                case 'uuid':
                    return false;
            }
        }
        return true;
    }

    private static function getStringNamedTypeOfValue($var) {
        if (UUIDGenerator::isUUID($var))
            return 'uuid';
        if (is_array($var))
            return "array";
        if (is_bool($var))
            return "boolean";
        if (is_null($var))
            return "NULL";
        if (is_numeric($var)) {
            if (ctype_digit($var))
                return "integer";
            else
                return "float";
        }
        if (is_object($var))
            return "object";
        if (is_resource($var))
            return "resource";
        if (is_string($var))
            return "string";
        return false;
    }

}
