<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 1/14/2019
 * Time: 1:43 PM
 */

namespace common\modules\drole\models\implemented;


use common\modules\drole\models\gate\StructureOperationHandler;

class RecordUpdate
{
    public static function updateAllImplementedRecords($objectID, $objectName, $recordID, $droleID, $insertIfNotExist = false)
    {
        $parentObjects = self::getAllObjectsWithImplementedRecords(array(), $objectID, $objectName);//self::getAllObjectsWhereObjectIsPresent($objectName);
        if (!$parentObjects) {
            return false;
        }
        $usedStructuresForDroleID = array();
        $usedStructuresForDroleIDArray = array();
        //$usedAssemblyesStructures = array();
        $currentWorkObjectID = null;
        $isNewObject = true;
        $currentRecordsArray = array();
        $currentRecordsArray[$objectID] = array(0 => $recordID);

        foreach ($parentObjects as $parentLine) {
            //$parentLine = $parentObjects[$incrementObjects];
            if ($currentWorkObjectID == null || $currentWorkObjectID != $parentLine['current_id']) {
                $currentWorkObjectID = $parentLine['current_id'];
                $isNewObject = true;
            } else {
                $isNewObject = false;
            }
            $assemblyList = StructureUpdate::getAllAssembliesFromObjects($parentLine['parent_id'], $droleID, StructureUpdate::checkRights(StructureUpdate::getArrayForDrole($droleID)));
            $childAssemblyList = StructureUpdate::getAllAssembliesFromObjects($parentLine['current_id'], $droleID, StructureUpdate::checkRights(StructureUpdate::getArrayForDrole($droleID)));
            if ($isNewObject) {
                $usedStructuresForDroleID = array();
                $usedStructuresForDroleIDArray = array();
            }
            $nextRecords = array();
            if ($assemblyList && $assemblyList != 1) {
                foreach ($assemblyList as $parentsAssembly) {

                    $jsonString = StructureUpdate::getPresentJsonStructureFromPresents($usedStructuresForDroleID, $parentsAssembly['drole_id']);

                    if (!$jsonString) {
                        $childCurrentAssembly = StructureUpdate::getActiveAssemblyForID($childAssemblyList, $parentsAssembly['drole_id']);
                        if (!$childCurrentAssembly) {
                            continue;
                        }
                        $jsonString = StructureOperationHandler::getFastStructureTreeForAssembly($parentsAssembly['drole_id'],
                            $parentLine['current_name'], $childCurrentAssembly['assembly_id']);
                        if (!$jsonString) {
                            //delete all from internal records
                            continue;
                        }
                        $usedStructuresForDroleID[$parentsAssembly['drole_id']] = $jsonString;
                        $usedStructuresForDroleIDArray[$parentsAssembly['drole_id']] = json_decode($jsonString, true);
                        /*StructureOperationHandler::updateFastStructureDeeply($parentLine['current_id'], $parentLine['current_name'],
                            $parentsAssembly['drole_id'], $childCurrentAssembly['assembly_id'], $jsonString);
                        */
                    }

                    foreach ($currentRecordsArray[$parentLine['current_id']] as $currentRecordID) {
                        $nextRecords = self::addRecordsToArray($nextRecords, self::updateRecordInImplemented($parentLine['current_id'],
                            $parentLine['current_name'], $currentRecordID, $parentLine['parent_id'], $parentLine['parent_name'],
                            $usedStructuresForDroleID[$parentsAssembly['drole_id']], $parentsAssembly['drole_id'], $parentsAssembly['assembly_id']));
                    }
                    /*echo "records array: " . json_encode($nextRecords);
                    exit;*/
                }
            }
            //
            $currentRecordsArray[$parentLine['parent_id']] = $nextRecords;
        }
    }

    public static function getAllObjectsWithImplementedRecords($parentObjects, $newObjectID, $newObjectName)
    {
        return StructureUpdate::insertNewObjectsToUpdateList($parentObjects, $newObjectID, $newObjectName);
    }

    public static function addRecordsToArray($recordsArray, $resultArray)
    {
        if (!$resultArray) {
            return $recordsArray;
        }
        foreach ($resultArray as $resultArrayLine) {
            $isPresent = false;
            foreach ($recordsArray as $mainArrayLine) {
                if ($resultArrayLine == $mainArrayLine) {
                    $isPresent = true;
                    break;
                }
            }
            if (!$isPresent) {
                array_push($recordsArray, $resultArrayLine);
            }
        }
        return $recordsArray;
    }

    public static function updateRecordInImplemented($childObjectID, $childObjectName, $childObjectsRecordID, $parentObjectID,
                                                     $parentObjectName, $childStructure, $droleID, $assemblyID)
    {
        $internalRecords = self::getAllRecordsIDForUpdateByObjectsRecordID($childObjectID, $childObjectName, $childObjectsRecordID,
            $parentObjectID, $parentObjectName);
        if (!$internalRecords) {
            return false;
        }
        $resultArray = array();
        foreach ($internalRecords as $internalIDLine) {
            if ($internalIDLine['record_id'] == null || $internalIDLine['record_id'] == 'NULL') {
                self::deleteUnusedImplemented($childObjectName, $internalIDLine['implemented_id']);
                continue;
            }
            StructureUpdate::updateInternalRecord($childObjectID, $childObjectName, $childStructure, $parentObjectID, $parentObjectName,
                $internalIDLine['implemented_id'], $droleID, true, $assemblyID);
            array_push($resultArray, $internalIDLine['record_id']);
        }
        return $resultArray;
    }

