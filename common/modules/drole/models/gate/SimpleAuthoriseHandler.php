<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 7/15/2018
 * Time: 11:27 PM
 */

namespace common\modules\drole\models\gate;


use common\modules\drole\models\UUIDGenerator;

class SimpleAuthoriseHandler
{
    public static function simpleParseAPIForExchanges()
    {
        $serviceID = "b56b99b6-2c6f-4103-849a-e914e8594869";
        Yii::$app->response->format = Response::FORMAT_JSON;
        $jsonIncoming = Yii::$app->request->post('json', null);
        if (!$jsonIncoming || CheckIncomingWords::checkRequestString($jsonIncoming)) {
            return APIHandler::getErrorArray(500, "Bad request.", true);
        }
        $jsonIncomingObject = json_decode($jsonIncoming, true);
        if (!isset($jsonIncomingObject['permission']) || !isset($jsonIncomingObject['work']) || !isset($jsonIncomingObject['filters'])) {
            return APIHandler::getErrorArray(401, "Not found permissions.", true);
        }
        $contactID = false;
        $droleID = false;
        if (isset($jsonIncomingObject['permission']['company_id']) && UUIDGenerator::isUUID($jsonIncomingObject['permission']['company_id']) &&
            isset($jsonIncomingObject['permission']['login']) && isset($jsonIncomingObject['permission']['signature']) && isset($jsonIncomingObject['work']['ctime'])) {
            //check values
            $sql = "select apikey, apiorders, apistatistic, apiwithdrawal, companiescontact_record_own.contact_id from companiescontact_data_use join companiescontact_record_own on companiescontact_record_own.id = companiescontact_data_use.id where 
companiescontact_record_own.company_id = '" . $jsonIncomingObject['permission']['company_id'] . "' and companiescontact_record_own.service_id = '" . $jsonIncomingObject['permission']['service_id'] . "' and 
companiescontact_record_own.contact_id = (select contact_data_use.id from contact_data_use where contact_data_use.login = '" . $jsonIncomingObject['permission']['login'] . "' limit 1)";
            $presentContact = \Yii::$app->db->createCommand($sql)->queryOne();
            if (!$presentContact || count($presentContact) < 1) {
                return APIHandler::getErrorArray(401, "Not found contact.", true);
            }
            if (!isset($presentContact['apikey']) || strlen($presentContact['apikey']) < 4) {
                return APIHandler::getErrorArray(401, "Not found apikey.", true);
            }
            $signature = base64_encode(hash_hmac('sha512', $jsonIncomingObject['permission']['login'] . $jsonIncomingObject['work']['ctime'], $presentContact['apikey'], true));
            if ($signature != $jsonIncomingObject['permission']['signature']) {
                return APIHandler::getErrorArray(401, "Signature is not equals.", true);
            }
            $contactID = $presentContact['contact_id'];
            $sql = "select * from registry_drole_base where role_id = '1c3bf8ff-7235-4400-974e-d7a3b58de566' and service_id = '$serviceID' and company_id = '" .
                $jsonIncomingObject['permission']['company_id'] . "'";
            $droleArray = \Yii::$app->db->createCommand($sql)->queryOne();
            if (!$droleArray || count($droleArray) < 1) {
                return APIHandler::getErrorArray(401, "Not found active role for the company.", true);
            }
            self::checkAPIPermissions($jsonIncomingObject['permission']['company_id'], $serviceID, $contactID, $jsonIncomingObject['work']['value'][0],
                $presentContact['apiorders'], $presentContact['apistatistic'], $presentContact['apiwithdrawal']);

        } else {
            return APIHandler::getErrorArray(401, "Wrong request for api.", true);
        }
    }

    private static function checkAPIPermissions($companyID, $serviceID, $contactID, $jsonRequest, $apiorders, $apistatistic, $apiwithdrawal)
    {
        $jsonArray = json_decode($jsonRequest, true);
        if (!isset($jsonArray['type'])) {
            return false;
        }
        if (($jsonArray['type'] == 0 || $jsonArray['type'] == 1 || $jsonArray['type'] == 2) && ($apiorders == true || $apiorders == "true")) {
            self::setRequestToDB($companyID, $serviceID, $contactID, $jsonRequest);
            return true;
        }
    }

    private static function setRequestToDB($companyID, $serviceID, $contactID, $jsonRequest)
    {
        $requestID = UUIDGenerator::v4();
        $sql = "insert into requestlist_data_use (id, json_request) values ('$requestID', '$jsonRequest')";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into requestlist_record_own (id, company_id, service_id, contact_id) values ('$requestID', '$companyID', '$serviceID', '$contactID')";
        \Yii::$app->db->createCommand($sql)->execute();
    }
}