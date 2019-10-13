<?php
namespace common\modules\drole\object;

use common\modules\drole\registry\DynamicAssemblyForObject;
use \common\modules\drole\registry\RegistryObjectValues;

class ObjectShort
{
    private $specialParamsArray;
    private $assemblyID;

    public function __construct($extSpecialParamsArray)
    {
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
            echo "[not found. exit]";
            return -2;
        } else {
            echo "[assembly: " . $this->assemblyID . "]";
        }
        $extObjectName = RegistryObjectValues::findOne([id => $objectId])->name;
        if ($extObjectName == '' || $extObjectName === false) {
            echo "[not found. exit]";
            return;
        } else {
            echo "[" . $extObjectName . "]";
        }
        $this->specialParamsArray['object_title'] = $extObjectName;
        $assemblyUseModel = new ObjectAssemblyUseModel($extObjectName);
        $sqlProviderAssembly = $assemblyUseModel->getAssemblyUsingFieldsObject($this->assemblyID);
        $this->assemblyFieldsArray = $sqlProviderAssembly->getModels();
        $structureFieldModel = new ObjectStructureModel($extObjectName);
        $this->structureFieldsArray = $structureFieldModel->getDataAnotherObjectsFromTable()->getModels();
        $this->hashIndex = $this->random_gen(6);
    }

    public function getAllDataFromObject($externalDemand = '')
    {
        $dataModelObject = new ObjectDataUseModel($this->specialParamsArray['object_title']);
        return $dataModelObject->getAllDataFromTable($this->specialParamsArray, $this->assemblyFieldsArray, $externalDemand);
    }

    public function getDataUseByRowID($rowID)
    {
        $dataModelObject = new ObjectDataUseModel($this->specialParamsArray['object_title']);
        return $dataModelObject->getRecordByRowID($rowID);
    }

    public function getAssemblyID()
    {
        //global $assemblyFieldsArray;
        return $this->assemblyID;
    }

    public function getAssemblyFieldsArray()
    {
        //global $assemblyFieldsArray;
        return $this->assemblyFieldsArray;
    }

    public function getStructureFieldsArray()
    {
        //global $structureFieldsArray;
        return $this->structureFieldsArray;
    }

    public function getObjectName()
    {
        //global $specialParamsArray;
        //print_r($specialParamsArray);
        return $this->specialParamsArray['object_title'];
    }

    public function getObjectID()
    {
        //global $specialParamsArray;
        //print_r($specialParamsArray);
        return $this->specialParamsArray['object'];
    }

    public function getInputParams()
    {
        return $this->specialParamsArray;
    }

    private function random_gen($length)
    {
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

    public function getHash()
    {
        //global $hashIndex;
        return $this->hashIndex;
    }
}
