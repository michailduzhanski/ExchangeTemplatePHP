<?php

namespace common\modules\drole\models\gate;

use common\modules\drole\models\object\LogObjectHandler;
use common\modules\drole\models\registry\RegistryClasses;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\UUIDGenerator;
use common\modules\drole\object\ObjectStructureModel;
use common\modules\drole\object\SimpleObjectHandler;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\registry\RegistryDescriptionRolesModel;
use Yii;
use yii\data\SqlDataProvider;

class CommonGateModel
{

    public static $currentWorkObjectName = "";
    public static $accessArray;
    private static $companyFieldName = 'company_id';
    private static $serviceFieldName = 'service_id';
    private static $roleFieldName = 'role_id';
    private static $contactFieldName = 'contact_id';
    private static $objectFieldName = 'object_id';
    private static $dynamicRoleFieldName = 'drole_id';
    private static $assemblyFieldName = 'assembly_id';
    private static $activeFieldName = 'active';
    private $parentMaps;

    public function getJsonDataFromJsonStructureArray($companyID, $serviceID, $contactID, $currentObjectID, $recordID, $globalStructure)
    {
        $resultArray = array();
        //get data record from db
        $fieldsWithTypes = '';
        $onlyFields = '';
        foreach ($globalStructure as $key => $record) {
            $fieldsWithTypes .= $record->name . " " . $record->type . ", ";
            $onlyFields .= $record->name . ', ';
        }
        if (strlen($fieldsWithTypes) <= 2) {
            return false;
        }
        $fieldsWithTypes = substr($fieldsWithTypes, 0, count($fieldsWithTypes) - 3);
        $onlyFields = substr($onlyFields, 0, count($onlyFields) - 3);
        //operation for standard query. company only
        $currentDataRecord = $this->getRecordForObjectAndID($currentObjectID, $companyID, $recordID, $onlyFields, $fieldsWithTypes)->getModels();
        if (!$currentDataRecord || $currentDataRecord == '' || $currentDataRecord == NULL) {
            return false;
        }
        $currentDataRecord = $currentDataRecord[0];
        //create real fields with a data
        $index = 0;
        foreach ($globalStructure as $key => $record) {
            $currentValue = $currentDataRecord[$record->name];
            $nested = $record->nested;
            if (!$nested || $nested == 'false') {
                $resultArray[$index] = $currentValue;
            } else {
                $nestedRecords = $this->getImplementedRecordsForNested($record->object, $currentValue)->getModels();
                if (!$nestedRecords || $nestedRecords == '' || $nestedRecords == NULL) {
                    $resultArray[$index] = false;
                } else {
                    $nestedResultArray = array();
                    $nestedIndex = 0;
                    foreach ($nestedRecords as $localRecord) {
                        $nestedResultArray[$nestedIndex] = $this->getJsonDataFromJsonStructureArray($companyID, $serviceID, $contactID, $record->object, $localRecord['id'], $nested);
                        $nestedIndex++;
                    }
                    $resultArray[$index] = $nestedResultArray;
                }
            }
            $index++;
        }
        return $resultArray;
    }

    public function setInternalFieldDataForParentObject($internalObjectID, $inputParams, $objectData, $fieldName)
    {
        $internalObject = new SimpleObjectHandler();
        $internalArray = $inputParams;
        foreach ($objectData as $record) {
            $externalDemand = $record[$fieldName];
            $internalArray['external_demand'] = $externalDemand;
            $internalObject->getAllDataFromObject($inputParams);
            //$externalObjectID
            //$record[$field['name']] =
        }
    }

