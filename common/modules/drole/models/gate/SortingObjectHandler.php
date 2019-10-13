<?php
/**
 * Created by PhpStorm.
 * User: ENGINEER
 * Date: 6/28/2018
 * Time: 1:33 PM
 */

namespace common\modules\drole\models\gate;

class SortingObjectHandler
{
    public static function getSubqueryFromJson($jsonSortParams, $structureArray, $objectName)
    {
        if (!$jsonSortParams || count($jsonSortParams) < 1) {
            return " order by " . $objectName . "_data_use.\"date_change\" desc";
        }
        $resultString = " order by ";
        foreach ($jsonSortParams as $sortRecord) {
            if (!is_numeric($sortRecord['map']) || !isset($structureArray[$sortRecord['map']]) || $structureArray[$sortRecord['map']]['name'] == "false") {
                //echo "wtf: " . $sortRecord['map'] . " = " . $structureArray[$sortRecord['map']]['name'];
                continue;
            }
            $sorting = 'desc';
            if ($sortRecord['sort'] == 0) {
                $sorting = 'asc';
            }
            $resultString .= " " . $objectName . "_data_use.\"" . $structureArray[$sortRecord['map']]['name'] . "\" " . $sorting . " ,";
        }
        if (strlen($resultString) < 12) {
            //echo " order by " . $objectName . "_data_use.\"date_change\" desc";
            return " order by " . $objectName . "_data_use.\"date_change\" desc";
        } else {
            //echo substr($resultString, 0, strlen($resultString) - 2);
            return substr($resultString, 0, strlen($resultString) - 2);
        }
    }
}