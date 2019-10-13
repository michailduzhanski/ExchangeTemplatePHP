<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 8/21/2018
 * Time: 9:52 AM
 */

namespace common\modules\drole\models\exchange;


class ExchangeChartHandler
{
    public function getChart($companyID, $serviceID, $currentCurrencyID, $baseCurrencyID, $typeChart)
    {
        $contactID = '00000000-0000-0000-0000-000000000015';
        //console.log('type chart: ' + typeChart)
        $precision = 8;
        switch ($typeChart) {
            case '172800':
                $contactID = '00000000-0000-0000-0000-000000000030';
                break;
            case '604800':
                $contactID = '00000000-0000-0000-0000-000000000060';
                break;
            case '1209600':
                $contactID = '00000000-0000-0000-0000-000000000120';
                break;
            case '2419200':
                $contactID = '00000000-0000-0000-0000-000000000240';
                break;
            case '4838400':
                $contactID = '00000000-0000-0000-0000-000000000720';
                break;
            case '14515200':
                $contactID = '00000000-0000-0000-0000-000000001440';
                break;
            default :
                $typeChart = '86400';
                break;
        }
        $intervalValue = (substr($contactID, 25, strlen($contactID)) * 60000);
        $sql = "SELECT jsonb_agg (query) from (select (intervaltransactions_data_use.interval * 1000) as \"0\", 
trunc(intervaltransactions_data_use.open::numeric, $precision) as \"1\", trunc(intervaltransactions_data_use.high::numeric, $precision) as \"2\", 
trunc(intervaltransactions_data_use.low::numeric, $precision) as \"3\", trunc(intervaltransactions_data_use.close::numeric, $precision) as \"4\", 
trunc(intervaltransactions_data_use.volume::numeric, $precision) as \"5\", trunc(intervaltransactions_data_use.average::numeric, $precision) as \"6\" 
from intervaltransactions_data_use join intervaltransactions_record_own on intervaltransactions_data_use.id = intervaltransactions_record_own.id 
where  intervaltransactions_data_use.close > '0' and intervaltransactions_record_own.company_id = '$companyID' and 
intervaltransactions_record_own.service_id = '$serviceID' and intervaltransactions_data_use.currencyid = '$currentCurrencyID' 
and intervaltransactions_data_use.basecurrencyid = '$baseCurrencyID' and intervaltransactions_record_own.contact_id = '$contactID' order by intervaltransactions_data_use.date_create)query";
        $objectsArray = \Yii::$app->db->createCommand($sql)->queryAll();
        if (!$objectsArray || count($objectsArray) < 1 || strlen($objectsArray[0]['jsonb_agg']) < 5) {
            return '{"data": {"status": "error", "type": "' . $typeChart . '", "chartdata": []}}';
        }
        $lastDateStart = strrpos($objectsArray[0]['jsonb_agg'], '{"0":');
        $lastDateEnd = strrpos($objectsArray[0]['jsonb_agg'], '"1":');
        //return substr($objectsArray[0]['jsonb_agg'], $lastDateStart + 5, $lastDateEnd - $lastDateStart - 7);
        $lastStockDate = substr($objectsArray[0]['jsonb_agg'], $lastDateStart + 5, $lastDateEnd - $lastDateStart - 7);
        $lastDateStart = strrpos($objectsArray[0]['jsonb_agg'], '"4":');
        $lastDateEnd = strrpos($objectsArray[0]['jsonb_agg'], '"5":');
        $lastClosePrice = substr($objectsArray[0]['jsonb_agg'], $lastDateStart + 4, $lastDateEnd - $lastDateStart - 6);

        $sql = "SELECT sum(amount) as volume, avg(amount) as average, min(price) as low, max(price) as high FROM cryptotransactions_data_use 
join cryptotransactions_record_own on cryptotransactions_data_use.id = cryptotransactions_record_own.id where 
cryptotransactions_record_own.company_id = '$companyID' and cryptotransactions_record_own.service_id = '$serviceID' and 
cryptotransactions_data_use.currencyid = '$currentCurrencyID' and cryptotransactions_data_use.basecurrencyid = '$baseCurrencyID' and 
date_create > '" . ($lastStockDate / 1000) . "'";
        $lastStatsArray = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$lastStatsArray || count($lastStatsArray) < 1) {
            return '{"data": {"status": "ok", "type": "' . $typeChart . '", "chartdata": ' . $objectsArray[0]['jsonb_agg'] . '}}';
        }
        $sql = "SELECT cryptotransactions_data_use.price FROM cryptotransactions_data_use join cryptotransactions_record_own on 
cryptotransactions_data_use.id = cryptotransactions_record_own.id where cryptotransactions_record_own.company_id = '$companyID' 
and cryptotransactions_record_own.service_id = '$serviceID' and cryptotransactions_data_use.currencyid = '$currentCurrencyID' and 
cryptotransactions_data_use.basecurrencyid = '$baseCurrencyID' and date_create > '" . ($lastStockDate / 1000) . "' order by cryptotransactions_data_use.date_create desc limit 1";
        $lastPrice = \Yii::$app->db->createCommand($sql)->queryOne();
        if (!$lastPrice || count($lastPrice) < 1) {
            return '{"data": {"status": "ok", "type": "' . $typeChart . '", "chartdata": ' . $objectsArray[0]['jsonb_agg'] . '}}';
        }
        $lowestPrice = $lastStatsArray['low'];
        $lastClosePrice = $lastClosePrice + 0;
        if($lowestPrice > $lastClosePrice){
            $lowestPrice = $lastClosePrice;
        }
        $highestPrice = $lastStatsArray['high'];
        if($highestPrice < $lastClosePrice){
            $highestPrice = $lastClosePrice;
        }
        $lastElement = ',{"0":' . ($lastStockDate + $intervalValue) . ',"1":' . $lastClosePrice . ',"2":' . $highestPrice . ',"3":' .
            $lowestPrice . ',"4":' . $lastPrice['price'] . ',"5":' . $lastStatsArray['volume'] . ',"6":' . $lastStatsArray['average'] . '}';
        $resultArray = substr($objectsArray[0]['jsonb_agg'], 0, strlen($objectsArray[0]['jsonb_agg']) - 1) . $lastElement . ']';
        return '{"data": {"status": "ok", "type": "' . $typeChart . '", "chartdata": ' . $resultArray . '}}';;
    }
}