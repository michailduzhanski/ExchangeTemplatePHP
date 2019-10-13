<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\RegistryObjects;
use yii\data\SqlDataProvider;

/**
 * Description of FiltersObjectHandler
 *
 * @author LILIYA
 */
class FiltersObjectHandler
{

    public static function getSubqueryFromDBForFilterGroup($objectName, $groupID, $structure)
    {
        $sql = "select * from " . $objectName . "_filter_group where id = '$groupID'";
        $providerGroups = new SqlDataProvider([
            'sql' => $sql
        ]);
        $groupRecordArray = $providerGroups->getModels();
        if (!$groupRecordArray) {
            return false;
        }
        $sql = "select * from " . $objectName . "_filter_record where company_id = '" . $groupRecordArray[0]['company_id'] . "'";
        $providerRecords = new SqlDataProvider([
            'sql' => $sql
        ]);
        $arrayOfFilterRecords = $providerRecords->getModels();
        return self::getSubqueryFromFilterGroup($groupRecordArray[0]['map'], $arrayOfFilterRecords, $structure, $objectName);
    }

    private static function getSubqueryFromFilterGroup($jsonGroupsRecord, $arrayOfFilterRecords, $structure, $objectName)
    {
        $arrayGroupsValues = json_decode($jsonGroupsRecord, true);
        return self::getSubqueryFromFilter($arrayGroupsValues, $arrayOfFilterRecords, $structure, $objectName);
    }

    private static function getSubqueryFromFilter($arrayGroupRecords, $arrayOfFilterRecords, $structure, $objectName)
    {
        $resultString = "";
        //echo print_r($arrayGroupRecords, true); exit;
        $arrayValues = $arrayGroupRecords['values'];
        $arraySigns = $arrayGroupRecords['signs'];
        $failArray = array();
        for ($i = 0; $i < count($arrayValues); $i++) {
            if (is_array($arrayValues[$i]['v_' . $i])) {
                $groupSubquery = self::getSubqueryFromFilter($arrayValues[$i]['v_' . $i], $arrayOfFilterRecords, $structure, $objectName);
                if ($groupSubquery) {
                    $resultString .= $groupSubquery;
                    if ($i < count($arraySigns)) {
                        $resultString .= " " . $arraySigns[$i]['s_' . $i] . " ";
                    }
                } else {
                    array_push($failArray, $i);
                    if ((count($failArray) + 1) == count($arrayValues)) {
                        return false;
                    }
                }
            } else {
                $filterSubquery = self::getSubqueryByFilterRecord(self::getFilterRecordFromArray($arrayValues[$i]['v_' . $i], $arrayOfFilterRecords), $structure, $objectName);
                if ($filterSubquery) {
                    $resultString .= $filterSubquery;
                    if ($i < count($arraySigns)) {
                        $resultString .= " " . $arraySigns[$i]['s_' . $i] . " ";
                    }
                } else {
                    array_push($failArray, $i);
                    if ((count($failArray) + 1) == count($arrayValues)) {
                        return false;
                    }
                }
            }
        }
        return "(" . $resultString . ")";
    }

    public static function getSubqueryByFilterRecord($filterRecord, $structure, $objectName)
    {
        if (!$filterRecord['valueobject']) {
            //if not exist class for compare
            $sqlSubquery = self::getSqlFromMap($filterRecord['map'], $filterRecord['compareoperation'], $structure, $objectName) .
                self::getValueWithCompareSign($filterRecord['compareoperation'], $filterRecord['value']);
        } else {
            $sqlSubquery = self::getSqlFromMap($filterRecord['map'], $filterRecord['compareoperation'], $structure, $objectName) .
                self::getStringMarkForCompareOperation($filterRecord['compareoperation']) .
                " (select " . $filterRecord['valuefield'] . " from " . $filterRecord['valueobject'] . "_data_use where id = '" . $filterRecord['value'] . "') ";
        }
        return $sqlSubquery;
    }

