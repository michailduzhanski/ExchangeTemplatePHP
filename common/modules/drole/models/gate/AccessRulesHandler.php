<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\registry\droles\ClassesConstants;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\UUIDGenerator;

/**
 * The handler is used to process data access rules.
 * Parameters on which access is to be transferred to the request data (company, role, service, contact) of the incoming request.
 * There are three levels of data modification. Priorities of use - by the indexes of the location of the rule.
 * The smaller index is the highest priority. Indices and priorities operate within one level of the modification type.
 * Indexes for access rules to the object (super admin: 0 - 10000; admin: 10001 - 50000). When deleting a rule, indexes must be shifted.
 *
 * First level of modification: Access to the entire object.
 *
 * Second level of modification: Access to the current dynamic user role.
 *
 * Third level of modification: Access to the group of filters of the current object.
 *
 *
 * @author LILIYA
 */
class AccessRulesHandler
{

    public static $permanentDeniedToDataObject = "fd36b04c-d3fe-4f28-a45c-364832c118e6";
    public static $permanentAccessToDataObject = "11111111-1111-1111-1111-111111111111";

    //put your code here
    public static function getAccessRulesForObjectByIncomingArray($objectID, $droleID, $contactID, $objectName, $changeDrole = true)
    {
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        if (!$droleArray || count($droleArray) < 1) {
            return APIHandler::getErrorArray(404);
        }
        $companyID = $droleArray['company_id'];
        $whereCompanyOwner = '';
        $whereCompanyToken = '';
        $whereRoleToken = " or (subjectclass = '" . ClassesConstants::$roleClassID . "' and subjectvalue = '" . $droleArray['role_id'] . "')";
        if (isset($droleArray['company_id']) && UUIDGenerator::isUUID($droleArray['company_id'])) {
            $whereCompanyToken = " or (subjectclass = '" . ClassesConstants::$companyClassID . "' and subjectvalue = '$companyID')";
            //$whereCompanyOwner = " company_id = '$companyID' and ";
        } else {
            if ($droleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['anonimous'] &&
                $droleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin'] &&
                $droleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superuserglobal']) {
                return false;
            }
            //$whereCompanyOwner = " company_id is NULL and ";
            $whereRoleToken = " or (subjectclass = '" . ClassesConstants::$roleClassID . "' and subjectvalue = '" . RegistryDescriptionRolesModel::$rolesArray['anonimous'] . "')";
        }
        //if drole changing is not neccessary
        $withoutDroleSuffix = '';
        if (!$changeDrole) {
            $withoutDroleSuffix = 'and (typeaccess != 1)';
        }
        $sql = "select * from " . $objectName . "_access_rules where $whereCompanyOwner ((subjectclass = '" .
            ClassesConstants::$serviceClassID . "' and subjectvalue = '" . ClassesConstants::$companyClassID . "') "
            . "or (subjectclass = '" . ClassesConstants::$contactClassID . "' and subjectvalue = '$contactID')" .
            $whereRoleToken . $whereCompanyToken . ") $withoutDroleSuffix order by typeaccess, priority";
        $provider = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$provider || count($provider) < 1) {
            return false;
        }
        /*$provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        if (!$provider || count($provider->getModels()) < 1) {
            //nothing is found
            return false;
        }
        return $provider->getModels();*/
        return $provider;
    }

    public static function checkAccesToDataObject($accessArray)
    {
        if (!$accessArray || count($accessArray) < 1) {
            return false;
        }
        foreach ($accessArray as $accessRecord) {
            if ($accessRecord['typeaccess'] == 0) {
                if (!$accessRecord['accessclass'] || !$accessRecord['controlclass']) {
                    if ($accessRecord['accesslevel'] == 0) {
                        //check for superadmin record
                        if ($accessRecord['classaccessvalue'] == self::$permanentAccessToDataObject) {
                            return true;
                        }
                    }
                } else {
                    if (self::checkAccessRule($accessArray)) {
                        if ($accessRecord['classaccessvalue'] == self::$permanentAccessToDataObject) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    public static function checkAccessRule($recordRule)
    {
        $accessValue = self::getCompareValueForAccessFilterRule($recordRule['accessclass'], $recordRule['accessfield'], $recordRule['accessrecord']);
        $compareValue = self::getCompareValueForControlFilterRule($recordRule['subjectclass'], $recordRule['controlsubjectfield'], $recordRule['subjectvalue'], $recordRule['controlclass'], $recordRule['controlfield']);
        return self::getOperandForType($recordRule['compareoperation'], $compareValue['getvalueforfiltercontrolrecord'], $accessValue['getvaluebyobjectfieldrecord']);
    }

    private static function getCompareValueForAccessFilterRule($objectID, $fieldID, $recordID)
    {
        $sql = "select * from getValueByObjectFieldRecord('$objectID', '$fieldID', '$recordID')";
        /*$provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        if (!$provider || count($provider->getModels()) < 1) {
            //nothing is found
            return false;
        }
        return $provider->getModels()[0];*/
        $provider = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$provider || count($provider) < 1) {
            return false;
        }
        return $provider;
    }

    private static function getCompareValueForControlFilterRule($sublectClass, $controlSubjectField, $recordSearchedValue, $objectID, $classForFieldID)
    {
        $sql = "select * from getvalueforfiltercontrolrecord('$sublectClass', '$controlSubjectField', '$recordSearchedValue', '$objectID', '$classForFieldID')";
        /*$provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        if (!$provider || count($provider->getModels()) < 1) {
            //nothing is found
            return false;
        }
        return $provider->getModels()[0];*/
        $provider = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$provider || count($provider) < 1) {
            return false;
        }
        return $provider;
    }

    private static function getOperandForType($type, $controlValue, $accessValue)
    {
        if (!self::isTrueType($type, $controlValue) || !self::isTrueType($type, $accessValue)) {
            return false;
        }
        $selfControl = self::getTrueType($type, $controlValue);
        $selfAccess = self::getTrueType($type, $accessValue);
        switch ($type) {
            case 0:
                return $selfControl == $selfAccess;
            case 1:
                return $selfControl >= $selfAccess;
            case 2:
                return $selfControl > $selfAccess;
            case 3:
                return $selfControl <= $selfAccess;
            case 4:
                return $selfControl < $selfAccess;
            case 5:
                return $selfControl != $selfAccess;
            case 6:
                return (stripos($selfAccess, $selfControl) !== false);
            default:
                return false;
        }
    }

    private static function isTrueType($type, $var)
    {
        switch ($type) {
            case 0:
                return true;
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                return is_numeric($var);
            case 6:
                return is_string($var);
            default:
                return false;
        }
    }

    private static function getTrueType($type, $var)
    {
        switch ($type) {
            case 0:
                if (is_bool($var))
                    return boolval($var);
                if (is_null($var))
                    return NULL;
                if (is_numeric($var)) {
                    if (ctype_digit($var))
                        return intval($var);
                    else
                        return floatval($var);
                }
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                if (is_numeric($var)) {
                    if (ctype_digit($var))
                        return intval($var);
                    else
                        return floatval($var);
                }
            case 6:
                return $var;
            default:
                return false;
        }
    }

    /** the method returns one identifier for the filter group of this object. if there were coincidences on the conditions
     * @param $accessArray
     * @return array|bool
     */
    public static function getFiltersValues($accessArray)
    {
        if (!$accessArray || count($accessArray) < 1 || (isset($accessArray['result']) && $accessArray['result'] == 404)) {
            return false;
        }
        $resultArray = array();
        foreach ($accessArray as $accessRecord) {
            if ($accessRecord['typeaccess'] == 2) {
                if (!$accessRecord['accessclass'] || !$accessRecord['controlclass']) {
                    if ($accessRecord['accesslevel'] == 0) {
                        //check for superadmin record
                        return $accessRecord['classaccessvalue'];
                        /*if ($accessRecord['classaccessvalue'] == self::$permanentAccessToDataObject) {
                            //array_push($resultArray, $accessRecord['classaccessvalue']);
                            return $accessRecord['classaccessvalue'];
                        }*/
                    }
                } else {
                    if (self::checkAccessRule($accessArray)) {
                        //array_push($resultArray, $accessRecord['classaccessvalue']);
                        return $accessRecord['classaccessvalue'];
                    }
                }
            }
        }
        return false;
        /*if (count($resultArray) > 0) {
            return $resultArray;
        } else {
            return false;
        }*/
    }

    public static function getNewDrole($accessArray)
    {
        if (!$accessArray || count($accessArray) < 1 || (isset($accessArray['result']) && $accessArray['result'] == 404)) {
            return false;
        }
        foreach ($accessArray as $accessRecord) {
            if (!isset($accessRecord['typeaccess'])) {
                echo json_encode($accessArray);
                exit;
            }
            if ($accessRecord['typeaccess'] == 1) {
                if (!$accessRecord['accessclass'] || !$accessRecord['controlclass']) {
                    if ($accessRecord['accesslevel'] == 0) {
                        //check for superadmin record
                        return $accessRecord['classaccessvalue'];
                    }
                } else {
                    if (self::checkAccessRule($accessArray)) {
                        return $accessRecord['classaccessvalue'];
                    }
                }
            }
        }
        return false;
    }

    public static function getBodyForAccessRules($jsonIncomBody)
    {
        //id uuid, field uuid, turn smallint, usef boolean, visible boolean, edit boolean, delete boolean, insert boolean, name character varying, class uuid, type text
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($jsonIncomBody['permission']['drole_id']);
        if (!$droleArray || RegistryDescriptionRolesModel::getAdministrationLevel($droleArray['role_id']) > 1) {
            return APIHandler::getErrorArray(403, 'not found for this role');
        }
        $objectName = RegistryObjects::getObjectNameByID($jsonIncomBody['work']['value']['object'])->name;
        $resultArray['structure'] = ['id', 'name', 'company_id', 'accesslevel', 'subjectclass', 'subjectvalue', 'accessclass',
            'accessfield', 'accessrecord', 'controlclass', 'controlfield', 'controlsubjectfield', 'compareoperation',
            'typeaccess', 'classaccessvalue', 'priority'];
        $resultArray['data'] = self::getListRules($jsonIncomBody['permission']['drole_id'], $objectName);
        //$resultArray['work'] = RegistryApiHandler::getWorkBlockForObjectRegistry($resultArray['data']);
        return $resultArray;
    }

    public static function getListRules($droleID, $objectName)
    {
        $droleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
        if (!$droleArray || count($droleArray) < 1) {
            return APIHandler::getErrorArray(404);
        }
        $companyID = $droleArray['company_id'];
        $accessWhere = "";
        if ($droleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            $accessWhere = " accesslevel > 0 and ";
        } else if ($droleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin'] &&
            $droleArray['role_id'] != RegistryDescriptionRolesModel::$rolesArray['admin']) {
            $accessWhere = " accesslevel > 1 and ";
        }
        $companyIDSubquery = "company_id = '$companyID'";

        $sql = "select * from " . $objectName . "_access_rules where $accessWhere $companyIDSubquery order by typeaccess, priority";
        $provider = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$provider || count($provider) < 1) {
            return false;
        }
        return AccessRulesHandler::getSubjectClassesWithValue($provider, $droleID);
        //return $provider;
    }

    public static function getSubjectClassesWithValue($listAccessArray, $droleID)
    {
        //$subjectClasses = array();
        $resultArray = array();
        foreach ($listAccessArray as $accessLine) {
            $lineSubjectClass = AccessRulesHandler::getClassAndValue($accessLine['subjectclass'], $accessLine['subjectvalue'], $droleID);
            if ($lineSubjectClass == null) {
                continue;
            }
            //$subjectClasses['id'] = array();
            //$subjectClasses['id']['subject'] = $lineSubjectClass;
            //$listAccessArray[$accessLine['id']]['subjectvalues'] = $lineSubjectClass;
            $companyName = self::getCompanyName($accessLine['company_id']);
            $accessLine['subjectvalues'] = $lineSubjectClass;
            $accessLine['companyname'] = $companyName;
            if($accessLine['accessclass'] != null && $accessLine['accessfield'] && $accessLine['accessrecord']){
                $lineAccessClass = AccessRulesHandler::getClassAndValue($accessLine['accessclass'], $accessLine['subjectvalue'], $droleID);
                $accessLine['accessvalues'] = $lineAccessClass;
            }
            array_push($resultArray, $accessLine);
        }
        return $resultArray;
    }

    public static function getClassAndValue($objectID, $recordID, $droleID)
    {
        $objectName = RegistryObjects::getObjectNameByID($objectID);
        if ($objectName == null) {
            return null;
        } else $objectName = $objectName->name;
        $structure = DataObjectAPIHandler::getObjectFastStructureByDrole($droleID, $objectName);
        //echo print_r($structure, true); exit;
        $decodedStructure = json_decode($structure['json_structure'], true);
        //ObjectOperationsHandler::getFastRecordWithObjectName();
        $special = '';
        $filterLine = ["map" => 0, "comp" => 0, "value" => $recordID];

        $special = FiltersObjectHandler::getSubscriberWhereByJsonFilter([$filterLine], $decodedStructure, $objectName);
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
        $sql = "select jsonb_agg(query) from (
            select $queryString FROM " . $objectName . "_data_use JOIN (SELECT * FROM " .
            $objectName . "_data_use where " . $objectName . "_data_use.id in (select " . $objectName . "_record_own.id from " .
            $objectName . "_record_own join (SELECT company_id, service_id FROM registry_drole_base WHERE id = 
            '$droleID') as registry_drole_base on registry_drole_base.company_id = " . $objectName . "_record_own.company_id 
            and registry_drole_base.service_id = " . $objectName . "_record_own.service_id) and " . $special .
            ") as fast on fast.id = " . $objectName . "_data_use.id ) query;";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$objectsArray || count($objectsArray) < 1) {
            return null;
        }
        return ["object" => $objectName, "structure" => $decodedStructure, "value" => json_decode($objectsArray['jsonb_agg'], true)];
    }

    public static function getCompanyName($recordID){
        $sql = "select * from company_data_use where id = '$recordID'";
        $company = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$company || count($company) < 1) {
            return null;
        }
        return $company['name'];
    }

}
