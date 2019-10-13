<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 8/20/2018
 * Time: 7:10 PM
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\RegistryObjects;

class SpecialQueryHandler
{

    public static function getBodyForSetObjectListValues($jsonIncomBody, $addedWhere = null)
    {
        //*************** start Fast Data Block ***********************
        $droleID = $jsonIncomBody['permission']['drole_id'];
        //$objectID = $jsonIncomBody['permission']['object_id'];
        $objectName = RegistryObjects::getObjectNameByID($jsonIncomBody['permission']['object_id'])->name;
        if ($jsonIncomBody['permission']['object_id'] != 'debc1348-d852-4d5a-835b-5a2bc4c5ead4' || !$objectName) {
            return APIHandler::getErrorArray(404, "Not found object for id.");
        }
        $token = DataObjectAPIHandler::getCommonFilterFromWorkParams($jsonIncomBody);
        $drolesMap = [$droleID];
        $accessArray = DataObjectAPIHandler::dataAccessValues($jsonIncomBody, $objectName, $droleID);
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
            $accessArray = DataObjectAPIHandler::dataAccessValues($jsonIncomBody, $objectName, $newDrole);
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
        $resultStructure = DataObjectAPIHandler::getObjectFastStructure($jsonIncomBody, $objectName);

        if (!$resultStructure || count($resultStructure) < 1) {
            return APIHandler::getErrorArray(403, "Not found structure for dynamic role. ");
        }
        $decodedStructure = json_decode($resultStructure['json_structure'], true);
        $ownercurrentMap = self::getMapIndexForFieldID($decodedStructure, 'ee6798e8-a845-47a5-a46b-78f8aa84051c');//ownercurrencyid
        $ownerbaseMap = self::getMapIndexForFieldID($decodedStructure, 'd94a9745-28a1-417d-8a72-1b22cdc0dfc6');//ownercurrencyid
        $typeTransactionMap = self::getMapIndexForFieldID($decodedStructure, '7e27659f-4a38-479f-a17d-0f70425b4942');//ownercurrencyid
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
        if (APIHandler::getSubqueryServicePermissionForGetter($jsonIncomBody['permission']['object_id'], $droleArray['role_id'])) {
            $sql = "select jsonb_agg(query) from (
                select $queryString FROM " . $objectName . "_data_use JOIN (select * from (select " . $objectName . "_data_use.* from " . $objectName . "_data_use where 
                " . $objectName . "_data_use.ownercurrencyid = '" . $jsonIncomBody['permission']['contact_id'] . "'" .
                $whereFilter . " union all 
                select " . $objectName . "_data_use.* from " . $objectName . "_data_use where 
                " . $objectName . "_data_use.ownerbasecurrencyid = '" . $jsonIncomBody['permission']['contact_id'] . "'" .
                $whereFilter . ") as 
                " . $objectName . "_data_use  " . $orderByValue .
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
        $sql = "select jsonb_agg(query) from (
                select $queryString FROM " . $objectName . "_data_use JOIN (select * from (select " . $objectName . "_data_use.* from " . $objectName . "_data_use where 
                " . $objectName . "_data_use.ownercurrencyid = '" . $jsonIncomBody['permission']['contact_id'] . "'" .
            $whereFilter . " union all 
                select " . $objectName . "_data_use.* from " . $objectName . "_data_use where 
                " . $objectName . "_data_use.ownerbasecurrencyid = '" . $jsonIncomBody['permission']['contact_id'] . "'" .
            $whereFilter . ") as 
                " . $objectName . "_data_use  " . $orderByValue .
            " " . $limitByValue . ") as fast on fast.id = " . $objectName . "_data_use.id " . $orderByValue .
            ") query;";
        /*if ($orderByValue != ' order by cryptotransactions_data_use."date_change" desc') {
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
        if (APIHandler::getSubqueryServicePermissionForGetter($jsonIncomBody['permission']['object_id'], $droleArray['role_id'])) {
            $prevID = null;
            for ($i = 0; $i < count($resultDataArray); $i++) {
                if ($resultDataArray[$i][0] == $prevID) {
                    $resultDataArray[$i][$typeTransactionMap] = ($resultDataArray[$i][$typeTransactionMap] == 0 ? 1 : 0);
                }
                if ($resultDataArray[$i][$ownercurrentMap] != $jsonIncomBody['permission']['contact_id']) {
                    $resultDataArray[$i][$ownercurrentMap] = 'null';
                }
                if ($resultDataArray[$i][$ownerbaseMap] != $jsonIncomBody['permission']['contact_id']) {
                    $resultDataArray[$i][$ownerbaseMap] = 'null';
                }
                $prevID = $resultDataArray[$i][0];
            }
        } else {
            $prevID = null;
            for ($i = 0; $i < count($resultDataArray); $i++) {
                if ($resultDataArray[$i][0] == $prevID) {
                    $resultDataArray[$i][$typeTransactionMap] = ($resultDataArray[$i][$typeTransactionMap] == 0 ? 1 : 0);
                }
                $prevID = $resultDataArray[$i][0];
            }
        }
        //id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text
        //*************** end Fast Data Block ***********************
        $resultArray = [];
        $resultArray['data'] = $resultDataArray;//self::getObjectFastData($jsonIncomBody);

        $resultArray['structure']['data'] = $decodedStructure;
        $resultArray['structure']['ltime'] = $resultStructure['ltime'];
        $resultArray['work'] = DataObjectAPIHandler::getWorkBlockForObjectRegistry($jsonIncomBody, $resultArray['data'], $resultArray['structure']);
        return $resultArray;
    }

    private static function getMapIndexForFieldID($structure, $fieldID)
    {
        for ($i = 0; $i < count($structure); $i++) {
            if ($structure[$i]['id'] == $fieldID) {
                return $i;
            }
        }
    }
}