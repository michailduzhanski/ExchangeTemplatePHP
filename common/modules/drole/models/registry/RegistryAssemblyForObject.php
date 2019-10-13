<?php

namespace common\modules\drole\models\registry;

use yii\db\ActiveRecord;

class RegistryAssemblyForObject extends ActiveRecord
{

    public static function tableName()
    {
        return '{{registry_assembly}}';
    }

    public static function getAllAssemblyesForObjectID($objectID)
    {
        return self::find()->where(['object_id' => $objectID])->all();
    }

    public static function getObjectNameByID($objectName)
    {
        return self::find()->where(['name' => $objectName])->all();
    }
}
