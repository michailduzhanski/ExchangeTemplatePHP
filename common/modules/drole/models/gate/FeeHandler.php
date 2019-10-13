<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 9/7/2018
 * Time: 12:25 PM
 */

namespace common\modules\drole\models\gate;


class FeeHandler
{
    public static function getFeeByClearAmount($currencyID, $amount, $coinSettings)
    {
        if (!is_numeric($amount)) {
            return APIHandler::getErrorArray(403, "Wrong amount.");
        }
        $summa = str_replace('/', '.', $amount);
        $summa = preg_replace('~\D+\.\,~', '', $summa);
        $summa = str_replace(',', '.', abs($summa));
        if ($currencyID == null || $amount == null || $coinSettings == null) {
            return APIHandler::getErrorArray(403, "Not found params.");
        }
        //$coinSettings = self::getCoinGateTrunkSettings($currencyID, $dynamicRoleArray);
        /*if (isset($coinSettings['code']) && $coinSettings['code'] != 200) {
            return $coinSettings;
        }*/
        if ($coinSettings['typefee'] == 0) {
            //0 - absolute fee
            //1 - percent fee

            if ($summa <= ($coinSettings['minfee'] * 2)) {
                return APIHandler::getErrorArray(403, "Not enough fee.");
            }
            return $coinSettings['fee'];
        } else {
            $complexFee = self::ceil_dec(($coinSettings['fee'] * $summa), 8, $separator = '.');
            if ($complexFee < $coinSettings['minfee']) {
                return APIHandler::getErrorArray(403, "Not enough fee.");
            }
            return $complexFee;
        }
    }

    private static function ceil_dec($numberUse, $precision, $separator = '.')
    {
        $numberpart = explode($separator, $numberUse);
        if (count($numberpart) < 2) {
            return $numberUse;
        }
        $numberpart[1] = substr_replace($numberpart[1], $separator, $precision, 0);
        $ceil_number = array($numberpart[0], $numberpart[1]);
        $number = implode($separator, $ceil_number);
        $koef = 1;
        if ($number < 0) {
            $koef = -1;
        }
        $mult = pow(10, $precision);
        return $koef * ceil(abs($number) * $mult) / $mult;
    }

    public static function getCoinGateTrunkSettings($currencyID, $dynamicRoleArray)
    {
        $sql = "select remotewallets_data_use.* from remotewallets_data_use join remotewallets_record_own on 
remotewallets_data_use.id = remotewallets_record_own.id where remotewallets_data_use.currencyid = '$currencyID' 
and remotewallets_record_own.company_id = '" .
            $dynamicRoleArray['company_id'] . "' and remotewallets_record_own.service_id = '" . $dynamicRoleArray['service_id'] .
            "' and remotewallets_record_own.contact_id = '" . \Yii::$app->user->getIdentity()->auth['uid'] . "'";
        $trunkResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$trunkResult || count($trunkResult) < 1) {
            return APIHandler::getErrorArray(403, "Not found settings.");
        }
        return $trunkResult;
    }
}