    public static function getAllRecordsIDForUpdateByObjectsRecordID($childObjectID, $childObjectName, $childObjectsRecordID, $parentObjectID, $parentObjectName)
    {
        $fieldsNamesArray = self::getFieldNameByIDObject($parentObjectName, $childObjectID);
        if (!$fieldsNamesArray) {
            return false;
        }
        $subquery = self::createSubQuery($parentObjectName, $childObjectName, $fieldsNamesArray) . " as record_id";
        $sql = "SELECT " . $childObjectName . "_implemented_records_objects.*, $subquery FROM " . $childObjectName .
            "_implemented_records_objects where 
" . $childObjectName . "_implemented_records_objects.implemented_id in (SELECT implemented_id FROM " . $childObjectName .
            "_implemented_records WHERE record_id = '$childObjectsRecordID') and " . $childObjectName .
            "_implemented_records_objects.object_id = '$parentObjectID'";
        /*$sql = "SELECT * FROM " . $childObjectName . "_implemented_records_objects where object_id = '$parentObjectID' and
implemented_id in (SELECT " . $childObjectName . "_implemented_records.implemented_id FROM " . $childObjectName . "_implemented_records 
WHERE record_id = '$childObjectsRecordID')";*/
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return $providerRecords;
    }

    private static function getFieldNameByIDObject($parentObjectName, $childObjectID)
    {
        $sql = "SELECT " . $parentObjectName . "_structure_fields.name FROM " . $parentObjectName . "_structure_fields where class = '$childObjectID'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return $providerRecords;
    }

    private static function createSubQuery($objectName, $childObjectName, $fieldsNamesArray)
    {
        $result = $objectName . "_data_use." . $fieldsNamesArray[0]['name'] . " = " . $childObjectName . "_implemented_records_objects.implemented_id ";
        for ($i = 1; $i < count($fieldsNamesArray); $i++) {
            $result .= " or " . $objectName . "_data_use." . $fieldsNamesArray[$i]['name'] . " = " . $childObjectName . "_implemented_records_objects.implemented_id ";
        }
        return "(select " . $objectName . "_data_use.id from " . $objectName . "_data_use where " . $result . ")";
    }

    private static function deleteUnusedImplemented($objectName, $implementedID)
    {
        $delete = "delete from " . $objectName . "_implemented_records_objects where implemented_id = '$implementedID'";
        \Yii::$app->db->createCommand($delete)->execute();
        $delete = "delete from " . $objectName . "_implemented_records where implemented_id = '$implementedID'";
        \Yii::$app->db->createCommand($delete)->execute();
    }

    //check present implemented records

    //check in original object

    public static function checkIfPresentImplementedParentDroleAssembly($objectID, $objectName, $implementedID, $droleID, $assemblyID)
    {
        $sql = "SELECT " . $objectName . "_data_use_implemented.* FROM " . $objectName .
            "_data_use_implemented WHERE implemented_id = '$implementedID' and drole_id = '$droleID' and assembly_id = '$assemblyID'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return true;
    }

    public static function deleteImplementedRecordsFromOriginObjectWithEmptyParentID($parentObjectID, $parentObjectName, $childObjectID, $childObjectName)
    {
        $fieldsNamesArray = self::getFieldNameByIDObject($parentObjectName, $childObjectID);
        if (!$fieldsNamesArray) {
            return false;
        }
        $subquery = self::createSubQuery($parentObjectName, $childObjectName, $fieldsNamesArray);
        $sql = "SELECT * FROM " . $childObjectName .
            "_implemented_records_objects where $subquery is null and 
 " . $childObjectName .
            "_implemented_records_objects.object_id = '$parentObjectID'";
        /*$sql = "SELECT * FROM " . $childObjectName . "_implemented_records_objects where object_id = '$parentObjectID' and
implemented_id in (SELECT " . $childObjectName . "_implemented_records.implemented_id FROM " . $childObjectName . "_implemented_records
WHERE record_id = '$childObjectsRecordID')";*/
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        foreach ($providerRecords as $notEnableLine) {
            $sql = "delete from " . $childObjectName . "_implemented_records where implemented_id = '" . $notEnableLine['implemented_id'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        $sql = "delete FROM " . $childObjectName .
            "_implemented_records_objects where $subquery is null and 
 " . $childObjectName .
            "_implemented_records_objects.object_id = '$parentObjectID'";
        \Yii::$app->db->createCommand($sql)->execute();
        return $providerRecords;
    }

    private static function checkIfPresentImplementedOrigin($objectID, $implementedID, $originObjectName)
    {
        $sql = "SELECT " . $originObjectName . "_implemented_records.implemented_id as first, " . $originObjectName .
            "_implemented_records_objects.implemented_id as second FROM " . $originObjectName . "_implemented_records join " .
            $originObjectName . "_implemented_records_objects on " . $originObjectName . "_implemented_records_objects.implemented_id = " .
            $originObjectName . "_implemented_records.implemented_id WHERE " . $originObjectName .
            "_implemented_records.implemented_id = '$implementedID' and " . $originObjectName .
            "_implemented_records_objects.object_id = '$objectID'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return true;
    }
}