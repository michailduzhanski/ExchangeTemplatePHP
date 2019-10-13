<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\object\LogObjectHandler;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\UUIDGenerator;
use yii\data\SqlDataProvider;

//use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;

/**
 * Description of ObjectOperationsHandler
 *
 * @author LILIYA
 */
class ObjectOperationsHandler
{

    //put your code here

    public static function checkPermissionForUpdate()
    {

    }

    public static function getAllAssemblyesForRecordID()
    {

    }

    public static function getAllCasesDataOfUse($objectID, $recordID)
    {
        $resultArray = array();
        $arrayAllObjects = self::getAllObjectsArray();
        $neccessaryObject = self::getCurrentObjectArray($arrayAllObjects, $objectID);
        if (!$neccessaryObject) {
            return false;
        }
        foreach ($arrayAllObjects as $objectRecord) {
            $foundRecords = self::getImplementedRecordsForRecordAndObject($neccessaryObject, $objectRecord, $recordID);
            if (!$foundRecords) {
                continue;
            }
            //try update implemented records
            foreach ($foundRecords as $foundRecord) {
                array_push($resultArray, ['object_id' => $objectID, 'record_id' => $recordID, 'used_object_id' => $objectRecord['id'], 'used_object_name' => $objectRecord['name'], 'used_record_id' => $foundRecord['id']]);
            }
        }
        return $resultArray;
    }

    public static function getAllObjectsArray()
    {
        $sql = "select * from registry_objects order by id desc";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        return $providerAllObjects->getModels();
    }

    public static function getCurrentObjectArray($arrayAllObjects, $objectID)
    {
        $neccessaryObject = false;
        foreach ($arrayAllObjects as $objectRecord) {
            if ($objectID == $objectRecord['id']) {
                $neccessaryObject = $objectRecord;
                break;
            }
        }
        if (!$neccessaryObject) {
            return false;
        }
        return $neccessaryObject;
    }

    public static function getImplementedRecordsForRecordAndObject($neccessaryObject, $currentObjectRecordArray, $recordID)
    {
        $sql = "select * from " . $currentObjectRecordArray['name'] . "_structure_fields WHERE class = '" . $neccessaryObject['id'] . "'";
        $providerImplementedRecords = new SqlDataProvider([
            'sql' => $sql
        ]);
        $implementedRecords = $providerImplementedRecords->getModels();
        if (!$implementedRecords) {
            return false;
        }
        $fieldsForSearch = '';
        foreach ($implementedRecords as $implementedRecord) {
            $fieldsForSearch .= $implementedRecord['name'] . ' in (select implemented_id from ' . $neccessaryObject['name'] . '_implemented_records where record_id = \'' . $recordID . '\') or ';
        }
        $sql = "select * from " . $currentObjectRecordArray['name'] . "_data_use where " . substr($fieldsForSearch, 0, strlen($fieldsForSearch) - 4);
        $providerUsedRecords = new SqlDataProvider([
            'sql' => $sql
        ]);
        $foundRecords = $providerUsedRecords->getModels();
        if (!$foundRecords) {
            return false;
        }
        return $foundRecords;
    }

    //new mechanism for update all records

    public static function getAllParentsRecordsForCurrentRecordID($objectID, $recordID)
    {
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        if (!$objectName) {
            return false;
        }
        $usedParentsRecords = self::getImplementedRecordsForObject($objectName, $recordID);
        if (!$usedParentsRecords) {
            return false;
        }
        $index = 0;
        while ($index < count($usedParentsRecords)) {
            array_merge($usedParentsRecords, self::getImplementedRecordsForObject($usedParentsRecords[$index]['object_id'], $usedParentsRecords[$index]['record_id']));
        }
        return $usedParentsRecords;
    }

    public static function getImplementedRecordsForObject($objectName, $recordID)
    {
        $sql = "SELECT * FROM " . $objectName . "_implemented_records_objects WHERE implemented_id IN (SELECT " .
            $objectName . "_implemented_records.implemented_id FROM " . $objectName . "_implemented_records WHERE " .
            $objectName . "_implemented_records.record_id = '$recordID')";
        $providerImplementedRecords = new SqlDataProvider([
            'sql' => $sql
        ]);
        $implementedRecords = $providerImplementedRecords->getModels();
        if (!$implementedRecords) {
            return false;
        }
        return $implementedRecords;
    }

    //return JSON only object

