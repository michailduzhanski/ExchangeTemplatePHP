<?php

namespace common\modules\drole\models\gate;

use common\modules\drole\models\auth\ContactAuth;
use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\UUIDGenerator;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use Yii;
use yii\data\SqlDataProvider;
use yii\web\Response;

class APIHandler
{

    public static function parseQuery()
    {
        SecurityHandler::checkArrayForHack(Yii::$app->request->post());
        Yii::$app->response->format = Response::FORMAT_JSON;
        //echo Yii::$app->request->post('json'); exit;
        $jsonIncoming = Yii::$app->request->post('json', '{"query":"test"}');
        //$jsonIncomingObject = json_decode($jsonIncoming, true);
        if ($jsonIncoming == '{"query":"test"}') {
            $jsonIncomingObject = JSONRegistryFactory::getRecordsListFromObject(false, '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24', '');
            //$jsonIncomingObject['filters'][1]['special'] = json_decode('[{"map":4,"comp":7,"value":"tutu"}]', true);
            //echo json_encode($jsonIncomingObject); exit;
            //$jsonIncomingObject = json_decode($jsonIncomingObject, true);
        }

        //$jsonIncomingObject = json_decode(JSONRegistryFactory::getRecordsListFromObject(true, '97086af0-956b-4380-a385-ea823cff377a', ''), true);
        //$jsonIncomingObject = json_decode('{"permission":{"object_id":"registry","service_id":"3db2f640-e01a-42ac-904e-87a46e0373fd","login":"matas","signature":"9NMmw7t1XTazVczHVtwM1oqFuwEwZsZEvD3ddxiomOAZ4htWPLqOXoNzLXbV8fh93Ws66ek8gkYR+cWgVSXvyg=="},"work":{"set":0,"operation":0,"ctime":1521571915.567,"value":{"object":"a89c5b6f-80c0-47ca-8b30-8647a5efbfe5","table":"fgroups"}},"filters":[{"common":""}]}', true);
        //$jsonIncomingObject = json_decode('{"permission":{"object_id":"registry","service_id":"b56b99b6-2c6f-4103-849a-e914e8594869","contact_id":"7d82bde3-7740-41d7-9610-8d1fc75db803","drole_id":"62900a19-88a9-4655-a7ac-71488070b659"},"work":{"set":0,"operation":0,"ctime":"0.06665600 1523975890","value":{"table":"companies","object":"a89c5b6f-80c0-47ca-8b30-8647a5efbfe5"}},"filters":[{"common":""}]}', true);
        //$jsonIncomingObject = json_decode('{"permission":{"object_id":"registry","service_id":"3db2f640-e01a-42ac-904e-87a46e0373fd","login":"matas","signature":"tGh/p6dCk9SPkU/aiMeSl0AnFJH352h/nFwZS4gRKpKlBSipounAJBEDmoXNQwr6+l5+Pzu8gxRwdZKf68PhIQ=="},"work":{"set":0,"operation":0,"ctime":1521629777.5507,"value":{"object":"a89c5b6f-80c0-47ca-8b30-8647a5efbfe5","table":"assemblies"}},"filters":[{"common":""}]}', true);
        //$jsonIncomingObject = json_decode('{"permission":{"object_id":"registry","service_id":"3db2f640-e01a-42ac-904e-87a46e0373fd","login":"matas","signature":"9NMmw7t1XTazVczHVtwM1oqFuwEwZsZEvD3ddxiomOAZ4htWPLqOXoNzLXbV8fh93Ws66ek8gkYR+cWgVSXvyg=="},"work":{"set":0,"operation":0,"ctime":1521571915.567,"value":{"object":"a89c5b6f-80c0-47ca-8b30-8647a5efbfe5"}},"filters":[{"common":""}]}', true);
        //$jsonIncomingObject = json_decode('{"permission":{"object_id":"registry","service_id":"3db2f640-e01a-42ac-904e-87a46e0373fd","login":"matas","signature":"Hufssb+bhoOM7C9WyIpG27Mkb9DrrZN9+J7K0+TAODaQe3HUeKdWlfAVLkcoYjIhb4akDCkGTPDB7MJG/4fuGw=="},"work":{"set":0,"operation":0,"ctime":1522063122.5487,"value":{"object":"a89c5b6f-80c0-47ca-8b30-8647a5efbfe5","table":"companies"}},"filters":[{"common":""}]}', true);
        //$droleID = DynamicRoleModel::getDynamicRoleWithParams($jsonIncomingObject->permission->contact_id, $jsonIncomingObject->permission->service_id);
        //print_r($this->getDynamicRole($jsonIncomingObject)); exit;
        else $jsonIncomingObject = json_decode($jsonIncoming, true);

        try {
            //return $jsonIncomingObject->permission;
            if (!isset($jsonIncomingObject['permission']) || !isset($jsonIncomingObject['work']) || !isset($jsonIncomingObject['filters'])) {
                return self::getErrorArray();
            }

            $permissionBlock = $jsonIncomingObject['permission'];
            $workBlock = $jsonIncomingObject['work'];

            $filtersBlock = $jsonIncomingObject['filters'];
            //permission
            $objectID = $permissionBlock['object_id'];
            $contactID = false;
            $droleID = false;
            if (isset($permissionBlock['contact_id'])) {
                $contactID = $permissionBlock['contact_id'];
                $droleID = self::getDynamicRole($jsonIncomingObject);
            }
            //check contact

            //work
            $viewType = $workBlock['set'];
            $operationType = $workBlock['operation'];
            if (isset($jsonIncomingObject['permission']) && isset($jsonIncomingObject['permission']['object_id']) &&
                $jsonIncomingObject['permission']['object_id'] == '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24' &&
                $viewType == 1 && $operationType == 2) {
                //update
            } else
                $authArray = self::checkAuthorise($droleID, $contactID);
            $isAuthorised = false;
            if (!isset($authArray) || isset($authArray['result'])) {
                return APIHandler::getErrorArray(401, "user is not registered.");
                /*$contactData = false;
                if (isset($permissionBlock['login'])) {
                    $contactData = ContactData::findByUsername($permissionBlock['login']);
                    if (!$contactData) {
                        $isAuthorised = false;
                    } else {
                        $isAuthorised = self::authorisingProcess($jsonIncomingObject, $contactData['password']);
                    }
                }
                if ($isAuthorised) {
                    if (!$contactID) {
                        $contactID = $contactData['id'];
                    }
                    if (!$droleID) {
                        $droleID = DynamicRoleModel::getDynamicRoleWithParams($contactID, RegistryDescriptionRolesModel::getMustService());
                    }
                    //process insert contact to db auth
                    //ContactAuth::deleteContactAuthByID($contactID);
                    ContactAuth::insertContactAuthByID($contactID, $droleID, '0.0.0.0', 'API', 'API', microtime(true) + 3600);
                    $jsonIncomingObject = self::updateJson($jsonIncomingObject, $contactID, $droleID);
                    //echo print_r($jsonIncomingObject, true);
                } else {
                    return $authArray;
                }*/
            }
            //print_r($this->getDynamicRole($jsonIncomingObject)); exit;
            if (\Yii::$app->user->getId() != $contactID) {
                $currentDynamicRole = DynamicRoleModel::getArrayOfDynamicRole($droleID);
                if ($currentDynamicRole['role_id'] != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
                    return APIHandler::getErrorArray(401, "Contact in query is not equals.");
                }
            }
            if ($viewType == 0) {
                if ($operationType == 0) {
                    return RegistryAPIHandler::parseQuery($jsonIncomingObject);
                    //$registryApi = new RegistryAPIHandler();
                    //return $registryApi->parseQuery($jsonIncomingObject);
                }
            } else if ($viewType == 1) {
                return DataObjectAPIHandler::parseQuery($jsonIncomingObject);
                //$objectApi = new DataObjectAPIHandler();
                //return $objectApi->parseQuery();
            }
        } catch (Exception $ex) {
            return self::getErrorArray(404, 'you have error!');
        }
        return self::getErrorArray(404, 'not found query.');
    }

