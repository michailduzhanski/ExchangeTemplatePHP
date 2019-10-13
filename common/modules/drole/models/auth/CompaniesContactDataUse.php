<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 9/17/2018
 * Time: 6:07 PM
 */

namespace common\modules\drole\models\auth;

use common\modules\drole\models\registry\DynamicRoleModel;

class CompaniesContactDataUse
{

    public static function tableName()
    {
        return '{{companiescontact_data_use}}';
    }

    public static function getContactDataByID($fieldName)
    {
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
        $sql = "select companiescontact_data_use." . $fieldName . " from companiescontact_data_use join companiescontact_record_own 
        on companiescontact_record_own.id = companiescontact_data_use.id where companiescontact_record_own.contact_id = '" .
            \Yii::$app->user->getIdentity()->auth['uid'] . "' and companiescontact_record_own.company_id = '" .
            $dynamicRoleArray['company_id'] . "' and companiescontact_record_own.service_id = '" . $dynamicRoleArray['service_id'] .
            "'";
        $ccResult = \Yii::$app->db->createCommand($sql)->queryOne();

        if (!$ccResult || count($ccResult) < 1) {
            return null;
        }
        return $ccResult[$fieldName];
    }

    public static function setContactDataByID($fieldName, $newValue)
    {
        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
        $sql = "update companiescontact_data_use set " . $fieldName . " = '$newValue' where companiescontact_data_use.id in 
        (select companiescontact_record_own.id from companiescontact_record_own where companiescontact_record_own.contact_id = '" .
            \Yii::$app->user->getIdentity()->auth['uid'] . "' and companiescontact_record_own.company_id = '" .
            $dynamicRoleArray['company_id'] . "' and companiescontact_record_own.service_id = '" . $dynamicRoleArray['service_id'] . "')";
        $ccResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$ccResult || count($ccResult) < 1) {
            return null;
        }
        return $ccResult[$fieldName];
    }

    public static function getMD5ForCurrent($companyID, $serviceID)
    {
        $sql = "select md5(companiescontact_data_use.id::character varying) from companiescontact_data_use join companiescontact_record_own on 
companiescontact_data_use.id = companiescontact_record_own.id where companiescontact_record_own.company_id = '$companyID' 
and companiescontact_record_own.service_id = '$serviceID' and companiescontact_record_own.contact_id = '" . \Yii::$app->user->getIdentity()->auth['uid'] . "'";
        $md5Result = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$md5Result || count($md5Result) < 1) {
            return null;
        }
        return $md5Result['md5'];
    }
}