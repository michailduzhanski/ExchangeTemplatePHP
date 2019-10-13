<?php
namespace common\modules\drole\models\registry;

use yii\db\ActiveRecord;

class RegistryObjects extends ActiveRecord
{

    public static function tableName()
    {
        return '{{registry_objects}}';
    }

    public static function getObjectIDByName($objectName)
    {
        return self::find()->where(['name' => $objectName])->one();
    }
    
    public static function getObjectNameByID($objectID)
    {
        return self::find()->where(['id' => $objectID])->one();
    }
}