    public static function getErrorArray($code = 404, $message = "data not found", $encode = false)
    {
        $resultArray = array();
        $resultArray['result'] = $code;
        $resultArray['message'] = [$innerMessage['error'] = $message];
        if ($encode) {
            return json_encode($resultArray);
        }
        return $resultArray;
    }

    private static function getDynamicRole($jsonIncoming)
    {
        return DynamicRoleModel::getDynamicRole($jsonIncoming);
    }

    public static function checkAuthorise($droleID, $contactID, $hash = null)
    {
        if (!$contactID) {
            return self::getErrorArray(403, "contact is not found.");
        }
        $authArray = ContactAuth::getContactAuthByID($contactID);
        if (!$authArray) {
            return self::getErrorArray(401, "contact is not authorised.");
        }
        if ($authArray['time'] < microtime(true)) {
            return self::getErrorArray(401, "contact is not authorised. time is expired.");
        }
        if ($hash && $hash != $authArray['drole']) {
            return self::getErrorArray(401, "wrong hash in request.");
        }
        if ($authArray['drole'] != $droleID) {// . $authArray['drole'] . " . " . $droleID
            return self::getErrorArray(401, "contact is not authorised. dynamic role is not equaled. " . $authArray['drole'] . " . " . print_r($droleID, true));
        } else
            return $authArray;
    }