    /*private static function getSqlFromMap($map, $operationType)
    {
        $mapElements = explode('.', $map);
        $resultString = "";
        for ($i = 0; $i < count($mapElements) - 1; $i++) {
            $resultString .= "->'" . $mapElements[$i] . "'";
        }
        $resultString .= "->>'" . $mapElements[$i] . "'";
        if ($operationType < 6) {
            //for last element
            $resultString = "(json_field " . $resultString . ")::double precision ";
        } else {
            $resultString = "json_field " . $resultString . " ";
        }
        return $resultString;
    }*/

    private static function getSqlFromMap($map, $operationType, $structureArray, $objectName)
    {
        $mapElements = explode('.', $map);
        //temporary design

        if (count($mapElements) > 1) {
            return '';
        }
        if (!is_numeric($mapElements[0]) || !isset($structureArray[$mapElements[0]]) || $structureArray[$mapElements[0]]['nested'] != "false") {
            return '';
        }
        if ($operationType == 7){
            return "lower(" . $objectName . "_data_use.\"" . $structureArray[$mapElements[0]]['name'] . "\")";
        }
        return "" . $objectName . "_data_use.\"" . $structureArray[$mapElements[0]]['name'] . "\"";
        //end
        $resultString = "";
        for ($i = 0; $i < count($mapElements) - 1; $i++) {
            $resultString .= "->'" . $mapElements[$i] . "'";
        }
        $resultString .= "->>'" . $mapElements[$i] . "'";
        if ($operationType < 6) {
            //for last element
            $resultString = "(json_field " . $resultString . ")::double precision ";
        } else {
            $resultString = "json_field " . $resultString . " ";
        }
        return $resultString;
    }

    private static function getValueWithCompareSign($operationType, $compareValue)
    {
        switch ($operationType) {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                return self::getStringMarkForCompareOperation($operationType) . " '" . $compareValue . "'";
            case 6:
                return "= '" . $compareValue . "'";
            case 7:
                return "::text ILIKE '%" . $compareValue . "%'";
        }
    }

    public static function getStringMarkForCompareOperation($operationType)
    {
        switch ($operationType) {
            case 0:
                return "=";
            case 1:
                return ">=";
            case 2:
                return ">";
            case 3:
                return "<=";
            case 4:
                return "<";
            case 5:
                return "!=";
            default:
                return "=";
        }
    }

    private static function getFilterRecordFromArray($filterID, $arrayOfFilterRecords)
    {
        foreach ($arrayOfFilterRecords as $filterRecord) {
            if ($filterRecord['id'] == $filterID) {
                return $filterRecord;
            }
        }
        return false;
    }

