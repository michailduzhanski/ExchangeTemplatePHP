<?php

namespace common\modules\drole\models\registry\droles;

class RegistryDescriptionRolesModel {

    public static $superadmin = '1d021b86-41c6-47c1-a38e-0aa89b98dc28';
    public static $superuserglobal = '2b021b86-41c6-47c1-a38e-0aa89b98dc29';
    public static $admin = '65fe5829-ff9a-4b58-aa76-d8a92eaeee7e';
    public static $superuserlocal = '3c021b86-41c6-47c1-a38e-0aa89b98dc30';
    public static $anonimus = '69ebe402-022a-4fb1-9472-f16c4b768c26';
    public static $rolesArray = [
        'superadmin' => '1d021b86-41c6-47c1-a38e-0aa89b98dc28',
        'superuserglobal' => '2b021b86-41c6-47c1-a38e-0aa89b98dc29',
        'admin' => '65fe5829-ff9a-4b58-aa76-d8a92eaeee7e',
        'superuserlocal' => '3c021b86-41c6-47c1-a38e-0aa89b98dc30',
        'anonimous' => '69ebe402-022a-4fb1-9472-f16c4b768c26'
    ];

    public static function getForQueryArray() {
        $resultString = '';
        foreach (self::$rolesArray as $roleID) {
            $resultString .= $roleID . ',';
        }
        return "{" . substr($resultString, 0, strlen($resultString) - 1) . "}";
    }

    public static function getRolesForRegistry() {
        return "{" . self::$rolesArray['superadmin'] . "," . self::$rolesArray['superuserglobal'] . "," . self::$rolesArray['admin'] . "," . self::$rolesArray['superuserlocal'] . "}";
    }

    public static function getRolesForAdminSiteAccess($operationType) {
        switch ($operationType) {
            case 0:return [self::$rolesArray['superadmin'], self::$rolesArray['superuserglobal'], self::$rolesArray['admin'], self::$rolesArray['superuserlocal']];
            case 1:return [self::$rolesArray['superadmin'], self::$rolesArray['admin']];
        }
    }
    
    public static function getAdminCompany() {
        return 'af09ea17-d47c-452d-93de-2c89157b9d5b';
    }

    public static function getMustService() {
        return 'b56b99b6-2c6f-4103-849a-e914e8594869';
    }

    public static function compaireUpdatesDataArrays($objectID) {
        switch ($objectID) {
            case '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24':
            case '97086af0-956b-4380-a385-ea823cff377a':
            case '2ed029b6-d745-4f85-8d9f-2dccd2a7da37':
            case '3db2f640-e01a-42ac-904e-87a46e0373fd':
            case '32f596c7-42ca-4046-a6ab-d537eddf800c':
            case '8101a4d2-9c11-46bf-9821-cbdacd760d5d':
                return false;
            default:
                return true;
        }
    }

    public static function getObjectForRegistry($stringName) {
        switch ($stringName) {
            case 'companies':
                //return '2ed029b6-d745-4f85-8d9f-2dccd2a7da37';
                return 'a89c5b6f-80c0-47ca-8b30-8647a5efbfe5';
            case 'roles':
                return '97086af0-956b-4380-a385-ea823cff377a';
            case 'services':
                return '3db2f640-e01a-42ac-904e-87a46e0373fd';
            case 'contacts':
                return '38d6b0f4-e756-448a-8177-83ecf487b094';
            default:
                return false;
        }
        return false;
    }

    public static function getAdministrationLevel($role) {
        switch ($role) {
            case self::$rolesArray['superadmin']:
            case self::$rolesArray['superuserglobal']:
                return 0;
            case self::$rolesArray['admin']:
            case self::$rolesArray['superuserlocal']:
                return 1;
            default:
                return 10;
        }
    }

}
