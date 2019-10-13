<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 9/10/2018
 * Time: 12:03 PM
 */

namespace common\modules\drole\models\wactions;


use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\UUIDGenerator;

class CreateAllWallets
{
    public static function createWalletsForContact($companyID, $serviceID, $contactID, $reWright = false)
    {
        //for alfa testing
        self::createAllAccounts($companyID, $serviceID, $contactID);
        //end
        $allTrunks = self::getCoinGateTrunkSettings($companyID, $serviceID);
        foreach ($allTrunks as $allTrunk) {
            $resultArray = self::getNewWallet($allTrunk, $companyID, $serviceID, $contactID, $reWright);
            if ($resultArray['result'] == 200) {
                //start update history
                //echo json_encode($resultArray['message']); exit;
                self::updateWallet($resultArray['message'][0], $allTrunk, $contactID);
            }
        }
        return APIHandler::getErrorArray(200, "Action ended.");
    }

    public static function createAllAccounts($companyID, $serviceID, $contactID)
    {
        $sql = "select coin_data_use.* from coin_data_use join coin_record_own on coin_data_use.id = coin_record_own.id where coin_record_own.company_id = 
'$companyID' and coin_record_own.service_id = '$serviceID' and coin_data_use.status = '200' order by date_create";
        $coinResult = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($coinResult as $coinRecord) {

            $sql = "select walletcoin_data_use.id, walletcoin_data_use.wallet from walletcoin_data_use join walletcoin_record_own on walletcoin_record_own.id = walletcoin_data_use.id 
where walletcoin_data_use.ownerid = '$contactID' and walletcoin_data_use.currencyid = '" . $coinRecord['id'] . "' 
and walletcoin_record_own.company_id = '" . $companyID . "' and walletcoin_record_own.service_id = '" . $serviceID . "'";
            $walletResult = \Yii::$app->db->createCommand($sql)->queryOne();
            if (!$walletResult || count($walletResult) < 1) {
                //insert new wallet
                $newID = UUIDGenerator::v4();
                $newDate = microtime(true);
                $insertWalletSQL = "insert into walletcoin_data_use (id, date_create, date_change, currencyid, balance, ownerid, wallet) 
values ('$newID', '$newDate', '$newDate', '" . $coinRecord['id'] . "', '0', '$contactID', '')";
                \Yii::$app->db->createCommand($insertWalletSQL)->execute();
                $insertOwnerSQL = "insert into walletcoin_record_own (id, company_id, service_id, contact_id) 
values ('$newID', '" . $companyID . "', '" . $serviceID . "', '$contactID')";
                \Yii::$app->db->createCommand($insertOwnerSQL)->execute();
            }
        }

    }

    private static function getCoinGateTrunkSettings($companyID, $serviceID)
    {
        $sql = "select remotewallets_data_use.*, remotewallets_record_own.company_id, remotewallets_record_own.service_id from remotewallets_data_use 
join remotewallets_record_own on remotewallets_data_use.id = remotewallets_record_own.id where remotewallets_data_use.currencyid in 
(select coin_data_use.id from coin_data_use join coin_record_own on coin_data_use.id = coin_record_own.id where 
coin_record_own.company_id = '$companyID' and coin_record_own.service_id = '$serviceID' and coin_data_use.status = '200')";
        //echo $sql; exit;
        $trunkResult = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$trunkResult || count($trunkResult) < 1) {
            return APIHandler::getErrorArray(403, "Not found settings.");
        }
        return $trunkResult;
    }

    private static function getNewWallet($remoteWalletSettings, $companyID, $serviceID, $contactID, $reWright)
    {
        if (!$reWright) {
            //check present wallets
            $sql = "select * from walletcoin_data_use join walletcoin_record_own on walletcoin_data_use.id = walletcoin_record_own.id where
 walletcoin_record_own.company_id = '$companyID' and walletcoin_record_own.service_id = '$serviceID' and walletcoin_record_own.contact_id = '$contactID' 
 and walletcoin_data_use.currencyid = '" . $remoteWalletSettings['currencyid'] . "'";
            $walletResult = \Yii::$app->db->createCommand($sql)->queryOne();
            if ($walletResult && count($walletResult) > 1) {
                return APIHandler::getErrorArray(403, "Wallet present yet.");
            }
        }
        $portToken = "";
        if ($remoteWalletSettings['port'] != null && $remoteWalletSettings['port'] != "") {
            $portToken = ":" . $remoteWalletSettings['port'];
        }
        $tokenTime = (microtime(true) * 1000);
        //echo "[ value : " . $remoteWalletSettings['serverlogin'] . ", $tokenTime, " . $remoteWalletSettings['serverpass'] . "]";

        $signature = base64_encode(hash_hmac('sha512', $remoteWalletSettings['serverlogin'] . $tokenTime, $remoteWalletSettings['serverpass'], true));
        /*$jsonParams = ('login=' . $remoteWalletSettings['serverlogin'] . '&command=getsuccessfull&coinid=' . $remoteWalletSettings['currencyid'] . '&token=' . self::myUrlEncode($signature) .
            '&ltime=' . $remoteWalletSettings['date_change'] . '');*/
        $args = [
            'login' => $remoteWalletSettings['serverlogin'],
            'command' => 'getnewaddress',
            'coinid' => $remoteWalletSettings['currencyid'],
            'token' => self::myUrlEncode($signature)
        ];
        //echo "[ token : " . json_encode($args) . "]";
        $request = curl_init($remoteWalletSettings['address'] . $portToken . "?t=" . $tokenTime);
        //curl_setopt($request, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $args);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($request);
        //echo $result;
        curl_close($request);
        return (json_decode($result, true));
    }

    private static function myUrlEncode($string)
    {
        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($replacements, $entities, urlencode($string));
    }

    private static function updateWallet($transactionsArray, $remoteWalletSettings, $contactID)
    {
        $lastSuccessfullUpdate = null;
        $sql = "select walletcoin_data_use.id, walletcoin_data_use.wallet from walletcoin_data_use join walletcoin_record_own on walletcoin_record_own.id = walletcoin_data_use.id 
where walletcoin_data_use.ownerid = '$contactID' and walletcoin_data_use.currencyid = '" . $remoteWalletSettings['currencyid'] . "' 
and walletcoin_record_own.company_id = '" . $remoteWalletSettings['company_id'] . "' and walletcoin_record_own.service_id = '" . $remoteWalletSettings['service_id'] . "'";
        $walletResult = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$walletResult || count($walletResult) < 1) {
            //insert new wallet
            $newID = UUIDGenerator::v4();
            $newDate = microtime(true);
            $insertWalletSQL = "insert into walletcoin_data_use (id, date_create, date_change, currencyid, balance, ownerid, wallet) 
values ('$newID', '$newDate', '$newDate', '" . $remoteWalletSettings['currencyid'] . "', '0', '$contactID', '$transactionsArray')";
            \Yii::$app->db->createCommand($insertWalletSQL)->execute();
            $insertOwnerSQL = "insert into walletcoin_record_own (id, company_id, service_id, contact_id) 
values ('$newID', '" . $remoteWalletSettings['company_id'] . "', '" . $remoteWalletSettings['service_id'] . "', '$contactID')";
            \Yii::$app->db->createCommand($insertOwnerSQL)->execute();
        } else if ($walletResult['wallet'] == null) {
            //update wallet
            $updateWalletSQL = "update walletcoin_data_use set wallet = '$transactionsArray' where 
id = '" . $walletResult['id'] . "'";
            \Yii::$app->db->createCommand($updateWalletSQL)->execute();
        }
    }
}