<?php

namespace common\modules\drole\models\gate;

use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\UUIDGenerator;
use common\modules\drole\models\webtools\JSONRegistryFactory;

class DataObjectAPIHandler
{

    public static function parseQuery($jsonIncomingObject)
    {
        //echo json_encode($jsonIncomingObject); exit;
        $resultArray = APIHandler::getErrorArray();
        if (!isset($jsonIncomingObject['permission']) || !isset($jsonIncomingObject['work'])) {
            return $resultArray;
        }
        $permissionBlock = $jsonIncomingObject['permission'];
        $workBlock = $jsonIncomingObject['work'];
        //$filtersBlock = $jsonIncomingObject['filters'];
        //permission
        $objectID = $permissionBlock['object_id'];
        //work
        $viewType = $workBlock['set'];
        $operationType = $workBlock['operation'];
        //$value = $workBlock->value;
        //$viewType = 0;
        //$objectID = 'registry_objects';
        if ($viewType == 1 && $operationType == 0 && UUIDGenerator::isUUID($objectID)) {
            //get structure for object
            $resultArray['result'] = 200;
            $resultArray['message'] = ["success"];

            $data = self::getBodyForSetObjectListValues($jsonIncomingObject);
            if (isset($data['code'])) {
                return $data;
            }
            $resultArray['data'] = $data;
            //return $resultArray;
        } else if ($viewType == 1 && ($operationType == 2 || $operationType == 3) && UUIDGenerator::isUUID($objectID)) {
            //get structure for object
            if ($operationType == 2)
                return UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomingObject);
            else if ($operationType == 3) {
                return UpdateDataObjectHandler::deleteInnerRecord($jsonIncomingObject);
            }
        } else if ($operationType == 4 && UUIDGenerator::isUUID($objectID)) {
            //get structure for object
            $resultArray['result'] = 200;
            $resultArray['message'] = ["success"];

            $data = SpecialQueryHandler::getBodyForSetObjectListValues($jsonIncomingObject);
            if (isset($data['code'])) {
                return $data;
            }
            $resultArray['data'] = $data;
        } else if ($operationType == 5 && UUIDGenerator::isUUID($objectID)) {
            //get structure for object
            $resultArray['result'] = 200;
            $resultArray['message'] = ["success"];

            $data = WalletDataObjectAPIHandler::getBodyForSetObjectListValues($jsonIncomingObject);
            if (isset($data['code'])) {
                return $data;
            }
            $resultArray['data'] = $data;
        }
        return $resultArray;
        //return '{"result":"404", "message":[{"error":"data not found"}]}':
    }

    private static function getBodyForSetObjectListValues($jsonIncomBody, $addedWhere = null)
    {
        //*************** start Fast Data Block ***********************
        $droleID = $jsonIncomBody['permission']['drole_id'];
        //$objectID = $jsonIncomBody['permission']['object_id'];
        $objectName = RegistryObjects::getObjectNameByID($jsonIncomBody['permission']['object_id'])->name;
        if ($jsonIncomBody['permission']['object_id'] == 'debc1348-d852-4d5a-835b-5a2bc4c5ead4' || !$objectName) {
            return APIHandler::getErrorArray(404, "Not found object for id.");
        }
        $token = self::getCommonFilterFromWorkParams($jsonIncomBody);
        $drolesMap = [$droleID];
        $accessArray = self::dataAccessValues($jsonIncomBody, $objectName, $droleID);
        if (isset($accessArray['code'])) {
            return $accessArray;
        }
        $newDrole = AccessRulesHandler::getNewDrole($accessArray);
        while ($newDrole) {
            if (in_array($newDrole, $drolesMap)) {
                return APIHandler::getErrorArray(404, "Wrong way. Recursion of the dynamics role found. " .
                    print_r($drolesMap, true));
            }
            array_push($drolesMap, $newDrole);
            $accessArray = self::dataAccessValues($jsonIncomBody, $objectName, $newDrole);
            if (isset($accessArray['code'])) {
                return $accessArray;
            }
            $newDrole = AccessRulesHandler::getNewDrole($accessArray);
        }
        if (!$newDrole || $newDrole == '') {
            $newDrole = $drolesMap[count($drolesMap) - 1];
            $jsonIncomBody['permission']['drole_id'] = $newDrole;
            $droleID = $newDrole;
        }
        $resultStructure = self::getObjectFastStructure($jsonIncomBody, $objectName);
        if (!$resultStructure || count($resultStructure) < 1) {
            return APIHandler::getErrorArray(403, "Not found structure for dynamic role. ");
        }
        $decodedStructure = json_decode($resultStructure['json_structure'], true);
        //echo json_encode($accessArray);
        $filterGroup = AccessRulesHandler::getFiltersValues($accessArray);
        $whereFilter = '';
        if ($filterGroup) {
            $whereFilter = FiltersObjectHandler::getSubqueryFromDBForFilterGroup($objectName, $filterGroup, $decodedStructure);
            if (strlen($whereFilter) > 4)
                $whereFilter = ' and (' . FiltersObjectHandler::getSubqueryFromDBForFilterGroup($objectName, $filterGroup) . ')';
        }
        if (strlen($token) > 0) {
            $whereFilter .= ' and json_field::text like \'\'%' . $token . '%\'\'';
        }
        $special = '';
        if (isset($jsonIncomBody['filters'][1]['special'])) {
            $special = FiltersObjectHandler::getSubscriberWhereByJsonFilter($jsonIncomBody['filters'][1]['special'], $decodedStructure, $objectName);
        }
        if (strlen($special) > 0) {
            $whereFilter .= ' and ' . $special;
        }
        if ($addedWhere) {
            $whereFilter .= ' and ' . $addedWhere;
        }
        $sortingBlock = null;
        if (isset($jsonIncomBody['filters'][2]['sorting'])) {
            $sortingBlock = $jsonIncomBody['filters'][2]['sorting'];
        }
        $limitBlock = null;
        if (isset($jsonIncomBody['filters'][3]['limit'])) {
            $limitBlock = $jsonIncomBody['filters'][3]['limit'];
        }
        //echo " [ sorting block: " . json_encode($sortingBlock) . "] ";
        $orderByValue = SortingObjectHandler::getSubqueryFromJson($sortingBlock, $decodedStructure, $objectName);
        //echo " [ order subquery: $orderByValue] ";
        $limitByValue = PaginationObjectHandler::getSubqueryFromJson($limitBlock, $decodedStructure, $objectName);
        $queryString = "";
        for ($structureIndex = 0; $structureIndex < count($decodedStructure); $structureIndex++) {
            if ($decodedStructure[$structureIndex]['nested'] == "false") {
                $queryString .= "fast.\"" . $decodedStructure[$structureIndex]['name'] . "\" as \"" . $structureIndex . "\", ";
            } else {
                $queryString .= "(select json_field from " . $objectName . "_data_use_implemented where " . $objectName .
                    "_data_use_implemented.implemented_id = " . $objectName . "_data_use.\"" . $decodedStructure[$structureIndex]['name'] .
                    "\" and drole_id = '" . $droleID . "') as \"" . $structureIndex . "\", ";
            }
        }
        $queryString = substr($queryString, 0, strlen($queryString) - 2);

        /*$sql = "select jsonb_agg(query) from (
            select $queryString FROM " . $objectName . "_data_use JOIN (SELECT * FROM " .
            $objectName . "_data_use where " . $objectName . "_data_use.id in (select " . $objectName . "_record_own.id from " .
            $objectName . "_record_own join (SELECT company_id, service_id FROM registry_drole_base WHERE id = 
            '$droleID') as registry_drole_base on registry_drole_base.company_id = " . $objectName . "_record_own.company_id 
            and registry_drole_base.service_id = " . $objectName . "_record_own.service_id) " . $whereFilter . " " . $orderByValue .
            " " . $limitByValue . ") as fast on fast.id = " . $objectName . "_data_use.id
            ) query;";*/
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        if (APIHandler::getSubqueryServicePermissionForGetter($jsonIncomBody['permission']['object_id'], $droleArray['role_id']) == 2) {
            $sql = "select jsonb_agg(query) from (
                select $queryString FROM " . $objectName . "_data_use JOIN (SELECT * FROM " .
                $objectName . "_data_use where " . $objectName . "_data_use.id in (select " . $objectName . "_record_own.id from " .
                $objectName . "_record_own where " . $objectName . "_record_own.contact_id = '" . $jsonIncomBody['permission']['contact_id'] . "') " . $whereFilter . " " . $orderByValue .
                " " . $limitByValue . ") as fast on fast.id = " . $objectName . "_data_use.id " . $orderByValue .
                ") query;";
        } else {
            $sql = "select jsonb_agg(query) from (
            select $queryString FROM " . $objectName . "_data_use JOIN (SELECT * FROM " .
                $objectName . "_data_use where " . $objectName . "_data_use.id in (select " . $objectName . "_record_own.id from " .
                $objectName . "_record_own join (SELECT company_id, service_id FROM registry_drole_base WHERE id = 
            '$droleID') as registry_drole_base on registry_drole_base.company_id = " . $objectName . "_record_own.company_id 
            and registry_drole_base.service_id = " . $objectName . "_record_own.service_id) " . $whereFilter . " " . $orderByValue .
                " " . $limitByValue . ") as fast on fast.id = " . $objectName . "_data_use.id " . $orderByValue .
                ") query;";
        }
        /*if ($jsonIncomBody['permission']['object_id'] == '4438a6ab-db08-4421-a8bf-9221a8ca7e18') {
            echo $sql;
        }*/
        /*if ($objectName == "marketcoin") {
            echo $sql;
        }*/
        /*if ($whereFilter != '') {
            echo $sql;
            exit;
        }*/
        //$sql = "SELECT * FROM " . $objectName . "_data_use_fast JOIN (select * from getallfastdatause('$objectID', '$newDrole', '" .
        //    $whereFilter . " " . $orderByValue . "') as ds(json_field jsonb)) as result on result.id = " . $objectName . "_data_use_fast.id";
        /*$providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();*/
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$objectsArray || count($objectsArray) < 1) {
            return APIHandler::getErrorArray(404, "Not found fast data use.");
        }

        //echo json_encode($objectsArray); exit;
        $resultDataArray = json_decode($objectsArray['jsonb_agg'], true);
        //id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text
        //*************** end Fast Data Block ***********************
        $resultArray = [];
        $resultArray['data'] = $resultDataArray;//self::getObjectFastData($jsonIncomBody);

        $resultArray['structure']['data'] = $decodedStructure;
        $resultArray['structure']['ltime'] = $resultStructure['ltime'];
        $resultArray['work'] = self::getWorkBlockForObjectRegistry($jsonIncomBody, $resultArray['data'], $resultArray['structure']);
        /*if ($objectName == "coin") {
            echo json_encode($resultArray);
        }*/
        return $resultArray;
    }

    public function getCommonFilterFromWorkParams($jsonIncomBody)
    {
        $commonToken = '';
        if (isset($jsonIncomBody['filters'])) {
            foreach ($jsonIncomBody['filters'] as $filter) {
                if (isset($filter['common'])) {
                    $commonToken = $filter['common'];
                    break;
                }
            }
        }
        return $commonToken;
    }

    public function dataAccessValues($jsonIncomBody, $objectName, $droleID)
    {
        $accessRulesArray = AccessRulesHandler::getAccessRulesForObjectByIncomingArray($jsonIncomBody['permission']['object_id'], $droleID, $jsonIncomBody['permission']['contact_id'], $objectName);
        /*if (!$accessRulesArray || isset($accessRulesArray['result'])){
            return $accessRulesArray;
        }*/
        if (!$accessRulesArray || isset($accessRulesArray['result']) || !AccessRulesHandler::checkAccesToDataObject($accessRulesArray)) {
            return APIHandler::getErrorArray(404, "Not found data access for dynamic role.");
        }
        return $accessRulesArray;
    }

    public static function getObjectFastStructure($jsonIncomBody, $objectName)
    {
        $droleID = $jsonIncomBody['permission']['drole_id'];
        $sql = "select json_structure, (select date_change from " . $objectName . "_log where table_name = 
        'structure_fields' order by date_change desc limit 1) as ltime from " . $objectName . "_structure_use_fast where drole_id = '$droleID'";
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function getObjectFastStructureByDrole($droleID, $objectName)
    {
        $sql = "select json_structure, (select date_change from " . $objectName . "_log where table_name = 
        'structure_fields' order by date_change desc limit 1) as ltime from " . $objectName . "_structure_use_fast where drole_id = '$droleID'";
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function getWorkBlockForObjectRegistry($jsonIncomBody, $data, $structure = false)
    {
        //echo ObjectOperationsHandler::returnIndexedJSON($structure);exit;
        $cdtime = microtime(true);
        $cstime = $cdtime;
        if (isset($jsonIncomBody['work']['ctime'])) {
            $ctime = $jsonIncomBody['work']['ctime'];
        }
        //$resultRecordTime = microtime(true);
        if ($data)
            foreach ($data as $record) {
                if ($record['2'] != 'null' && $record['2'] != '' && $record['2'] < $cdtime) {
                    $cdtime = $record['2'];
                }
            }
        if ($structure) {
            if ($structure['ltime'] != 'null' && $structure['ltime'] != '' && $structure['ltime'] < $cstime) {
                $cstime = $structure['ltime'];
            }
        } else {
            $cstime = $cdtime;
        }
        $resultArray['ctime'] = $cdtime;
        $resultArray['stime'] = $cstime;
        $resultArray['dtime'] = $cdtime;
        return $resultArray;
    }

    public static function getValuesForCompanyFilterRole($objectID)
    {
        $jsonIncomBody = JSONRegistryFactory::getRecordsListFromObject(false, '97086af0-956b-4380-a385-ea823cff377a', '');
        return self::getBodyForSetObjectListValues($jsonIncomBody, "(json_field ->> ''0'')::uuid in (SELECT role_id FROM registry_drole_base WHERE id in (SELECT drole_id FROM registry_drole_assembly WHERE object_id = ''$objectID''))");
    }

    /*public static function getObjectFastData($jsonIncomBody)
    {
        $droleID = $jsonIncomBody['permission']['drole_id'];
        $objectID = $jsonIncomBody['permission']['object_id'];
        $objectName = RegistryObjects::getObjectNameByID($jsonIncomBody['permission']['object_id'])->name;
        if (!$objectName) {
            return APIHandler::getErrorArray(404, "Not found object for id.");
        }
        $token = self::getCommonFilterFromWorkParams($jsonIncomBody);
        $drolesMap = [$droleID];
        $accessArray = self::dataAccessValues($jsonIncomBody, $objectName, $droleID);
        if (isset($accessArray['code'])) {
            return $accessArray;
        }
        $newDrole = AccessRulesHandler::getNewDrole($accessArray);
        while ($newDrole) {
            if (in_array($newDrole, $drolesMap)) {
                return APIHandler::getErrorArray(404, "Wrong way. Recursion of the dynamics role found. " .
                    print_r($drolesMap, true));
            }
            array_push($drolesMap, $newDrole);
            $accessArray = self::dataAccessValues($jsonIncomBody, $objectName, $newDrole);
            if (isset($accessArray['code'])) {
                return $accessArray;
            }
            $newDrole = AccessRulesHandler::getNewDrole($accessArray);
        }
        if (!$newDrole || $newDrole == '') {
            $newDrole = $drolesMap[count($drolesMap) - 1];
        }
        $filterGroup = AccessRulesHandler::getFiltersValues($accessArray);
        $whereFilter = '';
        if ($filterGroup) {
            $whereFilter = FiltersObjectHandler::getSubqueryFromDBForFilterGroup($objectName, $filterGroup);
            if (strlen($whereFilter) > 4)
                $whereFilter = ' and (' . $whereFilter . ')';
        }
        if (isset($jsonIncomBody['filters']['special'])) {
            $special = FiltersObjectHandler::getSubscriberWhereByJsonFilter($jsonIncomBody['filters']['special'], $structureArray, $objectName);
        }
        if (strlen($special) > 0) {
            $whereFilter .= ' and ' . $special;
        }
        if (strlen($token) > 0) {
            $whereFilter .= ' and json_field::text like \'\'%' . $token . '%\'\'';
        }
        $sql = "select * from getallfastdatause('$objectID', '$newDrole', '" . $whereFilter . "') as ds(json_field jsonb)";
        $providerAllObjects = new SqlDataProvider([
            'sql' => $sql
        ]);
        $objectsArray = $providerAllObjects->getModels();
        if (!$objectsArray) {
            return false;
        }
        $resultArray = [];
        foreach ($objectsArray as $record) {
            array_push($resultArray, json_decode($record['json_field'], true));
        }
        //
        //echo print_r($objectsArray); exit;
        //echo $objectsArray[0]['usef']; exit;
        return $resultArray;
    }*/

    public function getFilterFromToken($filterString)
    {

    }
}
