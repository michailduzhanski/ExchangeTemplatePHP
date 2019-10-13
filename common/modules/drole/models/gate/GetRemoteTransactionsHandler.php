<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 9/6/2018
 * Time: 4:30 PM
 */

namespace common\modules\drole\models\gate;

use common\modules\drole\models\UUIDGenerator;

class GetRemoteTransactionsHandler
{
    private static $operationsPrecision = 9;

    public static function updateAllTransactions()
    {
        //$dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
        $allTrunks = self::getCoinGateTrunkSettings();
        foreach ($allTrunks as $allTrunk) {
            $resultArray = self::getLastTransactions($allTrunk);
            if ($resultArray['code'] == 200) {
                //start update history
                self::updateTransactions($resultArray['message'], $allTrunk);
            }
        }
        return APIHandler::getErrorArray(200, "Action ended.");
    }

    private static function getCoinGateTrunkSettings()
    {
        $sql = "select remotewallets_data_use.*, remotewallets_record_own.company_id, remotewallets_record_own.service_id from remotewallets_data_use 
join remotewallets_record_own on remotewallets_data_use.id = remotewallets_record_own.id where remotewallets_data_use.currencyid in 
(select coin_data_use.id from coin_data_use join coin_record_own on coin_data_use.id = coin_record_own.id where coin_data_use.status = '200')";
        $trunkResult = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$trunkResult || count($trunkResult) < 1) {
            return APIHandler::getErrorArray(403, "Not found settings.");
        }
        return $trunkResult;
    }

    private static function getLastTransactions($remoteWalletSettings)
    {
        $portToken = "";
        if ($remoteWalletSettings['port'] != null && $remoteWalletSettings['port'] != "") {
            $portToken = ":" . $remoteWalletSettings['port'];
        }
        $tokenTime = (microtime(true) * 1000);
        $signature = base64_encode(hash_hmac('sha512', $remoteWalletSettings['serverlogin'] . $tokenTime, $remoteWalletSettings['serverpass'], true));
        $jsonParams = ('login=' . $remoteWalletSettings['serverlogin'] . '&command=getsuccessfull&coinid=' . $remoteWalletSettings['currencyid'] . '&token=' . self::myUrlEncode($signature) .
            '&ltime=' . $remoteWalletSettings['date_change'] . '');


        $args = [
            'login' => $remoteWalletSettings['serverlogin'],
            'command' => 'getsuccessfull',
            'coinid' => $remoteWalletSettings['currencyid'],
            'token' => self::myUrlEncode($signature),
            'ltime' => $remoteWalletSettings['date_change']
        ];
        $request = curl_init($remoteWalletSettings['address'] . $portToken . "?t=" . $tokenTime);
        //curl_setopt($request, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $args);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($request);
        curl_close($request);
        return (json_decode($result, true));
    }

    private static function myUrlEncode($string)
    {
        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($replacements, $entities, urlencode($string));
    }

    private static function updateTransactions($transactionsArray, $remoteWalletSettings)
    {
        $lastSuccessfullUpdate = null;
        foreach ($transactionsArray as $recordTransaction) {
            $currentEndDate = self::updateTransactionRecord($recordTransaction, $remoteWalletSettings);
            $lastSuccessfullUpdate = ($currentEndDate == null ? $lastSuccessfullUpdate : $currentEndDate);
        }
        self::updateLastTimeSuccessTransaction($remoteWalletSettings['currencyid'], $lastSuccessfullUpdate);
    }

    private static function updateTransactionRecord($transactionRecord, $remoteWalletSettings)
    {
        $dateSuccessfull = null;
        //echo json_encode($transactionRecord);exit;
        $fromSubquery = '';
        $toSubquery = '';
        if ($transactionRecord['address'] != '' && $transactionRecord['address'] != 'null') {
            $fromSubquery = "walletcoin_data_use.wallet = '" . $transactionRecord['address'] . "'";
        }
        if ($transactionRecord['to'] != '' && $transactionRecord['to'] != 'null') {
            $toSubquery = "walletcoin_data_use.wallet = '" . $transactionRecord['to'] . "'";
        }
        if ($fromSubquery != '' && $toSubquery != '') {
            $toSubquery = " or " . $toSubquery;
        }
        if ($fromSubquery == '' && $toSubquery == '') {
            return null;
        }
        $sql = "select walletcoin_data_use.*, walletcoin_record_own.company_id, walletcoin_record_own.service_id from 
walletcoin_data_use join walletcoin_record_own on walletcoin_data_use.id = walletcoin_record_own.id where " . $fromSubquery . $toSubquery;
        //echo $sql;
        $trunkResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$trunkResult || count($trunkResult) < 1) {
            return;
        }
        $txType = ($transactionRecord['category'] == 'receive' ? 0 : 1);
        //$feeValue = FeeHandler::getFeeByClearAmount($remoteWalletSettings['currencyid'], $transactionRecord['amount'], $remoteWalletSettings);
        $sql = "select financetransactions_data_use.*, financetransactions_record_own.company_id, financetransactions_record_own.service_id from 
