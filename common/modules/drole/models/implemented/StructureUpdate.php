<?php
/**
 * Created by PhpStorm.
 * User: ENGINEER
 * Date: 1/8/2019
 * Time: 3:45 PM
 */

namespace common\modules\drole\models\implemented;

use common\modules\drole\models\gate\ObjectOperationsHandler;
use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;


class StructureUpdate
{
    public static function updateStructuresByInnerObjects($objectID, $objectName, $droleID, $insertNewFastStructure = true, $forAllDroles = false)
    {
        $parentObjects = self::insertNewObjectsToUpdateList(array(), $objectID, $objectName);//self::getAllObjectsWhereObjectIsPresent($objectName);
        if (!$parentObjects) {
            return false;
        }
        $usedStructuresForDroleID = array();
        //$usedAssemblyesStructures = array();
        $currentWorkObjectID = null;
        $isNewObject = true;
        //while ($parentObjects != null && $incrementObjects < count($parentObjects)) {
        foreach ($parentObjects as $parentLine) {
            //$parentLine = $parentObjects[$incrementObjects];
            if ($currentWorkObjectID == null || $currentWorkObjectID != $parentLine['current_id']) {
                $currentWorkObjectID = $parentLine['current_id'];
                $isNewObject = true;
            } else {
                $isNewObject = false;
            }
            //$incrementObjects++;
            $assemblyList = array();
            $childAssemblyList = array();
            if ($forAllDroles) {
                $assemblyList = self::getAllAssembliesFromObjects($parentLine['parent_id'], $droleID, self::checkRights(self::getArrayForDrole($droleID)));
                $childAssemblyList = self::getAllAssembliesFromObjects($parentLine['current_id'], $droleID, self::checkRights(self::getArrayForDrole($droleID)));
            } else {
                $assemblyList = self::getAssemblyListInParent($parentLine['parent_name'], $droleID);
                $childAssemblyList = self::getAssemblyListInParent($parentLine['current_name'], $droleID);
            }
            if ($isNewObject) {
                $usedStructuresForDroleID = array();
            }
            if ($assemblyList && $assemblyList != 1) {
                foreach ($assemblyList as $parentsAssembly) {
                    $jsonString = self::getPresentJsonStructureFromPresents($usedStructuresForDroleID, $parentsAssembly['drole_id']);
                    if (!$jsonString && $insertNewFastStructure) {
                        $childCurrentAssembly = self::getActiveAssemblyForID($childAssemblyList, $droleID);
                        if (!$childCurrentAssembly) {
                            continue;
                        }
                        $jsonString = StructureOperationHandler::getFastStructureTreeForAssembly($parentsAssembly['drole_id'],
                            $parentLine['current_name'], $childCurrentAssembly['assembly_id']);
                        if (!$jsonString) {
                            //delete all from internal records
                            continue;
                        } else {
                            $usedStructuresForDroleID[$parentsAssembly['drole_id']] = $jsonString;
                            $jsonString = json_decode($jsonString, true);
                        }
                        StructureOperationHandler::updateFastStructureDeeply($parentLine['current_id'], $parentLine['current_name'],
                            $parentsAssembly['drole_id'], $childCurrentAssembly['assembly_id'], $usedStructuresForDroleID[$parentsAssembly['drole_id']]);
                    }
                    self::updateAllRecordsByAssembly($parentLine['current_id'],
                        $parentLine['current_name'], $usedStructuresForDroleID[$parentsAssembly['drole_id']], $parentLine['parent_id'],
                        $parentLine['parent_name'], $parentsAssembly['drole_id'], true, $parentsAssembly['assembly_id']);
                }
            }
        }
    }

