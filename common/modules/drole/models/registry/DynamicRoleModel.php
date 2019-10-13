<?php

namespace common\modules\drole\models\registry;

use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\registry\droles\DroleRelationRules;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\UUIDGenerator;
use yii\data\SqlDataProvider;

class DynamicRoleModel
{

    //constants
    private static $registryTable = 'registry_drole_base';

    public static function getDynamicRole($inputArray)
    {
        //echo print_r($inputArray);exit;
        try {
            $dynamicRoleID = false;
            $roleID = false;
            $dynamicRoleID = false;
            if (!isset($inputArray['permission']) || !isset($inputArray['permission']['contact_id'])) {
                return APIHandler::getErrorArray();
            }
            $permissionBlock = $inputArray['permission'];
            if (!isset($permissionBlock['drole_id'])) {
                //not found drole_id
                if (!isset($permissionBlock['role_id'])) {
                    $staticRole = self::checkConstantRolesForContact($permissionBlock['contact_id']);
                    if (!$staticRole) {
                        $companyID = false;
                        if (isset($permissionBlock['company_id'])) {
                            $companyID = $permissionBlock['company_id'];
                        }
                        if (!isset($permissionBlock['service_id'])) {
                            return false;
                        }
                        $dynamicRoleID = self::checkRolesForContactCompanyService($permissionBlock['contact_id'], $companyID, $permissionBlock['service_id']);
                        //return APIHandler::getErrorArray();
                    } else {
                        $roleID = $staticRole['role_id'];
                        $dynamicRoleID = $staticRole['id'];
                    }
                }
            } else {
                //found drole_id
                $dynamicRoleID = $permissionBlock['drole_id'];
                $sql = "SELECT drole_id FROM registry_drole_contacts where contact_id = '" . $permissionBlock['contact_id'] . "' and drole_id = '" . $dynamicRoleID . "'";
                //echo $sql;
                $presentDroleProvider = \Yii::$app->db->createCommand($sql)->queryAll();
                if (!$presentDroleProvider || count($presentDroleProvider) < 1) {
                    return APIHandler::getErrorArray();
                }
            }
            return $dynamicRoleID;
        } catch (Exception $ex) {
            return APIHandler::getErrorArray();
        }
    }

