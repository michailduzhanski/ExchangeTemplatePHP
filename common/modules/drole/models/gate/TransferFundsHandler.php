<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 9/17/2018
 * Time: 1:49 PM
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\UUIDGenerator;

class TransferFundsHandler
{
    private static $operationsPrecision = 9;

    public static function transferFunds($currencyID, $sendAmount, $toUserHash)
    {
        if (!is_numeric($sendAmount)) {
            return APIHandler::getErrorArray(403, "Wrong amount.");
        }
        $summa = str_replace('/', '.', $sendAmount);
        $summa = preg_replace('~\D+\.\,~', '', $summa);
        $summa = str_replace(',', '.', abs($summa));

        $dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
        //get really funds
        $sql = "select walletcoin_data_use.* from walletcoin_data_use join walletcoin_record_own on 
walletcoin_data_use.id = walletcoin_record_own.id where walletcoin_data_use.currencyid = '$currencyID' and walletcoin_record_own.company_id = '" .
            $dynamicRoleArray['company_id'] . "' and walletcoin_record_own.service_id = '" . $dynamicRoleArray['service_id'] .
            "' and walletcoin_record_own.contact_id = '" . \Yii::$app->user->getIdentity()->auth['uid'] . "'";
        $walletResult = \Yii::$app->db->createCommand($sql)->queryOne();

        if (!$walletResult || count($walletResult) < 1) {
            return APIHandler::getErrorArray(403, "Not found wallet.");
        }
        if ($summa > $walletResult['balance']) {
            return APIHandler::getErrorArray(403, "Coin not enough.");
        }
        $remoteWalletSettings = self::getCoinGateTrunkSettings($currencyID, $dynamicRoleArray);


        if (is_array($remoteWalletSettings) && isset($remoteWalletSettings['result'])) {
            //for testing:
            $remoteWalletSettings = ['valueunauthorised' => 100000000, 'valuauthorised' => 100000000000];
            //return $remoteWalletSettings;
        }
        $selfLogin = self::getSelfLogin();
        if (is_array($selfLogin) && isset($selfLogin['code'])) {
            return $selfLogin;
        }
        /*$feeValue = FeeHandler::getFeeByClearAmount($currencyID, $summa, $remoteWalletSettings);
        if (!$feeValue || is_array($feeValue)) {
            return $feeValue;
        }*/
        $ethalonMarketLimit = self::getMarketValuesForUSDLimit($dynamicRoleArray['company_id'], $dynamicRoleArray['service_id'], $currencyID);
        if ($ethalonMarketLimit == null || (($ethalonMarketLimit['ask'] == null || $ethalonMarketLimit['ask'] == 0) && ($ethalonMarketLimit['high24h'] == null || $ethalonMarketLimit['high24h'] == 0))) {
            return APIHandler::getErrorArray(403, "Ethalon market is not present.");
        }
        $veryf = self::isAuthorisedCustomer($dynamicRoleArray['company_id'], $dynamicRoleArray['service_id'], \Yii::$app->user->getIdentity()->auth['uid']);
        $lastSumm = self::getLastDayTransactions($dynamicRoleArray['company_id'], $dynamicRoleArray['service_id'], \Yii::$app->user->getIdentity()->auth['uid'], $currencyID);
        $koef = $ethalonMarketLimit['ask'];
        if ($koef == null || $koef == 0) {
            $koef = $ethalonMarketLimit['high24h'];
        }
        $currentLimit = $remoteWalletSettings['valueunauthorised'];
        if ($veryf == 200) {
            $currentLimit = $remoteWalletSettings['valueauthorised'];
        }
        if ($summa > (($currentLimit / $koef) - $lastSumm)) {
            return APIHandler::getErrorArray(403, "Limit is reached.");
        }
        $resultArray = self::sendFundsAction($dynamicRoleArray, $currencyID, $summa, $toUserHash, $walletResult['wallet']);
        //echo json_encode($resultArray);
        //echo print_r($resultArray['message'][0]); exit;
        if ($resultArray['result'] == 200) {
            self::updateArithmeticPrecisionCurrency($dynamicRoleArray['company_id'], $dynamicRoleArray['service_id'],
                \Yii::$app->user->getIdentity()->auth['uid'], $currencyID, $walletResult['balance'], (-1 * ($summa)));
            self::updateArithmeticPrecisionCurrency($dynamicRoleArray['company_id'], $dynamicRoleArray['service_id'],
                $resultArray['message'][0]['ownerid'], $currencyID, $resultArray['message'][0]['balance'], $summa);
            self::setTransaction($dynamicRoleArray['company_id'], $dynamicRoleArray['service_id'], \Yii::$app->user->getIdentity()->auth['uid'],
                $currencyID, $selfLogin, $resultArray['message'][0]['contact_name'],
                (-1 * ($summa)), '', 0);
            self::setTransaction($dynamicRoleArray['company_id'], $dynamicRoleArray['service_id'], $resultArray['message'][0]['ownerid'],
                $currencyID, $selfLogin, $resultArray['message'][0]['contact_name'],
                $summa, '', 0);
            $resultArray = APIHandler::getErrorArray(200, "Successfull transfer.");
        }
        return $resultArray;
    }