    public function updateFastRecord($objectID, $companyID, $serviceID, $droleID, $recordID, $contactID, $incomingMap, $newValue)
    {
        $fastStructure = $this->getFastStructure($objectID, $droleID)->getModels();
        $fastData = $this->getFastRecord($objectID, $droleID, $recordID)->getModels();
        if (!$fastStructure || !$fastData) {
            return false;
        }
        $fastStructure = json_decode($fastStructure[0]['json_structure'], true);
        $fastData = json_decode($fastData[0]['json_field'], true);
        $mapElements = explode('.', $incomingMap);
        $permission = 2; //edit permission
        $checkPermission = $this->recursiveCheckTreePermission($objectID, $fastStructure, $mapElements, $permission);
        if (!$checkPermission) {
            return false;
        }
        $mapIndex = 0;
        $currentRecord = $recordID;
        $currentObject = $objectID;
        $currentField = false;
        $currentFieldName = false;
        $currentFastDataRecord = $fastData;
        $changeNestedRecord = false;
        while ($mapIndex < count($mapElements)) {
            $mapNestedIndex = $mapIndex + 1;
            $changeNestedRecord = false;
            if ($mapNestedIndex < count($mapElements)) {
                $currentFastDataRecord = $this->getNextLevelRecordData($mapElements[$mapIndex], $mapElements[$mapNestedIndex], $currentFastDataRecord);
                $currentRecord = $currentFastDataRecord[0];
                $changeNestedRecord = true;
            } else {
                $currentField = $checkPermission['id'];
                $currentFieldName = $checkPermission['name'];
                $changeNestedRecord = false;
            }
            $mapIndex = $mapIndex + 2;
        }
        $parentStructureObject = $objectID;
        //check, if old and new values is equals
        if ($currentFastDataRecord[$mapElements[count($mapElements) - 1]] == $newValue) {
            return false;
        }
        if (count($mapElements) > 2) {
            $parentStructureObject = $this->getRootEditObjectFromStructure($mapElements, $fastStructure);
        }
        if ($changeNestedRecord) {
            //is not working
        } else {
            //update data use table and recursive update fast implementations tables in parent objects
            $this->updateValueInRecord($parentStructureObject, RegistryObjects::getObjectNameByID($parentStructureObject)->name, $currentRecord, $currentField, $currentFieldName, $currentFastDataRecord[$mapElements[count($mapElements) - 1]], $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0');
        }
        $allCases = $this->getAllCasesDataOfUse($parentStructureObject, $currentRecord);
        $usedObjectsArray = array();
        $indexOfCasesArray = 0;
        while ($indexOfCasesArray < count($allCases)) {
            if (!in_array($allCases[$indexOfCasesArray]['used_object_id'], $usedObjectsArray)) {
                $localCases = $this->getAllCasesDataOfUse($allCases[$indexOfCasesArray]['used_object_id'], $allCases[$indexOfCasesArray]['used_record_id']);
                $allCases = $this->compaireUpdatesDataArrays($allCases, $localCases);
                for ($j = 0; $j < count($localCases); $j++) {
                    $isPresented = false;
                    for ($i = 0; $i < count($allCases); $i++) {
                        if ($localCases[$j]['used_object_id'] == $allCases[$i]['used_object_id'] && $localCases[$j]['used_record_id'] == $allCases[$i]['used_record_id']) {
                            $isPresented = true;
                            break;
                        }
                    }
                    if (!$isPresented) {
                        array_push($allCases, $localCases[$j]);
                    }
                }
                array_push($usedObjectsArray, $allCases[$indexOfCasesArray]['used_object_id']);
            }
            $indexOfCasesArray++;
        }

        foreach ($allCases as $caseLineRecord) {
            $currentObjectID = $caseLineRecord['used_object_id'];
            $currentRecordID = $caseLineRecord['used_record_id'];
            $structure = $this->getFastStructure($currentObjectID, $droleID)->getModels();
            if (!$structure) {
                $structure = $this->updateFastStructureAndReturnNew($currentObjectID, $droleID);
            } else {
                $structure = $structure[0]['json_structure'];
            }
            $dataUseValue = $this->getJsonDataFromJsonStructure($companyID, $currentObjectID, $currentRecordID, json_decode($structure));
            $this->deleteFastRecord($currentObjectID, $currentRecordID);
            $this->setFastRecord($currentObjectID, $droleID, $currentRecordID, $dataUseValue);
        }
        return '{"object":"' . $currentObject . '", "record":"' . $currentRecord . '"}';
    }

    public function getFastStructure($objectID, $droleID)
    {
        $params = [':object_id' => $objectID, ':drole_id' => $droleID];
        $sql = "select * from getfaststructure(:object_id, :drole_id) as ds(id uuid, assembly_id uuid, json_structure jsonb)";
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    public function getFastRecord($objectID, $droleID, $recordID)
    {
        $params = [':object_id' => $objectID, ':drole_id' => $droleID, ':record_id' => $recordID];
        $sql = "select * from getfastdatause(:object_id, :drole_id, :record_id) as ds(id uuid, assembly_id uuid, record_id uuid, json_field jsonb)";
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    public function recursiveCheckTreePermission($objectID, $structureArray, $mapElements, $permission)
    {
        $mapIndex = 0;
        $nextLevelStructure = $structureArray;

        while ($mapIndex < count($mapElements)) {
            if ($mapIndex != 0) {
                $nextLevelStructure = $nextLevelStructure['nested'];
            }
            //verification. an odd open element must not be a nested structure
            try {
                if ($nextLevelStructure[$mapElements[$mapIndex]]['nested'] != 'false' && $mapIndex + 1 >= count($mapElements)) {
                    return false;
                }
            } catch (yii\base\ErrorException $ex) {
                return false; //'{error. map elements is not equal structure}' ;
            }
            $nextLevelStructure = $this->checkPermissionRecord($mapElements[$mapIndex], $nextLevelStructure, $permission);
            if (!$nextLevelStructure) {
                return false;
            }
            //getNextLevelRecordData($mapElements[$mapIndex], $mapElements[$mapIndex + 1], $dataArray, $nextLevelStructure);
            $mapIndex = $mapIndex + 2;
        }
        return $nextLevelStructure;
    }

    public function checkPermissionRecord($currentPositionIndex, $structureArray, $permission)
    {
        $nextLevelStructureRecord = $structureArray[$currentPositionIndex];
        $structureNestedPermission = $nextLevelStructureRecord['perm'];
        if ($permission > $structureNestedPermission) {
            return false;
        }
        return $nextLevelStructureRecord;
    }

    //$globalStructure - array

    public function getNextLevelRecordData($currentPositionIndex, $internalPositionIndex, $dataArray)
    {
        //check is present data
        $internalRecordsArray = $dataArray[$currentPositionIndex];
        if ($internalRecordsArray == false) {
            return false;
        }
        return $internalRecordsArray[$internalPositionIndex];
    }

    //$globalStructure - json object

    public function getRootEditObjectFromStructure($mapElements, $structure)
    {

        $nextLevelStructure = $structure;
        $nestedStructure = array();
        $resultObject = false;
        //$odd = count($mapElements) % 2 === 0;
        //$lastElement = ($forNestedRecord == true ? count($mapElements)
        $mapIndex = 0;
        while ($mapIndex < count($mapElements)) {
            $nextElement = $nextLevelStructure[$mapElements[$mapIndex]];
            if (!$nextElement['nested']) {
                return $resultObject;
            } else {
                $nextLevelStructure = $nextElement['nested'];
                if ($mapIndex + 2 < count($mapElements))
                    $resultObject = $nextElement['object'];
            }
            $mapIndex = $mapIndex + 2;
        }
        return $resultObject;
    }

    public function updateValueInRecord($objectID, $objectName, $recordID, $fieldToUpdateID, $fieldToUpdateName, $oldValue, $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0')
    {
        $sql = "update " . $objectName . "_data_use set " . $fieldToUpdateName . " = '" . $newValue . "' where id = '" . $recordID . "'";
        \Yii::$app->db->createCommand($sql)->execute();
        LogObjectHandler::updateLogRecordForObject($objectName, $objectName . "_data_use", $recordID, $fieldToUpdateID, $oldValue, $newValue, $droleID, $contactID, $ipAddress);
    }

    /* private function getRecursiveDataFromNestedObjects($parentObject, $extInputParams, $dataObjectRecords) {
      $structureFields = $parentObject->getStructureFieldsArray();
      $presentAssembly = $parentObject->getAssemblyFieldsArray();
      $inputParams = $extInputParams;
      for ($i = 0; $i < count($dataObjectRecords); $i++) {
      $dataRow = $dataObjectRecords[$i];
      foreach ($structureFields as $field) {
      $fieldID = $field['id'];
      foreach ($presentAssembly as $assembly) {
      $assemblyFieldID = $assembly['field'];
      if ($assemblyFieldID == $fieldID) {
      $inputParams['object'] = $field['class'];
      $currentObject = new SimpleObjectHandler($inputParams);
      $provider = $currentObject->getAllDataFromObject($dataObjectRecords[$i][$assembly['turn']]);
      if ($provider != '' && $provider != []) {
      $fieldData = $provider->getModels();
      //$fieldData = $currentObject->getAllDataFromObject($dataObjectRecords[$i][$field['name']]);
      $dataObjectRecords[$i][$assembly['turn']] = $this->getRecursiveDataFromNestedObjects($currentObject, $inputParams, $fieldData);
      }
      }
      }
      }
      }
      return $dataObjectRecords;
      } */

    public function getAllCasesDataOfUse($objectID, $recordID)
    {
        $resultArray = array();
        $sql = "select * from registry_objects order by id desc";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();
        $neccessaryObject = false;
        foreach ($objectsArray as $objectRecord) {
            if ($objectID == $objectRecord['id']) {
                $neccessaryObject = $objectRecord;
                break;
            }
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
            $fieldsForSearch = '';
            foreach ($implementedRecords as $implementedRecord) {
                $fieldsForSearch .= $implementedRecord['name'] . ' in (select implemented_id from ' . $neccessaryObject['name'] . '_implemented_records where record_id = \'' . $recordID . '\') or ';
            }

            $sql = "select * from " . $objectRecord['name'] . "_data_use where " . substr($fieldsForSearch, 0, strlen($fieldsForSearch) - 4);
            $providerUsedRecords = new SqlDataProvider([
                'sql' => $sql
            ]);
            $foundRecords = $providerUsedRecords->getModels();
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

    public function compaireUpdatesDataArrays($mustArray, $incomingArray)
    {
        for ($j = 0; $j < count($incomingArray); $j++) {
            $insert = false;
            for ($i = 0; $i < count($mustArray); $i++) {
                if ($mustArray[$i]['record_id'] == $incomingArray[$j]['used_record_id']) {
                    array_splice($mustArray, $i, 0, $incomingArray[$j]);
                    $insert = true;
                    break;
                }
            }
            if (!$insert) {
                array_push($mustArray, $incomingArray[$j]);
                break;
            }
        }
        return $mustArray;
    }

    public function updateFastStructureAndReturnNew($currentObjectID, $droleID)
    {
        $fieldInternalArray = $this->getFieldAllParamsForAssembly($currentObjectID, $droleID)->getModels();
        $structure = $this->getFastStructureTree($currentObjectID, $droleID, $fieldInternalArray);
        $this->deleteFastStructure($currentObjectID, $droleID);
        $this->setFastStructure($currentObjectID, $droleID, $structure);
        return $structure;
    }

    public function getFastStructureTree($parentObjectID, $dynamicRoleID, $presentAssemblyWithStructureFields)
    {
        $increment = 0;
        $resultArray = array();
        $resultString = '';
        foreach ($presentAssemblyWithStructureFields as $assemblyField) {
            $currentID = $assemblyField['field'];
            $currentName = $assemblyField['name'];
            $currentType = $this->getFieldType($assemblyField['type']);
            $currentValueClassID = $assemblyField['class'];
            $currentPermission = $this->getPermissionType($assemblyField);
            //$currentPosition = $increment;
            $fieldInternalArray = $this->getFieldAllParamsForAssembly($currentValueClassID, $dynamicRoleID)->getModels();
            if (!$fieldInternalArray || $fieldInternalArray == '' || $fieldInternalArray == NULL) {
                $internalArray = '"false"';
            } else {
                $internalArray = $this->getFastStructureTree($currentValueClassID, $dynamicRoleID, $fieldInternalArray);
            }
            $resultArray[$increment] = ['name' => $currentName, 'id' => $currentID, 'type' => $currentType, 'perm' => $currentPermission, 'object' => $currentValueClassID, 'nested' => $internalArray];
            $resultString .= '"' . $increment . '":{"name":"' . $currentName . '","id":"' . $currentID . '","type":"' . $currentType . '","perm":"' . $currentPermission . '","object":"' . $currentValueClassID . '","nested":' . $internalArray . '},';
            //array_push($resultArray, ['pos'=>$increment, 'id'=>$currentID, 'perm'=>$currentPermission, 'object'=>$currentValueClassID, 'internal'=> $internalArray]);
            $increment++;
        }
        //return $resultArray;
        return '{' . substr($resultString, 0, strlen($resultString) - 1) . '}';
    }

    private function getFieldType($strType)
    {
        if (UUIDGenerator::isUUID($strType)) {
            return 'uuid';
        }
        switch (strtolower($strType)) {
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

    //if the card contains an odd number of elements - it's a normal situation. then we process the specific element in the record
    //
    //else we process the whole record

    private function getPermissionType($fieldArray)
    {
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

    /* private function getLastElementObjectFromMap($recordID, $fastData, $mapElements, $structureArray){
      $mapIndex = 0;
      $currentRecord = $recordID;
      $currentField = false;
      $currentFieldName = false;
      $currentFastDataRecord = $fastData;
      $changeNestedRecord = false;
      while ($mapIndex < count($mapElements)) {
      $mapNestedIndex = $mapIndex + 1;
      $changeNestedRecord = false;
      if ($mapNestedIndex < count($mapElements)) {
      $currentFastDataRecord = $this->getNextLevelRecordData($mapElements[$mapIndex], $mapElements[$mapNestedIndex], $currentFastDataRecord);
      $currentRecord = $currentFastDataRecord[0];
      $changeNestedRecord = true;
      } else {
      $currentField = $structureArray['id'];
      $currentFieldName = $structureArray['name'];
      $changeNestedRecord = false;
      }
      $mapIndex = $mapIndex + 2;
      }
      } */

    public function getFieldAllParamsForAssembly($currentObjectID, $droleID)
    {
        $params = [':object_id' => $currentObjectID, ':drole_id' => $droleID];
        $sql = "select * from getStructureFor(:object_id, :drole_id) as ds(id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text)";
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    public function deleteFastStructure($objectID, $droleID)
    {
        $sql = "select deleteCurrentRecordFastStructure('$objectID', '$droleID')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public function setFastStructure($objectID, $droleID, $jsonString)
    {
        $sql = "select insertIntoFastStructure('$objectID', '$droleID', '$jsonString', '" . UUIDGenerator::v4() . "')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public function getJsonDataFromJsonStructure($droleID, $currentObjectID, $recordID, $globalStructure)
    {
        $resultArray = array();
        $resultString = '';
        //get data record from db
        $fieldsWithTypes = '';
        $onlyFields = '';
        foreach ($globalStructure as $key => $record) {
            $fieldsWithTypes .= $record->name . " " . $record->type . ", ";
            $onlyFields .= $record->name . ', ';
        }
        if (strlen($fieldsWithTypes) <= 2) {
            echo 'end1';
            return false;
        }
        $fieldsWithTypes = substr($fieldsWithTypes, 0, count($fieldsWithTypes) - 3);
        $onlyFields = substr($onlyFields, 0, count($onlyFields) - 3);
        //operation for standard query. company only
        $currentDataRecord = $this->getRecordForObjectAndID($currentObjectID, $droleID, $recordID, $onlyFields, $fieldsWithTypes)->getModels();
        if (!$currentDataRecord || $currentDataRecord == '' || $currentDataRecord == NULL) {
            echo 'end2';
            return false;
        }
        $currentDataRecord = $currentDataRecord[0];
        //create real fields with a data
        $index = 0;
        foreach ($globalStructure as $key => $record) {
            $currentValue = $currentDataRecord[$record->name];
            $nested = $record->nested;
            if (!$nested || $nested == 'false') {
                $resultString .= '"' . $index . '":"' . $currentValue . '",';
            } else {
                $nestedRecords = $this->getImplementedRecordsForNested($record->object, $currentValue)->getModels();
                if (!$nestedRecords || $nestedRecords == '' || $nestedRecords == NULL) {
                    $resultString .= '"' . $index . '":"false",';
                } else {
                    $nestedResultArray = array();
                    $nestedResultString = '';
                    $nestedIndex = 0;
                    foreach ($nestedRecords as $localRecord) {
                        $nestedResultString .= '"' . $nestedIndex . '":' . $this->getJsonDataFromJsonStructure($droleID, $record->object, $localRecord['id'], $nested) . ',';
                        $nestedIndex++;
                    }
                    $resultString .= '"' . $index . '":{' . substr($nestedResultString, 0, strlen($nestedResultString) - 1) . '},';
                }
            }
            $index++;
        }
        return '{' . substr($resultString, 0, strlen($resultString) - 1) . '}';
    }

    public function deleteFastRecord($objectID, $recordID)
    {
        $sql = "select deleteCurrentRecordFastDataUse('$objectID', '$recordID')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public function setFastRecord($objectID, $droleID, $recordID, $jsonString)
    {
        $sql = "select insertIntoFastDataUse('$objectID', '$droleID', '$recordID', '$jsonString', '" . UUIDGenerator::v4() . "')";
        \Yii::$app->db->createCommand($sql)->execute();
        //$params = [':object_id' => $objectID, ':drole_id' => $droleID, ':record_id' => $recordID, ':json_field' => $jsonString, ':current_id' => UUIDGenerator::v4()];
        //$sql = "select insertIntoFastDataUse(:object_id, :drole_id, :record_id, :json_field, :current_id)";
        //$provider = new SqlDataProvider([
        //    'sql' => $sql,
        //    'params' => $params
        //]);
    }

    public function updateNestedFunction($objectID, $nestedObjectID, $recordID, $valueRecordID)
    {
        //
    }

    public function checkRecursion($mustArray, $rootRecord)
    {
        for ($i = 0; $i < count($mustArray); $i++) {
            if ($mustArray[$i]['used_record_id'] == $rootRecord) {
                return $mustArray[$i];
            }
        }
        return false;
    }

    public function getParentRecordFromStructure($mapElements, $structure)
    {
        $mapIndex = 0;
        $nextLevelStructure = $structure;
        $nestedStructure = array();
        //$odd = count($mapElements) % 2 === 0;
        //$lastElement = ($forNestedRecord == true ? count($mapElements)
        while ($mapIndex < count($mapElements)) {
            $nextElement = $nextLevelStructure[$mapElements[$mapIndex]];
            if (!$nextElement['nested']) {
                return $nextLevelStructure;
            } else {
                $nextLevelStructure = $nextElement['nested'];
            }

            $mapIndex = $mapIndex + 2;
        }
        return $nextLevelStructure;
    }

    public function updateStructureFieldNameDescription($objectID, $droleID, $contactID, $fieldID, $newName = NULL, $newClassID = NULL, $newDescription = NULL)
    {
        //get access right
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        if (!$objectName) {
            return false;
        }
        $fieldArray = false;
        if ($fieldID && $fieldID != '') {
            $currentFieldsList = $this->getFieldAllParamsForAssembly($objectID, $droleID)->getModels();
            foreach ($currentFieldsList as $field) {
                if ($field['field'] == $fieldID) {
                    $fieldArray = $field;
                    break;
                }
            }
        } else {
            $fieldID = UUIDGenerator::v4();
        }
        if ($fieldArray && $fieldArray['edit'] != 1) {
            return false;
        }
        if (($fieldArray && (($newName && $newName != 'id') || $newClassID) && $fieldArray['name'] != 'id') || (($newName && $newName != 'id') && $newClassID)) {
            //if ((($newName && $newName != 'id') || $newClassID) && ($fieldArray && $fieldArray['name'] != 'id')) {
            //update name
            if (!$fieldArray) {
                $fieldArray = $fieldID;
            }
            $this->updateFieldAndAlterData($objectID, $objectName, $fieldArray, $newName, $newClassID, $droleID, $contactID);
            if (!$fieldArray) {

            }
        }
        if ($newDescription) {
            //update description
            $oldDescription = self::getDescriptionCurrentObjectValue($objectName, "structure_fields", $fieldID);
            $sql = "delete from " . $objectName . "_description where table_name = 'structure_fields' and record_id = '" . $fieldID . "'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "insert into " . $objectName . "_description values ('" . UUIDGenerator::v4() . "', 'structure_fields', '" . $fieldID . "', '" . $newDescription . "')";
            \Yii::$app->db->createCommand($sql)->execute();
            LogObjectHandler::updateLogRecordForObject($objectName, $objectName . "_description", $fieldID, 'description', $oldDescription, $newDescription, $droleID, $contactID);
        }
    }

    private function updateFieldAndAlterData($objectID, $objectName, $fieldArray, $fieldName, $fieldClass, $droleID, $contactID)
    {
        if ($this->checkRecursiveUseDataObject($fieldClass, $objectName)) {
            return false;
        }
        $sql = "select * from " . $objectName . "_structure_fields where name = '$fieldName'";
        $providerCompaire = new SqlDataProvider([
            'sql' => $sql
        ]);

        $compareArray = $providerCompaire->getModels();
        $fieldID = '';
        $oldFieldName = false;
        $oldFieldClass = false;
        //if compare array is fill
        if (!($compareArray && $fieldArray['field'] != $compareArray[0]['id'])) {
            $oldName = '';
            if (!$fieldArray || UUIDGenerator::isUUID($fieldArray)) {
                //insert
                if (!$fieldName || $fieldName == '' || !$fieldClass || $fieldClass == '') {
                    return false;
                }
                $fieldID = UUIDGenerator::v4();
                $sql = "insert into " . $objectName . "_structure_fields values ('$fieldID', '$fieldName', '$fieldClass')";
                Yii::$app->db->createCommand($sql)->execute();
                $tableClass = $this->getFieldType($fieldClass);
                $sql = "ALTER TABLE " . $objectName . "_data_use ADD $fieldName $tableClass NULL;";
                Yii::$app->db->createCommand($sql)->execute();
            } else {
                //update
                $fieldID = $fieldArray['field'];
                $oldFieldName = $fieldArray['name'];
                $oldFieldClass = $fieldArray['class'];
                if (!$fieldName || $fieldName == '' || $fieldName == $oldFieldName) {
                    $fieldName = $oldFieldName;
                } else {
                    $this->updateDataUseFieldName($objectName, $oldFieldName, $fieldName);
                }
                if (!$fieldClass || $fieldClass == '') {
                    $fieldClass = $oldFieldClass;
                } else {
                    if (!$this->checkCompatibilityOfTypes($oldFieldClass, $fieldClass)) {
                        return false;
                    }
                }
                $sql = "update " . $objectName . "_structure_fields set name = '$fieldName', class = '$fieldClass' where id = '$fieldID'";
                Yii::$app->db->createCommand($sql)->execute();
                //if($fieldClass != $oldFieldClass){
                $this->updateDataUseFieldType($objectID, $objectName, $droleID, $fieldID, $fieldName, $this->getFieldTypeByID($fieldClass));
                $this->updateStructureFieldRecursively($objectID, $objectName, $droleID, $fieldID, $fieldName, $fieldClass);
                //}
                $oldName = $fieldArray['name'];
            }
            if ($oldFieldName && $fieldName != $oldFieldName) {
                LogObjectHandler::updateLogRecordForObject($objectName, $objectName . "_structure_fields", $fieldID, 'name', $oldFieldName, $fieldName, $droleID, $contactID);
            }
            if ($oldFieldClass && $fieldClass != $oldFieldClass) {
                LogObjectHandler::updateLogRecordForObject($objectName, $objectName . "_structure_fields", $fieldID, 'class', $oldFieldClass, $fieldClass, $droleID, $contactID);
            }
            return true;
        } else {
            //echo "name is presented yet.";
            return false;
        }
    }

    public function checkRecursiveUseDataObject($objectInsertedID, $objectBaseName)
    {
        $sql = "select * from " . $objectBaseName . "_structure_fields where id = '$objectInsertedID'";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        if ($provider->getModels()) {
            return false;
        }
        $sql = "select * from " . $objectBaseName . "_structure_use_fast where json_structure::text LIKE '%\"object\": \"$objectInsertedID\"%'";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        if ($provider->getModels()) {
            return false;
        }
        return true;
    }

    private function updateDataUseFieldName($objectName, $oldFieldName, $fieldName)
    {
        $sql = "ALTER TABLE " . $objectName . "_data_use RENAME COLUMN $oldFieldName TO $fieldName";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    private function checkCompatibilityOfTypes($oldValue, $newValue)
    {
        $oldRealClass = $this->getFieldTypeByID($oldValue);
        $newRealClass = $this->getFieldTypeByID($newValue);
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

    private function getFieldTypeByID($uuidType)
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

    private function updateDataUseFieldType($objectID, $objectName, $droleID, $fieldID, $fieldName, $fieldClass)
    {
        $sql = "ALTER TABLE " . $objectName . "_data_use ALTER $fieldName TYPE $fieldClass USING $fieldName::$fieldClass, ALTER $fieldName DROP DEFAULT, ALTER $fieldName DROP NOT NULL";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public function updateStructureFieldRecursively($objectID, $objectName, $droleID, $fieldID, $fieldName, $fieldClass)
    {
        $allCases = $this->getAllCasesStructureOfUse($objectID);
        //array_splice($allCases, 0, 0, [['object_id' => $fieldClass, 'used_object_id' => $objectID, 'used_object_name' => $objectName]]);
        $usedObjectsArray = array();
        $indexOfCasesArray = 0;
        while ($indexOfCasesArray < count($allCases)) {
            if (!in_array($allCases[$indexOfCasesArray]['used_object_id'], $usedObjectsArray)) {
                $localCases = $this->getAllCasesStructureOfUse($allCases[$indexOfCasesArray]['used_object_id']);
                $allCases = $this->compaireUpdatesStructureArrays($allCases, $localCases);
                for ($j = 0; $j < count($localCases); $j++) {
                    $isPresented = false;
                    for ($i = 0; $i < count($allCases); $i++) {
                        if ($localCases[$j]['used_object_id'] == $allCases[$i]['used_object_id']) {
                            $isPresented = true;
                            break;
                        }
                    }
                    if (!$isPresented && !in_array($localCases[$j], $usedObjectsArray)) {
                        array_push($allCases, $localCases[$j]);
                    }
                }
                array_push($usedObjectsArray, $allCases[$indexOfCasesArray]['used_object_id']);
            }
            $indexOfCasesArray++;
        }
        foreach ($allCases as $caseLineRecord) {
            //get all assemblyes with that field
            $sql = "SELECT distinct on (id) * FROM " . $caseLineRecord['used_object_name'] . "_assembly_fields_use WHERE field in (SELECT " . $caseLineRecord['used_object_name'] . "_structure_fields.id FROM " . $caseLineRecord['used_object_name'] . "_structure_fields where class = '" . $caseLineRecord['object_id'] . "')";
            $providerAllAssemblyes = new SqlDataProvider([
                'sql' => $sql
            ]);
            $assemblyesArray = $providerAllAssemblyes->getModels();
            //update assemblyes
            foreach ($assemblyesArray as $assembly) {
                $sql = "SELECT " . $caseLineRecord['used_object_name'] . "_assembly_fields_use.*, " . $caseLineRecord['used_object_name'] . "_structure_fields.class, " . $caseLineRecord['used_object_name'] . "_structure_fields.name, (select name from registry_classes where id = class) as type FROM " . $caseLineRecord['used_object_name'] . "_assembly_fields_use INNER JOIN " . $caseLineRecord['used_object_name'] . "_structure_fields on (" . $caseLineRecord['used_object_name'] . "_assembly_fields_use.field = " . $caseLineRecord['used_object_name'] . "_structure_fields.id)";
                $providerAssemblyFields = new SqlDataProvider([
                    'sql' => $sql
                ]);
                $assemblyFieldsArray = $providerAssemblyFields->getModels();
                $valueGateModel = $this->getFastStructureTree($caseLineRecord['used_object_id'], $droleID, $assemblyFieldsArray);
                $this->deleteFastStructure($caseLineRecord['used_object_id'], $droleID);
                $this->setFastStructure($caseLineRecord['used_object_id'], $droleID, $valueGateModel);
            }
        }
    }

    /** function only for structure.
     *  get all objects where object placed in first line
     */
    private function getAllCasesStructureOfUse($objectID)
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
    }

    /* private function updateLogRecordForObject($objectName, $objectTable, $recordID, $fieldToUpdateID, $oldValue, $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0') {
      $insertQuery = 'INSERT INTO ' . $objectName . '_log (id, table_name, record_id, field, value_old, value_new, date_change, drole_id, operator_id, ip_address) VALUES (\'' . UUIDGenerator::v4() . '\', \'' . $objectTable . '\', \'' . $recordID . '\', \'' . $fieldToUpdateID . '\', \'' . $oldValue . '\', \'' . $newValue . '\', \'' . microtime(true) . '\', \'' . $droleID . '\', \'' . $contactID . '\', \'' . $ipAddress . '\')';
      \Yii::$app->db->createCommand($insertQuery)->execute();
      } */

    public function compaireUpdatesStructureArrays($mustArray, $incomingArray)
    {
        if (!$incomingArray) {
            return $mustArray;
        }
        if (!$mustArray) {
            return $incomingArray;
        }
        for ($j = 0; $j < count($incomingArray); $j++) {
            $insert = false;
            for ($i = 0; $i < count($mustArray); $i++) {

                try {
                    if ($mustArray[$i]['object_id'] == $incomingArray[$j]['object_id']) {
                        $insert = true;
                        break;
                    }
                } catch (yii\base\ErrorException $ex) {
                    return false;
                }
            }
            if (!$insert) {
                array_push($mustArray, $incomingArray[$j]);
                break;
            }
        }
        return $mustArray;
    }

    public static function getDescriptionCurrentObjectValue($objectName, $tableName, $recordID)
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

    public function deleteObjectDataStructureField($objectID, $droleID, $contactID, $fieldID)
    {
        $fieldArray = false;
        if ($fieldID) {
            $currentFieldsList = $this->getFieldAllParamsForAssembly($objectID, $droleID)->getModels();
            foreach ($currentFieldsList as $field) {
                if ($field['field'] == $fieldID) {
                    $fieldArray = $field;
                    break;
                }
            }
        } else {
            return false;
        }
        if ($fieldArray && $fieldArray['delete'] != 1) {
            return false;
        }
        $objectName = RegistryObjects::getObjectNameByID($objectID)->name;
        $sql = "delete from " . $objectName . "_structure_fields where id = '$fieldID'";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "ALTER TABLE " . $objectName . "_data_use DROP " . $fieldArray['name'];
        \Yii::$app->db->createCommand($sql)->execute();
        return true;
    }

    public function updateAssemblyFieldTurn($assemblyID, $objectName, $fieldID, $position, $isDelete = false)
    {
        $sql = "select * from " . $objectName . "_assembly_fields_use where id = '$assemblyID' order by turn";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        if (!$provider->getModels()) {
            return false;
        }
        $arrayAssemblyes = $provider->getModels();
        $currentFieldArray = false;
        for ($i = 0; $i < count($arrayAssemblyes);) {
            if ($arrayAssemblyes[$i]['field'] == $fieldID) {
                //delete
                $currentFieldArray = $arrayAssemblyes[$i];
                array_splice($arrayAssemblyes, $i, 1);
            } else {
                $arrayAssemblyes[$i]['turn'] = $i;
                $i++;
            }
        }
        if (!$isDelete) {
            if ($position > -1 && $position < count($arrayAssemblyes)) {
                //insert
                array_splice($arrayAssemblyes, $position, 0, $currentFieldArray);
            } else {
                array_push($arrayAssemblyes, $currentFieldArray);
            }
            for ($i = 0; $i < count($arrayAssemblyes); $i++) {
                $arrayAssemblyes[$i]['turn'] = $i;
            }
        } else {
            $sql = "delete from " . $objectName . "_assembly_fields_use where id = '$fieldID'";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        foreach ($arrayAssemblyes as $recordToUpdate) {
            $sql = "update " . $objectName . "_assembly_fields_use set turn = '" . $recordToUpdate['turn'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        return true;
    }

    private function getJsonDataForStructure($companyID, $serviceID, $contactID, $currentObjectID, $recordID, $globalStructure)
    {
        $resultArray = array();
        //get data record from db
        $fieldsWithTypes = '';
        foreach ($globalStructure as $record) {
            $fieldsWithTypes .= $record['name'] . " " . $record['type'] . ", ";
        }
        if (strlen($fieldsWithTypes) <= 2) {
            return false;
        }

        $fieldsWithTypes = substr($fieldsWithTypes, 0, count($fieldsWithTypes) - 2);
        //operation for standard query. company only
        $currentDataRecord = $this->getRecordForObjectAndID($currentObjectID, $companyID, $recordID, $fieldsWithTypes)->getModels();
        if (!$currentDataRecord || $currentDataRecord == '' || $currentDataRecord == NULL) {
            return false;
        }
        $currentDataRecord = $currentDataRecord[0];
        //create real fields with a data
        $index = 0;
        foreach ($globalStructure as $record) {
            $currentValue = $currentDataRecord[$record['name']];
            $nested = $record['nested'];
            if (!$nested) {
                $resultArray[$index] = $currentValue;
            } else {
                $nestedRecords = $this->getImplementedRecordsForNested($record['object'], $currentValue)->getModels();
                if (!$nestedRecords || $nestedRecords == '' || $nestedRecords == NULL) {
                    $resultArray[$index] = false;
                } else {
                    $nestedResultArray = array();
                    $nestedIndex = 0;
                    foreach ($nestedRecords as $localRecord) {
                        $nestedResultArray[$nestedIndex] = $this->getJsonDataForStructure($companyID, $serviceID, $contactID, $record['object'], $localRecord['id'], $nested);
                        $nestedIndex++;
                    }
                    $resultArray[$index] = $nestedResultArray;
                }
            }
            $index++;
        }
        return $resultArray;
    }

    //assembly values update

    private function getRecordForObjectAndID($currentObjectID, $companyID, $recordID, $onlyFields, $fieldsWithTypes)
    {
        $params = [':object_id' => $currentObjectID, ':company_id' => $companyID, ':record_id' => $recordID, ':fields_list' => $onlyFields];
        $sql = "select * from getdatarecordforcompanyowner(:object_id, :company_id, :record_id, :fields_list) as ds(" . $fieldsWithTypes . ")";
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    private function getImplementedRecordsForNested($currentObjectID, $implementedID)
    {
        $params = [':object_id' => $currentObjectID, ':implemented_id' => $implementedID];
        $sql = "select * from getimplementedrecords(:object_id, :implemented_id) as ds(id uuid)";
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

}
