<?php
namespace common\modules\drole\changer;

use common\modules\drole\registry\DynamicRoleModel;
use common\modules\drole\registry\RegistryDescriptionRolesModel;
use common\modules\drole\object\SimpleObjectHandler;
use common\modules\drole\registry\RegistryObjectValues;
use common\modules\drole\object\ObjectStructureModel;
use common\modules\drole\object\TestObject;

class DataChangeMaker
{

    public static $currentWorkObjectName = "";
    private static $companyFieldName = 'company_id';
    private static $serviceFieldName = 'service_id';
    private static $roleFieldName = 'role_id';
    private static $contactFieldName = 'contact_id';
    private static $objectFieldName = 'object_id';
    private static $dynamicRoleFieldName = 'drole_id';
    private static $assemblyFieldName = 'assembly_id';
    private static $activeFieldName = 'active';
    public static $inputParamsArray;
    public static $objectsArray;
    private $paramsNamedArray;
    private $globalDataArray;
    private $arrayOfUsefulObjects;
    private $objectsTree;
    private $objectsList;
    private $parentMaps;
    private $objectFieldRelativeHashMap;

    public function getRecords($inputParams)
    {
        //$externalDemand = $inputParams['external_demand'];
        //global $globalDataArray, $objectsTree, $objectsList, $parentMaps, $objectFieldRelativeHashMap;

        $companyID = $inputParams['objectid03'];
        $serviceID = $inputParams['objectid04'];
        $roleID = $inputParams['objectid05'];
        $contactID = $inputParams['objectid01'];
        $objectId = $inputParams['object'];
        $drole = $this->getDrole($companyID, $serviceID, $roleID);
        $inputParams['drole'] = $drole;
        //get all objects from permissions
        //$objectInputParams = ['drole' => $drole, 'object' => $objectId];
        self::$objectsArray = $this->getObjectsArray($inputParams);
        $firstObject = new SimpleObjectHandler($inputParams);

        $this->globalDataArray = $firstObject->getAllDataFromObject();
        //print_r($firstObject->getAllDataFromObject());
        //print_r($this->globalDataArray);
        //exit;
        //$this->objectsTree = [$firstObject->getHash() => []];

        $this->objectFieldRelativeHashMap = [];
        $firstHash = $firstObject->getHash();
        $this->objectsList = [$firstHash => $firstObject];
        //echo '[first element: ' . print_r([$firstHash => []], true) . ']';
        $this->objectsTree = $this->getStructureTree($firstObject, $inputParams, [$firstHash => []]);
        //echo json_encode($this->objectsTree);
        //echo json_encode($this->parentMaps);
        //echo json_encode($this->createStructureMap($firstHash, [$firstHash => []]));
        //test block

        echo '[-------------------------------------------------------------------------------------------------]';
        //echo json_encode($this->objectFieldRelativeHashMap);
        //echo '[data array: ' . print_r($this->globalDataArray, true) . ']';
        echo json_encode($this->getDataUseFromMapObjects($firstHash, $this->globalDataArray));
        exit;
        echo '[start function getDataUseFromObject(' . $firstObject->getObjectName() . ')]';
        $structureFieldModel = new ObjectStructureModel($firstObject->getObjectName());
        $structureFields = $structureFieldModel->getDataAnotherObjectsFromTable()->getModels();
        $presentAssembly = $firstObject->getAssemblyFieldsArray();
        $resultArray = $this->getDataUseFromObject($inputParams, $this->globalDataArray, $structureFields, $presentAssembly);
        echo '[-------------------------------------------------------------------------------------------------]';
        echo '[-------------------------------------------------------------------------------------------------]';
        echo '[-------------------------------------------------------------------------------------------------]';
        echo json_encode($resultArray);
        //print_r($resultArray);
    }

