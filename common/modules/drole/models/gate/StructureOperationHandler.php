<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\implemented\StructureUpdate;
use common\modules\drole\models\object\LogObjectHandler;
use common\modules\drole\models\object\ObjectTablesWizardPostgres;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\RegistryAssemblyForObject;
use common\modules\drole\models\registry\RegistryClasses;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\UUIDGenerator;
use yii\data\SqlDataProvider;

/**
 * Description of StructureOperationHandler
 *
 * @author LILIYA
 */
class StructureOperationHandler
{

    public static function updateStructureFieldNameDescription($objectID, $droleID, $contactID, $fieldID, $newName = NULL, $newClassID = NULL, $newDescription = NULL)
    {
        //get access right
        $authArray = APIHandler::checkAuthorise($droleID, $contactID);
        if (isset($authArray['result']) && $authArray['result'] > 400) {
            return $authArray;
        }
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        if (!$objectName) {
            return false;
        }
        $fieldArray = false;
        $hasSameName = false;
        $dynamicRoleValues = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        if ($dynamicRoleValues['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin'] && $dynamicRoleValues['role_id'] != RegistryDescriptionRolesModel::$rolesArray['admin']) {
            return APIHandler::getErrorArray(404, "You have not permission.");
        }
        if ($fieldID && $fieldID != '') {
            $currentFieldsList = self::getFieldAllParamsForAssembly($objectID, $droleID)->getModels();
            foreach ($currentFieldsList as $field) {
                if ($field['field'] == $fieldID) {
                    $fieldArray = $field;
                    //break;
                }
                if (ctype_digit(substr($newName, 0, 1))) {
                    $hasSameName = true;
                }
            }
        } else {
            $fieldID = UUIDGenerator::v4();
        }
        if ($newDescription) {
            //update description
            $oldDescription = self::getDescriptionCurrentValue($objectName, "structure_fields", $fieldID);
            if (!$oldDescription || $newDescription != $oldDescription) {
                $sql = "delete from " . $objectName . "_description where table_name = 'structure_fields' and record_id = '" . $fieldID . "'";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "insert into " . $objectName . "_description values ('" . UUIDGenerator::v4() . "', 'structure_fields', '" . $fieldID . "', '" . $newDescription . "')";
                \Yii::$app->db->createCommand($sql)->execute();
                LogObjectHandler::updateLogRecordForObject($objectName, "description", $fieldID, 'description', $oldDescription, $newDescription, $droleID, $contactID);
            }
        }
        if (($fieldArray && $fieldArray['edit'] != 1) || (!$fieldArray && $hasSameName)) {
            return false;
        }
        if (($fieldArray && (($newName && $newName != 'id') || $newClassID) && $fieldArray['name'] != 'id' && $fieldArray['name'] != 'date_create') || (($newName && $newName != 'id' && $newName != 'date_create') && $newClassID)) {
            //if ((($newName && $newName != 'id') || $newClassID) && ($fieldArray && $fieldArray['name'] != 'id')) {
            //update name
            if (!$fieldArray) {
                $fieldArray = ['field' => $fieldID];
            }
            if (self::updateFieldAndAlterData($objectID, $objectName, $fieldArray, $newName, $newClassID, $droleID, $contactID)) {
                self::updateNamesInFilters($fieldID, $newName, 0);
                self::updateAllAssembliesInObjectWithField($objectID, $droleID, $objectName, $fieldID);
                //ObjectOperationsHandler::updateAllRecordsRecursivelyAfterUpdateAssembly($objectID, $droleID, $objectName, false);
                //ObjectOperationsHandler::updateAllRecordsEnterPoint($objectID, $objectName, $fieldID);
                StructureUpdate::updateStructuresByInnerObjects($objectID, $objectName, $droleID, true, false);
                //RecordUpdate::updateAllImplementedRecords($objectID, $objectName, $recordID, $droleID, true)
            }
        }
        return APIHandler::getErrorArray(200, "success");
    }

    public static function getFieldAllParamsForAssembly($currentObjectID, $droleID)
    {
        $params = [':object_id' => $currentObjectID, ':drole_id' => $droleID];
        $sql = "select * from getStructureFor(:object_id, :drole_id) as ds(id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text)";
        //echo "select * from getStructureFor($currentObjectID, $droleID) as ds(id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text)";
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    private static function getDescriptionCurrentValue($objectName, $tableName, $recordID)
    {
        $sql = "select description from " . $objectName . "_description where record_id = '$recordID' and table_name = '$tableName'";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $provider->getModels();
        if (!$objectsArray) {
            return 'null';
        }
        return $objectsArray[0]['description'];
    }

    private static function updateFieldAndAlterData($objectID, $objectName, $fieldArray, $fieldName, $fieldClass, $droleID, $contactID)
    {
        if (self::checkRecursiveUseDataObject($fieldClass, $objectID)) {
            return false;
        }
        $sql = "select * from " . $objectName . "_structure_fields where name = '$fieldName'";
        $providerCompaire = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerCompaire || count($providerCompaire) < 1) {
            return true;
        }

        $compareArray = $providerCompaire;
        $fieldID = '';
        $oldFieldName = false;
        $oldFieldClass = false;
        //if compare array is fill
        if (!($compareArray && $fieldArray['field'] != $compareArray[0]['id'])) {
            $oldName = '';
            if (!$fieldArray || !isset($fieldArray['id'])) {
                //insert
                if (!$fieldName || $fieldName == '' || !$fieldClass || $fieldClass == '') {
                    return false;
                }
                $fieldID = $fieldArray['field'];
                $sql = "insert into " . $objectName . "_structure_fields values ('$fieldID', '$fieldName', '$fieldClass')";
                \Yii::$app->db->createCommand($sql)->execute();
                //$tableClass = self::getFieldType($fieldClass);
                $tableClass = self::getFieldTypeByID($fieldClass);
                $sql = "ALTER TABLE " . $objectName . "_data_use ADD $fieldName $tableClass NULL;";
                \Yii::$app->db->createCommand($sql)->execute();
                self::insertStructureFieldToAssembly($objectID, $objectName, $fieldID, $fieldName, $droleID, $contactID);
            } else {
                //update
                $fieldID = $fieldArray['field'];
                $oldFieldName = $fieldArray['name'];
                $oldFieldClass = $fieldArray['class'];
                if (!$fieldName || $fieldName == '' || $fieldName == $oldFieldName) {
                    $fieldName = $oldFieldName;
                } else {
                    self::updateDataUseFieldName($objectName, $oldFieldName, $fieldName);
                }
                if (!$fieldClass || $fieldClass == '') {
                    $fieldClass = $oldFieldClass;
                } else {
                    if (!self::checkCompatibilityOfTypes($oldFieldClass, $fieldClass)) {
                        return false;
                    }
                }
                $sql = "update " . $objectName . "_structure_fields set name = '$fieldName', class = '$fieldClass' where id = '$fieldID'";
                \Yii::$app->db->createCommand($sql)->execute();
                //if($fieldClass != $oldFieldClass){
                self::updateDataUseFieldType($objectID, $objectName, $droleID, $fieldID, $fieldName, self::getFieldTypeByID($fieldClass));
                //self::updateStructureFieldRecursively($objectID, $objectName, $droleID, $fieldID, $fieldName, $fieldClass);
                //}
                $oldName = $fieldArray['name'];
            }
            if ($oldFieldName && $fieldName != $oldFieldName) {
                LogObjectHandler::updateLogRecordForObject($objectName, "structure_fields", $fieldID, 'name', $oldFieldName, $fieldName, $droleID, $contactID);
            }
            if ($oldFieldClass && $fieldClass != $oldFieldClass) {
                LogObjectHandler::updateLogRecordForObject($objectName, "structure_fields", $fieldID, 'class', $oldFieldClass, $fieldClass, $droleID, $contactID);
            }
            return true;
        } else {
            //echo "name is presented yet.";
            return false;
        }
    }

