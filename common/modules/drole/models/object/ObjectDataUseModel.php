<?php
namespace common\modules\drole\object;

use yii\data\SqlDataProvider;
use common\modules\drole\object\DBWorkConstructor;
use common\modules\drole\gate\CommonGateModel;
use common\modules\drole\registry\droles\DroleRelationRules;
use common\modules\drole\registry\droles\RegistryDescriptionRolesModel;

class ObjectDataUseModel extends DBWorkConstructor
{

    public function __construct($extObjectName)
    {
        $extSuffixName = "data_use";
        parent::__construct($extObjectName, $extSuffixName);
    }

    //get by record_id

    public function getAllDataFromTable($inputQueryPermissions, $arrayOfAssemblyPermissions, $externalDemand = '')
    {
        //global $objectName;
        //echo '[ObjectDataUseModel::getAllDataFromTable(' . print_r($inputQueryPermissions, true) . ')]';
        $drole = $inputQueryPermissions['drole'];
        //$externalDemand = $inputQueryPermissions['external_demand'];
        if ($externalDemand != '') {
            $resultArray = $this->getRecordsByDemands($arrayOfAssemblyPermissions, $externalDemand);
            return $resultArray;
        }
        $querySuffix = '';
        $checkRole = DroleRelationRules::getTheRoleForInputParams($inputQueryPermissions);
        if ($inputQueryPermissions[DroleRelationRules::$requestObjectID] == DroleRelationRules::$contactsObjectID && $checkRole == RegistryDescriptionRolesModel::$admin) {
            /* $arrayKeys = array_keys(CommonGateModel::$objectsArray);
              for ($i = 0; $i < count(CommonGateModel::$objectsArray); $i++) {
              $querySuffix .= '(object_id = \'' . $arrayKeys[$i] . '\' and own_id = \'' . CommonGateModel::$objectsArray[$arrayKeys[$i]] . '\') or ';
              }
              if (count($arrayKeys) > 0) {
              $querySuffix = substr($querySuffix, 0, strlen($querySuffix) - 4);
              } */
            $querySuffix = '(object_id = \'' . DroleRelationRules::$contactsObjectID . '\' and own_id = \'' . $inputQueryPermissions[DroleRelationRules::$contactsObjectID] . '\')';
            $byDroleProvider = new SqlDataProvider([
                'sql' => 'SELECT * FROM ' . $this->objectName . '_record_own where ' . $querySuffix
                , 'totalCount' => 1
            ]);
            if ($byDroleProvider->getModels() === false || empty($byDroleProvider->getModels())) {
                return [];
            } else {
                $recordsPresent = $byDroleProvider->getModels();
            }
            if (count($recordsPresent) > 0) {
                return $this->getRecords($arrayOfAssemblyPermissions, $recordsPresent)->getModels();
            }
        }
        //echo '[SELECT * FROM ' . $this->objectName . '_data_drole_access where drole_id = \'' . $drole . '\']';
        $byDroleProvider = new SqlDataProvider([
            'sql' => 'SELECT * FROM ' . $this->objectName . '_data_drole_access where drole_id = \'' . $drole . '\''
            //, 'totalCount' => $count
        ]);
        $recordsPresent = array();
        if ($byDroleProvider->getModels() === false || empty($byDroleProvider->getModels())) {
            return [];
        } else {
            $recordsPresent = $byDroleProvider->getModels();
        }
        if (count($recordsPresent) > 0) {
            return $this->getRecords($arrayOfAssemblyPermissions, $recordsPresent)->getModels();
        } else {
            return [];
        }
    }

    public function getRecordByRowID($usedFieldsByAssembly, $rowID)
    {
        $fieldsUse = '';
        foreach ($usedFieldsByAssembly as $line) {
            $fieldsUse .= $line['name'] . ' as "' . $line['turn'] . '", ';
            //$fieldsUse .= $line['name'] . ', ';
        }
        $fieldsUse = substr($fieldsUse, 0, strlen($fieldsUse) - 2);

        $provider = new SqlDataProvider([
            'sql' => 'SELECT ' . $fieldsUse . ' FROM ' . self::getTableName() . ' where id = \'' . $rowID . '\''
        ]);
        return $provider;
    }

    private function getRecords($usedFieldsByAssembly, $arrayOfID)
    {
        //global $objectName;
        $fieldsUse = '';
        $idsUse = '';
        foreach ($usedFieldsByAssembly as $line) {
            $fieldsUse .= $line['name'] . ' as "' . $line['turn'] . '", ';
        }
        $fieldsUse = substr($fieldsUse, 0, strlen($fieldsUse) - 2);
        //echo '[' . $fieldsUse . ']';
        foreach ($arrayOfID as $line) {
            $idsUse .= ' id = \'' . $line['record_id'] . '\' or ';
        }
        $idsUse = substr($idsUse, 0, strlen($idsUse) - 4);
        //echo '[' . $idsUse . ']';
        //echo '[SELECT ' . $fieldsUse . ' FROM ' . self::getTableName() . ' where ' . $idsUse . ']';
        $query = 'SELECT ' . $fieldsUse . ' FROM ' . self::getTableName() . ' where ' . $idsUse;
        if (self::getTableName() == 'address_data_use') {
            echo '[get data for assembly: ' . $query . ']';
        }
        $provider = new SqlDataProvider([
            'sql' => $query
        ]);
        return $provider;
    }

    private function getRecordsByDemands($usedFieldsByAssembly, $implementedID)
    {
        //global $objectName;

        $fieldsUse = '';

        if ($usedFieldsByAssembly === false || $usedFieldsByAssembly == '' || $usedFieldsByAssembly == []) {
            return [];
        }
        //echo '[$usedFieldsByAssembly = ' . print_r($usedFieldsByAssembly[0], true) . ', count = ' . count($usedFieldsByAssembly) . ']';
        foreach ($usedFieldsByAssembly as $line) {
            $fieldsUse .= $line['name'] . ' as "' . $line['turn'] . '", ';
        }
        $fieldsUse = substr($fieldsUse, 0, strlen($fieldsUse) - 2);
        //echo '[SELECT ' . $fieldsUse . ' FROM ' . self::getTableName() . ' where id in (select record_id from ' .
        $this->objectName . '_implemented_records where ' . $this->objectName . '_implemented_records.implemented_id = \'' . $implementedID . '\')]';
        if ($implementedID == 'contactimp02') {
            //echo '[getRecordsByDemands SELECT ' . $fieldsUse . ' FROM ' . self::getTableName() . ' where id in (select record_id from ' .
            $this->objectName . '_implemented_records where ' . $this->objectName . '_implemented_records.implemented_id = \'' . $implementedID . '\')]';
        }
        /*if (self::getTableName() == 'links_data_use') {
            echo '[get links_data_use for assembly: ' . 'SELECT ' . $fieldsUse . ' FROM ' . self::getTableName() . ' where id in (select record_id from ' .
            $this->objectName . '_implemented_records where ' . $this->objectName . '_implemented_records.implemented_id = \'' . $implementedID . '\')' . ']';
            echo '[implemented array: ' . print_r($implementedID, true) . ']';
            exit;
        }*/
        $provider = new SqlDataProvider([
            'sql' => 'SELECT ' . $fieldsUse . ' FROM ' . self::getTableName() . ' where id in (select record_id from ' .
            $this->objectName . '_implemented_records where ' . $this->objectName . '_implemented_records.implemented_id = \'' . $implementedID . '\')'
        ]);
        return $provider;
    }
}
