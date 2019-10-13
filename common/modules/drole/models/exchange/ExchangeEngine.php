<?php

/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 7/8/2018
 * Time: 5:00 PM
 */

namespace common\modules\drole\models\exchange;

use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\UUIDGenerator;

class ExchangeEngine
{
    private static $operationsPrecision = 9;

    public static function updateAllRequestsFromList($companyID, $serviceID)
    {
        $sql = "select requestlist_data_use.*, requestlist_record_own.contact_id from requestlist_data_use join requestlist_record_own on requestlist_record_own.id = requestlist_data_use.id 
where requestlist_record_own.company_id = '$companyID' and requestlist_record_own.service_id = '$serviceID' order by requestlist_data_use.date_create asc";
        $presentRequestsList = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$presentRequestsList || count($presentRequestsList) < 1) {
            return false;
        }
        foreach ($presentRequestsList as $requestRow) {

            $sql = "delete from requestlist_data_use where id = '" . $requestRow['id'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();
            $sql = "delete from requestlist_record_own where id = '" . $requestRow['id'] . "'";
            \Yii::$app->db->createCommand($sql)->execute();
            $json_request = json_decode($requestRow['json_request'], true);
            if (!isset($json_request['type'])) {
                continue;
            }
            if ($json_request['type'] == 0 || $json_request['type'] == 1) {
                if (isset($json_request['currentcurrencyid']) && isset($json_request['basecurrencyid']) && isset($json_request['amount']) && isset($json_request['price'])
                    && is_numeric($json_request['amount']) && is_numeric($json_request['price']) && UUIDGenerator::isUUID($json_request['currentcurrencyid'])
                    && UUIDGenerator::isUUID($json_request['basecurrencyid'])) {
                    $checkMarket = self::checkMarketAvailable($companyID, $serviceID, $json_request['basecurrencyid'], $json_request['currentcurrencyid']);
                    if (!$checkMarket || count($checkMarket) < 1) {
                        $resultOrder = APIHandler::getErrorArray(404, "Market not found.", true);
                    } else {
                        $resultOrder = self::createOrder($requestRow['contact_id'],
                            $companyID, $serviceID, $json_request['currentcurrencyid'], $json_request['basecurrencyid'],
                            $json_request['amount'], $json_request['price'], $json_request['type']);
                    }
                    self::setResponseRecord($companyID, $serviceID, $requestRow['contact_id'], $resultOrder, $json_request['currentcurrencyid'], $json_request['basecurrencyid']);
                }
            } else if ($json_request['type'] == 2) {
                $resultOrder = self::deleteCryptoOrder($companyID, $serviceID, $json_request['orderid']);
                if (is_array($resultOrder))
                    self::setResponseRecord($companyID, $serviceID, $requestRow['contact_id'], '{"result":"200", "type":"2", "message": "order was deleted"}',
                        $resultOrder['currencyid'], $resultOrder['basecurrencyid']);
            }
        }
    }

