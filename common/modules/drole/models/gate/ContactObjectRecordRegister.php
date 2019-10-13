<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 8/1/2018
 * Time: 1:06 PM
 */

namespace common\modules\drole\models\gate;


class ContactObjectRecordRegister
{
    public static function checkPresentRecord($recordID)
    {
        $contactObjectName = RegistryObjects::getObjectNameByID('7052a1e5-8d00-43fd-8f57-f2e4de0c8b24')->name;
        $sql = "select * from " . $contactObjectName . "_records_own where id = '$recordID'";
        $recordArray = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$recordArray || count($recordArray) < 1) {
            return false;
        } else {
            return true;
        }
    }

    public static function checkIfUpdateAvailable($jsonRequestArray)
    {
        if (isset($jsonRequestArray['permission']) && isset($jsonRequestArray['permission']['object_id']) && $jsonRequestArray['permission']['object_id'] == '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24') {

        } else {
            return false;
        }
    }
}