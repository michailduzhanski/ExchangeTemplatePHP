<?php
namespace common\modules\drole\registry;

use yii\db\ActiveRecord;

class DynamicAssemblyForObject extends ActiveRecord
{

    //constants
    private static $registryTable = 'registry_drole_assembly';
    /*private static $companyObjectName = 'company';
    private static $serviceObjectName = 'service';
    private static $roleObjectName = 'role';*/

    public static function tableName()
    {
        return '{{' . self::$registryTable . '}}';
    }
}