    public static function insertNewObjectsToUpdateList($parentObjects, $newObjectID, $newObjectName)
    {
        if (count($parentObjects) > 8) {
            return $parentObjects;
        }
        $newParentObjects = self::getAllObjectsWhereObjectIsPresent($newObjectID, $newObjectName);
        $innerObjects = array();
        if (!$newParentObjects) return $parentObjects;
        $index = 0;
        foreach ($newParentObjects as $newObjectLine) {
            $isPresent = false;
            foreach ($parentObjects as $parentObjectLine) {
                if ($newObjectLine['parent_id'] == $parentObjectLine['parent_id'] ||
                    $newObjectLine['parent_id'] == $parentObjectLine['current_id']) {
                    $isPresent = true;
                    break;
                }
            }
            if (!$isPresent) {
                array_push($parentObjects, $newObjectLine);
                $childIncludeObjects = self::insertNewObjectsToUpdateList($parentObjects, $newObjectLine['parent_id'],
                    $newObjectLine['parent_name']);
                foreach ($childIncludeObjects as $includeLine) {
                    array_push($innerObjects, $includeLine);
                }
            }
        }
        foreach ($innerObjects as $newObjectLine) {
            $isPresent = false;
            foreach ($parentObjects as $parentObjectLine) {
                if ($newObjectLine['parent_id'] == $parentObjectLine['parent_id'] ||
                    $newObjectLine['parent_id'] == $parentObjectLine['current_id']) {
                    $isPresent = true;
                    break;
                }
            }
            if (!$isPresent) {
                array_push($parentObjects, $newObjectLine);
            }
        }
        return $parentObjects;
    }