    public static function getBodyForFilterRecords($jsonIncomBody)
    {
        //id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($jsonIncomBody['permission']['drole_id']);
        if (!$droleArray || RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id']) > 1) {
            return APIHandler::getErrorArray(403, 'not found for this role');
        }
        $objectName = RegistryObjects::getObjectNameByID($jsonIncomBody['work']['value']['object'])->name;
        $resultArray['structure'] = ['id', 'name', 'company', 'accesslevel', 'map', 'compareoperation', 'valueobject', 'valuefield', 'value', 'exvalueobjectname', 'exvaluefieldname', 'ltime', 'description'];
        $resultArray['data'] = self::getValueByFilter(self::getCommonFilterFromWorkParams($jsonIncomBody), self::getListOfFilterRecordsFromDB($objectName, $droleArray['company_id'], RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id'])));
        $resultArray['work'] = RegistryApiHandler::getWorkBlockForObjectRegistry($resultArray['data']);
        return $resultArray;
    }

    private static function getValueByFilter($commonToken, $data)
    {
        if ($commonToken && strlen(trim($commonToken)) > 1) {
            $resultArray = array();
            foreach ($data as $record) {
                if (strpos($record['name'], $commonToken) === false && strpos($record['description'], $commonToken) === false) {
                    //do nothing
                } else {
                    array_push($resultArray, $record);
                }
            }
        } else {
            return $data;
        }
        return $resultArray;
    }

    public static function getCommonFilterFromWorkParams($jsonIncomBody)
    {
        $commonToken = '';
        if (isset($jsonIncomBody['work']['filters'])) {
            foreach ($jsonIncomBody['work']['filters'] as $filter) {
                if (isset($filter['common'])) {
                    $commonToken = $filter['common'];
                    break;
                }
            }
        }
        return $commonToken;
    }

    public static function getListOfFilterRecordsFromDB($objectName, $companyID, $accessLevel, $filterID = NULL)
    {
        $sql = "select " . $objectName . "_filter_record.*, (select date_change from " .
            $objectName . "_log where table_name = 'filter_record' and record_id = " . $objectName . "_filter_record.id) as ltime, (select description from " .
            $objectName . "_description where table_name = 'filter_record' and record_id = " . $objectName . "_filter_record.id) as description from " .
            $objectName . "_filter_record where";
        if ($filterID) {
            $sql .= " id = '$filterID'";
        } else {
            $sql .= " company_id = '" . $companyID . "' and accesslevel >= '$accessLevel'";
        }
        $providerRecords = new SqlDataProvider([
            'sql' => $sql
        ]);
        $arrayOfFilterRecords = $providerRecords->getModels();
        if (!$arrayOfFilterRecords) {
            return false;
        }
        return $arrayOfFilterRecords;
    }

    public static function getBodyForFilterGroups($jsonIncomBody)
    {
        //id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($jsonIncomBody['permission']['drole_id']);
        if (!$droleArray || RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id']) > 1) {
            return APIHandler::getErrorArray(403, 'not found for this role');
        }
        $objectName = RegistryObjects::getObjectNameByID($jsonIncomBody['work']['value']['object'])->name;
        $resultArray['structure'] = ['id', 'name', 'company', 'accesslevel', 'map', 'ltime', 'description'];
        $resultArray['data'] = self::getValueByFilter(self::getCommonFilterFromWorkParams($jsonIncomBody), self::getListOfFilterGroupsFromDB($objectName, $droleArray['company_id'], RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id'])));
        $resultArray['work'] = RegistryApiHandler::getWorkBlockForObjectRegistry($resultArray['data']);
        return $resultArray;
    }

    public static function getListOfFilterGroupsFromDB($objectName, $companyID, $accessLevel, $groupID = NULL)
    {
        $sql = "select " . $objectName . "_filter_group.*, (select date_change from " .
            $objectName . "_log where table_name = 'filter_group' and record_id = " . $objectName . "_filter_group.id) as ltime, (select description from " .
            $objectName . "_description where table_name = 'filter_group' and record_id = " . $objectName . "_filter_group.id) as description from " .
            $objectName . "_filter_group where";
        if ($groupID) {
            $sql .= " id = '$groupID'";
        } else {
            $sql .= " company_id = '" . $companyID . "' and accesslevel >= '$accessLevel'";
        }
        $providerRecords = new SqlDataProvider([
            'sql' => $sql
        ]);
        $arrayOfFilterRecords = $providerRecords->getModels();
        if (!$arrayOfFilterRecords) {
            return false;
        }
        return $arrayOfFilterRecords;
    }

    public static function updateObjectGroupValueDescription($objectName, $droleID, $contactID, $recordID, $newDescription)
    {
        $oldDescription = self::getDescriptionCurrentObjectValue($objectName, "filter_record", $recordID);
        $sql = "delete from " . $objectName . "_description where table_name = 'filter_record' and record_id = '" . $recordID . "'";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_description values ('" . UUIDGenerator::v4() . "', 'filter_record', '" . $recordID . "', '" . $newDescription . "')";
        \Yii::$app->db->createCommand($sql)->execute();
        LogObjectHandler::updateLogRecordForObject($objectName, $objectName . "_description", $recordID, 'description', $oldDescription, $newDescription, $droleID, $contactID);
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

    public static function getSubscriberWhereByJsonFilter($filtersBlock, $structureArray, $objectName)
    {
        $whereSQL = "";
        foreach ($filtersBlock as $filterRecord) {
            if (!is_numeric($filterRecord['map']) || !isset($structureArray[$filterRecord['map']])) {
                continue;
            }
            $whereSQL .= self::getSqlFromMap($filterRecord['map'], $filterRecord['comp'], $structureArray, $objectName) . self::getValueWithCompareSign($filterRecord['comp'], $filterRecord['value']) . " and ";
        }
        if ($whereSQL != "") {
            return "(" . substr($whereSQL, 0, strlen($whereSQL) - 4) . ")";
        }
    }

    private static function setFilterRule($objectName, $droleID, $contactID, $filterRecordID, $arrayOfRule)
    {
        //rule consists from:
        //map - path to compare element in data_use,
        //compareoperation - compare sign,
        //valueobject - compare object,
        //valuefield - compare field in data_use of compare object,
        //value - record_id in data_use of compare object or specific value
        //exvalueobjectname - string object name (only for speed)
        //exvaluefieldname - string field name (only for speed)
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        $accessLevel = RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id']);
        if (!$droleArray || $accessLevel > 1) {
            return APIHandler::getErrorArray(403, 'not found for this role.');
        }
        $arrayOfFilterRecords = self::getListOfFilterRecordsFromDB($objectName, $droleArray['company_id'], $accessLevel, $filterRecordID);
        if (!$arrayOfFilterRecords) {
            return APIHandler::getErrorArray(403, 'not found for this role.');
        } else {
            //update filter record
            if ($arrayOfFilterRecords[0]['company_id'] != $droleArray['company_id']) {
                return APIHandler::getErrorArray(403, 'bad filter for company.');
            }
            $valueobject = '';
            if ($arrayOfRule['valueobject']) {
                $valueobject = ", valueobject = '" . $arrayOfRule['valueobject'] . "'";
            }
            $exvalueobjectname = '';
            if ($arrayOfRule['exvalueobjectname']) {
                $exvalueobjectname = ", exvalueobjectname = '" . $arrayOfRule['exvalueobjectname'] . "'";
            }
            $valuefield = '';
            if ($arrayOfRule['valuefield']) {
                $valuefield = ", valuefield = '" . $arrayOfRule['valuefield'] . "'";
            }
            $exvaluefieldname = '';
            if ($arrayOfRule['exvaluefieldname']) {
                $exvaluefieldname = ", exvaluefieldname = '" . $arrayOfRule['exvaluefieldname'] . "'";
            }
            $sql = "update " . $objectName . "_filter_record set map = '" . $arrayOfRule['map'] . "', value = '" .
                $arrayOfRule['value'] . "', compareoperation = '" . $arrayOfRule['compareoperation'] . "'" .
                $valueobject . $valuefield . $exvalueobjectname . $exvaluefieldname . " where id = '$filterRecordID';";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        foreach ($arrayOfRule as $key => $value) {
            LogObjectHandler::updateLogRecordForObject($objectName, "filter_record", $filterRecordID, $key, $arrayOfFilterRecords[0][$key], $value, $droleID, $contactID);
        }
    }

    private static function setGroupRule($objectName, $droleID, $contactID, $filterRecordID, $mapJSON)
    {
        //rule consists from:
        //map - path to compare element in data_use
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        $accessLevel = RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id']);
        if (!$droleArray || $accessLevel > 1) {
            return APIHandler::getErrorArray(403, 'not found for this role.');
        }
        $arrayOfFilterRecords = self::getListOfGroupRecordsFromDB($objectName, $droleArray['company_id'], $accessLevel, $filterRecordID);
        if (!$arrayOfFilterRecords) {
            return APIHandler::getErrorArray(403, 'not found for this role.');
        } else {
            //update filter record
            if ($arrayOfFilterRecords[0]['company_id'] != $droleArray['company_id']) {
                return APIHandler::getErrorArray(403, 'bad group for company.');
            }

            $sql = "update " . $objectName . "_filter_record set map = '" . $mapJSON . "' where id = '$filterRecordID';";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        LogObjectHandler::updateLogRecordForObject($objectName, "filter_group", $filterRecordID, 'map', $arrayOfFilterRecords[0]['map'], $mapJSON, $droleID, $contactID);
    }

    private static function setFilterRecord($objectName, $droleID, $contactID, $filterRecordID, $name, $description)
    {
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        $accessLevel = RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id']);
        if (!$droleArray || $accessLevel > 1) {
            return APIHandler::getErrorArray(403, 'not found for this role.');
        }
        $arrayOfFilterRecords = self::getListOfFilterRecordsFromDB($objectName, $droleArray['company_id'], $accessLevel, $filterRecordID);
        $oldName = '';
        if (!$arrayOfFilterRecords) {
            //insert new filter
            $sql = "insert into " . $objectName . "_filter_record (id, name, company_id, accesslevel) values ('$filterRecordID', '$name', '" . $droleArray['company_id'] . "', '$accessLevel')";
            \Yii::$app->db->createCommand($sql)->execute();
        } else {
            //update filter record
            $oldName = $arrayOfFilterRecords[0]['name'];
            if ($arrayOfFilterRecords[0]['company_id'] != $droleArray['company_id']) {
                return APIHandler::getErrorArray(403, 'bad filter for company.');
            }
            $sql = "update " . $objectName . "_filter_record set name = '$name' where id = '$filterRecordID';";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        LogObjectHandler::updateLogRecordForObject($objectName, "filter_record", $filterRecordID, 'name', $oldName, $name, $droleID, $contactID);
        self::updateObjectFilterValueDescription($objectName, "filter_record", $droleID, $contactID, $filterRecordID, $description);
    }

    public static function updateObjectFilterValueDescription($objectName, $objectTable, $droleID, $contactID, $recordID, $newDescription)
    {
        $oldDescription = self::getDescriptionCurrentObjectValue($objectName, $objectTable, $recordID);
        $sql = "delete from " . $objectName . "_description where table_name = '$objectTable' and record_id = '" . $recordID . "'";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_description values ('" . UUIDGenerator::v4() . "', '$objectTable', '" . $recordID . "', '" . $newDescription . "')";
        \Yii::$app->db->createCommand($sql)->execute();
        LogObjectHandler::updateLogRecordForObject($objectName, $objectName . "_description", $recordID, 'description', $oldDescription, $newDescription, $droleID, $contactID);
    }

    private static function setGroupRecord($objectName, $droleID, $contactID, $filterRecordID, $name, $description)
    {
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        $accessLevel = RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id']);
        if (!$droleArray || $accessLevel > 1) {
            return APIHandler::getErrorArray(403, 'not found for this role.');
        }
        $arrayOfFilterRecords = self::getListOfGroupRecordsFromDB($objectName, $droleArray['company_id'], $accessLevel, $filterRecordID);
        $oldName = '';
        if (!$arrayOfFilterRecords) {
            //insert new filter
            $sql = "insert into " . $objectName . "_filter_group (id, name, company_id, accesslevel) values ('$filterRecordID', '$name', '" . $droleArray['company_id'] . "', '$accessLevel')";
            \Yii::$app->db->createCommand($sql)->execute();
        } else {
            //update filter record
            $oldName = $arrayOfFilterRecords[0]['name'];
            if ($arrayOfFilterRecords[0]['company_id'] != $droleArray['company_id']) {
                return APIHandler::getErrorArray(403, 'bad filter group for company.');
            }
            $sql = "update " . $objectName . "_filter_group set name = '$name' where id = '$filterRecordID';";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        LogObjectHandler::updateLogRecordForObject($objectName, "filter_group", $filterRecordID, 'name', $oldName, $name, $droleID, $contactID);
        self::updateObjectFilterValueDescription($objectName, "filter_group", $droleID, $contactID, $filterRecordID, $description);
    }

}