    private static function checkRecursiveUseDataObject($objectInsertedID, $objectBaseID)
    {
        if (!RegistryObjects::getObjectNameByID($objectInsertedID)) {
            return false;
        }
        $objectInsertedName = RegistryObjects::getObjectNameByID($objectInsertedID)->name;
        $sql = "select * from " . $objectInsertedName . "_structure_fields where id = '$objectBaseID'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if ($providerRecords && count($providerRecords) > 0) {
            return true;
        }

        $sql = "select * from " . $objectInsertedName . "_structure_use_fast where json_structure::text LIKE '%\"object\": \"$objectBaseID\"%'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if ($providerRecords && count($providerRecords) > 0) {
            return true;
        }
        return false;
    }

    private static function getFieldTypeByID($uuidType)
    {
        $activeRecordValue = RegistryClasses::getObjectNameByID($uuidType);
        if (!$activeRecordValue) {
            return 'uuid';
        }
        $objectName = strtolower($activeRecordValue->name);
        switch ($objectName) {
            case 'string':
                return 'character varying(255)';
            case NULL:
            case '':
                return 'uuid';
            case 'double':
            case 'timestamp':
                return 'double precision';
            case 'image':
                return 'text';
            case 'sequence string':
                return 'uuid';
            default:
                return $objectName;
        }
    }

    private static function insertStructureFieldToAssembly($objectID, $objectName, $fieldID, $fieldName, $droleID, $contactID)
    {
        $sql = "insert into " . $objectName . "_assembly_fields_use (id, field, turn, usef, visible, edit, delete, insert) values "
            . "((SELECT assembly_id FROM registry_drole_assembly WHERE drole_id = '$droleID' "
            . "AND object_id = '$objectID' AND active = '1' limit 1), '$fieldID', (select count(*) as count from " .
            $objectName . "_assembly_fields_use where id = (SELECT assembly_id FROM registry_drole_assembly WHERE drole_id = '$droleID' "
            . "AND object_id = '$objectID' AND active = '1' limit 1)), '1', '1', '1', '1', '1')";
        \Yii::$app->db->createCommand($sql)->execute();
        LogObjectHandler::updateLogRecordForObject($objectName, "assembly_fields_use", $fieldID, 'name', '', $fieldName, $droleID, $contactID);
    }

    private static function updateDataUseFieldName($objectName, $oldFieldName, $fieldName)
    {
        $sql = "ALTER TABLE " . $objectName . "_data_use RENAME COLUMN $oldFieldName TO $fieldName";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    private static function checkCompatibilityOfTypes($oldValue, $newValue)
    {
        $oldRealClass = self::getFieldTypeByID($oldValue);
        $newRealClass = self::getFieldTypeByID($newValue);
        if ($oldRealClass == 'uuid' || $oldRealClass == 'text' || $oldRealClass == 'character varying') {
            switch ($newRealClass) {
                case 'integer':
                case 'bigint':
                case 'float':
                case 'double precision':
                case 'boolean':
                    return false;
            }
        } else if ($oldRealClass == 'text' || $oldRealClass == 'character varying') {
            switch ($newRealClass) {
                case 'integer':
                case 'float':
                case 'double precision':
                case 'boolean':
                case 'uuid':
                    return false;
            }
        } else if ($oldRealClass == 'integer' || $oldRealClass == 'bigint') {
            switch ($newRealClass) {
                case 'float':
                case 'double precision':
                case 'boolean':
                case 'uuid':
                    return false;
            }
        } else if ($oldRealClass == 'float' || $oldRealClass == 'double precision') {
            switch ($newRealClass) {
                case 'integer':
                case 'bigint':
                case 'boolean':
                case 'uuid':
                    return false;
            }
        }
        return true;
    }

    private static function updateDataUseFieldType($objectID, $objectName, $droleID, $fieldID, $fieldName, $fieldClass)
    {
        $sql = "ALTER TABLE " . $objectName . "_data_use ALTER $fieldName TYPE $fieldClass USING $fieldName::$fieldClass, ALTER $fieldName DROP DEFAULT, ALTER $fieldName DROP NOT NULL";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    //structure functions

    public static function updateNamesInFilters($id, $name, $typeOfUpdate)
    {
        $listOfObjects = RegistryObjects::find()->all();
        $resultArray = [];
        foreach ($listOfObjects as $arrayObject) {
            if ($typeOfUpdate == 0) {
//update for field of object
                $sql = "update " . $arrayObject['name'] . "_filter_record set exvaluefieldname = '$name' where valuefield = '$id'";
                $provider = new SqlDataProvider([
                    'sql' => $sql
                ]);
            } else if ($typeOfUpdate == 1) {
//update for class of object
                $sql = "update " . $arrayObject['name'] . "_filter_record set exvalueobjectname = '$name' where valueobject = '$id'";
                $provider = new SqlDataProvider([
                    'sql' => $sql
                ]);
            }
        }
        return json_encode($resultArray);
    }

    /*public static function getFastStructureJsonForAssembly($objectID, $assemblyID)
    {
        $droleID = DynamicRoleModel::getDroleForAssembly($objectID, $assemblyID);
        if (!$droleID) {
            return false;
        } else {
            $droleID = $droleID[0]['drole_id'];
        }
        $startAssemblyFields = self::getFieldAllParamsForAssembly($objectID, $droleID);
        if (!$startAssemblyFields) {
            return false;
        }
        $result = self::getFastStructureTree($droleID, $startAssemblyFields->getModels());
        return $result;
    }*/

    /**
     * Function for update all assemblies in object. if field is null, then update all assemblies.
     * @param $objectID
     * @param $droleID
     * @param $objectName
     * @param $fieldID
     * @return bool
     */
    public static function updateAllAssembliesInObjectWithField($objectID, $droleID, $objectName, $fieldID, $typeOperation = 0)
    {
        //$allAssemblies = self::getListAssembliesWithField($objectName, $fieldID);
        $sql = "select * from registry_drole_assembly where assembly_id in (SELECT id from registry_assembly where id in 
(SELECT id FROM " . $objectName . "_assembly_fields_use WHERE field = '$fieldID') and type = '0') and active = '1'";
        $allAssemblies = \Yii::$app->db->createCommand($sql)->queryAll();
        //echo "get assembly: " . $sql;
        if (!$allAssemblies || count($allAssemblies) < 1) {
            return false;
        }
        foreach ($allAssemblies as $assemblyRecord) {
            $droleIDForAssembly = $assemblyRecord['drole_id'];//DynamicRoleModel::getDroleForAssembly($objectID, $assemblyRecord['id']);
            /*if (!$droleIDForAssembly) {
                continue;
            } else {
                $droleIDForAssembly = $droleIDForAssembly[0]['drole_id'];
            }*/
            if ($typeOperation == 1) {
                $sql = "delete from " . $objectName . "_assembly_fields_use where (field = '$fieldID' and id = '" . $assemblyRecord['id'] . "')";
                \Yii::$app->db->createCommand($sql)->execute();
            }
            //if ($assemblyRecord['type'] == 0) {
            $jsonString = self::getFastStructureTreeForAssembly($droleIDForAssembly, $objectName, $assemblyRecord['id']);
            if (!$jsonString) {
                continue;
            }
            //$deleteSQL = "delete from " . $objectName . "_assembly_fields_use where field_id = '$fieldID' and id = '" . $assemblyRecord['assembly_id'] . "'";
            //\Yii::$app->db->createCommand($deleteSQL)->execute();
            self::updateFastStructureDeeply($objectID, $objectName, $droleIDForAssembly, $assemblyRecord['assembly_id'], $jsonString);
            self::updateAllUsefulAssemblyConstructionsForAll($objectID, $droleIDForAssembly, $assemblyRecord['assembly_id'], $objectName);
            /*$allAssemblyFields = self::getStaticAssemblyStructureForFunction($objectName, $assemblyRecord['assembly_id']);
            for ($i = 1; $i < count($allAssemblyFields); $i++) {
                $sqlUpdate = "update " . $objectName . "_assembly_fields_use set turn = " . $i . " where id = '" .
                    $allAssemblyFields[$i]['id'] . "' and field = '" . $allAssemblyFields[$i]['field'] . "'";
                \Yii::$app->db->createCommand($sqlUpdate)->execute();
            }*/
            //}
        }
        //
    }

    public static function getFastStructureTreeForAssembly($droleID, $objectName, $assemblyID)
    {
        $sql = "SELECT " . $objectName . "_assembly_fields_use.*, " . $objectName . "_structure_fields.name, " . $objectName . "_structure_fields.class, 
(select name from registry_classes where registry_classes.id = " . $objectName . "_structure_fields.class) as type  
FROM " . $objectName . "_assembly_fields_use inner join " . $objectName . "_structure_fields on " . $objectName . "_assembly_fields_use.field = " . $objectName .
            "_structure_fields.id  where " . $objectName . "_assembly_fields_use.id = '" . $assemblyID . "' order by " . $objectName . "_assembly_fields_use.turn";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        $allAssemblyFields = $provider->getModels();
        if (!$allAssemblyFields) {
            return false;
        }
        $jsonString = ObjectOperationsHandler::returnIndexedJSON(self::getFastStructureTree($droleID, $allAssemblyFields));
        return $jsonString;
    }

    private static function getFastStructureTree($dynamicRoleID, $presentAssemblyWithStructureFields)
    {
        $increment = 0;
        $resultArray = array();
        foreach ($presentAssemblyWithStructureFields as $assemblyField) {
            $currentID = $assemblyField['field'];
            $currentName = $assemblyField['name'];
            $currentType = self::getFieldType($assemblyField['type']);
            $currentValueClassID = $assemblyField['class'];
            $currentPermission = self::getPermissionType($assemblyField);
            $fieldInternalArray = self::getFieldAllParamsForAssembly($currentValueClassID, $dynamicRoleID)->getModels();
            $internalArray = self::getFastStructureTree($dynamicRoleID, $fieldInternalArray);
            $resultArray[$increment] = ['name' => $currentName, 'id' => $currentID, 'type' => $currentType, 'perm' => $currentPermission, 'object' => $currentValueClassID, 'nested' => $internalArray];
            $increment++;
        }
        return $resultArray;
    }

    private static function getFieldType($strType)
    {
        if (UUIDGenerator::isUUID($strType)) {
            return 'uuid';
        }
        switch (strtolower($strType)) {
            case 'image':
                return 'image';
            case 'string':
                return 'character varying';
            case NULL:
            case '':
                return 'uuid';
            case 'double':
            case 'timestamp':
                return 'double precision';
            case 'image':
                return 'text';
            case 'sequence string':
                return 'uuid';
            default:
                return $strType;
        }
    }

    public static function getPermissionType($fieldArray)
    {
        if ($fieldArray['usef'] == 1 && $fieldArray['visible'] != 1 && $fieldArray['edit'] != 1 && $fieldArray['delete'] != 1 && $fieldArray['insert'] == 1)
            return 16;
        if ($fieldArray['usef'] != 1)
            return 0;
        if ($fieldArray['visible'] != 1)
            return 1;
        if ($fieldArray['edit'] != 1)
            return 2;
        if ($fieldArray['delete'] != 1)
            return 3;
        if ($fieldArray['insert'] != 1)
            return 4;
        return 5;
    }

    public static function updateFastStructureDeeply($objectID, $objectName, $droleID, $assemblyID, $jsonString)
    {
        self::deleteFastStructure($objectID, $objectName, $droleID, $assemblyID);
        $sql = "select * from registry_drole_assembly where drole_id = '$droleID' and assembly_id = '$assemblyID' and object_id = '$objectID' and active = '1'";
        $checkValue = \Yii::$app->db->createCommand($sql)->queryAll();
        //echo $sql;
        if (!$checkValue || count(!$checkValue) < 0) {
            return false;
        }
        self::setFastStructure($objectID, $objectName, $droleID, $assemblyID, $jsonString);
    }

    private static function deleteFastStructure($objectID, $objectName, $droleID, $assemblyID)
    {
        $sql = "delete from " . $objectName . "_structure_use_fast where drole_id = '$droleID' and assembly_id = '$assemblyID'";
        //$sql = "select deleteCurrentRecordFastStructure('$objectID', '$droleID')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    private static function setFastStructure($objectID, $objectName, $droleID, $assemblyID, $jsonString)
    {
        if (substr($jsonString, 0, 1) === "{") {
            //good job
        } else {
            $jsonString = substr($jsonString, 1, strlen($jsonString) - 2);
        }
        $sql = "insert into " . $objectName . "_structure_use_fast values ('$droleID', '$assemblyID', '$jsonString')";
        //$sql = "select insertIntoFastStructure('$objectID', '$droleID', '" . $jsonString . "', '" . UUIDGenerator::v4() . "')";
        //echo $sql;
        \Yii::$app->db->createCommand($sql)->execute();
        return true;
    }

    /** Function for update all assemblies of object
     * @param $objectID
     * @param $assemblyID
     * @param $objectName
     * @return bool
     * @throws \yii\db\Exception
     */
    private static function updateAllUsefulAssemblyConstructionsForAll($objectID, $droleID, $assemblyID, $objectName)
    {
        echo "[try update all assemblyes]";
        $currentAssemblyStructureArray = self::getFastStructureForAssemblyWithCheck(
            $objectID, $droleID, $assemblyID, $objectName);
        /*$droleID = DynamicRoleModel::getDroleForAssembly($objectID, $assemblyID);
        if (!$droleID) {
            return false;
        } else {
            $droleID = $droleID['drole_id'];
        }*/
        $arrayOfParentAssemblyes = self::selectFirstLineIncomingObjectInAssemblies($objectID, $objectName, $droleID);
        if (!$arrayOfParentAssemblyes) {
            return false;
        }
        if (!$currentAssemblyStructureArray || count($currentAssemblyStructureArray) < 1) {
            $currentAssemblyStructureArray = "false";
        }
        foreach ($arrayOfParentAssemblyes as $assemblyRecord) {
            //update structure
            $currentObjectName = $assemblyRecord['object_name'];
            $sql = "update " . $currentObjectName . "_structure_use_fast set json_structure = jsonb_set(json_structure, '{" . $assemblyRecord['indexpos'] . ", nested}', '" . $currentAssemblyStructureArray .
                "', false) where assembly_id = '" . $assemblyRecord['assembly_id'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();
            //self::updateAllUsefulAssemblyConstructionsForAll($assemblyRecord['object_id'], $droleID, $assemblyRecord['assembly_id'], $currentObjectName);
        }
        StructureUpdate::updateStructuresByInnerObjects($objectID, $objectName, $droleID, true, true);
        return true;
    }

    public static function getFastStructureForAssemblyWithCheck($objectID, $droleID, $assemblyID, $objectName)
    {
        $jsonString = false;
        $fastStructure = self::getFastStructureForAssembly($objectName, $droleID, $assemblyID);
        if (!$fastStructure) {
//            $droleID = DynamicRoleModel::getDroleForAssembly($objectID, $droleID, $assemblyID);
            if (!$droleID) {
                return false;
            }
            $jsonString = ObjectOperationsHandler::returnIndexedJSON(self::getFastStructureTreeForAssembly($droleID,
                $objectName, $assemblyID));
            $assemblyModelRecord = RegistryAssemblyForObject::find()->where(['id' => $assemblyID])->one();
            if ($assemblyModelRecord->type == 0) {
                self::updateFastStructureDeeply($objectID, $objectName, $droleID, $assemblyID, $jsonString);
            }
        } else {
            if (!isset($fastStructure[0])) {
                $jsonString = $fastStructure['json_structure'];
            } else {
                $jsonString = $fastStructure[0]['json_structure'];
            }
        }
        if (strpos($jsonString, '"') === 0) {
            $jsonString = substr($jsonString, 1, strlen($jsonString) - 2);
        }
        return $jsonString;
    }

    private static function getFastStructureForAssembly($objectName, $droleID, $assemblyID)
    {
        $sql = "SELECT * from " . $objectName . "_structure_use_fast where drole_id = '$droleID' and assembly_id = '$assemblyID'";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        return $objectsArray;
    }

    private static function selectFirstLineIncomingObjectInAssemblies($searchedObjectID, $objectName, $droleID, $onlyDrole = true)
    {
        $objectsList = StructureUpdate::getAllObjectsWhereObjectIsPresent($searchedObjectID, $objectName);
        if (!$objectsList) {
            return false;
        }
        $resultList = array();
        foreach ($objectsList as $parentObjectLine) {
            $assemblyWithKeysForObject = StructureUpdate::getAssemblyesWhereFirstLineObjectIsPresent($searchedObjectID,
                $parentObjectLine['parent_id'], $parentObjectLine['parent_name'], ($onlyDrole ? false : $droleID));
            if (!$assemblyWithKeysForObject) continue;
            foreach ($assemblyWithKeysForObject as $assemblyLine) {
                if ($assemblyLine['indexpos'] != null && $assemblyLine['indexpos'] != 'NULL') {
                    array_push($resultList, $assemblyLine);
                }
            }
        }
        if (count($resultList) < 1) {
            return false;
        }
        return $resultList;
        /*$onlyDroleSuffix = "active = 1 and drole_id = '$droleID'";
        if (!$onlyDrole) {
            $onlyDroleSuffix = '';
        }


        $sql = "select * from getStructureWhereSearchObjectInFirstLine('$searchedObjectID') as ds(object_id uuid, assembly_id uuid, indexpos int)";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return $providerRecords;*/
        /*if (!$provider->getModels()) {
            return false;
        }
        return $provider->getModels();*/
    }

    public static function getListOfActiveAssemblies($objectID, $objectName)
    {
        $sql = "SELECT " . $objectName . "_structure_use_fast.* FROM " . $objectName . "_structure_use_fast join (select registry_drole_assembly.assembly_id from registry_drole_assembly where active = '1' and object_id = '$objectID') as registry_drole_assembly on registry_drole_assembly.assembly_id = " . $objectName . "_structure_use_fast.assembly_id";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function deleteStructureField($objectID, $droleID, $contactID, $fieldID, $JSONArrayOfUseful = NULL, $checkIt = true)
    {
        $authArray = APIHandler::checkAuthorise($droleID, $contactID);
        if (isset($authArray['result']) && $authArray['result'] > 400) {
            return $authArray;
        }
        if ($checkIt && $checkIt != 'false') {
            $usedArray = self::getJSONUsedFieldInObjects($fieldID);
            if ($usedArray) {
                return $usedArray;
            }
        }
        $dynamicRoleValues = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        if ($dynamicRoleValues['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin'] && $dynamicRoleValues['role_id'] != RegistryDescriptionRolesModel::$rolesArray['admin']) {
            return APIHandler::getErrorArray(404, "You have not permission.");
        }
        //get objectName
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        //get field data
        $fieldArray = false;
        if ($fieldID && $fieldID != '') {
            $currentFieldsList = self::getFieldAllParamsForAssembly($objectID, $droleID)->getModels();
            foreach ($currentFieldsList as $field) {
                if ($field['field'] == $fieldID) {
                    $fieldArray = $field;
                    break;
                }
            }
            if (!$fieldArray)
                return APIHandler::getErrorArray();
        } else {
            return APIHandler::getErrorArray();
        }
        $fieldName = $fieldArray['name'];
        self::deleteAllUsageOfField($objectName, $fieldID, $fieldName);
        self::updateAllAssembliesInObjectWithField($objectID, $droleID, $objectName, $fieldID, 1);
        $sql = "select * from registry_drole_assembly where assembly_id in (SELECT id from registry_assembly where id in 
(SELECT id FROM " . $objectName . "_assembly_fields_use WHERE field = '$fieldID'))";
        $allAssemblies = \Yii::$app->db->createCommand($sql)->queryAll();
        //echo "get assembly: " . $sql;
        if (!$allAssemblies || count($allAssemblies) < 1) {
            return false;
        }
        foreach ($allAssemblies as $assemblyRecord) {
            $sqlDelete = "delete from " . $objectName . "_assembly_fields_use where id = '" . $assemblyRecord['assembly_id'] . "' and field = '$fieldID'";
            \Yii::$app->db->createCommand($sqlDelete)->execute();
            $allAssemblyFields = self::getStaticAssemblyStructureForFunction($objectName, $assemblyRecord['assembly_id']);
            for ($i = 0; $i < count($allAssemblyFields); $i++) {
                $sqlUpdate = "update " . $objectName . "_assembly_fields_use set turn = " . $i . " where id = '" .
                    $allAssemblyFields[$i]['id'] . "' and field = '" . $allAssemblyFields[$i]['field'] . "'";
                \Yii::$app->db->createCommand($sqlUpdate)->execute();
            }
        }
        //return;
    }

    private static function getJSONUsedFieldInObjects($fieldID)
    {
        $listOfObjects = RegistryObjects::find()->all();
        $resultArray = [];
        foreach ($listOfObjects as $arrayObject) {
            $resultObjectArray = self::getJSONUsefulFromObject($arrayObject, $fieldID);
            if (is_array($resultObjectArray) && count($resultObjectArray) > 0) {
                $resultArray[$arrayObject['name']] = $resultObjectArray;
            }
        }
        return json_encode($resultArray);
    }

    private static function getJSONUsefulFromObject($arrayObject, $fieldID)
    {
        $resultArray = [];
        $listOfAssemblies = self::getAssembliesWithField($arrayObject['name'], $fieldID);
        if ($listOfAssemblies) {
            $resultArray['assemblies'] = $listOfAssemblies;
        }
        $listOfAccess = self::getArrayFromAccessRulesByFieldID($arrayObject['name'], $fieldID);
        if ($listOfAccess) {
            $resultArray['rules'] = $listOfAccess;
        }
        $listOfFilters = self::getArrayFromFiltersByFieldID($arrayObject['name'], $fieldID);
        if ($listOfFilters) {
            $resultArray['filters'] = $listOfFilters;
        }
        return $resultArray;
    }

    private static function getAssembliesWithField($objectName, $fieldID)
    {
        $sql = "select id from " . $objectName . "_assembly_fields_use where field = '$fieldID'";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        return $provider->getModels();
    }

    private static function getArrayFromAccessRulesByFieldID($objectName, $fieldID)
    {
        $sql = "select id from " . $objectName . "_access_rules where (accessfield = '$fieldID') or 
        (controlfield = '$fieldID')";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        return $provider->getModels();
    }

    private static function getArrayFromFiltersByFieldID($objectName, $fieldID)
    {
        $sql = "select id from " . $objectName . "_filter_record where (valuefield = '$fieldID')";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        return $provider->getModels();
    }

    private static function deleteAllUsageOfField($objectName, $fieldID, $fieldName)
    {
        //delete from structure
        $sql = "delete from " . $objectName . "_structure_fields where id = '$fieldID'";
        \Yii::$app->db->createCommand($sql)->execute();
        //delete from description
        $sql = "delete from " . $objectName . "_description where table_name = 'structure_fields' and record_id = '$fieldID'";
        \Yii::$app->db->createCommand($sql)->execute();
        //delete from data
        $sql = "ALTER TABLE " . $objectName . "_data_use DROP " . $fieldName;
        \Yii::$app->db->createCommand($sql)->execute();
        //delete from rules
        $sql = "delete from " . $objectName . "_access_rules where (accessfield = '$fieldID') or 
        (controlfield = '$fieldID')";
        \Yii::$app->db->createCommand($sql)->execute();
        //delete from filter
        $sql = "delete from " . $objectName . "_filter_record where (valuefield = '$fieldID')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    private static function getStaticAssemblyStructureForFunction($objectName, $assemblyID)
    {
        $sql = "SELECT " . $objectName . "_assembly_fields_use.*, " . $objectName . "_structure_fields.name, " . $objectName . "_structure_fields.class, 
(select name from registry_classes where registry_classes.id = " . $objectName . "_structure_fields.class) as type, (select description from " . $objectName .
            "_description where table_name = 'structure_fields' and record_id = " . $objectName . "_structure_fields.id) as description  
FROM " . $objectName . "_assembly_fields_use inner join " . $objectName . "_structure_fields on " . $objectName . "_assembly_fields_use.field = " . $objectName .
            "_structure_fields.id where " . $objectName . "_assembly_fields_use.id = '" . $assemblyID . "' order by " . $objectName . "_assembly_fields_use.turn";
        $allAssemblyFields = \Yii::$app->db->createCommand($sql)->queryAll();
        //$allAssemblyFields = $provider->getModels();
        return $allAssemblyFields;
    }

    public static function deleteDataObject($objectID, $droleID, $contactID, $JSONArrayOfUseful = NULL, $checkIt = false)
    {
        $authArray = APIHandler::checkAuthorise($droleID, $contactID);
        if (isset($authArray['result']) && $authArray['result'] > 400) {
            return $authArray;
        }
        //get objectName
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        if ($checkIt && $checkIt != 'false') {
            $usedArray = self::getJSONUsedObjectInDB($objectID, $droleID, $objectName, false);
            //echo print_r($usedArray, true);
            if ($usedArray && count($usedArray) > 0) {
                //echo print_r($usedArray[0]);
                return $usedArray;
            }
        }
        //delete all usages
        self::getJSONUsedObjectInDB($objectID, $droleID, $objectName, true);

        //delete all tables
        $listOfTables = ObjectTablesWizardPostgres::getTokensOfTables();
        foreach ($listOfTables as $tableName) {
            $sql = "drop table " . $objectName . $tableName;
            \Yii::$app->db->createCommand($sql)->execute();
        }
        $sql = "delete from registry_objects where id = '$objectID'";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "delete from registry_assembly where object_id = '$objectID'";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "delete from registry_drole_assembly where object_id = '$objectID'";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "delete from registry_description where record_id = '$objectID'";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    private static function getJSONUsedObjectInDB($objectID, $droleID, $objectName, $delete = false)
    {
        $listOfObjects = RegistryObjects::find()->all();
        $resultArray = [];
        foreach ($listOfObjects as $arrayObject) {
            $resultObjectArray = self::getJSONUsefulObjectInAnotherObjects($arrayObject, $objectID);
            if (is_array($resultObjectArray) && count($resultObjectArray) > 0) {
                if (!$delete) {
                    $resultArray[$arrayObject['name']] = $resultObjectArray;
                } else {
                    self::deleteAllUsageOfField($arrayObject['name'], $objectID, $objectName);
                    //update parent assemblies and data
                    return self::updateAllAssembliesInObjectWithField($arrayObject['id'], $droleID, $arrayObject['name'], $objectID, 1);
                }
            }
        }
        if (empty($resultArray)) {
            return false;
        }
        return json_encode($resultArray);
    }

    private static function getJSONUsefulObjectInAnotherObjects($arrayObject, $objectID)
    {
        $resultArray = [];
        $listOfAssemblies = self::getAssembliesWithClassLikeObject($arrayObject['name'], $objectID);
        if ($listOfAssemblies) {
            $resultArray['assemblies'] = $listOfAssemblies;
        }
        $listOfAccess = self::getArrayFromAccessRulesByObjectID($arrayObject['name'], $objectID);
        if ($listOfAccess) {
            $resultArray['rules'] = $listOfAccess;
        }
        $listOfFilters = self::getArrayFromFiltersByObjectID($arrayObject['name'], $objectID);
        if ($listOfFilters) {
            $resultArray['filters'] = $listOfFilters;
        }
        return $resultArray;
    }

    private static function getAssembliesWithClassLikeObject($objectName, $objectID)
    {
        $sql = "SELECT * FROM " . $objectName . "_assembly_fields_use WHERE field = (SELECT " . $objectName . "_structure_fields.id FROM " . $objectName . "_structure_fields WHERE class = '$objectID')";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        return $provider->getModels();
    }

    private static function getArrayFromAccessRulesByObjectID($objectName, $objectID)
    {
        $sql = "select id from " . $objectName . "_access_rules where (accessclass = '$objectID') or 
        (controlclass = '$objectID')";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        return $provider->getModels();
    }

    private static function getArrayFromFiltersByObjectID($objectName, $objectID)
    {
        $sql = "select id from " . $objectName . "_filter_record where (valueobject = '$objectID')";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        return $provider->getModels();
    }

    public static function updateStructureAndAssembliesRecursivelyAfterFieldChanged($objectID, $droleID, $objectName, $fieldID)
    {
        self::updateAllAssembliesInObjectWithField($objectID, $droleID, $objectName, $fieldID);
    }

    public static function updateAllUsefulAssemblyConstructionsForDrole($objectID, $droleID)
    {
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        $currentAssemblyStructureArray = StructureOperationHandler::getFastStructureWithCheck($objectID, $droleID);
        if (!$objectName) {
            return false;
        }
        if (!$currentAssemblyStructureArray || count($currentAssemblyStructureArray) < 1) {
            $currentAssemblyStructureArray = "false";
        }
        $arrayOfParentAssemblyes = self::selectFirstLineIncomingObjectInAssemblies($objectID, $objectName, $droleID);
        if (!$arrayOfParentAssemblyes) {
            return false;
        }
        foreach ($arrayOfParentAssemblyes as $assemblyRecord) {
            //update structure
            $currentObjectName = $assemblyRecord['object_name'];
            $sql = "update " . $currentObjectName . "_structure_use_fast set json_structure = jsonb_set(json_structure, '{" .
                $assemblyRecord['indexpos'] . ", nested}', '" . $currentAssemblyStructureArray .
                "', false) where assembly_id = '" . $assemblyRecord['assembly_id'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();
            self::updateAllUsefulAssemblyConstructionsForDrole($assemblyRecord['object_id'], $droleID);
        }
        return true;
    }

    public static function getFastStructureWithCheck($objectID, $droleID)
    {

        $objectName = RegistryObjects::getObjectNameByID($objectID);
        if (!$objectName || count($objectName) < 1) {
            return APIHandler::getErrorArray();
        }
        $objectName = $objectName->name;
        $assemblyID = DynamicRoleModel::getAssemblyForDrole($objectID, $droleID);
        if (!$objectName || count($objectName) < 1) {
            return APIHandler::getErrorArray();
        }
        $assemblyID = $assemblyID['assembly_id'];
        return self::getFastStructureForAssemblyWithCheck($objectID, $droleID, $assemblyID, $objectName);
        /*
        $jsonString = false;
        $fastStructure = self::getFastStructure($objectID, $droleID);
        if (!$fastStructure->getModels()) {
            $startAssemblyFields = self::getFieldAllParamsForAssembly($objectID, $droleID);
            if (!$startAssemblyFields->getModels() || count($startAssemblyFields->getModels()) < 1) {
                return false;
            }
            $jsonString = ObjectOperationsHandler::returnIndexedJSON(self::getFastStructureTree($droleID, $startAssemblyFields->getModels()));
            $assemblyModelRecord = RegistryAssemblyForObject::find()->where(['id' => $startAssemblyFields->getModels()[0]['id']])->one();
            if ($assemblyModelRecord->type == 0) {
                self::updateFastStructureDeeply($objectID, $droleID, $jsonString);
            }
        } else {
            $jsonString = $fastStructure->getModels()[0]['json_structure'];
        }
        return $jsonString;*/
    }

    public static function getFastStructure($objectID, $droleID, $assemblyID)
    {
        $sql = "select * from getfaststructure('$objectID', '$droleID', '$assemblyID') as ds(drole_id uuid, assembly_id uuid, json_structure jsonb)";
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function deleteAssembly($objectID, $droleID, $contactID, $assemblyID, $JSONArrayOfUseful = NULL, $checkIt = false)
    {
        $authArray = APIHandler::checkAuthorise($droleID, $contactID);
        if (isset($authArray['result']) && $authArray['result'] > 400) {
            return $authArray;
        }
        $sql = "select * from registry_drole_assembly where drole_id = '$droleID' and active = '1'";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray || $objectsArray[0]['assembly_id'] == $assemblyID) {
            if ($dynamicRoleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
                return (APIHandler::getErrorArray(404, "Access rights to the assembly are limited."));
            }
        }
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        $assemblyModelRecord = RegistryAssemblyForObject::find()->where(['id' => $assemblyID])->one();
        if ($checkIt && $checkIt != 'false' && $assemblyModelRecord->type == 0) {
            $assemblyDroleID = DynamicRoleModel::getDroleForAssembly($objectID, $assemblyID);
            if (!$assemblyDroleID) {
            } else {
                $arrayOfParentAssemblyes = self::selectFirstLineIncomingObjectInAssemblies($objectID, $objectName, $assemblyDroleID[0]['drole_id']);
            }
            if (isset($arrayOfParentAssemblyes) && count($arrayOfParentAssemblyes) > 0) {
                return $arrayOfParentAssemblyes;
            }
        }
        //echo "try delete assembly.";
        if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            $sql = "delete from registry_description where table_name = 'registry_assembly' and record_id = '" . $assemblyID . "'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "delete from " . $objectName . "_assembly_fields_use where id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "delete from " . $objectName . "_structure_use_fast where assembly_id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "delete from " . $objectName . "_data_use_implemented where assembly_id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "delete from registry_assembly where id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "delete from registry_drole_assembly where assembly_id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
        } else if ($dynamicRoleArray['role_id'] == RegistryDescriptionRolesModel::$rolesArray['admin']) {
            $sql = "delete from registry_drole_assembly where assembly_id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "insert into registry_drole_assembly values ('" . UUIDGenerator::v4() . "', '$droleID', '$assemblyID', '$objectID', '0')";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "delete from " . $objectName . "_structure_use_fast where assembly_id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "delete from " . $objectName . "_data_use_implemented where assembly_id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "update registry_assembly set type = '1' where id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        self::updateAllAssembliesInObjectWithField($objectID, $droleID, $objectName, null);
        /*if ($assemblyModelRecord->type == 0) {
            self::updateAllUsefulAssemblyConstructionsForAll($objectID, $assemblyID, $objectName);
        }*/
    }

    public static function updatePositionAndPermission($objectID, $droleID, $contactID, $assemblyID, $fieldID, $index, $usef, $visible, $edit, $delete, $insert)
    {
        $authArray = APIHandler::checkAuthorise($droleID, $contactID);
        if (isset($authArray['result']) && $authArray['result'] > 400) {
            return $authArray;
        }
        $sql = "select registry_drole_assembly.*, (select name from registry_objects where registry_objects.id = registry_drole_assembly.object_id) 
as objectname, (select role_id from registry_drole_base where registry_drole_base.id = registry_drole_assembly.drole_id) as role_id from registry_drole_assembly where assembly_id = '$assemblyID' and object_id = '$objectID' and active = 1";
        $currentDrolesRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$currentDrolesRecords || count($currentDrolesRecords) < 1) {
            return APIHandler::getErrorArray(404, "Nothing to do.");
        }
        //$currentAssemblyRecord = $provider->getModels();
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        if (!$dynamicRoleArray || count($dynamicRoleArray) < 1) {
            return APIHandler::getErrorArray(404, "You have not any dynamic roles. Check it!");
        }

        foreach ($currentDrolesRecords as $usedDrole) {
            if ($usedDrole['drole_id'] == $droleID) {
                if ($usedDrole['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
                    return APIHandler::getErrorArray(404, "You can not edit your current assembly.");
                }
            }
        }
        /*if ($currentAssemblyRecord[0]['assembly_id'] == $assemblyID) {
            if ($dynamicRoleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
                return APIHandler::getErrorArray(404, "You can not edit your current assembly.");
            }
        }*/
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        $allAssemblyFields = self::getStaticAssemblyStructureForFunction($objectName, $assemblyID);
        $parentAssemblyFields = null;
        if ($dynamicRoleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            $assemblyForDrole = DynamicRoleModel::getAssemblyForDrole($objectID, $droleID);
            if (!$assemblyForDrole || count($assemblyForDrole) < 1) {
                return APIHandler::getErrorArray(404, "You have not any assemblies. Check it!");
            }
            $parentAssemblyFields = self::getStaticAssemblyStructureForFunction($objectName, $assemblyForDrole[0]['assembly_id']);
        } else {
            $parentAssemblyFields = self::getStaticAssemblyStructureForSuperadmin($objectName);
        }

        $currentIndex = self::getCurrentIndex($allAssemblyFields, $fieldID);
        if ($fieldID == $allAssemblyFields[0]['field'] && ($dynamicRoleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin'] || $currentIndex != $index)) {
            return APIHandler::getErrorArray(404, "You can not edit id field.");
        }
        if (($fieldID == $allAssemblyFields[1]['field'] || $fieldID == $allAssemblyFields[2]['field']) && ($currentIndex != $index || !$usef || $usef == 'false' || $edit == 'true' || $delete == 'true' || $insert == 'true')) {
            return APIHandler::getErrorArray(404, "Incorrect field editing.");
        }
        if (($index == 2 && $fieldID != $allAssemblyFields[2]['field']) || ($index == 1 && $fieldID != $allAssemblyFields[1]['field']) || ($index == 0 && $fieldID != $allAssemblyFields[0]['field'])) {
            return APIHandler::getErrorArray(404, "You can not edit this turn.");
        }
        $operationType = 0;
        if ($dynamicRoleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            $wArr = self::checkPermissionInParentAssemblyArray($fieldID, ['usef' => $usef, 'visible' => $visible, 'edit' => $edit, 'delete' => $delete, 'insert' => $insert], $parentAssemblyFields);
        } else {
            $wArr = ['usef' => $usef, 'visible' => $visible, 'edit' => $edit, 'delete' => $delete, 'insert' => $insert];
        }

        if (!$wArr) {
            return APIHandler::getErrorArray(404, "Incorrect field editing.");
        }
        if (!$usef || $usef == 'false') {
            //delete field
            $sqlDelete = "delete from " . $objectName . "_assembly_fields_use where id = '$assemblyID' and field = '$fieldID'";
            \Yii::$app->db->createCommand($sqlDelete)->execute();
            if ($currentIndex < 0) {
                $operationType = -1;
            }
        } else {
            //update

            if ($currentIndex < 0) {
                $index = count($allAssemblyFields);
                //insert new element to assembly
                $sqlInsert = "insert into " . $objectName . "_assembly_fields_use values ('$assemblyID', '$fieldID', '$index', '"
                    . $wArr['usef'] . "', '" . $wArr['visible'] . "', '" . $wArr['edit'] . "', '" . $wArr['delete'] . "', '" . $wArr['insert'] . "')";

                \Yii::$app->db->createCommand($sqlInsert)->execute();
                $operationType = 1;
            } else {
                //update element
                $sqlUpdate = "update " . $objectName . "_assembly_fields_use set turn = $index, usef = '"
                    . $wArr['usef'] . "', visible = '" . $wArr['visible'] . "', edit = '" . $wArr['edit'] . "', delete = '"
                    . $wArr['delete'] . "', insert = '" . $wArr['insert'] . "' where id = '$assemblyID' and field = '$fieldID'";
                //echo $sqlUpdate;
                \Yii::$app->db->createCommand($sqlUpdate)->execute();
                $operationType = 2;
            }
        }
        //update turn all elements
        if ($operationType == 0) {
            array_splice($allAssemblyFields, $currentIndex, 1);
        } else if ($operationType == 1) {
            array_push($allAssemblyFields, ['id' => $assemblyID, 'field' => $fieldID, 'turn' => count($allAssemblyFields),
                'usef' => $wArr['usef'], 'visible' => $wArr['visible'], 'edit' => $wArr['edit'], 'delete' => $wArr['delete'],
                'insert' => $wArr['insert']]);
        } else if ($operationType == 2) {
            $editedElement = $allAssemblyFields[$currentIndex];
            array_splice($allAssemblyFields, $currentIndex, 1);
            if ($index > count($allAssemblyFields)) {
                array_push($allAssemblyFields, $editedElement);
            } else {
                if ($currentIndex > 1 && $index < 2) {
                    $index = 2;
                }
                array_splice($allAssemblyFields, $index, 0, [$editedElement]);
            }
        }
        for ($i = 1; $i < count($allAssemblyFields); $i++) {
            $sqlUpdate = "update " . $objectName . "_assembly_fields_use set turn = " . $i . " where id = '" .
                $allAssemblyFields[$i]['id'] . "' and field = '" . $allAssemblyFields[$i]['field'] . "'";
            \Yii::$app->db->createCommand($sqlUpdate)->execute();
        }

        $assemblyModelRecord = RegistryAssemblyForObject::find()->where(['id' => $assemblyID])->one();
        //echo "start update structure";
        if ($assemblyModelRecord->type == 0) {
            $sql = "SELECT * FROM registry_drole_assembly WHERE object_id = '$objectID' AND assembly_id = '$assemblyID' AND active = '1'";
            $drolesArrayForCurrentAssembly = \Yii::$app->db->createCommand($sql)->queryAll();
            if (!$drolesArrayForCurrentAssembly || count($drolesArrayForCurrentAssembly) < 1) {
                return true;
            }
            //update all fast structures json for current assembly
            foreach ($drolesArrayForCurrentAssembly as $currentActiveDrole) {
                $droleIDForAssembly = $currentActiveDrole['drole_id'];
                $sql = "delete from " . $objectName . "_data_use_implemented where assembly_id = '$assemblyID'";
                \Yii::$app->db->createCommand($sql)->execute();
                $jsonString = self::getFastStructureTreeForAssembly($droleIDForAssembly, $objectName, $assemblyID);
                //echo "try update assembly with params: $droleIDForAssembly, $objectName, $assemblyID";
                self::updateFastStructureDeeply($objectID, $objectName, $droleIDForAssembly, $assemblyID, $jsonString);
                ////self::updateAllAssembliesInObjectWithField($objectID, $droleID, $objectName, $fieldID);
                //update all assemblies in rise
                //self::updateAllUsefulAssemblyConstructionsForAll($objectID, $droleIDForAssembly, $assemblyID, $objectName);

                //ObjectOperationsHandler::updateAllRecordsInAssemblyStartsWithChangedField($objectID, $objectName, $assemblyID, $droleIDForAssembly, $fieldID, $jsonString);
            }
            StructureUpdate::updateStructuresByInnerObjects($objectID, $objectName, $droleID, true, true);
            //ObjectOperationsHandler::updateAllRecordsEnterPoint($objectID, $objectName, $fieldID, $assemblyID);


            /*$droleIDForAssembly = DynamicRoleModel::getDroleForAssembly($objectID, $assemblyID);
            if (!$droleIDForAssembly) {
                return APIHandler::getErrorArray(404, "Assembly is not have dynamic role.");
            } else {
                $droleIDForAssembly = $droleIDForAssembly[0]['drole_id'];
            }
            $sql = "delete from " . $objectName . "_data_use_fast where assembly_id = '$assemblyID'";
            \Yii::$app->db->createCommand($sql)->execute();
            $jsonString = self::getFastStructureTreeForAssembly($droleIDForAssembly, $objectName, $assemblyID);
            //echo "try update assembly with params: $droleIDForAssembly, $objectName, $assemblyID";
            self::updateFastStructureDeeply($objectID, $droleIDForAssembly, $jsonString);
            //self::updateAllAssembliesInObjectWithField($objectID, $droleID, $objectName, $fieldID);
            //update all assemblies in rise
            self::updateAllUsefulAssemblyConstructionsForAll($objectID, $assemblyID, $objectName);
            ObjectOperationsHandler::setNewDataUseFastRecordsForAssembly($objectID, $objectName, $assemblyID, $droleIDForAssembly);*/
        }
    }

    private static function getStaticAssemblyStructureForSuperadmin($objectName)
    {
        $sql = "SELECT (select 1000 as turn), (select false as usef), (select false as visible), (select false as edit), (select false as \"delete\"), 
        (select false as \"insert\"), " . $objectName . "_structure_fields.id as field, " . $objectName . "_structure_fields.name, " . $objectName . "_structure_fields.class, 
(select name from registry_classes where registry_classes.id = " . $objectName . "_structure_fields.class) as type, (select description from " . $objectName .
            "_description where table_name = 'structure_fields' and record_id = " . $objectName . "_structure_fields.id) as description  
FROM " . $objectName . "_structure_fields";
        $allAssemblyFields = \Yii::$app->db->createCommand($sql)->queryAll();
        //$allAssemblyFields = $provider->getModels();
        return $allAssemblyFields;
    }

    private static function getCurrentIndex($assemblyArray, $fieldID)
    {
        for ($i = 0; $i < count($assemblyArray); $i++) {
            if ($assemblyArray[$i]['field'] == $fieldID) {
                return $i;
            }
        }
        return -1;
    }

    private static function checkPermissionInParentAssemblyArray($fieldID, $workArray, $parentArray)
    {
        if ($parentArray == null) {
            return $workArray;
        }
        $presentArray = false;
        foreach ($parentArray as $workRecord) {
            if ($workRecord['field'] == $fieldID) {
                $presentArray = $workRecord;
                break;
            }
        }
        if (!$presentArray) {
            return false;
        }
        foreach ($workArray as $key => $value) {
            if ($presentArray[$key] == false) {
                $workArray[$key] = 'false';
            }
        }
        return $workArray;
    }

    private static function getListAssembliesWithField($objectName, $fieldID)
    {
        $fieldSearchSuffix = "where " . $objectName .
            "_assembly_fields_use.field = '$fieldID'";
        if (!$fieldID || $fieldID == '') {
            $fieldSearchSuffix = "";
        }
        $sql = "SELECT " . $objectName . "_assembly_fields_use.*, registry_assembly.type FROM " . $objectName .
            "_assembly_fields_use INNER JOIN registry_assembly ON " . $objectName .
            "_assembly_fields_use.id = registry_assembly.id " . $fieldSearchSuffix;
        //$sql = "select * from " . $objectName . "_assembly_fields_use " . $fieldSearchSuffix;
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        //print_r($provider);
        $allAssemblies = $provider->getModels();
        return $allAssemblies;
    }

    private static function getListOfAllAssembliesStructureForObject($objectID)
    {

    }

    /* function only for structure.
     *  get all objects where object placed in first line
     */
    /*private static function getAllCasesStructureOfUse($objectID)
    {
        $resultArray = array();
        $sql = "select * from registry_objects";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();
        $neccessaryObject = array();
        $index = 0;
        foreach ($objectsArray as $objectRecord) {
            if ($objectID == $objectRecord['id']) {
                $neccessaryObject = $objectRecord;
                array_splice($objectsArray, $index, 1);
                break;
            }
            $index++;
        }
        if (!$neccessaryObject) {
            return false;
        }
        foreach ($objectsArray as $objectRecord) {
            $sql = "select * from " . $objectRecord['name'] . "_structure_fields WHERE class = '" . $objectID . "'";
            $providerImplementedRecords = new SqlDataProvider([
                'sql' => $sql
            ]);
            $implementedRecords = $providerImplementedRecords->getModels();
            if (!$implementedRecords) {
                continue;
            }
            array_push($resultArray, ['object_id' => $objectID, 'used_object_id' => $objectRecord['id'], 'used_object_name' => $objectRecord['name']]);
        }
        array_splice($resultArray, 0, 0, ['object_id' => $objectID, 'used_object_id' => $neccessaryObject['id'], 'used_object_name' => $neccessaryObject['name']]);
        return $resultArray;
    }*/

}