    public static function getAllObjectsWhereObjectIsPresent($objectID, $objectName)
    {
        $sql = "SELECT ('$objectID') as current_id, ('$objectName') as current_name, object_id as parent_id, (select name from registry_objects 
where registry_objects.id = object_id) as parent_name FROM " .
            $objectName . "_implemented_records_objects group by object_id";
        $providerImplementedRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerImplementedRecords || count($providerImplementedRecords) < 1) {
            return false;
        }
        return $providerImplementedRecords;
    }

    public static function getAllAssembliesFromObjects($objectID, $droleID, $right = 0)
    {
        if ($right == 0) {
            return false;
        }
        if ($right == 2) {
            $sql = "SELECT drole_id, assembly_id FROM registry_drole_assembly where drole_id in 
(SELECT turtle.id FROM registry_drole_base join registry_drole_base as turtle on registry_drole_base.company_id = turtle.company_id 
and registry_drole_base.service_id = turtle.service_id WHERE registry_drole_base.id = '$droleID') and object_id = '$objectID' 
and drole_id != '" . RegistryDescriptionRolesModel::$rolesArray['superadmin'] . "' and active = '1'";
        } else if ($right == 1) {
            $sql = "SELECT drole_id, assembly_id FROM registry_drole_assembly where object_id = '$objectID' and active = '1'";
        }
        $providerImplementedRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerImplementedRecords || count($providerImplementedRecords) < 1) {
            return false;
        }
        return $providerImplementedRecords;
    }

    public static function checkRights($arrayDrole)
    {
        if (!$arrayDrole || !isset($arrayDrole[0]['role_id'])) {
            return 0;
        }
        if ($arrayDrole[0]['role_id'] == RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            return 1;
        }
        if ($arrayDrole[0]['role_id'] == RegistryDescriptionRolesModel::$rolesArray['admin']) {
            return 2;
        }
        return 0;
    }

    public static function getArrayForDrole($droleID)
    {
        $sql = "SELECT * FROM registry_drole_base where id = '$droleID'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return $providerRecords;
    }

    public static function getAssemblyListInParent($parentObjectName, $droleID)
    {
        $sql = "SELECT drole_id, assembly_id FROM " . $parentObjectName . "_structure_use_fast where drole_id = '$droleID'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return $providerRecords;
    }

    public static function getPresentJsonStructureFromPresents($usedStructuresForDroleID, $droleID)
    {
        foreach ($usedStructuresForDroleID as $line) {
            if ($line[0] == $droleID) {
                return $line[1];
            }
        }
        return false;
    }

    public static function getActiveAssemblyForID($assemblyArray, $droleID)
    {
        if (!$assemblyArray) return false;
        foreach ($assemblyArray as $assemblyLine) {
            if ($assemblyLine['drole_id'] == $droleID) {
                return $assemblyLine;
            }
        }
        return false;
    }

    private static function updateAllRecordsByAssembly($childObjectID, $childObjectName, $childStructure, $parentObjectID,
                                                       $parentObjectName, $droleID, $tryInsert = false, $assemblyID = false)
    {
        $internalRecords = self::getAllRecordsIDForUpdateByObjectID($childObjectName, $parentObjectID);
        if (!$internalRecords) {
            return false;
        }
        foreach ($internalRecords as $internalIDLine) {
            //check if present in parent
            self::updateInternalRecord($childObjectID, $childObjectName, $childStructure, $parentObjectID, $parentObjectName,
                $internalIDLine['implemented_id'], $droleID, $tryInsert, $assemblyID);
        }
    }

    //update record

    public static function getAllRecordsIDForUpdateByObjectID($childObjectName, $parentObjectID)
    {
        $sql = "SELECT * FROM " . $childObjectName . "_implemented_records_objects WHERE object_id = '$parentObjectID'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return $providerRecords;
    }

    public static function updateInternalRecord($childObjectID, $childObjectName, $childStructure, $parentObjectID,
                                                $parentObjectName, $parentImplementedID, $droleID, $tryInsert = false, $assemblyID = false)
    {
        $internalRecords = self::getInnerRecordsForInternal($childObjectName, $parentImplementedID);
        /*if ($parentObjectName = 'descriptioncoin') {
            echo "==[for $droleID found: " . json_encode($internalRecords) . "]==";
        }*/
        if (!$internalRecords) {
            //delete implemented record from parent
        } else {
            $line = array();
            foreach ($internalRecords as $recordValue) {
                $newInternalDataLine = ObjectOperationsHandler::getFastRecord($childObjectID, $childObjectName, $droleID,
                    $recordValue['record_id'], json_decode($childStructure, true));
                if (!$newInternalDataLine) {

                } else
                    array_push($line, $newInternalDataLine);
            }
            $indexedJson = str_replace(array("\r\n", "\r", "\n"), '', (ObjectOperationsHandler::returnIndexedJSON($line, false)));
            $sql = "UPDATE " . $parentObjectName . "_data_use_implemented SET json_field = '$indexedJson'
WHERE implemented_id = '$parentImplementedID' AND drole_id = '$droleID'";
            if ($tryInsert && $assemblyID) {
                if (!RecordUpdate::checkIfPresentImplementedParentDroleAssembly($parentObjectID, $parentObjectName, $parentImplementedID, $droleID, $assemblyID))
                    $sql = "insert into " . $parentObjectName . "_data_use_implemented (implemented_id, drole_id, assembly_id, json_field) 
                values ('$parentImplementedID', '$droleID', '$assemblyID', '$indexedJson')";
            }
            \Yii::$app->db->createCommand($sql)->execute();
        }
    }

    private static function getInnerRecordsForInternal($objectName, $internalRecordID)
    {
        $sql = "SELECT record_id from " . $objectName . "_implemented_records where implemented_id = '$internalRecordID' order by turn";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        return $providerRecords;
    }

    public static function getAssemblyesWhereFirstLineObjectIsPresent($searchedObjectID, $parentObjectID, $parentObjectName, $drole_id = false)
    {
        $droleSubquery = " where " . $parentObjectName . "_structure_use_fast.drole_id = '$drole_id'";
        if (!$drole_id) {
            $droleSubquery = "";
        }
        $sql = "SELECT ('$parentObjectID') as object_id, ('$parentObjectName') as object_name, " . $parentObjectName . "_structure_use_fast.assembly_id, (select key from jsonb_each((SELECT json_structure::jsonb from " .
            $parentObjectName . "_structure_use_fast where " . $parentObjectName .
            "_structure_use_fast.assembly_id = couple.assembly_id)) where value->>'object' = '$searchedObjectID') as indexpos from " .
            $parentObjectName . "_structure_use_fast join " . $parentObjectName . "_structure_use_fast as couple on " . $parentObjectName .
            "_structure_use_fast.assembly_id = couple.assembly_id" . $droleSubquery;

        $providerImplementedRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerImplementedRecords || count($providerImplementedRecords) < 1) {
            return false;
        }
        return $providerImplementedRecords;
    }

    public static function updateParentInternalByRecordID($objectID, $objectName, $recordID)
    {
        //get all structures
        $sql = "SELECT " . $objectName . "_implemented_records_objects.* FROM " . $objectName . "_implemented_records join " . $objectName . "_implemented_records_objects 
on " . $objectName . "_implemented_records.implemented_id = " . $objectName . "_implemented_records_objects.implemented_id where 
" . $objectName . "_implemented_records.record_id = '$recordID'";
        $providerRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$providerRecords || count($providerRecords) < 1) {
            return false;
        }
        //update all parent records with internal
    }


}