    public static function updateFastRecord($objectID, $droleID, $recordID, $contactID, $incomingMap, $newValue)
    {
        //return;
        if (!$incomingMap || !$objectID || !$droleID || !$recordID || !$contactID) {
            return APIHandler::getErrorArray(404, "Some values is not present.");
        }
        $fastStructure = StructureOperationHandler::getFastStructureWithCheck($objectID, $droleID);
        $fastData = self::getFastRecord($objectID, $droleID, $recordID);

        if (!$fastStructure) {
            return APIHandler::getErrorArray(404, "Not found structure.");
        } else {
            $fastStructure = json_decode($fastStructure, true);
            if (!$fastData || !$fastData->getModels()) {
                $fastData = self::getJsonDataFromJsonStructureArray($objectID, $droleID, $recordID, $fastStructure);
            } else {
                $fastData = json_decode($fastData->getModels()[0]['json_field'], true);
            }
            //echo print_r($fastStructure, true);
            //echo '-----------------------------------------';exit;
        }

        $mapElements = explode('.', $incomingMap);
        //check trying edit id field
        if ($mapElements[0] == 0) {
            return APIHandler::getErrorArray(404, "You cant edit id.");
        }
        $permission = 2; //edit permission

        /*$lastStructureElement = null;
        if (count($mapElements) < 3) {
            $lastStructureElement = ['id' => $objectID, 'name' => RegistryObjects::getObjectNameByID($objectID)->name];
        } else {

        }*/
        $lastStructureElement = self::getLastObjectStructureInMapWithPermission($fastStructure, $mapElements, $permission);
        $parentStructureObjectID = $objectID;
        $parentStructure = $fastStructure;
        //check, if old and new values is equals
        $dataRecord = self::getRecordIDForChange($fastData, $mapElements);
        if (count($mapElements) > 2) {
            $parentStructure = self::getRootEditObjectFromStructure($mapElements, $fastStructure);
            $parentStructureObjectID = $parentStructure['object'];
        }
        $parentStructureObjectName = RegistryObjects::getObjectNameByID($parentStructureObjectID)->name;
        //check if we have same value;

        $dataRecordID = null;
        //check the type of inserted
        if (count($mapElements) % 2 === 0) {
            //insert in implemented record. only for object.
            if (!UUIDGenerator::isUUID($newValue)) {
                return APIHandler::getErrorArray(404, "New value must be UUID.");;
            }
            $usefulObjectID = $parentStructure[$mapElements[0]]['object'];
            if (!RegistryObjects::getObjectNameByID($usefulObjectID) || !isset(RegistryObjects::getObjectNameByID($usefulObjectID)->name)) {
                //echo json_encode($parentStructure);
                exit;
            }
            $usefulObjectName = RegistryObjects::getObjectNameByID($usefulObjectID)->name;
            $fieldIDInParent = $parentStructure[$mapElements[0]]['id'];
            $fieldNameInParent = $parentStructure[$mapElements[0]]['name'];
            $parentRecordID = $dataRecord[0];
            $dataRecordID = $parentRecordID;
            $oldValue = $fastData[$mapElements[count($mapElements) - 2]];
            $currentFieldValue = null;
            $sql = "select $fieldNameInParent from " . $parentStructureObjectName . "_data_use where id = '" . $dataRecordID . "'";
            $currentFieldValue = \Yii::$app->db->createCommand($sql)->queryOne();
            if (!$currentFieldValue || count($currentFieldValue) < 1) {
                //insert
                $currentFieldValue = null;
            } else {
                //update
                $currentFieldValue = $currentFieldValue[$fieldNameInParent];
            }
            $implementedRecordID = self::insertIntoImplementedRecord($usefulObjectID, $usefulObjectName, $parentStructureObjectID, $newValue, $parentRecordID, $fieldIDInParent, $mapElements[count($mapElements) - 1], $currentFieldValue);
            if ($oldValue == '' || $oldValue == 'null') {
                self::updateValueInRecord($parentStructureObjectName, $parentRecordID, $fieldIDInParent, $fieldNameInParent, '', $implementedRecordID, $droleID, $contactID);
            }
        } else {
            if ($lastStructureElement == null) {
                return APIHandler::getErrorArray(404, "You have not permission.");
            }
            if (isset($dataRecord[$mapElements[count($mapElements) - 1]]) && $dataRecord[$mapElements[count($mapElements) - 1]] == $newValue) {
                return APIHandler::getErrorArray(404, "You are trying change same value.");
            }
            if (UUIDGenerator::isUUID($newValue) && RegistryObjects::getObjectNameByID($newValue)->name) {
                return APIHandler::getErrorArray(404, "The type of the field is object.");
            }
            $dataRecordID = $dataRecord[0];
            //updateValueInRecord($objectName, $recordID, $fieldToUpdateID, $fieldToUpdateName, $oldValue, $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0')
            $oldValue = (isset($dataRecord[$mapElements[count($mapElements) - 1]]) ? $dataRecord[$mapElements[count($mapElements) - 1]] : '');
            self::updateValueInRecord($parentStructureObjectName, $dataRecord[0], $lastStructureElement['id'], $lastStructureElement['name'], $oldValue, $newValue, $droleID, $contactID);
        }
        //echo "newJsonRecord: " . print_r(json_decode(StructureOperationHandler::getFastStructureWithCheck($parentStructureObjectID, $droleID), true), true);
        $newJsonRecord = self::getJsonDataFromJsonStructureArray($parentStructureObjectID, $droleID, $dataRecordID, json_decode(StructureOperationHandler::getFastStructureWithCheck($parentStructureObjectID, $droleID), true));
        self::setFastRecord($parentStructureObjectID, $droleID, $dataRecordID, self::returnIndexedJSONForData($newJsonRecord));
        $nextLevelOfRecords = self::getListWhereUseCurrentRecordFromImplementedRecords($parentStructureObjectName, $dataRecordID);

        if (!$nextLevelOfRecords || !$nextLevelOfRecords->getModels()) {
            return APIHandler::getErrorArray(200, "success. not found implemented records.");
        } else {
            $nextLevelOfRecords = $nextLevelOfRecords->getModels();
        }
        foreach ($nextLevelOfRecords as $implementedTokenRecord) {
            self::recursiveUpdateFastDataUse($parentStructureObjectID, $dataRecordID, $implementedTokenRecord);
        }
    }