    private static function checkMarketAvailable($companyID, $serviceID, $baseCurrencyID, $currentCurrencyID)
    {
        $sql = "select coinmarkets_data_use.id from coinmarkets_data_use join coinmarkets_record_own on coinmarkets_data_use.id = coinmarkets_record_own.id where 
coinmarkets_record_own.company_id = '$companyID' and coinmarkets_record_own.service_id = '$serviceID' and coinmarkets_data_use.currentcurrencyid = '$currentCurrencyID' 
and coinmarkets_data_use.basecurrencyid = '$baseCurrencyID'";
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function createOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, $currencyAmount, $price, $tradeType)
    {
        if ($currencyAmount <= 0 || $price <= 0) {
            return \common\modules\drole\models\gate\APIHandler::getErrorArray(404, "Division by zero.", true);
        }
        $arrayCurrentCoinSettings = self::getCurrencySettings($companyID, $serviceID, $currencyID);
        $arrayBaseCoinSettings = self::getCurrencySettings($companyID, $serviceID, $baseCurrencyID);
        if ($tradeType == 1) {
            //sell
            return self::createSellOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, $currencyAmount, $price, $arrayCurrentCoinSettings, $arrayBaseCoinSettings);
        } else if ($tradeType == 0) {
            //buy
            return self::createBuyOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, $currencyAmount, $price, $arrayCurrentCoinSettings, $arrayBaseCoinSettings);
        }
        return APIHandler::getErrorArray(404, "Operation type not found.", true);
    }

    public static function getCurrencySettings($companyID, $serviceID, $currencyID)
    {
        $sql = "select * from coinsettings_data_use join coinsettings_record_own on coinsettings_data_use.id = coinsettings_record_own.id 
where coinsettings_data_use.currencyid = '$currencyID' and coinsettings_record_own.company_id = '$companyID' and 
coinsettings_record_own.service_id = '$serviceID'";
        $presentDroleProvider = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$presentDroleProvider || count($presentDroleProvider) != 1) {
            return -1;
        }
        return $presentDroleProvider;
    }

    public static function createSellOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, $currencyAmount, $price, $arrayCurrentCoinSettings, $arrayBaseCoinSettings)
    {
        $currentCoinTitle = self::getCurrencyTitle($companyID, $serviceID, $currencyID);
        $baseCoinTitle = self::getCurrencyTitle($companyID, $serviceID, $baseCurrencyID);
        if (!$currentCoinTitle || !$baseCoinTitle) {
            return APIHandler::getErrorArray(400, "Not found currency.", true);
        }
        $currentAmountInternal = self::checkAmount($contactID, $companyID, $serviceID, $currencyID);
        $checkCurrentAmount = $currencyAmount;
        if (!$currentAmountInternal || $currentAmountInternal <= $checkCurrentAmount) {
            if (!$currentAmountInternal || $currentAmountInternal <= 0) {
                return APIHandler::getErrorArray(400, "Not enough currency amount.", true);
            } else {
                $checkCurrentAmount = $currentAmountInternal;
            }
        }

        if (!self::checkFeeValue($checkCurrentAmount, $arrayCurrentCoinSettings[0]['basefee'], $arrayCurrentCoinSettings[0]['referalbasecurrencyfee'],
            $arrayCurrentCoinSettings[0]['minbaseamount'])) {
            return APIHandler::getErrorArray(400, "Not enough for fee payment. enter: $currencyAmount [$price]: $checkCurrentAmount * " . $arrayCurrentCoinSettings[0]['basefee'], true);
        }
        self::updateArithmeticPrecisionCurrency($companyID, $serviceID, $contactID, $currencyID, $currentAmountInternal, (0 - $checkCurrentAmount), 1);
        $enough = false;
        $sumAmount = 0;
        $sumBaseAmount = 0;
        $arrayUsefulOrders = [];
        $lastTimeRequest = null;
        while ($sumAmount < $currencyAmount && !$enough) {
            $arrayOrders = self::getBuyOrders($companyID, $serviceID, $baseCurrencyID, $currencyID);
            $firstPrice = 0;
            if (count($arrayOrders) > 0) {
                $firstPrice = $arrayOrders[0]['price'];
            } else {
                $enough = false;
                break;
            }
            if (count($arrayOrders) > 0 && $firstPrice >= $price) {
                //echo "start cycle orders [" . json_encode($arrayOrders) . "]";
                //exit;
                for ($i = 0; $i < count($arrayOrders); $i++) {
                    if ($arrayOrders[$i]['price'] < $price) {
                        $enough = false;
                        break;
                    }
                    //echo " [ $sumBaseAmount + " . $arrayOrders[$i]['baseamount'] . " > $baseCurrencyAmount ] ";
                    if ($sumAmount + $arrayOrders[$i]['amount'] > $currencyAmount) {
                        $deltaValue = $currencyAmount - $sumAmount;
                    } else {
                        $deltaValue = $arrayOrders[$i]['amount'];
                    }
                    $sumAmount += $deltaValue;//self::ceil_dec(($deltaValue / $arrayOrders[$i]['price']), self::$operationsPrecision);
                    //$sumBaseAmount += $deltaValue;
                    //die(json_encode([$arrayOrders[$i]['id'], $arrayOrders[$i]['uid'], $arrayOrders[$i]['price'], $deltaValue]));
                    if ($deltaValue > 0) {
                        array_push($arrayUsefulOrders, $arrayOrders[$i]);
                        $arrayUsefulOrders[count($arrayUsefulOrders) - 1]['delta'] = $deltaValue;
                        $arrayUsefulOrders[count($arrayUsefulOrders) - 1]['balance'] = $arrayOrders[$i]['amount'] - $deltaValue;
                        self::deleteCurrentOrder($companyID, $serviceID, $arrayOrders[$i]);
                    }
                    //array_push($arrayUsefulOrders, [$arrayOrders[$i]['id'], $arrayOrders[$i]['ownerid'], $arrayOrders[$i]['price'], $deltaValue, ($arrayOrders[$i]['amount'] - $deltaValue)]);
                    if ($deltaValue < $arrayOrders[$i]['amount']) {
                        $enough = true;
                        break;
                    }

                }
                //$lastTimeRequest = $arrayOrders[count($arrayOrders) - 1]['date_change'];
            } else {
                break;
            }
        }
        $idCurrentOrder = \common\models\UUIDGenerator::v4();
        //$currentPrice = $price;
        for ($i = 0; $i < count($arrayUsefulOrders); $i++) {
            //self::deleteCurrentOrder($companyID, $serviceID, $arrayUsefulOrders[$i]);
            //update payments in current cyclic order
            $buyerOldValue = self::checkAmount($arrayUsefulOrders[$i]['ownerid'], $companyID, $serviceID, $arrayUsefulOrders[$i]['currencyid']);
            $sellerOldValue = self::checkAmount($contactID, $companyID, $serviceID, $baseCurrencyID);
            $buyerNewValue = ($arrayUsefulOrders[$i]['balance'] > 0 ? $arrayUsefulOrders[$i]['delta'] : $arrayUsefulOrders[$i]['amount']);
            $sellerNewValue = self::floor_dec(($arrayUsefulOrders[$i]['delta'] * $arrayUsefulOrders[$i]['price']), self::$operationsPrecision);
            $amountBuyerCurrentFee = self::ceil_dec(($buyerNewValue * $arrayCurrentCoinSettings[0]['currentfee']), self::$operationsPrecision);
            if ($amountBuyerCurrentFee >= $buyerNewValue) {
                $amountBuyerCurrentFee = 0;
            }
            self::buyUpdatePayments($companyID, $serviceID, $arrayUsefulOrders[$i]['ownerid'], $arrayUsefulOrders[$i]['currencyid'],
                $buyerOldValue, $buyerNewValue, $price, $amountBuyerCurrentFee, $arrayCurrentCoinSettings[0]['referalcurrencyfee'], 1);
            $amountSellerCurrentFee = self::ceil_dec(($sellerNewValue * $arrayBaseCoinSettings[0]['basefee']), self::$operationsPrecision);
            if ($amountSellerCurrentFee >= $sellerNewValue) {
                $amountSellerCurrentFee = 0;
            }
            self::sellUpdatePayments($companyID, $serviceID, $contactID, $baseCurrencyID, $sellerOldValue,
                self::floor_dec(($arrayUsefulOrders[$i]['delta'] * $arrayUsefulOrders[$i]['price']), self::$operationsPrecision),
                $price, $amountSellerCurrentFee, $arrayBaseCoinSettings[0]['referalbasecurrencyfee'], 0);
            $transactionID = \common\models\UUIDGenerator::v4();
            $arrayUsefulOrders[$i]['tid'] = $transactionID;
            self::setTransaction($companyID, $serviceID, $contactID, $transactionID, $contactID, $arrayUsefulOrders[$i]['ownerid'],
                $currencyID, $baseCurrencyID, $idCurrentOrder, $arrayUsefulOrders[$i]['id'], $buyerNewValue, $sellerNewValue,
                $arrayUsefulOrders[$i]['price'], 1, $amountBuyerCurrentFee, $amountSellerCurrentFee);
            if ($arrayUsefulOrders[$i]['balance'] == 0) {
                //do nothing
            } else
                //insertNewOrder($arrayUsefulOrders[$i][1], $baseCurrencyTitle, $currencyTitle, $arrayUsefulOrders[$i][4], ($arrayUsefulOrders[$i][4] * $arrayUsefulOrders[$i][2]), $arrayUsefulOrders[$i][2], 'BUY');
                self::insertNewOrder("cryptoorders", $companyID, $serviceID, \common\models\UUIDGenerator::v4(), $arrayUsefulOrders[$i]['date_create'],
                    $arrayUsefulOrders[$i]['date_change'], $arrayUsefulOrders[$i]['ownerid'], $arrayUsefulOrders[$i]['currencyid'],
                    $arrayUsefulOrders[$i]['basecurrencyid'], $arrayUsefulOrders[$i]['balance'],
                    $arrayUsefulOrders[$i]['price'], self::floor_dec(($arrayUsefulOrders[$i]['balance'] * $arrayUsefulOrders[$i]['price']), self::$operationsPrecision), 0);

        }
        $orderMessage = '{}';
        //$tradeHistory = ', "tradeinfo": []';

        $tradeHistory = self::getResultArrayForSellPayments($arrayUsefulOrders, $baseCoinTitle['name'], $currentCoinTitle['name'],
            $arrayBaseCoinSettings[0]['basefee']);
        if (!$enough) {
            if (count($arrayUsefulOrders) < 1) {
                $sumAmount = $currencyAmount;
            } else {
                $sumAmount = ($currencyAmount - $sumAmount);
            }
            if (self::ceil_dec(($sumAmount), self::$operationsPrecision) > 0 && self::ceil_dec(($sumAmount * $price), self::$operationsPrecision) > 0) {
                self::insertNewOrder("cryptoorders", $companyID, $serviceID, $idCurrentOrder, microtime(true),
                    microtime(true), $contactID, $currencyID,
                    $baseCurrencyID, $sumAmount, $price, self::ceil_dec(($sumAmount * $price), self::$operationsPrecision), 1);
                $orderMessage = self::getNewCreatedOrderForSell($idCurrentOrder, $baseCoinTitle['name'], $currentCoinTitle['name'], $sumAmount, $price, $arrayBaseCoinSettings[0]['basefee']);
            }
        }
        return '{"result":"200", "type":"1"' . $tradeHistory . ', "message":' . $orderMessage . '}';
    }

    public static function getCurrencyTitle($companyID, $serviceID, $currencyID)
    {
        $sql = "select coin_data_use.name from coin_data_use where id = (select coin_record_own.id from coin_record_own where coin_record_own.id = '$currencyID' and coin_record_own.company_id = '$companyID' and coin_record_own.service_id = '$serviceID')";
        $presentDroleProvider = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$presentDroleProvider || count($presentDroleProvider) != 1) {
            return null;
        }
        return $presentDroleProvider[0];
    }

    public static function checkAmount($contactID, $companyID, $serviceID, $currencyID)
    {
        $sql = "select * from walletcoin_data_use where walletcoin_data_use.currencyid = '$currencyID' and walletcoin_data_use.id in 
(select walletcoin_record_own.id from walletcoin_record_own where walletcoin_record_own.company_id = '$companyID' and 
walletcoin_record_own.service_id = '$serviceID' and walletcoin_record_own.contact_id = '$contactID')";
        $presentDroleProvider = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$presentDroleProvider || count($presentDroleProvider) != 1) {
            return null;
        }
        return $presentDroleProvider[0]['balance'];
    }

    public static function checkFeeValue($amount, $feePercent, $referalFeePercent, $minAmount)
    {
        $feeAmount = $amount * $feePercent;
        //echo "check fee ($amount * $feePercent): fee=" . number_format($feeAmount, self::$operationsPrecision, '.', '') . " , min amount=" . number_format($minAmount, self::$operationsPrecision, '.', '');
        //$feeReferalAmount = $feeAmount * $referalFeePercent;
        //echo "{feeAmount: $feeAmount, feeReferalAmount: $feeReferalAmount}";
        if ($feeAmount < $minAmount) {
            return false;
        }
        return true;
    }

    private static function updateArithmeticPrecisionCurrency($companyID, $serviceID, $contactID, $currencyID, $oldValue, $addValue, $up = 0)
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
        self::setNewValueForCurrency($companyID, $serviceID, $contactID, $currencyID, $oldValue, $newValue);
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

    private static function setNewValueForCurrency($companyID, $serviceID, $contactID, $currencyID, $oldValue, $newValue)
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

    public static function getBuyOrders($companyID, $serviceID, $baseCurrencyID, $currencyID)
    {
        return self::getAvailableOrders($companyID, $serviceID, $baseCurrencyID, $currencyID, 0);
    }

    public static function getAvailableOrders($companyID, $serviceID, $baseCurrencyID, $currencyID, $tradeType)
    {
        $incrementType = "asc";
        if ($tradeType == 0) {
            $incrementType = "desc";
        }
        /*$dateChangeSubquery = '';
        if ($lastTimeRequest != null) {
            $dateChangeSubquery = " and cryptoorders_data_use.date_change > '$lastTimeRequest'";
        }*/
        $sql = "select cryptoorders_data_use.* from cryptoorders_data_use join cryptoorders_record_own on cryptoorders_data_use.id = cryptoorders_record_own.id 
where tradetype = '$tradeType' and cryptoorders_data_use.currencyid = '$currencyID' and cryptoorders_data_use.basecurrencyid = '$baseCurrencyID' and cryptoorders_record_own.company_id = '$companyID' and 
cryptoorders_record_own.service_id = '$serviceID' order by price $incrementType, date_change asc limit 10";
        $dataProvider = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$dataProvider || count($dataProvider) < 1) {
            return null;
        }
        return $dataProvider;
    }

    function deleteCurrentOrder($companyID, $serviceID, $orderRecord, $status = 200)
    {
        self::insertNewOrder("archivecryptoorders", $companyID, $serviceID, $orderRecord['id'], $orderRecord['date_create'],
            $orderRecord['date_change'], $orderRecord['ownerid'], $orderRecord['currencyid'], $orderRecord['basecurrencyid'],
            $orderRecord['amount'], $orderRecord['price'], $orderRecord['baseamount'], $orderRecord['tradetype'], $status);
        self::deleteOrder("cryptoorders", $orderRecord['id']);
    }

    function insertNewOrder($tableTible, $companyID, $serviceID, $id, $dateCreate, $dateChange, $ownerID, $currencyid, $basecurrencyid, $amount, $price, $baseamount, $tradetype, $status = 200)
    {
        $fieldSubquery = '';
        $valuesSubquery = '';
        if($tableTible == "archivecryptoorders"){
            $fieldSubquery = ', status';
            $valuesSubquery = ", '$status'";
        }
        $insertSQL = "insert into " . $tableTible . "_data_use (id, date_create, date_change, ownerid, currencyid, basecurrencyid, amount, price, baseamount, tradetype $fieldSubquery) 
        values ('$id', '$dateCreate', '$dateChange', '$ownerID', '$currencyid', '$basecurrencyid', '" . number_format($amount, self::$operationsPrecision, '.', '') .
            "', '" . number_format($price, self::$operationsPrecision, '.', '') . "', '" . number_format($baseamount, self::$operationsPrecision, '.', '') . "', '$tradetype' $valuesSubquery)";
        \Yii::$app->db->createCommand($insertSQL)->execute();
        $insertOwnerID = "insert into " . $tableTible . "_record_own (id, company_id, service_id, contact_id) values ('$id', '$companyID', '$serviceID', '$ownerID')";
        \Yii::$app->db->createCommand($insertOwnerID)->execute();
        return $id;
    }

    function deleteOrder($tableTible, $idOrder)
    {
        $deleteQuery = "DELETE FROM " . $tableTible . "_data_use WHERE id = '" . $idOrder . "'";
        \Yii::$app->db->createCommand($deleteQuery)->execute();
        $deleteQuery = "DELETE FROM " . $tableTible . "_record_own WHERE id = '" . $idOrder . "'";
        \Yii::$app->db->createCommand($deleteQuery)->execute();
    }

    function buyUpdatePayments($companyID, $serviceID, $contactID, $currencyID, $oldValue, $newValue, $price, $amountCurrentFee, $currentReferalFee, $up = 0)
    {
        //$amountCurrentFee = self::ceil_dec(($newValue * $currentFee), self::$operationsPrecision);
        if ($amountCurrentFee > $newValue) {
            $amountCurrentFee = 0;
        } else {
            $newValue = $newValue - $amountCurrentFee;
        }
        //updateAmountInternalMarket($UID, ($fromAmount - $fee), 'Bought:" . $price, $up);
        //$notes = 'Receive payment from exchanger . order #' . $orderID . ", price: " . $price;
        self::updateArithmeticPrecisionCurrency($companyID, $serviceID, $contactID, $currencyID, $oldValue, $newValue, $up);
        //$nodeUID = getNodeID($UID);
        //$parentTreeNode = substr($nodeUID, 0, strrpos($nodeUID, "."));
        $UID_Parent = self::getSponsorUID($companyID, $serviceID, $contactID);
        if ($newValue > 0 && $UID_Parent && count($UID_Parent) > 0) {
            $sponsorAmountArray = self::checkAmount($UID_Parent, $companyID, $serviceID, $currencyID);
            $sponsorOldValue = null;
            if ($sponsorAmountArray != null && count($sponsorAmountArray) > 0) {
                $sponsorOldValue = $sponsorAmountArray[0]['balance'];
            }
            $sponsorFee = $currentReferalFee * $amountCurrentFee;
            self::updateArithmeticPrecisionCurrency($companyID, $serviceID, $UID_Parent, $currencyID, $sponsorOldValue, $sponsorFee, $up);
            //updateAmountInternalMarket($UID_Parent, ($fee * 0.2), "Referal payment. user: " . getLoginForUserID($UID), 0, self::$operationsPrecision);
        }
    }

    private static function getSponsorUID($companyID, $serviceID, $contactID)
    {
        $sql = "select sponsorcontactid from companiescontact_data_use where id = (select companiescontact_record_own.id 
from companiescontact_record_own where company_id = '$companyID' and service_id = '$serviceID' and contact_id = '$contactID' limit 1)";
        $dataProvider = \Yii::$app->db->createCommand($sql)->queryOne();
    }

    private static function sellUpdatePayments($companyID, $serviceID, $contactID, $currencyID, $oldValue, $newValue, $price, $amountCurrentFee, $currentReferalFee, $up = 0)
    {
        //$amountCurrentFee = self::ceil_dec(($newValue * $currentFee), self::$operationsPrecision);
        if ($amountCurrentFee > $newValue) {
            $newValue = 0;
        } else {
            $newValue = $newValue - $amountCurrentFee;
        }
        //updateAmountInternalMarket($UID, ($fromAmount - $fee), 'Bought:" . $price, $up);
        //$notes = 'Receive payment from exchanger . order #' . $orderID . ", price: " . $price;
        self::updateArithmeticPrecisionCurrency($companyID, $serviceID, $contactID, $currencyID, $oldValue, $newValue, $up);
        //$nodeUID = getNodeID($UID);
        //$parentTreeNode = substr($nodeUID, 0, strrpos($nodeUID, "."));
        $UID_Parent = self::getSponsorUID($companyID, $serviceID, $contactID);
        if ($newValue > 0 && $UID_Parent && count($UID_Parent) > 0) {
            $sponsorOldAmount = self::checkAmount($UID_Parent, $companyID, $serviceID, $currencyID);
            $sponsorOldValue = null;
            if ($sponsorOldAmount != null) {
                $sponsorOldValue = $sponsorOldAmount;
            }
            $sponsorFee = $currentReferalFee * $amountCurrentFee;
            self::updateArithmeticPrecisionCurrency($companyID, $serviceID, $UID_Parent, $currencyID, $sponsorOldValue, $sponsorFee, $up);
            //updateAmountInternalMarket($UID_Parent, ($fee * 0.2), "Referal payment. user: " . getLoginForUserID($UID), 0, self::$operationsPrecision);
        }
    }

    private static function setTransaction($companyID, $serviceID, $contactID, $id, $ownercurrencyID, $ownerbasecurrencyID,
                                           $currencyID, $baseCurrencyID, $orderCurrency, $orderBaseCurrency, $currentCurrencyAmount,
                                           $baseCurrencyAmount, $price, $tradeType, $currentCurrencyFee, $baseCurrencyFee)
    {
        $sql = "insert into cryptotransactions_data_use (id, ownercurrencyid, ownerbasecurrencyid, currencyid, basecurrencyid,
ordercurrency, orderbasecurrency, baseamount, amount, price, tradetype, currentratefee, baseratefee) values ('$id', '$ownercurrencyID', '$ownerbasecurrencyID', '$currencyID',
'$baseCurrencyID', '$orderCurrency', '$orderBaseCurrency', '$baseCurrencyAmount', '$currentCurrencyAmount', '$price', '$tradeType', '$currentCurrencyFee', '$baseCurrencyFee')";
        \Yii::$app->db->createCommand($sql)->execute();
        $insertOwnerID = "insert into cryptotransactions_record_own (id, company_id, service_id, contact_id) values ('$id', '$companyID', '$serviceID', '$contactID')";
        \Yii::$app->db->createCommand($insertOwnerID)->execute();
    }

    private static function getResultArrayForSellPayments($arrayUsefulOrders, $fromCurrency, $toCurrency, $fee)
    {
        $result = '';
        $allCount = count($arrayUsefulOrders);
        for ($i = 0; $i < $allCount; $i++) {
            $row = $arrayUsefulOrders[$i];
            $fromAmount = $row['delta'];
            $toAmountTotal = $row['price'] * $row['delta'];
            $workFee = ($toAmountTotal * $fee);
            $result .= '{"key":"' . $row['tid'] . '", "fromcurrency":"' . $fromCurrency . '", "tocurrency":"' . $toCurrency .
                '", "fromamount":"' . number_format($toAmountTotal, self::$operationsPrecision, '.', '') . '", "toamount_tot":"' . $fromAmount .
                '", "price":"' . $row['price'] . '", "fee":"' . number_format($workFee, self::$operationsPrecision, '.', '') . '", "toamount":"' .
                number_format(($toAmountTotal - $workFee), self::$operationsPrecision, '.', '') . '"}, ';
        }
        if ($allCount > 0) {
            return ', "tradeinfo": [' . substr($result, 0, strlen($result) - 2) . ']';
        }
        return ', "tradeinfo": []';
    }

    private static function getNewCreatedOrderForSell($orderID, $fromCurrency, $toCurrency, $toAmountTotal, $price, $fee, $myownorder = 'false')
    {
        $fromAmount = $toAmountTotal * $price;
        $workFee = ($fromAmount * $fee);
        $result = '{"orderid":"' . $orderID . '", "ordertype":"BUY", "fromcurrency":"' . $fromCurrency . '", "tocurrency":"' . $toCurrency .
            '", "fromamount":"' . $fromAmount . '", "toamount_tot":"' . number_format($toAmountTotal, self::$operationsPrecision, '.', '') .
            '", "price":"' . $price . '", "fee":"' . number_format($workFee, self::$operationsPrecision, '.', '') . '", "toamount":"' .
            number_format(($fromAmount - $workFee), self::$operationsPrecision, '.', '') . '", "created":"yes", "myownorder":"' . $myownorder . '"} ';
        return $result;
    }

    public static function createBuyOrder($contactID, $companyID, $serviceID, $currencyID, $baseCurrencyID, $currencyAmount, $price, $arrayCurrentCoinSettings, $arrayBaseCoinSettings)
    {
        $currentCoinTitle = self::getCurrencyTitle($companyID, $serviceID, $currencyID);
        $baseCoinTitle = self::getCurrencyTitle($companyID, $serviceID, $baseCurrencyID);
        if (!$currentCoinTitle || !$baseCoinTitle) {
            return APIHandler::getErrorArray(400, "Not found currency.", true);
        }
        $currentAmountInternal = self::checkAmount($contactID, $companyID, $serviceID, $baseCurrencyID);
        $checkBaseAmount = self::ceil_dec(($currencyAmount * $price), self::$operationsPrecision);
        if (!$currentAmountInternal || $currentAmountInternal <= $checkBaseAmount) {
            if (!$currentAmountInternal || $currentAmountInternal <= 0) {
                return APIHandler::getErrorArray(400, "Not enough currency amount.", true);
            } else {
                $checkBaseAmount = $currentAmountInternal;
            }
        }

        if (!self::checkFeeValue($checkBaseAmount, $arrayBaseCoinSettings[0]['basefee'], $arrayBaseCoinSettings[0]['referalbasecurrencyfee'],
            $arrayBaseCoinSettings[0]['minbaseamount'])) {
            return APIHandler::getErrorArray(400, "Not enough for fee payment. enter: $currencyAmount [$price]: " . $arrayBaseCoinSettings[0]['basefee'] . " * " . $arrayBaseCoinSettings[0]['referalbasecurrencyfee'] . " = " . $arrayBaseCoinSettings[0]['minbaseamount'], true);
        }
        self::updateArithmeticPrecisionCurrency($companyID, $serviceID, $contactID, $baseCurrencyID, $currentAmountInternal, ( -1 * $checkBaseAmount), 1);
        $enough = false;
        $sumAmount = 0;
        $sumBaseAmount = 0;
        $arrayUsefulOrders = [];
        while ($sumAmount < $currencyAmount && !$enough) {
            $arrayOrders = self::getSellOrders($companyID, $serviceID, $baseCurrencyID, $currencyID);
            $firstPrice = 0;
            if (count($arrayOrders) > 0) {
                $firstPrice = $arrayOrders[0]['price'];
            } else {
                $enough = false;
                break;
            }
            if (count($arrayOrders) > 0 && $firstPrice <= $price) {
                for ($i = 0; $i < count($arrayOrders); $i++) {
                    if ($arrayOrders[$i]['price'] > $price) {
                        $enough = false;
                        break;
                    }
                    if ($sumAmount + $arrayOrders[$i]['amount'] > $currencyAmount) {
                        $deltaValue = $currencyAmount - $sumAmount;
                    } else {
                        $deltaValue = $arrayOrders[$i]['amount'];
                    }
                    $sumAmount += $deltaValue;
                    $sumBaseAmount += $deltaValue * $arrayOrders[$i]['price'];
                    //die(json_encode([$arrayOrders[$i]['id'], $arrayOrders[$i]['uid'], $arrayOrders[$i]['price'], $deltaValue]));
                    if ($deltaValue > 0) {
                        array_push($arrayUsefulOrders, $arrayOrders[$i]);
                        $arrayUsefulOrders[count($arrayUsefulOrders) - 1]['delta'] = $deltaValue;
                        $arrayUsefulOrders[count($arrayUsefulOrders) - 1]['balance'] = $arrayOrders[$i]['amount'] - $deltaValue;
                        self::deleteCurrentOrder($companyID, $serviceID, $arrayOrders[$i]);
                    }
                    //array_push($arrayUsefulOrders, [$arrayOrders[$i]['id'], $arrayOrders[$i]['ownerid'], $arrayOrders[$i]['price'], $deltaValue, ($arrayOrders[$i]['amount'] - $deltaValue)]);
                    if ($deltaValue < $arrayOrders[$i]['amount']) {
                        $enough = true;
                        break;
                    }

                }
            } else {
                break;
            }
        }
        //echo " end cycle with: sumAmount = $sumAmount, currencyAmount = $currencyAmount";
        $idCurrentBaseOrder = \common\models\UUIDGenerator::v4();
        $currentPrice = $price;
        for ($i = 0; $i < count($arrayUsefulOrders); $i++) {
            //$currentCurrencyFee = $arrayUsefulOrders[$i]['delta'] * $arrayCurrentCoinSettings['currentfee'];
            //insertNewOrder($UID, $baseCurrencyTitle, $currencyTitle, $arrayUsefulOrders[$i][3], ($arrayUsefulOrders[$i][3] * $arrayUsefulOrders[$i][2]), $arrayUsefulOrders[$i][2], 'SELL');
            //$self_order_id = mysqli_insert_id(getLink());
            //updateAmountInternalMarket($UID, ($arrayUsefulOrders[$i][3] * -1), 'Create order #' . $self_order_id . " with price: " . $arrayUsefulOrders[$i][2], 1, $CurrencyDigits);
            //update payments in current cyclic order
            $sellerOldValue = self::checkAmount($arrayUsefulOrders[$i]['ownerid'], $companyID, $serviceID,
                $arrayUsefulOrders[$i]['basecurrencyid']);
            $buyerOldValue = self::checkAmount($contactID, $companyID, $serviceID, $currencyID);
            $sellerNewValue = ($arrayUsefulOrders[$i]['balance'] > 0 ? self::floor_dec(($arrayUsefulOrders[$i]['delta'] * $arrayUsefulOrders[$i]['price']),
                self::$operationsPrecision) : $arrayUsefulOrders[$i]['baseamount']);
            $buyerNewValue = $arrayUsefulOrders[$i]['delta'];
            $amountBuyerCurrentFee = self::ceil_dec(($buyerNewValue * $arrayCurrentCoinSettings[0]['currentfee']), self::$operationsPrecision);
            if ($amountBuyerCurrentFee >= $buyerNewValue) {
                $amountBuyerCurrentFee = 0;
            }
            self::buyUpdatePayments($companyID, $serviceID, $contactID, $currencyID,
                $buyerOldValue, $buyerNewValue, $price, $amountBuyerCurrentFee, $arrayCurrentCoinSettings[0]['referalcurrencyfee'], 1);
            $amountSellerCurrentFee = self::ceil_dec(($sellerNewValue * $arrayBaseCoinSettings[0]['basefee']), self::$operationsPrecision);
            if ($amountSellerCurrentFee >= $sellerNewValue) {
                $amountSellerCurrentFee = 0;
            }
            self::sellUpdatePayments($companyID, $serviceID, $arrayUsefulOrders[$i]['ownerid'], $arrayUsefulOrders[$i]['basecurrencyid'], $sellerOldValue,
                $sellerNewValue, $price, $amountSellerCurrentFee, $arrayBaseCoinSettings[0]['referalbasecurrencyfee'], 0);
            $transactionID = \common\models\UUIDGenerator::v4();
            $arrayUsefulOrders[$i]['tid'] = $transactionID;
            self::setTransaction($companyID, $serviceID, $contactID, $transactionID, $arrayUsefulOrders[$i]['ownerid'], $contactID,
                $currencyID, $baseCurrencyID, $arrayUsefulOrders[$i]['id'], $idCurrentBaseOrder, $buyerNewValue,
                $sellerNewValue, $arrayUsefulOrders[$i]['price'], 0, $amountBuyerCurrentFee, $amountSellerCurrentFee);
            if ($arrayUsefulOrders[$i]['balance'] == 0) {
                //do nothing
            } else
                //insertNewOrder($arrayUsefulOrders[$i][1], $baseCurrencyTitle, $currencyTitle, $arrayUsefulOrders[$i][4], ($arrayUsefulOrders[$i][4] * $arrayUsefulOrders[$i][2]), $arrayUsefulOrders[$i][2], 'BUY');
                self::insertNewOrder("cryptoorders", $companyID, $serviceID, \common\models\UUIDGenerator::v4(), $arrayUsefulOrders[$i]['date_create'],
                    $arrayUsefulOrders[$i]['date_change'], $arrayUsefulOrders[$i]['ownerid'], $arrayUsefulOrders[$i]['currencyid'],
                    $arrayUsefulOrders[$i]['basecurrencyid'], $arrayUsefulOrders[$i]['balance'], $arrayUsefulOrders[$i]['price'],
                    self::ceil_dec(($arrayUsefulOrders[$i]['balance'] * $arrayUsefulOrders[$i]['price']), self::$operationsPrecision), 1);
            //insertNewOrder($UID, $baseCurrency, $currency, $amount, $baseAmount, $price, $tradeType)
            //$currentPrice = $arrayUsefulOrders[$i]['price'];
        }
        $orderMessage = '{}';
        //$tradeHistory = ', "tradeinfo": []';
        $tradeHistory = self::getResultArrayForBuyPayments($arrayUsefulOrders,
            $baseCoinTitle['name'], $currentCoinTitle['name'], $arrayCurrentCoinSettings[0]['currentfee']);
        if (!$enough) {
            if (count($arrayUsefulOrders) < 1) {
                $sumAmount = $currencyAmount;
            } else {
                $sumAmount = ($currencyAmount - $sumAmount);
            }
            if (self::ceil_dec(($sumAmount), self::$operationsPrecision) > 0 && self::ceil_dec(($sumAmount * $price), self::$operationsPrecision) > 0) {
                self::insertNewOrder("cryptoorders", $companyID, $serviceID, $idCurrentBaseOrder, microtime(true),
                    microtime(true), $contactID, $currencyID,
                    $baseCurrencyID, $sumAmount, $currentPrice, self::floor_dec(($sumAmount * $price), self::$operationsPrecision), 0);
                $orderMessage = self::getNewCreatedOrderForBuy($idCurrentBaseOrder, $baseCoinTitle['name'], $currentCoinTitle['name'], $sumAmount, $price, $arrayCurrentCoinSettings[0]['currentfee']);
            }
        }
        return '{"result":"200", "type":"0"' . $tradeHistory . ', "message":' . $orderMessage . '}';
    }

    public static function getSellOrders($companyID, $serviceID, $baseCurrencyID, $currencyID)
    {
        return self::getAvailableOrders($companyID, $serviceID, $baseCurrencyID, $currencyID, 1);
    }

    private static function getResultArrayForBuyPayments($arrayUsefulOrders, $fromCurrency, $toCurrency, $fee)
    {
        $result = '';
        $allCount = count($arrayUsefulOrders);
        for ($i = 0; $i < $allCount; $i++) {
            $row = $arrayUsefulOrders[$i];
            $fromAmount = $row['price'] * $row['delta'];
            $toAmountTotal = $row['delta'];
            $workFee = ($toAmountTotal * $fee);
            $result .= '{"key":"' . $row['tid'] . '", "fromcurrency":"' . $fromCurrency . '", "tocurrency":"' . $toCurrency .
                '", "fromamount":"' . number_format($fromAmount, self::$operationsPrecision, '.', '') . '", "toamount_tot":"' . $row['delta'] .
                '", "price":"' . $row['price'] . '", "fee":"' . $workFee . '", "toamount":"' . ($toAmountTotal - $workFee) . '"}, ';
        }
        if ($allCount > 0) {
            return ', "tradeinfo": [' . substr($result, 0, strlen($result) - 2) . ']';
        }
        return ', "tradeinfo": []';
    }

    private static function getNewCreatedOrderForBuy($orderID, $fromCurrency, $toCurrency, $toAmountTotal, $price, $fee, $myownorder = 'false')
    {
        $fromAmount = self::ceil_dec($toAmountTotal * $price, self::$operationsPrecision);
        $workFee = ($toAmountTotal * $fee);
        $result = '{"orderid":"' . $orderID . '", "ordertype":"BUY", "fromcurrency":"' . $fromCurrency . '", "tocurrency":"' . $toCurrency .
            '", "fromamount":"' . number_format($fromAmount, self::$operationsPrecision, '.', '') . '", "toamount_tot":"' . self::ceil_dec($toAmountTotal, self::$operationsPrecision) .
            '", "price":"' . $price . '", "fee":"' . number_format($toAmountTotal * $fee, self::$operationsPrecision, '.', '') .
            '", "toamount":"' . ($toAmountTotal - $workFee) . '", "created":"yes", "myownorder":"' . $myownorder . '"} ';
        return $result;
    }

    public static function setResponseRecord($companyID, $serviceID, $contactID, $json_response, $currentCurrencyID, $baseCurrencyID)
    {
        $idRecord = UUIDGenerator::v4();
        $sql = "insert into responselist_data_use (id, jsonrespond, currentcurrencyid, basecurrencyid) values ('$idRecord', '$json_response', '$currentCurrencyID', '$baseCurrencyID')";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into responselist_record_own (id, company_id, service_id, contact_id) values ('$idRecord', '$companyID', '$serviceID', '$contactID')";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    private static function deleteCryptoOrder($companyID, $serviceID, $orderID)
    {
        $sql = "SELECT * FROM cryptoorders_data_use WHERE id = '" . $orderID . "'";
        $resultOrder = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$resultOrder || count($resultOrder) < 1) {
            return APIHandler::getErrorArray(500, "Not found order.", true);
        } else {

            $type = $resultOrder['tradetype'];
            if ($type == '1') {
                $oldValue = self::checkAmount($resultOrder['ownerid'], $companyID, $serviceID, $resultOrder['currencyid']);
                self::updateArithmeticPrecisionCurrency($companyID, $serviceID, $resultOrder['ownerid'], $resultOrder['currencyid'], $oldValue, $resultOrder['amount'], 0);
            } else if ($type == '0') {
                $oldValue = self::checkAmount($resultOrder['ownerid'], $companyID, $serviceID, $resultOrder['basecurrencyid']);
                self::updateArithmeticPrecisionCurrency($companyID, $serviceID, $resultOrder['ownerid'], $resultOrder['basecurrencyid'], $oldValue, $resultOrder['baseamount'], 0);
            }
            self::deleteCurrentOrder($companyID, $serviceID, $resultOrder, 500);
            return $resultOrder;
        }
    }

    private static function floor_dec_old($number, $precision, $separator = '.')
    {
        $numberpart = explode($separator, $number);
        if (count($numberpart) < 2) {
            return $number;
        }
        $numberpart[1] = substr_replace($numberpart[1], $separator, $precision, 0);
        if ($numberpart[0] >= 0) {
            $numberpart[1] = floor($numberpart[1]);
        } else {
            $numberpart[1] = ceil($numberpart[1]);
        }
        $ceil_number = array($numberpart[0], $numberpart[1]);
        return implode($separator, $ceil_number);
    }

    private static function updateAmountInternalMarket($UID, $addValue, $notes, $up = 1, $digitals = 9)
    {
        updateArithmeticPrecision($UID, $addValue, $notes, "amount_internal", $up, $digitals);
    }

    private static function addNotes($companyID, $serviceID, $contactID, $currencyID, $oldValue, $newValue, $operationType, $notes)
    {
        $id = \common\models\UUIDGenerator::v4();
        $sql = "insert into financelog_data_use (id, ownerid, currencyid, oldbalance, newbalance, operationtype, notes) values ('" .
            $id . "', '$contactID', '$currencyID', '$oldValue', '$newValue', '$operationType', '$notes')";
        \Yii::$app->db->createCommand($sql)->execute();
        $insertOwnerID = "insert into financelog_record_own (id, company_id, service_id, contact_id) values ('$id', '$companyID', '$serviceID', '$contactID')";
        \Yii::$app->db->createCommand($insertOwnerID)->execute();
    }
}