    public static function checkPermissionQuery($droleID, $contactID, $operationType = 1, $adminSite = true)
    {
        if (!DynamicRoleModel::checkDroleForContact($droleID, $contactID)) {
            return false;
        }
        $resultAuthorise = self::checkAuthorise($droleID, $contactID);
        if (isset($resultAuthorise['message']['error'])) {
            return false;
        }
        if ($adminSite) {
            $droleArray = DynamicRoleModel::getArrayOfDynamicRole($droleID);
            if ($droleArray && in_array($droleArray['role_id'], RegistryDescriptionRolesModel::getRolesForAdminSiteAccess($operationType))) {
                return true;
            } else
                return false;
        } else {
            return true;
        }
    }

    public static function setUserAsCustomerForHysiope($contactID)
    {
        return self::setUserAsRoleForCompanyAndService($contactID, 'af09ea17-d47c-452d-93de-2c89157b9d5b',
            'b56b99b6-2c6f-4103-849a-e914e8594869', '1c3bf8ff-7235-4400-974e-d7a3b58de566');
    }

    public static function setUserAsRoleForCompanyAndService($contactID, $companyID, $serviceID, $roleID)
    {
        $sql = "select * from registry_drole_base where company_id = '$companyID' and service_id = '$serviceID' and role_id = '$roleID'";
        $droleProvider = new SqlDataProvider([
            'sql' => $sql
        ]);
        if (($droleProvider->getModels()) && count($droleProvider->getModels()) > 0) {
            $sql = "select * from registry_drole_contacts where contact_id = '$contactID' and drole_id = '" . $droleProvider->getModels()[0]['id'] . "'";
            $contactProvider = \Yii::$app->db->createCommand($sql)->queryAll();
            if ($contactProvider && count($contactProvider) > 0) {
                return false;
            }
            $sql = "insert into registry_drole_contacts values('" . UUIDGenerator::v4() . "', '" . $droleProvider->getModels()[0]['id'] . "', '$contactID')";
            \Yii::$app->db->createCommand($sql)->execute();
        }
    }

    public static function checkServicePermissionForUpdate($ownerID, $contactID, $objectID, $roleID)
    {
        if (PrivateObjectsDataHandler::getLevelOfAccess($objectID) == 0 && $roleID != RegistryDescriptionRolesModel::$rolesArray['superadmin']) {
            return false;
        }
        if (PrivateObjectsDataHandler::getLevelOfAccess($objectID) < 2 && $roleID != RegistryDescriptionRolesModel::$rolesArray['superadmin'] &&
            $roleID != RegistryDescriptionRolesModel::$rolesArray['admin'] && $ownerID != $contactID) {
            return false;
        }
        if (PrivateObjectsDataHandler::getLevelOfAccess($objectID) == 2 && $roleID != RegistryDescriptionRolesModel::$rolesArray['superadmin'] &&
            $roleID != RegistryDescriptionRolesModel::$rolesArray['admin'] && $ownerID != $contactID) {
            return false;
        }
        return true;
    }