    private static function getCoinGateTrunkSettings($currencyID, $dynamicRoleArray)
    {
        $sql = "select remotewallets_data_use.* from remotewallets_data_use join remotewallets_record_own on 
remotewallets_data_use.id = remotewallets_record_own.id where remotewallets_data_use.currencyid = '$currencyID' and remotewallets_record_own.company_id = '" .
            $dynamicRoleArray['company_id'] . "' and remotewallets_record_own.service_id = '" . $dynamicRoleArray['service_id'] .
            "' and remotewallets_record_own.contact_id = '" . \Yii::$app->user->getIdentity()->auth['uid'] . "'";
        $trunkResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$trunkResult || count($trunkResult) < 1) {
            return APIHandler::getErrorArray(403, "Not found settings.");
        }
        return $trunkResult;
    }

    private static function getSelfLogin()
    {
        $sql = "select contact_data_use.login from contact_data_use where id = '" . \Yii::$app->user->getIdentity()->auth['uid'] . "'";
        $NameRecord = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$NameRecord || count($NameRecord) < 1) {
            return APIHandler::getErrorArray(404, "Not found user.");
        }
        return $NameRecord['login'];
    }

    public static function getMarketValuesForUSDLimit($companyID, $serviceID, $currencyID)
    {
        $sql = "select coinmarkets_data_use.* from coinmarkets_data_use join coinmarkets_record_own on coinmarkets_data_use.id = coinmarkets_record_own.id where 
coinmarkets_record_own.company_id = '$companyID' and coinmarkets_record_own.service_id = '$serviceID' and 
coinmarkets_data_use.basecurrencyid = '00000000-430d-4a57-a7ec-ff125372ae09' and coinmarkets_data_use.currentcurrencyid = '$currencyID'";
        $marketResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$marketResult || count($marketResult) < 1) {
            return null;
        }
        return $marketResult;
    }

    public static function isAuthorisedCustomer($companyID, $serviceID, $contactID)
    {
        $sql = "select istruth from companiescontact_data_use join companiescontact_record_own on 
companiescontact_data_use.id = companiescontact_record_own.id where companiescontact_record_own.company_id = '$companyID' and 
companiescontact_record_own.service_id = '$serviceID' and companiescontact_record_own.contact_id = '$contactID'";
        $veryfResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$veryfResult || count($veryfResult) < 1) {
            return 100;
        }
        return $veryfResult['istruth'];
    }

    public static function getLastDayTransactions($companyID, $serviceID, $contactID, $currencyID)
    {
        $sql = "select sum(abs(amount)) as summm from financetransactions_data_use join financetransactions_record_own on 
financetransactions_data_use.id = financetransactions_record_own.id where financetransactions_record_own.company_id = '$companyID' and 
financetransactions_record_own.service_id = '$serviceID' and financetransactions_record_own.contact_id = '$contactID' and 
financetransactions_data_use.currencyid = '$currencyID' and financetransactions_data_use.date_create >= '" . microtime(true) . "' and (type = 1 or type = 2)";
        $sumResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$sumResult || count($sumResult) < 1) {
            return 0;
        }
        return $sumResult['summm'];
    }

    private static function sendFundsAction($dynamicRoleArray, $currencyID, $amount, $toUserHash, $fromAddr)
    {
        $sql = "select walletcoin_data_use.*, (select contact_data_use.login from contact_data_use where contact_data_use.id = walletcoin_data_use.ownerid) as contact_name from walletcoin_data_use join walletcoin_record_own on 
walletcoin_data_use.id = walletcoin_record_own.id where walletcoin_data_use.currencyid = '$currencyID' and walletcoin_record_own.company_id = '" .
            $dynamicRoleArray['company_id'] . "' and walletcoin_record_own.service_id = '" . $dynamicRoleArray['service_id'] .
            "' and walletcoin_record_own.contact_id = (select companiescontact_record_own.contact_id from companiescontact_record_own where md5(companiescontact_record_own.id::varchar) = '" .
            $toUserHash . "')";
        $walletResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$walletResult || count($walletResult) < 1) {
            return APIHandler::getErrorArray(404, "Not found user.");
        }
        $resultArray = ['ownerid' => $walletResult['ownerid'], 'wallet' => $walletResult['wallet'], 'balance' => $walletResult['balance'], 'contact_name' => $walletResult['contact_name']];
        return APIHandler::getErrorArray(200, $resultArray);
    }

    private static function updateArithmeticPrecisionCurrency($companyID, $serviceID, $contactID, $currencyID, $oldValue, $addValue, $up = 1)
    {
        if ($addValue == 0 || ($oldValue == null && $addValue < 0)) {
            return;
        }
        if ($up == 1) {
            $addValue = self::ceil_dec($addValue, self::$operationsPrecision);
        } else {
            $addValue = self::floor_dec($addValue, self::$operationsPrecision);
        }
        //$oldValue = getMetaKeyForUser($UID, $meta_key);
        if ($addValue < 0 && $oldValue < $addValue) {
            $addValue = $oldValue * -1;
        }
        $newValue = $oldValue + $addValue;
        self::setNewValueForCurrency($companyID, $serviceID, $contactID, $currencyID, $newValue);
        //self::addNotes($companyID, $serviceID, $contactID, $currencyID, $oldValue, $newValue, 1, $notes);
        return $addValue;
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

    private static function floor_dec($numberUse, $precision = 9, $separator = '.')
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
        return $koef * floor(abs($number) * $mult) / $mult;
    }

    private static function setNewValueForCurrency($companyID, $serviceID, $contactID, $currencyID, $newValue)
    {
        $checkSQL = "select walletcoin_data_use.balance from walletcoin_data_use join walletcoin_record_own on 
walletcoin_data_use.id = walletcoin_record_own.id where walletcoin_data_use.currencyid = '$currencyID' and 
walletcoin_record_own.company_id = '$companyID' and walletcoin_record_own.service_id = '$serviceID' and walletcoin_record_own.contact_id = '$contactID'";
        $balanceRecord = \Yii::$app->db->createCommand($checkSQL)->queryOne();
        if (!$balanceRecord || count($balanceRecord) < 1) {
            $id = \common\models\UUIDGenerator::v4();
            $sql = "insert into walletcoin_data_use (id, currencyid, balance, ownerid) values ('" .
                $id . "', '$currencyID', '$newValue', '$contactID')";
            \Yii::$app->db->createCommand($sql)->execute();
            $insertOwnerID = "insert into walletcoin_record_own (id, company_id, service_id, contact_id) values ('$id', '$companyID', '$serviceID', '$contactID')";
            \Yii::$app->db->createCommand($insertOwnerID)->execute();
        } else {

            $sql = "update walletcoin_data_use set date_change = '" . microtime(true) . "', balance = '" . number_format($newValue, self::$operationsPrecision, '.', '') .
                "' where walletcoin_data_use.currencyid = '$currencyID' and id in 
(select id from walletcoin_record_own where company_id = '$companyID' and service_id = '$serviceID' and contact_id = '$contactID')";
            //echo $sql;
            \Yii::$app->db->createCommand($sql)->execute();
        }
    }

    private static function setTransaction($companyID, $serviceID, $contactID, $currencyID, $walletFrom, $walletTo,
                                           $amount, $txID, $fee)
    {
        $newRecordID = UUIDGenerator::v4();
        if ($txID == '') {
            $txID = $newRecordID;
        }
        $sql = "insert into financetransactions_data_use (id, currencyid, walletfrom, walletto, txid,
amount, confirmations, type, status, fee) values ('$newRecordID', '$currencyID', '$walletFrom', '$walletTo', '$txID', '$amount', '0', '2', '200','$fee')";
        \Yii::$app->db->createCommand($sql)->execute();
        $insertOwnerID = "insert into financetransactions_record_own (id, company_id, service_id, contact_id) values ('$newRecordID', '$companyID', '$serviceID', '$contactID')";
        \Yii::$app->db->createCommand($insertOwnerID)->execute();
    }

    public static function checkUserPresentByMD5($MD5)
    {
        $checkSQL = "select contact_data_use.login from contact_data_use where contact_data_use.id = (select companiescontact_record_own.contact_id 
from companiescontact_record_own where md5(companiescontact_record_own.id::varchar) = '" . $MD5 . "')";
        $loginRecord = \Yii::$app->db->createCommand($checkSQL)->queryOne();
        if (!$loginRecord || count($loginRecord) < 1) {
            return null;
        } else {
            return $loginRecord['login'];
        }
    }

    private static function myUrlEncode($string)
    {
        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($replacements, $entities, urlencode($string));
    }
}