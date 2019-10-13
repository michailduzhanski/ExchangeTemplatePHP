<?php
namespace common\modules\drole\registry;

use yii\db\ActiveRecord;

class RegistryObjectValues extends ActiveRecord
{

    //constants
    private static $registryTable = 'registry_objects';
    /*private static $companyObjectName = 'company';
    private static $serviceObjectName = 'service';
    private static $roleObjectName = 'role';*/

    public static function tableName()
    {
        return '{{' . self::$registryTable . '}}';
    }

    //function check dynrole for contact id
    /*public static function getDynamicRoleForContactWithLocalization($companyID, $serviceID, $roleID)
    {
        echo "[" . self::$companyObjectName . "]";
        return self::find()->where([self::$companyObjectName . '_id' => $companyID,
                self::$serviceObjectName . '_id' => $serviceID, self::$roleObjectName . '_id' => $roleID])->one();
    }*/
}
