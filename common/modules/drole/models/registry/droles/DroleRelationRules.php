<?php
namespace common\modules\drole\registry\droles;

use common\modules\drole\registry\droles\RegistryDescriptionRolesModel;
use yii\data\SqlDataProvider;
use common\modules\drole\registry\payment\RegistryDescriptionPaymentModel;

class DroleRelationRules
{

    //SELECT drole_id, count(drole_id) FROM `registry_droles` WHERE (meta_key like 'objectid03' and `value` LIKE 'companyid01') or (meta_key like 'objectid01' and `value` LIKE 'roleid01') GROUP BY drole_id HAVING COUNT(drole_id) = 2 

    public static $companyObjectID = '2ed029b6-d745-4f85-8d9f-2dccd2a7da37';
    public static $contactsObjectID = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';
    public static $serviceObjectID = '3db2f640-e01a-42ac-904e-87a46e0373fd';
    public static $roleObjectID = '97086af0-956b-4380-a385-ea823cff377a';
    public static $payObjectID = 'fc98affc-9164-4b57-b1a0-e9169305dfce';
    public static $requestObjectID = 'object';
    public static $arrayDroleElements = [];

    //public static $arrayDroleElements;// = [$companyObjectID, $serviceObjectID, $roleObjectID, $payObjectID];

    public static function getTheRoleForInputParams($inputParams)
    {
        $companyID = $inputParams[self::$companyObjectID];
        $serviceID = $inputParams[self::$serviceObjectID];
        $roleID = $inputParams[self::$roleObjectID];
        $contactID = $inputParams[self::$contactsObjectID];
        $requestObjectID = $inputParams[self::$requestObjectID];
        if ($companyID == '') {
            if ($requestObjectID == self::$contactsObjectID) {
                return RegistryDescriptionRolesModel::$admin;
            } else
                return RegistryDescriptionRolesModel::$anonimus;
        }
        if ($serviceID == '' && ($roleID != RegistryDescriptionRolesModel::$superadmin) && ($roleID != RegistryDescriptionRolesModel::$admin)) {
            return RegistryDescriptionRolesModel::$anonimus;
        }
        if ($companyID == '' || $serviceID == '' || $roleID == '') {
            return RegistryDescriptionRolesModel::$anonimus;
        } else
            return $roleID;
    }

    public static function getRulesArray()
    {
        return [self::$companyObjectID, self::$serviceObjectID, self::$roleObjectID, self::$payObjectID];
    }

    public static function getAccessElementsFromInput($inputArray)
    {
        self::$arrayDroleElements = self::getRulesArray();
        //echo "[" . print_r(self::$arrayDroleElements, true) . "]";
        $resultArray = [];
        for ($i = 0; $i < count(self::$arrayDroleElements); $i++) {
            $resultArray[self::$arrayDroleElements[$i]] = self::choiceFunctionForGetElement(self::$arrayDroleElements[$i], $inputArray[self::$arrayDroleElements[$i]], $inputArray);
        }
        return $resultArray;
    }
    
    /*public static function getAccessElementsFromInput($inputArray)
    {
        self::$arrayDroleElements = self::getRulesArray();
        echo "[" . print_r(self::$arrayDroleElements, true) . "]";
        $resultArray = [];
        for ($i = 0; $i < count(self::$arrayDroleElements); $i++) {
            $resultArray[self::$arrayDroleElements[$i]] = self::choiceFunctionForGetElement(self::$arrayDroleElements[$i], $inputArray[self::$arrayDroleElements[$i]], $inputArray);
        }
        return $resultArray;
    }*/

    public static function choiceFunctionForGetElement($key, $value, $inputArray)
    {
        switch ($key) {
            case self::$companyObjectID :
            case self::$serviceObjectID :
            case self::$roleObjectID :
                return $value;
            case self::$payObjectID:
                return self::getAccessElementFromPayModule($key, $value, $inputArray);
            default: return $value;
        }
    }

    public static function getAccessElementFromPayModule($key, $value, $inputArray)
    {
        //check anonymous
        $presentRole = self::getTheRoleForInputParams($inputArray);
        if ($presentRole == RegistryDescriptionRolesModel::$anonimus) {
            return RegistryDescriptionPaymentModel::$success;
        }
        //start search payment for input company 
        $query = 'select * from pay_data_use where "' . self::$companyObjectID . '" = \'' . $inputArray[self::$companyObjectID]
            . '\' and "' . self::$serviceObjectID . '" = \'' . $inputArray[self::$serviceObjectID] . '\' '
            . ' and object_id = \'' . $inputArray[self::$requestObjectID] . '\' and "' . self::$contactsObjectID . '" is NULL';
        //echo "[$query]";
        $provider = new SqlDataProvider([
            'sql' => $query
        ]);
        $companyPaymentStatus = $provider->getModels()[0]['payment_status'];
        if ($companyPaymentStatus == RegistryDescriptionPaymentModel::$test || $companyPaymentStatus == RegistryDescriptionPaymentModel::$success) {
            $query = 'select * from pay_data_use where "' . self::$companyObjectID . '" = \'' . $inputArray[self::$companyObjectID]
                . '\' and "' . self::$serviceObjectID . '" = \'' . $inputArray[self::$serviceObjectID] . '\' '
                . ' and object_id = \'' . $inputArray[self::$requestObjectID] . '\' and "' . self::$contactsObjectID . '" = \'' . $inputArray[self::$contactsObjectID] . '\'';
            //echo "[$query]";
            $provider = new SqlDataProvider([
                'sql' => $query
            ]);
            $contactPaymentStatus = $provider->getModels()[0]['payment_status'];
            if ($companyPaymentStatus == RegistryDescriptionPaymentModel::$test || $companyPaymentStatus == RegistryDescriptionPaymentModel::$success) {
                return $contactPaymentStatus;
            } else
                return -1;
        } else {
            return -1;
        }
    }
}
