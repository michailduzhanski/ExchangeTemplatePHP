<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 6/28/2018
 * Time: 4:02 PM
 */

namespace common\modules\drole\models\gate;


class PaginationObjectHandler
{
    public static function getSubqueryFromJson($jsonLimitParams, $objectName)
    {
        $maxLimit = 50;
        if (!$jsonLimitParams || count($jsonLimitParams) < 1) {
            return " limit " . $maxLimit . " ";
        }
        $realLimit = 0;
        if ((!isset($jsonLimitParams['lmt']) || !is_numeric($jsonLimitParams['lmt'])) && $jsonLimitParams['lmt'] >= 0) {
            $realLimit = $maxLimit;
        } else {
            $realLimit = ($jsonLimitParams['lmt'] > $maxLimit ? $maxLimit : $jsonLimitParams['lmt']);
        }
        if ((!isset($jsonLimitParams['off']) || !is_numeric($jsonLimitParams['off'])) && $jsonLimitParams['off'] >= 0) {
            return " limit " . $realLimit . " ";
        }
        $offsetLimits = $jsonLimitParams['off'];
        $previousRecordsCount = 0;
        if (!isset($jsonLimitParams['prev']) || !is_numeric($jsonLimitParams['prev']) || $jsonLimitParams['prev'] < 1) {
        } else {
            $previousRecordsCount = $jsonLimitParams['prev'];
        }
        if (!isset($jsonLimitParams['asc']) || !is_numeric($jsonLimitParams['asc']) || $jsonLimitParams['asc'] < 0) {
            //if ($jsonLimitParams['prev'] < $realLimit) {
            if ($jsonLimitParams['off'] == 0 || $jsonLimitParams['off'] < $realLimit) {
                return " limit " . $realLimit . " ";
            } else {
                $offsetLimits = $offsetLimits - $realLimit;
            }
            //}
        } else if ($jsonLimitParams['asc'] == 0) {
            //do nothing
        } else if ($jsonLimitParams['asc'] > 0) {
            if ($previousRecordsCount < $realLimit) {
                //do nothing
            } else {
                $offsetLimits = $offsetLimits + $realLimit;
            }
        }
        return " limit " . $realLimit . " offset " . $offsetLimits . " ";
    }
}