    private function getStructureTree($parentObject, $extInputParams, $presentArray)
    {
        //global $objectsList, $parentMaps, $objectFieldRelativeHashMap;
        //echo '[start work with object: ' . $parentObject->getObjectName() . ' #' . $parentObject->getHash() . ']';
        $structureFields = $parentObject->getStructureFieldsArray();
        //echo '[structure field for: ' . $parentObject->getObjectName() . ' #' . $parentObject->getHash() . ' is: ' . print_r($structureFields, true) . ']';
        $presentAssembly = $parentObject->getAssemblyFieldsArray();
        $inputParams = $extInputParams;
        $parentHash = $parentObject->getHash();
        $arrayOfInnerValues = $presentArray[$parentHash];
        $arrayOfRelation = [];
        foreach ($structureFields as $field) {
            $fieldID = $field['id'];
            foreach ($presentAssembly as $assembly) {
                $assemblyFieldID = $assembly['field'];
                if ($assemblyFieldID == $fieldID) {
                    $inputParams['object'] = $field['class'];
                    $currentFieldObject = new SimpleObjectHandler($inputParams);
                    $currentHash = $currentFieldObject->getHash();
                    $this->objectsList[$currentHash] = $currentFieldObject;
                    $this->parentMaps[$currentHash] = $parentHash;
                    if ($arrayOfInnerValues == null || count($arrayOfInnerValues) == 0) {
                        $arrayOfInnerValues = [$currentHash => []];
                    } else {
                        array_push($arrayOfInnerValues, [$currentHash => []]);
                    }
                    array_push($arrayOfRelation, [$currentFieldObject->getObjectName() => $currentHash]);
                    $presentArray[$parentHash] = $this->getStructureTree($currentFieldObject, $inputParams, $arrayOfInnerValues);
                }
            }
        }
        $this->objectFieldRelativeHashMap[$parentHash] = $arrayOfRelation;
        return $presentArray;
    }

    private function getChildsForParent($parentHash)
    {
        //global $parentMaps;
        $resultArray = [];
        $keys = array_keys($this->parentMaps);
        for ($i = 0; $i < count($keys); $i++) {
            $value = $this->parentMaps[$keys[$i]];
            //echo '[ #' . $value . ' == #' . $parentHash . ' ]';
            if ($value == $parentHash) {
                array_push($resultArray, [$keys[$i] => []]);
            }
        }
        return $resultArray;
    }

    private function createStructureMap($startHash, $resultMap)
    {
        $inheritsArray = $this->getChildsForParent($startHash);
        for ($i = 0; $i < count($inheritsArray); $i++) {
            $keys = array_keys($inheritsArray[$i]);
            //echo '[' . $startHash . ':' . print_r($keys[0], true) . ']';
            $inheritsArray[$i] = $this->createStructureMap($keys[0], [$keys[0] => []]);
        }
        $resultMap[$startHash] = $inheritsArray;
        return $resultMap;
    }

    private function getDataUseFromMapObjects($parentHash, $dataObjectRecords)
    {
        //global $objectsList, $parentMaps, $objectFieldRelativeHashMap;
        //$currentObject = $objectsList[$parentHash];
        $inheritsArray = $this->getChildsForParent($parentHash);
        $parentObjectsStructure = $this->objectsList[$parentHash]->getStructureFieldsArray();
        //echo '***********[structure current object: ' . print_r($parentObjectsStructure, true) . ']********************';
        //echo '***********[assembly current object: ' . print_r($this->objectsList[$parentHash]->getAssemblyFieldsArray(), true) . ']********************';
        for ($i = 0; $i < count($dataObjectRecords); $i++) {
            //echo '[data record: ' . print_r($dataObjectRecords[$i], true) . ']';
            if (count($this->objectFieldRelativeHashMap[$parentHash]) > 0) {
                //echo '[try work with objects: ' . print_r($this->objectFieldRelativeHashMap[$parentHash], true) . ']===================================';
                foreach ($this->objectFieldRelativeHashMap[$parentHash] as $key => $value) {
                    $fieldName = array_keys($value)[0];
                    $inheritHash = $value[$fieldName];
                    $int = $key;
                    //echo '[' . $i . ': field: ' . $fieldName . ', hash: ' . $inheritHash . ']';
                    $currentObject = $this->objectsList[$inheritHash];

                    //echo '[try work with hash: ' . $inheritHash . ', field: ' . $fieldName . ', object: ' . $currentObject->getObjectName() . ', key: ' . $int . ', fieldname: ' . $parentObjectsStructure[$int]['name'] . ']';
                    $fieldData = $currentObject->getAllDataFromObject($dataObjectRecords[$i][$parentObjectsStructure[$key]['name']])->getModels();
                    $dataObjectRecords[$i][$parentObjectsStructure[$key]['name']] = $this->getDataUseFromMapObjects($inheritHash, $fieldData);
                }
            }
        }
        return $dataObjectRecords;
    }

