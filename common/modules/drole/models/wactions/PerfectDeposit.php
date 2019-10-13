<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 9/26/2018
 * Time: 5:45 PM
 */

namespace common\modules\drole\models\wactions;


use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\gate\WithdrawalFundsHandler;
use common\modules\drole\models\UUIDGenerator;
use common\modules\drole\models\webtools\JSONRegistryFactory;

class PerfectDeposit
{

    public static function updateRequest()
    {
        $altHash = strtoupper(md5('4K2461GflddLfbtXWZBJQnGXX'));
        define('ALTERNATE_PHRASE_HASH', $altHash);

        $string = \Yii::$app->request->post('PAYMENT_ID', '') . ':' . \Yii::$app->request->post('PAYEE_ACCOUNT', '') . ':' .
            \Yii::$app->request->post('PAYMENT_AMOUNT', '') . ':' . \Yii::$app->request->post('PAYMENT_UNITS', '') . ':' .
            \Yii::$app->request->post('PAYMENT_BATCH_NUM', '') . ':' .
            \Yii::$app->request->post('PAYER_ACCOUNT', '') . ':' . ALTERNATE_PHRASE_HASH . ':' .
            \Yii::$app->request->post('TIMESTAMPGMT', '');

        $hash = strtoupper(md5($string));
        if ($hash == \Yii::$app->request->post('V2_HASH', '')) {
            self::setPerfectMoney(\Yii::$app->request->post('PAYMENT_BATCH_NUM', ''),
                'af09ea17-d47c-452d-93de-2c89157b9d5b', 'b56b99b6-2c6f-4103-849a-e914e8594869',
                '00000000-430d-4a57-a7ec-ff125372ae09', \Yii::$app->request->post('PAYMENT_AMOUNT', ''),
                \Yii::$app->request->post('PAYER_ACCOUNT', ''));

            //sendMailToUserFromAdmin("adm.matrix.coin@gmail.com", $siteName . '. Receiving a payment.', "Hello, on your account was credited with money: " . $delta . " " . $_POST['PAYMENT_UNITS']);
            \Yii::$app->response->redirect(\yii\helpers\Url::to('/profile/default/perfdone?result=success'))->send();
            return true;
            //echo "success request with params: " . json_encode(\Yii::$app->request->post());
        } else {
            \Yii::$app->response->redirect(\yii\helpers\Url::to('/profile/default/perfdone?result=wrong'))->send();
            return true;
            //echo "bad request with params: " . json_encode(\Yii::$app->request->post());
        }
        exit;
    }

    public static function setPerfectMoney($transactionID, $companyID, $serviceID, $currencyID, $receiveAmount, $fromWallet)
    {
        if (!is_numeric($receiveAmount)) {
            return APIHandler::getErrorArray(403, "Wrong amount.");
        }

        $contactID = \Yii::$app->user->getId();
        $objectID = '4bfe0dd7-9e54-4de5-b9fa-3e4882bcd82d';
        $walletStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($objectID, \Yii::$app->user->getIdentity()->auth['drole']);
        $json = JSONRegistryFactory::getRecordsListFromObject(false, $objectID);

        $json['filters'][1] = json_decode('{"special":[{"map":"' . self::getIndexFromArray(json_decode($walletStructure, true), '5b296714-e069-457e-b606-3a40bea5b2f2') .
            '","comp":"6","value":"' . $currencyID . '"},{"map":"' . self::getIndexFromArray(json_decode($walletStructure, true), '8bfee8c9-c297-4124-a43b-909748e243a6') .
            '","comp":"6","value":"' . $contactID . '"}]}', true);
        $resultValues = \common\modules\drole\models\gate\DataObjectAPIHandler::parseQuery($json);

        $resultValues = $resultValues['data']['data'][0];
        $walletID = 'not found';
        $walletBalance = 0;
        if ($resultValues != null) {
            $walletID = $resultValues[self::getIndexFromArray(json_decode($walletStructure, true), '1db747bd-0828-4572-b6e5-33be5bc031e2')];
            $walletBalance = $resultValues[self::getIndexFromArray(json_decode($walletStructure, true), 'd8a9e95a-2fc4-474a-8cd7-6f2f3a7d54e5')];
        } else {
            $newID = UUIDGenerator::v4();
            $newDate = microtime(true);
            $insertWalletSQL = "insert into walletcoin_data_use (id, date_create, date_change, currencyid, balance, ownerid, wallet) 
values ('$newID', '$newDate', '$newDate', '$currencyID', '0', '$contactID', '')";
            \Yii::$app->db->createCommand($insertWalletSQL)->execute();
            $insertOwnerSQL = "insert into walletcoin_record_own (id, company_id, service_id, contact_id) 
values ('$newID', '$companyID', '$serviceID', '$contactID')";
            \Yii::$app->db->createCommand($insertOwnerSQL)->execute();
        }

        $summa = str_replace('/', '.', $receiveAmount);
        $summa = preg_replace('~\D+\.\,~', '', $summa);
        $summa = str_replace(',', '.', abs($summa));

        $sql = "select * from financetransactions_data_use where txid = '$transactionID'";
        $txArray = \Yii::$app->db->createCommand($sql)->queryOne();
        //echo "sql insert: " . $sql;
        //echo "[ $objectsArray ]";
        if (!$txArray || count($txArray) < 1) {
            WithdrawalFundsHandler::updateArithmeticPrecisionCurrency($companyID, $serviceID,
                $contactID, $currencyID, $walletBalance, $summa);
            WithdrawalFundsHandler::setTransaction($companyID, $serviceID, $contactID,
                $currencyID, $fromWallet, $walletID, $summa, $transactionID, 0, 0, 200);
        } else {
            return APIHandler::getErrorArray(403, "Transaction present yet.");
        }
    }

    private static function getIndexFromArray($currentStructure, $fieldID)
    {
        for ($i = 0; $i < count($currentStructure); $i++) {
            if ($currentStructure[$i]['id'] == $fieldID) {
                return $i;
            }
        }
        return null;
    }

}