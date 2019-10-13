<?php

namespace common\modules\drole\models\registry;

use yii\db\ActiveRecord;

class RegistryClasses extends ActiveRecord {

    public static function tableName() {
        return '{{registry_classes}}';
    }

    public static function getObjectIDByName($objectName) {
        return self::find()->where(['name' => $objectName])->one();
    }

    public static function getObjectNameByID($objectName) {
        return self::find()->where(['id' => $objectName])->one();
    }

    public static function getAllObjects() {
        return self::find()->all();
    }

}