    private function getDataUseFromObject($parentHash, $dataObjectRecords)
    {
        global $globalDataArray, $objectsTree, $objectsList, $parentMaps;
        $parentObject = $objectsList[$parentHash];
        $structureFields = $parentObject->getStructureFieldsArray();
        $presentAssembly = $parentObject->getAssemblyFieldsArray();
        $presentFields = [];
        foreach ($structureFields as $field) {
            $fieldID = $field['id'];
            foreach ($presentAssembly as $assembly) {
                $assemblyFieldID = $assembly['field'];
                if ($assemblyFieldID == $fieldID) {
                    echo '[try work with class: ' . $field['class'] . ']';
                    $inputParams['object'] = $field['class'];
                    $currentFieldObject = $this->getSimpleObject($inputParams);
                    //$inputParams['hash'] = $currentFieldObject->getHash();
                    //$this->checkUsefulObjectAndAdd($currentFieldObject);
                    //$hash = $currentFieldObject->getHash();

                    $structureFieldModel = new ObjectStructureModel($currentFieldObject->getObjectName());
                    $structureFields = $structureFieldModel->getDataAnotherObjectsFromTable()->getModels();
                    $presentAssembly = $currentFieldObject->getAssemblyFieldsArray();
                    for ($i = 0; $i < count($dataObjectRecords); $i++) {
                        $testValue = $currentFieldObject->getAllDataFromObject($dataObjectRecords[$i][$field['name']])->getModels();
                        if ($field['name'] == 'account') {
                            echo '[function object: ' . $inputParams['object'] . ', internal object: ' . $currentFieldObject->getObjectName() . ']';
                            //echo '[current object: ' . $this->getObjectByHash($inputParams['hash'])->getObjectName() . ', hash: ' . $this->getObjectByHash($inputParams['hash'])->getHash() . ']';
                            //echo '[parent object: ' . $parentObject->getObjectName() . ', hash: ' . $parentObject->getHash() . ']';
                            echo '[yess! for: ' . $dataObjectRecords[$i][$field['name']] . ', find: ' . print_r($testValue, true) . ']';
                        }
                        $dataObjectRecords[$i][$field['name']] = $this->getDataUseFromObject($inputParams, $testValue, $structureFields, $presentAssembly);
                    }
                    //continue;
                }
            }
        }
        return $dataObjectRecords;
    }

    public function checkUsefulObjectAndAdd($object)
    {
        global $arrayOfUsefulObjects;
        echo '[try add object: ' . $object->getObjectName() . ' #' . $object->getHash() . ']';
        if ($arrayOfUsefulObjects == null) {
            $arrayOfUsefulObjects = [$object->getHash() => $object];
            echo '[add checkUsefulObjectAndAdd(' . $object->getObjectName() . ')]';
            return;
        } else {
            //echo '[first wtf]';
        }
        $keysOfArray = array_keys($arrayOfUsefulObjects);
        if (!in_array($object->getHash(), $keysOfArray)) {
            echo '[add checkUsefulObjectAndAdd(' . $object->getObjectName() . ')]';
            $arrayOfUsefulObjects[$object->getHash()] = $object;
        } else {
            //echo '[second wtf]';
        }
    }

    public function getObjectByHash($hash)
    {
        global $arrayOfUsefulObjects;
        return $arrayOfUsefulObjects[$hash];
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

    public function setRecord($inputParams)
    {
        
    }

    public function updateRecord($inputParams)
    {
        
    }

    public function deleteRecord($inputParams)
    {
        
    }

    private function getDrole($companyID, $serviceID, $roleID)
    {
        $dynamicRoleModel = DynamicRoleModel::findOne(self::createParams($companyID, $serviceID, $roleID))->id;
        if ($dynamicRoleModel == '' || $dynamicRoleModel === false) {
            return -1;
        } else {
            return $dynamicRoleModel;
        }
    }

    private function createParams($companyID, $serviceID, $roleID)
    {
        //for anonimous role has one assembly for all companies
        $resultParams = [self::$serviceFieldName => $serviceID, self::$roleFieldName => $roleID];
        if ($roleID != RegistryDescriptionRolesModel::$anonimus) {
            $resultParams[self::$companyFieldName] = $companyID;
        }
        return $resultParams;
    }

    private function getObjectsArray($inputParams)
    {
        $resultArray = [];
        $extObjectName = RegistryObjectValues::find()->select(['id'])->asArray()->all();
        $inputKeys = array_keys($inputParams);
        //print_r(($extObjectName));
        foreach ($extObjectName as $line) {
            if (in_array($line['id'], $inputKeys)) {
                $resultArray[$line['id']] = $inputParams[$line['id']];
            }
        }
        return $resultArray;
    }
}