    private static function checkConstantRolesForContact($contactID)
    {
        $sql = "SELECT * FROM registry_drole_base where id in (SELECT drole_id FROM registry_drole_contacts where contact_id = '$contactID') and role_id = ANY('" . RegistryDescriptionRolesModel::getForQueryArray() . "')";
        $presentDroleProvider = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$presentDroleProvider || count($presentDroleProvider) < 1) {
            return false;
        }
        foreach (RegistryDescriptionRolesModel::$rolesArray as $constantRole) {
            $presentDrole = self::checkIsPresentRole($presentDroleProvider, $constantRole);
            if (!$presentDrole) {
                //continue search
            } else {
                return $presentDrole;
            }
        }
        return false;
    }

    private static function checkIsPresentRole($arrayDrolesForContact, $constantRole)
    {
        //echo  $constantDrole;
        //echo print_r($arrayDrolesForContact, true); exit;
        foreach ($arrayDrolesForContact as $record) {
            if ($record['role_id'] == $constantRole) {
                return $record;
            }
        }
        return false;
    }

    private static function checkRolesForContactCompanyService($contactID, $companyID, $serviceID, $roleID = NULL)
    {
        if (!$companyID || $companyID == '' || $companyID == NULL || !$roleID || $roleID == '' || $roleID == NULL) {

            if ($serviceID) {
                $sql = "SELECT * FROM registry_drole_base where service_id = '$serviceID' and company_id is NULL and role_id = '" . RegistryDescriptionRolesModel::$rolesArray['anonimous'] . "'";
                $presentDroleProvider = \Yii::$app->db->createCommand($sql)->queryAll();
                if (!$presentDroleProvider || count($presentDroleProvider) < 1) {
                    return false;
                }
                return $presentDroleProvider[0]['id'];
            }
            return self::getDefaultDynamicRoleForContact($contactID);
        } else {
            $sql = "SELECT * FROM registry_drole_base where service_id = '$serviceID' and company_id = '$companyID' and role_id = '$roleID'";
            $presentDroleProvider = \Yii::$app->db->createCommand($sql)->queryAll();
            if (!$presentDroleProvider || count($presentDroleProvider) < 1) {
                return false;
            }
            return $presentDroleProvider[0]['id'];
        }
        return false;
    }

    private static function getDefaultDynamicRoleForContact($contactID)
    {
        $sql = "SELECT * FROM registry_drole_base where id in (SELECT drole_id FROM registry_drole_contacts where contact_id = '$contactID')";
        $presentDroleProvider = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$presentDroleProvider) {
            return false;
        }
        return $presentDroleProvider[0]['id'];
    }

    public static function getDefaultDynamicRoleForService($inputArray, $serviceID)
    {

    }

    public static function getDynamicRoleWithParams($contactID, $serviceID = NULL, $companyID = NULL, $roleID = NULL, $droleID = NULL)
    {
        //echo print_r($inputArray);exit;
        try {
            $dynamicRoleID = false;
            if (!isset($contactID)) {
                return APIHandler::getErrorArray();
            }
            if (!isset($droleID)) {
                //not found drole_id
                if (!isset($roleID)) {
                    $staticRole = self::checkConstantRolesForContact($contactID);
                    if (!$staticRole) {
                        //if (!isset($serviceID)) {
                        //    return false;
                        //}
                        $dynamicRoleID = self::checkRolesForContactCompanyService($contactID, $companyID, $serviceID);
                        //return APIHandler::getErrorArray();
                    } else {
                        $roleID = $staticRole['role_id'];
                        $dynamicRoleID = $staticRole['id'];
                    }
                }
            } else {
                //found drole_id
                $dynamicRoleID = $droleID;
                $query = "SELECT drole_id FROM registry_drole_contacts where contact_id = '" . $contactID . "' and drole_id = '" . $dynamicRoleID . "'";
                $presentDroleProvider = new SqlDataProvider([
                    'sql' => $query
                ]);
                if (!$presentDroleProvider->getModels()) {
                    return APIHandler::getErrorArray();
                }
            }
            return $dynamicRoleID;
        } catch (Exception $ex) {
            return APIHandler::getErrorArray();
        }
    }

    public static function getArrayOfDynamicRole($droleID)
    {
        if (!$droleID || !UUIDGenerator::isUUID($droleID)) {
            return false;
        }
        $sql = "SELECT * FROM registry_drole_base where id = '$droleID'";
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function isAccessToRegistryByDrole($droleID)
    {
        $query = "SELECT * FROM registry_drole_base where id = '$droleID' and role_id = ANY('" . RegistryDescriptionRolesModel::getRolesForRegistry() . "')";
        $presentDroleProvider = new SqlDataProvider([
            'sql' => $query
        ]);
        if (!$presentDroleProvider->getModels()) {
            return false;
        }
        return true;
    }

    public static function isAccessToRegistryByRole($roleID)
    {
        if ($roleID == RegistryDescriptionRolesModel::$rolesArray['superadmin'] || $roleID == RegistryDescriptionRolesModel::$rolesArray['superuserglobal'])
            return true;
        else
            return false;
    }

    public static function isAccessDeleteForDrole($droleID)
    {

    }

    public static function getDroleForAssembly($objectID, $assemblyID)
    {
        $sql = "SELECT * FROM registry_drole_assembly WHERE object_id = '$objectID' AND assembly_id = '$assemblyID' AND active = '1'";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        return $objectsArray;
    }

    public static function getAllActiveDrolesForAssembly($objectID, $assemblyID)
    {
        $sql = "SELECT * FROM registry_drole_assembly WHERE object_id = '$objectID' AND assembly_id = '$assemblyID' AND active = '1'";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getAssemblyForDrole($objectID, $droleID)
    {
        $sql = "SELECT * FROM registry_drole_assembly WHERE object_id = '$objectID' AND drole_id = '$droleID' AND active = '1'";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        return $objectsArray;
    }

    public static function checkDroleForContact($droleID, $contactID)
    {
        $sql = "SELECT id FROM registry_drole_contacts WHERE drole_id = '$droleID' AND contact_id = '$contactID'";
        $provider = new SqlDataProvider([
            'sql' => $sql
        ]);
        if (!$provider) {
            return false;
        }
        return count($provider->getModels()) > 0;
    }

    public static function getAnonymousDynamicArray($companyID, $serviceID)
    {
        $sql = "SELECT * FROM registry_drole_base WHERE company_id = '$companyID' AND service_id = '$serviceID' AND role_id = '69ebe402-022a-4fb1-9472-f16c4b768c26'";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        return $objectsArray;
    }

}