    public static function getFastRecord($objectID, $objectName, $droleID, $recordID, $structureOfAssembly)
    {
        //create query string
        $queryString = "";
        for ($structureIndex = 0; $structureIndex < count($structureOfAssembly); $structureIndex++) {
            if ($structureOfAssembly[$structureIndex]['nested'] == "false") {
                $queryString .= "fast.\"" . $structureOfAssembly[$structureIndex]['name'] . "\" as \"" . $structureIndex . "\", ";
            } else {
                $queryString .= "(select json_field from " . $objectName . "_data_use_implemented where " . $objectName .
                    "_data_use_implemented.implemented_id = " . $objectName . "_data_use.\"" . $structureOfAssembly[$structureIndex]['name'] .
                    "\" and drole_id = '" . $droleID . "') as \"" . $structureIndex . "\", ";
            }
        }
        $queryString = substr($queryString, 0, strlen($queryString) - 2);
        $sql = "select $queryString from " . $objectName . "_data_use join (select * from " . $objectName . "_data_use where id = '$recordID' order by date_create desc) as fast on fast.id = " . $objectName . "_data_use.id ";
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function getJsonDataFromJsonStructureArray($currentObjectID, $droleID, $recordID, $globalStructure)
    {
        $resultArray = array();
        //get data record from db
        $fieldsWithTypes = '';
        $onlyFields = '';
        //$globalStructure = json_decode($extGlobalStructure, true);
        foreach ($globalStructure as $key => $record) {
            $fieldsWithTypes .= $record['name'] . " " . self::returnReverseType($record['type']) . ", ";
            $onlyFields .= $record['name'] . ', ';
        }
        if (strlen($fieldsWithTypes) <= 2) {
            return false;
        }
        $fieldsWithTypes = substr($fieldsWithTypes, 0, count($fieldsWithTypes) - 3);
        $onlyFields = substr($onlyFields, 0, count($onlyFields) - 3);
        //operation for standard query. company only
        $currentDataRecord = self::getRecordForObjectAndID($currentObjectID, $droleID, $recordID, $onlyFields, $fieldsWithTypes);
        if (!$currentDataRecord || count($currentDataRecord) < 1) {
            return false;
        }
        $currentDataRecord = $currentDataRecord[0];
        //create real fields with a data
        $index = 0;
        foreach ($globalStructure as $key => $record) {
            $currentValue = $currentDataRecord[$record['name']];
            $nested = $record['nested'];
            if (!$nested || $nested == 'false') {
                $resultArray[$index] = $currentValue;
            } else {
                $nestedRecords = self::getImplementedRecordsForNested($record['object'], $currentValue);
                if (!$nestedRecords || $nestedRecords == '' || $nestedRecords == NULL) {
                    $resultArray[$index] = false;
                } else {
                    $nestedResultArray = array();
                    $nestedIndex = 0;
                    foreach ($nestedRecords as $localRecord) {
                        $nestedResultArray[$nestedIndex] = self::getJsonDataFromJsonStructureArray($record['object'], $droleID, $localRecord['id'], $nested);
                        $nestedIndex++;
                    }
                    $resultArray[$index] = $nestedResultArray;
                }
            }
            $index++;
        }
        return $resultArray;
    }

    private static function returnReverseType($innerType)
    {
        switch ($innerType) {
            case 'image' :
                return 'text';
            default:
                return $innerType;
        }
    }

    //get data from structure

    private static function getRecordForObjectAndID($currentObjectID, $droleID, $recordID, $onlyFields, $fieldsWithTypes)
    {
        $sql = "select * from getdatarecordforcompanyowner('$currentObjectID', '$droleID', '$recordID', '$onlyFields') as ds($fieldsWithTypes)";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    private static function getImplementedRecordsForNested($currentObjectID, $implementedID)
    {
        $sql = "select * from getimplementedrecords('$currentObjectID', '$implementedID') as ds(id uuid)";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    private static function getLastObjectStructureInMapWithPermission($structureArray, $mapElements, $permission)
    {
        //if (count($mapElements) < 3) {
        //    return false;
        //}
        //$lastObjectFromStructure = false;
        $isOdd = false;
        //check the type of inserted
        if (count($mapElements) % 2 === 0) {
            //insert in implemented record. only for object.
            $isOdd = true;
        }
        //
        $currentFastDataRecord = $structureArray;
        $mapIndex = 0;
        while ($mapIndex < count($mapElements)) {
            if (!isset($currentFastDataRecord[$mapElements[$mapIndex]])) {
                return false;
            }
            $currentFastDataRecord = $currentFastDataRecord[$mapElements[$mapIndex]];
            //if(!isset($currentFastDataRecord['perm'])){
            //echo "[$mapIndex] " . print_r($currentFastDataRecord, true);
            //exit;
            // }
            if ($currentFastDataRecord['perm'] < $permission) {
                return false;
            }
            //echo "[$mapIndex] " . $mapElements[$mapIndex] . " = " . print_r($currentFastDataRecord, true) . '---------------------------------------';
            if ($mapIndex != count($mapElements) - 1) {
                $currentFastDataRecord = $currentFastDataRecord['nested'];
            }
            if ((!$currentFastDataRecord && !$isOdd && $mapIndex < count($mapElements) - 2)) {
                //echo $currentFastDataRecord;
                return false;
            }
            $mapIndex = $mapIndex + 2;
        }
        return $currentFastDataRecord;
    }

    private static function getRecordIDForChange($fastData, $mapElements)
    {
        $mapIndex = 0;
        $currentFastDataRecord = $fastData;
        $maxCount = count($mapElements) - 1;
        if (count($mapElements) % 2 === 0) {
            $maxCount--;
        }
        while ($mapIndex < $maxCount) {
            if (count($currentFastDataRecord) > $mapElements[$mapIndex] && isset($currentFastDataRecord[$mapElements[$mapIndex]]))
                $currentFastDataRecord = $currentFastDataRecord[$mapElements[$mapIndex]];
            else return false;
            //$currentFastDataRecord = self::getNextLevelRecordData($mapElements[$mapIndex], $mapElements[$mapIndex], $currentFastDataRecord);
            /*$mapNestedIndex = $mapIndex + 1;
            if ($mapNestedIndex < count($mapElements)) {
                $currentFastDataRecord = self::getNextLevelRecordData($mapElements[$mapIndex], $mapElements[$mapNestedIndex], $currentFastDataRecord);
                //$currentRecord = $currentFastDataRecord[0];
            }*/
            $mapIndex++;
            //$mapIndex = $mapIndex + 2;
        }
        return $currentFastDataRecord;
    }

    private static function getRootEditObjectFromStructure($mapElements, $structure)
    {
        $nextLevelStructure = $structure;
        $mapIndex = 0;
        $resultObject = null;
        while ($mapIndex < count($mapElements)) {
            $nextElement = $nextLevelStructure[$mapElements[$mapIndex]];
            if (!$nextElement['nested']) {
                if ($mapIndex + 2 < count($mapElements))
                    return null;
                return $resultObject;
            } else {
                $nextLevelStructure = $nextElement['nested'];
                if ($mapIndex + 2 < count($mapElements))
                    //$resultObject = $nextElement['object'];
                    $resultObject = $nextElement;
            }
            $mapIndex = $mapIndex + 2;
        }
        return $resultObject;
    }

    public static function insertIntoImplementedRecord($objectID, $objectName, $objectIDParent, $recordID, $fieldID, $index, $currentFieldValue)
    {
        //echo "insertIntoImplementedRecord($objectID, $objectName, $objectIDParent, $recordID, $fieldID, $index, $currentFieldValue)";
        if ($currentFieldValue == null) {
            //insert new record
            $implementedID = UUIDGenerator::v4();
            //$sql = "insert into " . $objectName . "_implemented_records_objects values ('" . $implementedID . "', '$objectIDParent', '$recordIDParent', '$fieldID')";
            $sql = "insert into " . $objectName . "_implemented_records_objects values ('" . $implementedID . "', '$objectIDParent', '$fieldID')";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "insert into " . $objectName . "_implemented_records values ('" . $implementedID . "', '$recordID', '0')";
            \Yii::$app->db->createCommand($sql)->execute();

            /*//$sql = "select implemented_id from " . $objectName . "_implemented_records where record_id = '$recordID'";
            $sql = "select * from " . $objectName . "_implemented_records_objects where object_id = '$objectIDParent' and 
        field_id = '$fieldID' and " . $objectName . "_implemented_records_objects.implemented_id in 
        (select " . $objectName . "_implemented_records.implemented_id from " . $objectName . "_implemented_records where record_id = '$recordID')";
            $presentImplemented = \Yii::$app->db->createCommand($sql)->queryAll();
            $implementedID = null;
            if (!$presentImplemented || count($presentImplemented) < 1) {
                //insert new record
                $implementedID = UUIDGenerator::v4();
                //$sql = "insert into " . $objectName . "_implemented_records_objects values ('" . $implementedID . "', '$objectIDParent', '$recordIDParent', '$fieldID')";
                $sql = "insert into " . $objectName . "_implemented_records_objects values ('" . $implementedID . "', '$objectIDParent', '$fieldID')";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "insert into " . $objectName . "_implemented_records values ('" . $implementedID . "', '$recordID', '0')";
                \Yii::$app->db->createCommand($sql)->execute();
            }*/
            return $implementedID;
        } else {
            $implementedID = $currentFieldValue;
            $sql = "select * from " . $objectName . "_implemented_records where implemented_id = '$implementedID'";
            $presentElements = \Yii::$app->db->createCommand($sql)->queryAll();
            $isPresent = false;
            if ($index < 0 || $index > count($presentElements)) {
                $index = count($presentElements);
            }
            foreach ($presentElements as $presentRecord) {
                if ($presentRecord['record_id'] == $recordID) {
                    $isPresent = true;
                    break;
                }
            }
            if (!$isPresent) {
                $sql = "insert into " . $objectName . "_implemented_records values ('" . $implementedID . "', '$recordID', '$index')";
                \Yii::$app->db->createCommand($sql)->execute();
                if ($index == count($presentElements)) {
                    //do nothing
                } else {
                    array_splice($presentElements, $index, 0, ['implemented_id' => $implementedID, 'record_id' => $recordID, 'turn' => $index]);

                    for ($i = 0; $i < count($presentElements); $i++) {
                        if (!isset($presentElements[$i]['implemented_id'])) {
                            continue;
                        }
                        $sql = "update " . $objectName . "_implemented_records set turn = '$i' where implemented_id = '" . $presentElements[$i]['implemented_id'] . "' and record_id = '" . $presentElements[$i]['record_id'] . "'";
                        \Yii::$app->db->createCommand($sql)->execute();
                    }
                }
            }
            return $implementedID;
        }
    }

    private static function updateValueInRecord($objectName, $recordID, $fieldToUpdateID, $fieldToUpdateName, $oldValue, $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0')
    {
        $sql = "update " . $objectName . "_data_use set " . $fieldToUpdateName . " = '" . $newValue . "' where id = '" . $recordID . "'";
        \Yii::$app->db->createCommand($sql)->execute();
        LogObjectHandler::updateLogRecordForObject($objectName, "data_use", $recordID, $fieldToUpdateID, $oldValue, $newValue, $droleID, $contactID, $ipAddress);
    }

    public static function setFastRecord($objectID, $droleID, $recordID, $jsonString)
    {
        self::deleteFastRecord($objectID, $recordID, $droleID);
        if (count($jsonString) > 1) {
            $jsonString = self::returnIndexedJSONForData($jsonString);
        }
        $sql = "select insertIntoFastDataUse('$objectID', '$droleID', '$recordID', '$jsonString', '" . UUIDGenerator::v4() . "')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    private static function deleteFastRecord($objectID, $recordID, $droleID)
    {
        $sql = "select deleteCurrentRecordFastDataUse('$objectID', '$recordID', '$droleID')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public static function returnIndexedJSONForData($array)
    {
        return self::returnIndexedJSON($array, false);
    }

    public static function returnIndexedJSON($array, $isStructure = true)
    {
        $resultJsonString = '';
        if (!is_array($array) || count($array) == 0) {
            if ($array != 0 && !$array) {
                if ($isStructure) {
                    return '"false"';
                } else
                    return 'null';
            }
            if (strlen($array) > 1 && substr($array, 0, 2) == '{"') {
                return $array;
            }
            return '"' . $array . '"';
        }
        foreach ($array as $key => $record) {
            $resultJsonString .= '"' . $key . '":' . self::returnIndexedJSON($record, $isStructure) . ',';
        }
        return '{' . substr($resultJsonString, 0, strlen($resultJsonString) - 1) . '}';
    }

    //return structure for map. only from second line!!! else return false.

    private static function getListWhereUseCurrentRecordFromImplementedRecords($objectName, $recordID)
    {
        $sql = "SELECT " . $objectName . "_implemented_records_objects.*, " . $objectName . "_implemented_records.turn FROM "
            . $objectName . "_implemented_records INNER JOIN " . $objectName . "_implemented_records_objects ON "
            . "(" . $objectName . "_implemented_records_objects.implemented_id = " . $objectName . "_implemented_records.implemented_id) "
            . "where " . $objectName . "_implemented_records.record_id = '" . $recordID . "'";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    private static function recursiveUpdateFastDataUse($objectID, $implementedToken, $startTime = null)
    {
        //UpdateDataObjectHandler::getTimeMarker("recursiveUpdateFastDataUse start", $startTime);
        //echo "start recursiveUpdateFastDataUse($objectID, " . json_encode($implementedToken) . ")";
        $currentObjectName = RegistryObjects::getObjectNameByID($implementedToken['object_id']);
        if (!$currentObjectName) {
            return APIHandler::getErrorArray(404, "Not found object name. check it: " . $implementedToken['object_id']);;
        }
        $currentObjectName = $currentObjectName->name;
        $currentFieldName = self::getFieldNameForQueries($currentObjectName, $implementedToken['field_id']);
        if (!$currentFieldName || count($currentFieldName) < 1) {
            return APIHandler::getErrorArray(404, "Not found field name for currentObjectName. check it: " . $implementedToken['field_id']);
        }
        $currentFieldName = $currentFieldName['name'];
        //echo json_encode($implementedToken);
        //$assembliesList = self::getListOfAssembliesThatUseRecordID($currentObjectName, $implementedToken['record_id'], $recordID);
        //$currentObjectID, $currentObjectName, $implementedID, $searchedFieldName
        $presentFastStructuresForImplementedRecord = self::getRealStructuresForImplementedRecord($implementedToken['object_id'], $currentObjectName, $implementedToken['implemented_id'], $currentFieldName);
        $beforeObjectName = RegistryObjects::getObjectNameByID($objectID);
        if (!$beforeObjectName) {
            return APIHandler::getErrorArray(404, "Not found field name for beforeObjectName. check it: " . $beforeObjectName);
        }
        $beforeObjectName = $beforeObjectName->name;
        $sql = "select * from " . $beforeObjectName . "_implemented_records where implemented_id = '" . $implementedToken['implemented_id'] . "' order by turn";
        $listOfRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$listOfRecords) {
            return false;
        }
        foreach ($presentFastStructuresForImplementedRecord as $fastStructureWithImplemented) {
            $currentStructure = $fastStructureWithImplemented['json_structure'];
            if (!$currentStructure) {
                return false;
            }
            $currentStructure = json_decode($currentStructure, true);
            $indexOfNestedStructure = self::getSubStructureIndexForField($currentStructure, $implementedToken['field_id']);
            if ($indexOfNestedStructure < 0) {
                continue;
            }
            $beforeStructure = $currentStructure[$indexOfNestedStructure]['nested'];
            $arrayOfFastRecordOfPreviousObject = array();
            foreach ($listOfRecords as $implementedRecord) {
                $fastRecordOfTheCurrent = self::getFastRecord($objectID, $beforeObjectName, $fastStructureWithImplemented['drole_id'], $implementedRecord['record_id'], $beforeStructure);
                if (!$fastRecordOfTheCurrent) {
                    continue;
                }
                array_push($arrayOfFastRecordOfPreviousObject, $fastRecordOfTheCurrent);
            }
            self::updateImplementedNestedRecord($currentObjectName, $fastStructureWithImplemented['drole_id'], $fastStructureWithImplemented['assembly_id'], $implementedToken['implemented_id'], $arrayOfFastRecordOfPreviousObject);
        }
        $sql = "select id from " . $currentObjectName . "_data_use where $currentFieldName = '" . $implementedToken['implemented_id'] . "'";
        $listWhereUsedImplementedRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$listWhereUsedImplementedRecords || count($listWhereUsedImplementedRecords) < 1) {
            return true;
        }
        foreach ($listWhereUsedImplementedRecords as $currentRecord) {
            $nextLevelOfRecords = self::getListWhereUseCurrentRecordFromImplementedRecords($currentObjectName, $currentRecord['id']);
            if (!$nextLevelOfRecords || count($nextLevelOfRecords) < 1) {
                continue;
            }
            foreach ($nextLevelOfRecords as $implementedTokenRecord) {
                self::recursiveUpdateFastDataUse($objectID, $implementedTokenRecord, $startTime);
            }
        }
        //UpdateDataObjectHandler::getTimeMarker("recursiveUpdateFastDataUse end", $startTime);
    }

    private static function getFieldNameForQueries($currentObjectName, $fieldID)
    {
        $sql = "select name from " . $currentObjectName . "_structure_fields where id = '$fieldID'";
        $dataArray = \Yii::$app->db->createCommand($sql)->queryOne();
        return $dataArray;
    }

    private static function getRealStructuresForImplementedRecord($currentObjectID, $currentObjectName, $implementedID, $searchedFieldName)
    {
        $sql = "SELECT " . $currentObjectName . "_structure_use_fast.* from " . $currentObjectName . "_structure_use_fast join (SELECT * FROM registry_drole_assembly where drole_id in (SELECT id FROM registry_drole_base join (SELECT " .
            $currentObjectName . "_record_own.company_id, " . $currentObjectName . "_record_own.service_id FROM " .
            $currentObjectName . "_record_own WHERE " . $currentObjectName . "_record_own.id in (SELECT " . $currentObjectName .
            "_data_use.id FROM " . $currentObjectName . "_data_use WHERE " . $currentObjectName . "_data_use." . $searchedFieldName .
            " = '" . $implementedID . "')) as record_own on registry_drole_base.company_id = record_own.company_id AND
        registry_drole_base.service_id = record_own.service_id) and object_id = '" . $currentObjectID . "' and active = '1') 
        as registry_drole_assembly on registry_drole_assembly.drole_id = " . $currentObjectName . "_structure_use_fast.drole_id and 
        registry_drole_assembly.assembly_id = " . $currentObjectName . "_structure_use_fast.assembly_id";
        return \Yii::$app->db->createCommand($sql)->queryAll();;
    }

    private static function getSubStructureIndexForField($arrayOfStructure, $searchedFieldID)
    {
        for ($index = 0; $index < count($arrayOfStructure); $index++) {
            if ($arrayOfStructure[$index]['nested'] != "false" && $arrayOfStructure[$index]['id'] == $searchedFieldID) {
                return $index;
            }
        }
        return -1;
    }

    private static function updateImplementedNestedRecord($objectName, $droleID, $assemblyID, $implementedID, $nestedObjectDataArray)
    {
        //echo "try work with: updateImplementedNestedRecord($objectName, $droleID, $assemblyID, $implementedID, " . self::returnIndexedJSONForData($nestedObjectDataArray) . ")";
        \Yii::$app->db->createCommand("delete from " . $objectName . "_data_use_implemented where implemented_id = '$implementedID' 
        and drole_id = '$droleID'")->execute();
        //array_push($usedImplementedRecords, array('implemented' => $currentRecord[$structureJsonString[$mapIndex]['name']], 'drole' => $droleForAssembly));
        $sql = "insert into " . $objectName . "_data_use_implemented values ('$implementedID','$droleID','$assemblyID','" . self::returnIndexedJSONForData($nestedObjectDataArray) . "')";
        \Yii::$app->db->createCommand($sql)->execute();
    }
//$objectStructure[$mapValuesArray[0]]['object'], $nestedObjectName, $jsonIncomingObject['permission']['object_id'],
//            $jsonIncomingObject['work']['value']['record'][0]['value'], $jsonIncomingObject['work']['value']['record'][1]['field'],
//            $internalFieldValue, $jsonIncomingObject['work']['value']['record'][1]['map'], $jsonIncomingObject['work']['value']['record'][1]['value']
    public static function deleteFromImplementedRecord($objectID, $objectName, $objectIDParent, $recordID, $fieldID, $implementedID, $map, $internalRecordID)
    {
        $sql = "select * from " . $objectName . "_implemented_records where implemented_id = '$implementedID'";
        $presentElements = \Yii::$app->db->createCommand($sql)->queryAll();
        $isPresent = false;
        $index = -1;
        for ($i = 0; $i < count($presentElements); $i++) {
            $presentRecord = $presentElements[$i];
            if ($presentRecord['record_id'] == $internalRecordID) {
                $isPresent = true;
                $index = $i;
                break;
            }
        }
        if (!$isPresent)
            return APIHandler::getErrorArray(404, "Not found id in implemented records.");

        array_splice($presentElements, $index, 1);
        $sql = "delete from " . $objectName . "_implemented_records where implemented_id = '$implementedID' and turn = '$index'";
        \Yii::$app->db->createCommand($sql)->execute();
        for ($i = 0; $i < count($presentElements); $i++) {
            if (!isset($presentElements[$i]['implemented_id'])) {
                continue;
            }
            $sql = "update " . $objectName . "_implemented_records set turn = '$i' where implemented_id = '" .
                $presentElements[$i]['implemented_id'] . "' and record_id = '" . $presentElements[$i]['record_id'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();
        }
    }

    public static function updateAllRecordsRecursivelyAfterUpdateAssembly($objectID, $droleID, $objectName, $isNeedUpdateAssembly = true)
    {
        //echo "try updateAllRecordsRecursivelyAfterUpdateAssembly($objectID, $droleID, $objectName, $isNeedUpdateAssembly)";
        if ($isNeedUpdateAssembly) {
            StructureOperationHandler::updateAllAssembliesInObjectWithField($objectID, $droleID, $objectName, false, $typeOperation = 0);
        }
        //update all records that have this transaction
        $arrayOfRecords = self::getRecordInCurrentObjectForAllAssemblies($objectID, $droleID, $objectName);
        //echo " - data array: " . print_r($arrayOfRecords->getModels(), true) . " - ";
        if ($arrayOfRecords->getModels()) {
            $fastStructure = json_decode(StructureOperationHandler::getFastStructureWithCheck($objectID, $droleID), true);
            foreach ($arrayOfRecords->getModels() as $dataRecord) {
                $newFastData = self::getJsonDataFromJsonStructureArray($objectID, $droleID, $dataRecord['record_id'], $fastStructure);
                self::deleteFastRecord($objectID, $dataRecord['record_id'], $droleID);
                self::setFastRecord($objectID, $droleID, $dataRecord['record_id'], $newFastData);
                $nextLevelOfRecords = self::getListWhereUseCurrentRecordFromImplementedRecords($objectName, $dataRecord['record_id']);
                if (!$nextLevelOfRecords || !$nextLevelOfRecords->getModels()) {
                    return false;
                } else {
                    $nextLevelOfRecords = $nextLevelOfRecords->getModels();
                }
                foreach ($nextLevelOfRecords as $implementedTokenRecord) {
                    self::recursiveUpdateFastDataUse($objectID, $dataRecord['record_id'], $implementedTokenRecord);
                }
            }
        }
    }

    public static function getRecordInCurrentObjectForAllAssemblies($objectID, $droleID, $objectName)
    {
        $params = [':drole_id' => $droleID, ':object_id' => $objectID];
        $sql = "SELECT record_id FROM " . $objectName . "_data_use_implemented WHERE assembly_id = "
            . "(SELECT assembly_id FROM registry_drole_assembly WHERE drole_id = :drole_id AND object_id = :object_id and active = 1 limit 1)";
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    public static function getFastRecordWithObjectName($objectID, $droleID, $objectName, $recordID)
    {
        $params = [':drole_id' => $droleID, ':object_id' => $objectID, ':record_id' => $recordID];
        $sql = "SELECT record_id FROM " . $objectName . "_data_use_implemented WHERE assembly_id = "
            . "(SELECT assembly_id FROM registry_drole_assembly WHERE drole_id = :drole_id AND object_id = :object_id and active = 1 limit 1) and record_id = :record_id";
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    public static function setNewDataUseFastRecordsForAssembly($objectID, $objectName, $assemblyID, $droleForAssembly, $structureAssemblyDrole)
    {
        //echo "updateAllRecordsRecursivelyAfterUpdateAssembly($objectID, $objectName, $assemblyID, $droleForAssembly)";
        //update all records that have this transaction
        //$arrayOfRecords = self::getAllRecordForAssembly($objectID, $objectName, $assemblyID);
        if (!is_array($structureAssemblyDrole)) {
            $structureAssemblyDrole = json_decode($structureAssemblyDrole, true);
        }
        //echo json_encode($structureAssemblyDrole);
        self::updateAllFieldsForAssembly($objectID, $objectName, $assemblyID, $droleForAssembly, $structureAssemblyDrole);
        /*echo " - data array: " . json_encode($arrayOfRecords) . " - ";
        exit;
        if ($arrayOfRecords) {
            $fastStructure = json_decode(StructureOperationHandler::getFastStructureForAssemblyWithCheck($objectID, $droleForAssembly, $assemblyID, $objectName), true);
            foreach ($arrayOfRecords as $dataRecord) {
                $newFastData = self::getJsonDataFromJsonStructureArray($objectID, $droleForAssembly, $dataRecord['record_id'], $fastStructure);
                self::deleteFastRecord($objectID, $dataRecord['record_id'], $droleForAssembly);
                self::setFastRecord($objectID, $droleForAssembly, $dataRecord['record_id'], $newFastData);
                $nextLevelOfRecords = self::getListWhereUseCurrentRecordFromImplementedRecords($objectName, $dataRecord['record_id']);
                if (!$nextLevelOfRecords || !$nextLevelOfRecords->getModels()) {
                    continue;
                } else {
                    $nextLevelOfRecords = $nextLevelOfRecords->getModels();
                }
                foreach ($nextLevelOfRecords as $implementedTokenRecord) {
                    self::recursiveUpdateFastDataUse($objectID, $dataRecord['record_id'], $implementedTokenRecord);
                }
            }
        }*/
    }

    private static function updateAllFieldsForAssembly($objectID, $objectName, $droleForAssembly, $structureJsonString)
    {
        /**/
        $nestedIndexes = array();
        for ($i = 0; $i < count($structureJsonString); $i++) {
            if (!isset($structureJsonString[$i]['nested'])) {
                //echo json_encode($structureJsonString);
                exit;
            }
            if ($structureJsonString[$i]['nested'] != "false") {
                array_push($nestedIndexes, $i);
            }
        }
        $sql = "select * from " . $objectName . "_data_use where id in (select * from " . $objectName . "_record_own join registry_drole_base 
        on " . $objectName . "_record_own.service_id = registry_drole_base.service_id and " . $objectName . "_record_own.company_id = registry_drole_base.company_id where registry_drole_base.id = '$droleForAssembly')";
        $recordsList = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($recordsList as $currentRecord) {
            if (count($nestedIndexes) > 0)
                foreach ($nestedIndexes as $mapIndex) {
                    $nestedObjectName = RegistryObjects::getObjectNameByID($structureJsonString[$mapIndex]['object'])->name;
                    $sql = "select * from " . $objectName . "_data_use where id = '" . $currentRecord['id'] . "'";
                    $recordsList = \Yii::$app->db->createCommand($sql)->queryAll();
                    //foreach ($recordsList as $currentRecord) {
                    //UpdateDataObjectHandler::getTimeMarker("record: " . $currentRecord[$structureJsonString[$mapIndex]['name']], $startTime);
                    if ($currentRecord[$structureJsonString[$mapIndex]['name']] == '') {
                        continue;
                    }
                    $sql = "select * from " . $nestedObjectName . "_implemented_records where implemented_id = '" .
                        $currentRecord[$structureJsonString[$mapIndex]['name']] . "' order by turn";
                    $implementedRecords = \Yii::$app->db->createCommand($sql)->queryAll();
                    if (!$implementedRecords || count($implementedRecords) < 1) {
                        continue;
                    } else {
                        $implementedArray = array();
                        for ($implementedIndex = 0; $implementedIndex < count($implementedRecords); $implementedIndex++) {
                            $implementedArray[$implementedIndex] = self::getJsonDataFromJsonStructureArray($structureJsonString[$mapIndex]['object'],
                                $droleForAssembly, $implementedRecords[$implementedIndex]['record_id'], $structureJsonString[$mapIndex]['nested']);
                        }
                        \Yii::$app->db->createCommand("delete from " . $objectName . "_data_use_implemented where 
                        implemented_id = '" . $currentRecord[$structureJsonString[$mapIndex]['name']] . "' and 
                        drole_id = '" . $droleForAssembly . "'")->execute();
                        //array_push($usedImplementedRecords, array('implemented' => $currentRecord[$structureJsonString[$mapIndex]['name']], 'drole' => $droleForAssembly));
                        $sql = "insert into " . $objectName . "_data_use_implemented values 
                        ('" . $currentRecord[$structureJsonString[$mapIndex]['name']] . "','" . $droleForAssembly . "',
                        '" . $droleForAssembly . "','" . self::returnIndexedJSONForData($implementedArray) . "')";
                        \Yii::$app->db->createCommand($sql)->execute();
                    }
                    //}
                }
            $nextLevelOfRecords = self::getListWhereUseCurrentRecordFromImplementedRecords($objectName, $currentRecord['id']);
            if (!$nextLevelOfRecords || count($nextLevelOfRecords) < 1) {
                continue;
            }

            foreach ($nextLevelOfRecords as $implementedTokenRecord) {
                self::recursiveUpdateFastDataUseForDrole($objectID, $droleForAssembly, $implementedTokenRecord);
            }
        }
    }

    private static function recursiveUpdateFastDataUseForDrole($objectID, $droleID, $implementedToken, $startTime = null)
    {
        //UpdateDataObjectHandler::getTimeMarker("recursiveUpdateFastDataUse start", $startTime);
        //echo "start recursiveUpdateFastDataUse($objectID, " . json_encode($implementedToken) . ")";
        $currentObjectName = RegistryObjects::getObjectNameByID($implementedToken['object_id']);
        if (!$currentObjectName) {
            return APIHandler::getErrorArray(404, "Not found object name. check it: " . $implementedToken['object_id']);;
        }
        $currentObjectName = $currentObjectName->name;
        $currentFieldName = self::getFieldNameForQueries($currentObjectName, $implementedToken['field_id']);
        if (!$currentFieldName || count($currentFieldName) < 1) {
            return APIHandler::getErrorArray(404, "Not found field name for currentObjectName. check it: " . $implementedToken['field_id']);
        }
        $currentFieldName = $currentFieldName['name'];
        //echo json_encode($implementedToken);
        //$assembliesList = self::getListOfAssembliesThatUseRecordID($currentObjectName, $implementedToken['record_id'], $recordID);
        //$currentObjectID, $currentObjectName, $implementedID, $searchedFieldName
        $sql = "SELECT * FROM contact_structure_use_fast where drole_id = '$droleID' and 
contact_structure_use_fast.assembly_id in (select registry_drole_assembly.assembly_id from registry_drole_assembly where 
registry_drole_assembly.drole_id = contact_structure_use_fast.drole_id and 
registry_drole_assembly.assembly_id = contact_structure_use_fast.assembly_id and active = '1')";
        $presentFastStructuresForImplementedRecord = $drolesArrayForCurrentAssembly = \Yii::$app->db->createCommand($sql)->queryAll();
        $beforeObjectName = RegistryObjects::getObjectNameByID($objectID);
        if (!$beforeObjectName) {
            return APIHandler::getErrorArray(404, "Not found field name for beforeObjectName. check it: " . $beforeObjectName);
        }
        $beforeObjectName = $beforeObjectName->name;
        $sql = "select * from " . $beforeObjectName . "_implemented_records where implemented_id = '" . $implementedToken['implemented_id'] . "' order by turn";
        $listOfRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$listOfRecords) {
            return false;
        }
        foreach ($presentFastStructuresForImplementedRecord as $fastStructureWithImplemented) {
            $currentStructure = $fastStructureWithImplemented['json_structure'];
            if (!$currentStructure) {
                return false;
            }
            $currentStructure = json_decode($currentStructure, true);
            $indexOfNestedStructure = self::getSubStructureIndexForField($currentStructure, $implementedToken['field_id']);
            if ($indexOfNestedStructure < 0) {
                continue;
            }
            $beforeStructure = $currentStructure[$indexOfNestedStructure]['nested'];
            $arrayOfFastRecordOfPreviousObject = array();
            foreach ($listOfRecords as $implementedRecord) {
                $fastRecordOfTheCurrent = self::getFastRecord($objectID, $beforeObjectName, $fastStructureWithImplemented['drole_id'], $implementedRecord['record_id'], $beforeStructure);
                if (!$fastRecordOfTheCurrent) {
                    continue;
                }
                array_push($arrayOfFastRecordOfPreviousObject, $fastRecordOfTheCurrent);
            }
            self::updateImplementedNestedRecord($currentObjectName, $fastStructureWithImplemented['drole_id'], $fastStructureWithImplemented['assembly_id'], $implementedToken['implemented_id'], $arrayOfFastRecordOfPreviousObject);
        }
        $sql = "select id from " . $currentObjectName . "_data_use where $currentFieldName = '" . $implementedToken['implemented_id'] . "'";
        $listWhereUsedImplementedRecords = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$listWhereUsedImplementedRecords || count($listWhereUsedImplementedRecords) < 1) {
            return true;
        }
        foreach ($listWhereUsedImplementedRecords as $currentRecord) {
            $nextLevelOfRecords = self::getListWhereUseCurrentRecordFromImplementedRecords($currentObjectName, $currentRecord['id']);
            if (!$nextLevelOfRecords || count($nextLevelOfRecords) < 1) {
                continue;
            }
            foreach ($nextLevelOfRecords as $implementedTokenRecord) {
                self::recursiveUpdateFastDataUseForDrole($objectID, $droleID, $implementedTokenRecord, $startTime);
            }
        }
        //UpdateDataObjectHandler::getTimeMarker("recursiveUpdateFastDataUse end", $startTime);
    }

    public static function getAllRecordForAssembly($objectID, $objectName, $assemblyID, $droleID)
    {
        $sql = "SELECT id as record_id FROM " . $objectName . "_data_use where id in (SELECT DISTINCT on (" . $objectName . "_record_own.id) " . $objectName . "_record_own.id 
FROM " . $objectName . "_record_own right JOIN registry_drole_base ON (registry_drole_base.company_id = " . $objectName . "_record_own.company_id and 
registry_drole_base.service_id = " . $objectName . "_record_own.service_id) where registry_drole_base.id in (SELECT drole_id FROM registry_drole_assembly 
WHERE assembly_id = '$assemblyID' AND active = '1'))";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        return $provider->getModels();
    }

    public static function updateAllRecordsInAssemblyStartsWithChangedField($objectID, $objectName, $assemblyID, $droleForAssembly, $firstLineFieldID, $structureJsonString)
    {
        $arrayOfRecords = self::getAllRecordsForAssemblyWithBody($objectID, $objectName, $assemblyID);
        $usedImplementedRecords = array();
        if (!is_array($structureJsonString)) {
            $structureJsonString = json_decode($structureJsonString, true);
        }
        $mapIndex = null;
        for ($index = 0; $index < count($structureJsonString); $index++) {
            if ($structureJsonString[$index]['id'] == $firstLineFieldID) {
                $mapIndex = $index;
                break;
            }
        }
        if (!$mapIndex || $mapIndex < 0) {
            return false;
        }
        $isUpdateNestedField = true;
        if (!$structureJsonString[$mapIndex]['nested'] || $structureJsonString[$mapIndex]['nested'] == "false") {
            //nothing change in current assembly
            $isUpdateNestedField = false;
        }
        $nestedObjectName = RegistryObjects::getObjectNameByID($structureJsonString[$mapIndex]['object']);
        foreach ($arrayOfRecords as $currentRecord) {
            //$key = array_search($currentRecord[$structureJsonString[$mapIndex]['name']], array_column($usedImplementedRecords, 'implemented'));
            //if(isset($key) && $key != '' && $usedImplementedRecords[$key][])
            //echo "start work with record: " . json_encode($currentRecord);
            if (in_array($currentRecord['id'], $usedImplementedRecords)) {
                continue;
            }

            if ($isUpdateNestedField) {
                $sql = "select * from " . $nestedObjectName . "_implemented_records where implemented_id = '" .
                    $currentRecord[$structureJsonString[$mapIndex]['name']] . "' order by turn";
                $implementedRecords = \Yii::$app->db->createCommand($sql)->queryAll();
                if (!$implementedRecords || count($implementedRecords) < 1) {
                    continue;
                } else {
                    $implementedArray = array();
                    for ($implementedIndex = 0; $implementedIndex < count($implementedRecords); $implementedIndex++) {
                        $implementedArray[$implementedIndex] = self::getJsonDataFromJsonStructureArray($structureJsonString[$mapIndex]['object'],
                            $droleForAssembly, $implementedRecords[$implementedIndex]['id'], $structureJsonString[$mapIndex]['nested']);
                    }
                    array_push($usedImplementedRecords, $currentRecord['id']);
                    \Yii::$app->db->createCommand("delete from " . $objectName . "_data_use_implemented where implemented_id = '" . $currentRecord[$structureJsonString[$mapIndex]['name']] . "' and drole_id = '$droleForAssembly'")->execute();
                    //array_push($usedImplementedRecords, array('implemented' => $currentRecord[$structureJsonString[$mapIndex]['name']], 'drole' => $droleForAssembly));
                    $sql = "insert into " . $objectName . "_data_use_implemented values ('" . $currentRecord[$structureJsonString[$mapIndex]['name']] . "','$droleForAssembly','$assemblyID','" . self::returnIndexedJSONForData($implementedArray) . "')";
                    \Yii::$app->db->createCommand($sql)->execute();
                }
            }
            //search and update parent objects
            $nextLevelOfRecords = self::getListWhereUseCurrentRecordFromImplementedRecords($objectName, $currentRecord['id']);

            if (!$nextLevelOfRecords || count($nextLevelOfRecords) < 1) {
                continue;
            }

            foreach ($nextLevelOfRecords as $implementedTokenRecord) {
                self::recursiveUpdateFastDataUse($objectID, $implementedTokenRecord);
            }
        }
    }

    /* private static function getImplementedRecordsByChild($objectName, $objectIDParent, $recordIDParent, $fieldIDParent){
      $sql = "select implemented_id from " . $objectName . "_implemented_records_objects where object_id = '$objectIDParent' and record_id = '$recordIDParent' and field_id = '$fieldIDParent' limit 1";
      $provider = new SqlDataProvider([
      'sql' => $sql
      ]);
      if(!$provider || count($provider->getModels()) < 1){
      //nothing is found
      return false;
      }
      return $provider->getModels();
      }

      private static function updateImplementedValueInRecord($objectIDParent, $recordIDParent, $fieldIDParent, $objectName, $recordID, $fieldToUpdateID, $fieldToUpdateName, $oldValue, $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0') {

      $sql = "update " . $objectName . "_data_use set " . $fieldToUpdateName . " = '" . $newValue . "' where id = '" . $recordID . "'";
      \Yii::$app->db->createCommand($sql)->execute();
      LogObjectHandler::updateLogRecordForObject($objectName, "data_use", $recordID, $fieldToUpdateID, $oldValue, $newValue, $droleID, $contactID, $ipAddress);
      } */

    public static function getAllRecordsForAssemblyWithBody($objectID, $objectName, $assemblyID)
    {
        $sql = "SELECT * FROM " . $objectName . "_data_use where id in (SELECT DISTINCT on (" . $objectName . "_record_own.id) " . $objectName . "_record_own.id
FROM " . $objectName . "_record_own right JOIN registry_drole_base ON (registry_drole_base.company_id = " . $objectName . "_record_own.company_id and
registry_drole_base.service_id = " . $objectName . "_record_own.service_id) where registry_drole_base.id in (SELECT drole_id FROM registry_drole_assembly
WHERE assembly_id = '$assemblyID' AND active = '1'))";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function updateValueInRecordRecursive($objectID, $objectName, $droleID, $firstLineFieldID, $recordID)
    {
        $sql = "select " . $objectName . "_structure_use_fast.* from " . $objectName . "_structure_use_fast join 
            (SELECT drole_id, assembly_id FROM registry_drole_assembly WHERE assembly_id in (select id from " . $objectName .
            "_assembly_fields_use where " . $objectName . "_assembly_fields_use.field = '$firstLineFieldID' and usef = 'true') 
                and active = '1') as registry_drole_assembly on " . $objectName . "_structure_use_fast.drole_id = registry_drole_assembly.drole_id 
                and " . $objectName . "_structure_use_fast.assembly_id = registry_drole_assembly.assembly_id";
        $drolesForUpdated = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$drolesForUpdated || count($drolesForUpdated) < 1) {
            return false;
        }
        foreach ($drolesForUpdated as $droleRecord) {
            $mapIndex = null;
            $structureJsonString = json_decode($droleRecord['json_structure'], true);
            for ($index = 0; $index < count($structureJsonString); $index++) {
                if ($structureJsonString[$index]['id'] == $firstLineFieldID) {
                    $mapIndex = $index;
                    break;
                }
            }
            if (!$mapIndex || $mapIndex < 0) {
                continue;
            }
            $isUpdateNestedField = true;
            if (!$structureJsonString[$mapIndex]['nested'] || $structureJsonString[$mapIndex]['nested'] == "false") {
                //nothing change in current assembly
                $isUpdateNestedField = false;
            }
            if ($isUpdateNestedField) {
                $nestedObjectName = RegistryObjects::getObjectNameByID($structureJsonString[$mapIndex]['object'])->name;
                $sql = "select * from " . $objectName . "_data_use where record_id = '$recordID'";
                $recordsList = \Yii::$app->db->createCommand($sql)->queryAll();
                foreach ($recordsList as $currentRecord) {
                    if ($currentRecord[$structureJsonString[$mapIndex]['name']] == '') {
                        continue;
                    }
                    $sql = "select * from " . $nestedObjectName . "_implemented_records where implemented_id = '" .
                        $currentRecord[$structureJsonString[$mapIndex]['name']] . "' order by turn";
                    $implementedRecords = \Yii::$app->db->createCommand($sql)->queryAll();
                    if (!$implementedRecords || count($implementedRecords) < 1) {
                        continue;
                    } else {
                        $implementedArray = array();
                        for ($implementedIndex = 0; $implementedIndex < count($implementedRecords); $implementedIndex++) {
                            $implementedArray[$implementedIndex] = self::getJsonDataFromJsonStructureArray($structureJsonString[$mapIndex]['object'],
                                $droleRecord['drole_id'], $implementedRecords[$implementedIndex]['record_id'], $structureJsonString[$mapIndex]['nested']);
                        }
                        \Yii::$app->db->createCommand("delete from " . $objectName . "_data_use_implemented where 
                        implemented_id = '" . $currentRecord[$structureJsonString[$mapIndex]['name']] . "' and 
                        drole_id = '" . $droleRecord['drole_id'] . "'")->execute();
                        //array_push($usedImplementedRecords, array('implemented' => $currentRecord[$structureJsonString[$mapIndex]['name']], 'drole' => $droleForAssembly));
                        $sql = "insert into " . $objectName . "_data_use_implemented values 
                        ('" . $currentRecord[$structureJsonString[$mapIndex]['name']] . "','" . $droleRecord['drole_id'] . "',
                        '" . $droleRecord['assembly_id'] . "','" . self::returnIndexedJSONForData($implementedArray) . "')";
                        \Yii::$app->db->createCommand($sql)->execute();
                    }
                }
            }
        }
        //search and update parent objects
        $sql = "select * from " . $objectName . "_data_use where " . $objectName . "_data_use.id in 
                (select " . $objectName . "_record_own.id from " . $objectName . "_record_own join 
        (SELECT company_id, service_id FROM registry_drole_base WHERE id = '" . $drolesForUpdated[0]['drole_id'] . "') as
        registry_drole_base on registry_drole_base.company_id = " . $objectName . "_record_own.company_id and
        registry_drole_base.service_id = " . $objectName . "_record_own.service_id)";
        $recordsList = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($recordsList as $currentRecord) {
            $nextLevelOfRecords = self::getListWhereUseCurrentRecordFromImplementedRecords($objectName, $currentRecord['id']);

            if (!$nextLevelOfRecords || count($nextLevelOfRecords) < 1) {
                continue;
            }

            foreach ($nextLevelOfRecords as $implementedTokenRecord) {
                self::recursiveUpdateFastDataUse($objectID, $implementedTokenRecord);
            }
        }
    }
}