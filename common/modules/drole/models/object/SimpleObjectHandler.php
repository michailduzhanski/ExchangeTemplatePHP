<?php

namespace common\modules\drole\object;

use common\modules\drole\registry\DynamicAssemblyForObject;
use common\modules\drole\registry\RegistryObjectValues;
use common\modules\drole\object\ObjectFastDataUse;

class SimpleObjectHandler {

    public static $currentWorkObjectName = "";
    public static $companyFieldName = 'company_id';
    public static $serviceFieldName = 'service_id';
    public static $roleFieldName = 'role_id';
    public static $contactFieldName = 'contact_id';
    public static $objectFieldName = 'object_id';
    public static $dynamicRoleFieldName = 'drole_id';
    public static $assemblyFieldName = 'assembly_id';
    public static $activeFieldName = 'active';
    public static $editFieldName = 'edit';
    private $specialParamsArray;
    private $assemblyFieldsArray;
    private $assemblyID;
    private $structureFieldsArray;
    public $hashIndex;

    public function __construct($extSpecialParamsArray) {
        //global $specialParamsArray, $assemblyFieldsArray, $structureFieldsArray, $hashIndex;
        $this->specialParamsArray = $extSpecialParamsArray;
        $objectId = $extSpecialParamsArray['object'];
        $dynamicRoleModel = $extSpecialParamsArray['drole'];
        $active = 1;

        if ($dynamicRoleModel == '' || $dynamicRoleModel === false) {
            echo "[not found. exit]";
            return -1;
        } else {
            echo "[drole: " . $dynamicRoleModel . "]";
        }
        $this->assemblyID = DynamicAssemblyForObject::findOne([self::$dynamicRoleFieldName => $dynamicRoleModel, self::$objectFieldName => $objectId, self::$activeFieldName => $active])->assembly_id;
        if ($this->assemblyID == '' || $this->assemblyID === false) {
            echo "[not found assembly id for drole: " . $dynamicRoleModel . " and object: " . $objectId . ". exit]";
            return -2;
        } else {
            echo "[assembly: " . $this->assemblyID . "]";
        }
        $extObjectName = RegistryObjectValues::findOne(['id' => $objectId])->name;
        if ($extObjectName == '' || $extObjectName === false) {
            echo "[not found. exit]";
            return;
        } else {
            echo "[" . $extObjectName . "]";
        }
        $this->specialParamsArray['object_title'] = $extObjectName;
        $assemblyUseModel = new ObjectAssemblyUseModel($extObjectName);
        $sqlProviderAssembly = $assemblyUseModel->getAssemblyUsingFieldsObject($this->assemblyID);
        $this->assemblyFieldsArray = $this->array_orderby($sqlProviderAssembly->getModels(), 'turn', SORT_ASC);
        $structureFieldModel = new ObjectStructureModel($extObjectName);
        $this->structureFieldsArray = $structureFieldModel->getDataAnotherObjectsFromTable()->getModels();
        $this->hashIndex = $this->random_gen(6);
    }

    function array_orderby() {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    public function getAllDataFromObject($externalDemand = '') {
        $dataModelObject = new ObjectDataUseModel($this->specialParamsArray['object_title']);
        return $dataModelObject->getAllDataFromTable($this->specialParamsArray, $this->assemblyFieldsArray, $externalDemand);
    }

    public function getFastDataFromObject() {
        $dataModelObject = new ObjectFastDataUse($this->specialParamsArray['object_title']);
        return $dataModelObject->getAllDataFromTable($this->specialParamsArray, $this->getAssemblyID(), $this->assemblyFieldsArray);
    }

    public function getDataUseByRowID($rowID) {
        $dataModelObject = new ObjectDataUseModel($this->specialParamsArray['object_title']);
        return $dataModelObject->getRecordByRowID($this->assemblyFieldsArray, $rowID);
    }

    public function getAssemblyID() {
        //global $assemblyFieldsArray;
        return $this->assemblyID;
    }

    public function getAssemblyFieldsArray() {
        //global $assemblyFieldsArray;
        return $this->assemblyFieldsArray;
    }

    public function getStructureFieldsArray() {
        //global $structureFieldsArray;
        return $this->structureFieldsArray;
    }

    public function getObjectName() {
        //global $specialParamsArray;
        //print_r($specialParamsArray);
        return $this->specialParamsArray['object_title'];
    }

    public function getObjectID() {
        //global $specialParamsArray;
        //print_r($specialParamsArray);
        return $this->specialParamsArray['object'];
    }

    public function getInputParams() {
        return $this->specialParamsArray;
    }

    private function random_gen($length) {
        $random = "";
        srand((double) microtime() * 1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890";
        // Add the special characters to $char_list if needed

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($char_list, (rand() % (strlen($char_list))), 1);
        }
        return $random;
    }

    public function getHash() {
        //global $hashIndex;
        return $this->hashIndex;
    }

}