    public static function getSubqueryServicePermissionForGetter($objectID, $roleID)
    {
        $accessLevel = PrivateObjectsDataHandler::getLevelOfAccessGetter($objectID);
        if (($accessLevel == '2')
            && $roleID != RegistryDescriptionRolesModel::$rolesArray['superadmin'] &&
            $roleID != RegistryDescriptionRolesModel::$rolesArray['admin']) {
            return 2;
        }
        if ($accessLevel < 3) {
            if ($accessLevel == '0') {
                if ($roleID == RegistryDescriptionRolesModel::$rolesArray['superadmin'])
                    return 0;
                else if ($roleID == RegistryDescriptionRolesModel::$rolesArray['admin'])
                    return 1;
                else return 2;
            } else if ($accessLevel == '1') {
                if ($roleID == RegistryDescriptionRolesModel::$rolesArray['superadmin'])
                    return 0;
                else if ($roleID == RegistryDescriptionRolesModel::$rolesArray['admin'])
                    return 1;
                else return 2;
            } else return 3;
        }
        /*if ($accessLevel == '1') {
            if ($roleID == RegistryDescriptionRolesModel::$rolesArray['superadmin'])
                return 0;
            else if ($roleID == RegistryDescriptionRolesModel::$rolesArray['admin'])
                return 1;
            else return 2;
        }
        if (($accessLevel == '2')
            && $roleID != RegistryDescriptionRolesModel::$rolesArray['superadmin'] &&
            $roleID != RegistryDescriptionRolesModel::$rolesArray['admin']) {
            return 2;
        }
        return 3;*/

    }

    public static function updateDroleForVerified()
    {
        //$contactAuthRecord = ContactAuth::getContactAuthByID(\Yii::$app->user->getId());
        $sql = "update site_contact_auth set drole = '88286f5e-ecd7-48d6-b2d1-69bed835a8c1' where uid = '" . \Yii::$app->user->getId() . "'";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public static function getSponsorIDByLogin($login)
    {
        $sql = "select contact_data_use.id from contact_data_use where contact_data_use.login = '$login' limit 1";
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function checkSponsorID($sponsorID)
    {
        $sql = "select contact_data_use.id from contact_data_use where contact_data_use.id = '$sponsorID' limit 1";
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

    private static function authorisingProcess($jsonIncoming, $bcrypt)
    {
        if (!isset($jsonIncoming['permission']['login']) || !isset($jsonIncoming['permission']['signature']) || !isset($jsonIncoming['work']['ctime']) || !isset($jsonIncoming['permission']['company_id'])) {
            return false;
        }
        $signature = base64_encode(hash_hmac('sha512', $jsonIncoming['permission']['login'] . $jsonIncoming['work']['ctime'], $bcrypt, true));
        if ($signature == $jsonIncoming['permission']['signature']) {
            return true;
        }
        return false;
    }

    private static function updateJson($jsonObject, $contactID, $droleID)
    {
        //$stringValue = json_encode($jsonObject);
        //$arrayJson = json_decode($stringValue, true);
        $jsonObject['permission']['contact_id'] = $contactID;
        $jsonObject['permission']['drole_id'] = $droleID;
        //$stringValue = json_encode($arrayJson);
        //return json_decode($stringValue);
        return $jsonObject;
    }

    private static function authoriseOperation($jsonIncoming)
    {
        try {
            $permissionBlock = $jsonIncoming['permission'];
            $droleID = $permissionBlock['drole_id'];
            $contactID = $permissionBlock['contact_id'];
        } catch (Exception $ex) {
            return self::getErrorArray();
        }
    }

}

?>