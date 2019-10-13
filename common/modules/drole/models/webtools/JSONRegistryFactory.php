<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\drole\models\webtools;

use common\modules\drole\models\auth\ContactAuth;
use common\modules\drole\models\registry\DynamicRoleModel;

//use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;

/**
 * Description of JSONRegistryFactory
 *
 * @author LILIYA
 */
class JSONRegistryFactory
{

    //put your code here
    public static function getObjectsList($isEncode, $tableName, $commonFilter = "", $ctime = FALSE)
    {
        return self::getLocalJSONRegistryForList($isEncode, ["table" => $tableName], $commonFilter, $ctime);
    }

    public static function getLocalJSONRegistryForList($isEncode, $valuesArray, $commonFilter, $ctime = FALSE)
    {
        if (!$ctime) {
            $ctime = microtime(true);
        }
        $UID = \Yii::$app->user->getId();
        $arrayData = ContactAuth::getContactAuthByID($UID);
        if (!$arrayData) {
            return false;
        }

        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($arrayData->drole);
        $resultArray = [
            "permission" => [
                "object_id" => "registry",
                "service_id" => $droleArray['service_id'],
                "contact_id" => $UID,
                "drole_id" => $droleArray['id']
            ],
            "work" => [
                "set" => 0,
                "operation" => 0,
                'ctime' => $ctime,
                "value" => $valuesArray//
            ],
            "filters" => [["common" => $commonFilter]]
        ];
        if ($isEncode)
            return json_encode($resultArray);
        else
            return $resultArray;
    }

    public static function getLocalJSONRegistryForListAnonymous($companyID, $serviceID, $isEncode, $valuesArray, $commonFilter, $ctime = FALSE)
    {
        if (!$ctime) {
            $ctime = microtime(true);
        }
        $sql = "select * from registry_drole_base where company_id = '$companyID' and service_id = '$serviceID' and role_id = '69ebe402-022a-4fb1-9472-f16c4b768c26'";
        $droleArray = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$droleArray || count($droleArray) < 1) {
            return false;
        }

        $resultArray = [
            "permission" => [
                "object_id" => "registry",
                "contact_id" => "anonymous",
                "service_id" => $serviceID,
                "drole_id" => $droleArray['id']
            ],
            "work" => [
                "set" => 0,
                "operation" => 0,
                'ctime' => $ctime,
                "value" => $valuesArray//
            ],
            "filters" => [["common" => $commonFilter]]
        ];
        if ($isEncode)
            return json_encode($resultArray);
        else
            return $resultArray;
    }

    public static function getAssembliesList($isEncode, $objectID, $commonFilter = "", $ctime = FALSE)
    {
        return self::getLocalJSONRegistryForList($isEncode, ["table" => "assembly", "object" => $objectID], $commonFilter, $ctime);
    }

    public static function getAccessRulesList($isEncode, $objectID, $commonFilter = "", $ctime = FALSE)
    {
        return self::getLocalJSONRegistryForList($isEncode, ["table" => "access", "object" => $objectID], $commonFilter, $ctime);
    }

    public static function getCompaniesList($isEncode, $objectID, $commonFilter = "", $ctime = FALSE)
    {
        return self::getLocalJSONRegistryForList($isEncode, ["table" => "companies", "object" => $objectID], $commonFilter, $ctime);
    }

    public static function getLocalJSONRegistryForStructure($isEncode, $objectID, $commonFilter = "", $ctime = FALSE)
    {
        return self::getLocalJSONRegistryForList($isEncode, ["object" => $objectID], $commonFilter, $ctime);
    }

    public static function getRecordsListFromObject($isEncode, $objectID, $commonFilter = "", $ctime = FALSE)
    {
        $result = self::getLocalJSONRegistryForList(false, [], $commonFilter, $ctime);
        $result['permission']['object_id'] = $objectID;
        $result['work']['set'] = 1;
        if ($isEncode)
            return json_encode($result);
        else
            return $result;
    }

    public static function getRecordsListFromObjectAnonymous($companyID, $serviceID, $isEncode, $objectID, $commonFilter = "", $ctime = FALSE)
    {
        $result = self::getLocalJSONRegistryForListAnonymous($companyID, $serviceID, false, [], $commonFilter, $ctime);
        $result['permission']['object_id'] = $objectID;
        $result['work']['set'] = 1;
        if ($isEncode)
            return json_encode($result);
        else
            return $result;
    }

    public static function updateObject($isEncode, $objectID, $arrayValuesField, $ctime = FALSE)
    {
        $result = self::getLocalJSONRegistryForList(false, [], '', $ctime);
        $result['permission']['object_id'] = $objectID;
        $result['work']['set'] = 1;
        $result['work']['operation'] = 2;
        $result['work']['value']['record'] = $arrayValuesField;
        if ($isEncode)
            return json_encode($result);
        else
            return $result;
    }

}