financetransactions_data_use join financetransactions_record_own on financetransactions_data_use.id = financetransactions_record_own.id where financetransactions_data_use.txid = '" .
            $transactionRecord['txid'] . "' and financetransactions_data_use.type = '$txType'";

        $txResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$txResult || count($txResult) < 1) {
            //insert new record and update if transaction is incoming
            $newID = UUIDGenerator::v4();
            $sqlRO = "insert into financetransactions_record_own values ('$newID', '" .
                $remoteWalletSettings['company_id'] . "', '" . $remoteWalletSettings['service_id'] . "', '" . $trunkResult['ownerid'] . "')";
            \Yii::$app->db->createCommand($sqlRO)->execute();
            //data_use update
            $fromWallet = $transactionRecord['to'];
            $toWallet = $transactionRecord['address'];
            //for send
            if ($txType == 1) {
                $toWallet = $transactionRecord['to'];
                $fromWallet = $transactionRecord['address'];
            }
            $feeValue = FeeHandler::getFeeByClearAmount($remoteWalletSettings['currencyid'], $transactionRecord['amount'], $remoteWalletSettings);
            $sqlDU = "insert into financetransactions_data_use (id, date_create, date_change, currencyid, walletfrom, walletto, txid, 
amount, confirmations, type, status, fee) values ('$newID', '" .
                microtime(true) . "', '" . microtime(true) . "', '" . $remoteWalletSettings['currencyid'] . "', '" .
                $fromWallet . "', '" . $toWallet . "', '" . $transactionRecord['txid'] . "', '" . $transactionRecord['amount'] . "', '" .
                $transactionRecord['confirmations'] . "', '" . $txType . "', '100', '" . $feeValue . "')";
            \Yii::$app->db->createCommand($sqlDU)->execute();
        } else {
            //update
            $status = 100;
            if ($transactionRecord['confirmations'] >= $remoteWalletSettings['successconfirm'] && $txResult['status'] < 200) {
                //update wallet account
                if ($txType == 0) {
                    self::updateArithmeticPrecisionCurrency($remoteWalletSettings['company_id'], $remoteWalletSettings['service_id'],
                        $trunkResult['ownerid'], $remoteWalletSettings['currencyid'], $trunkResult['balance'], $transactionRecord['amount'], 0);
                }
                $status = 200;
                //echo json_encode($transactionRecord);
                $dateSuccessfull = $transactionRecord['time'];
                //echo $dateSuccessfull;
            }
            $sqlDU = "update financetransactions_data_use set date_change = '" . microtime(true) . "', confirmations = '" .
                $transactionRecord['confirmations'] . "', status = '" . $status . "' where id = '" . $txResult['id'] . "'";
            //echo $sqlDU;
            \Yii::$app->db->createCommand($sqlDU)->execute();
        }
        //echo $dateSuccessfull;
        return $dateSuccessfull;
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

    private static function updateLastTimeSuccessTransaction($currencyID, $lastSuccessfullUpdate)
    {
        if ($lastSuccessfullUpdate == null) return;
        $sql = "update remotewallets_data_use set date_change = '" . $lastSuccessfullUpdate . "' where currencyid = '$currencyID'";
        \Yii::$app->db->createCommand($sql)->execute();
    }
}