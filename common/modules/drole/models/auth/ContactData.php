<?php

namespace common\modules\drole\models\auth;

use yii\db\ActiveRecord;

class ContactData extends ActiveRecord
{

    public static function tableName()
    {
        return '{{contact_data_use}}';
    }

    public static function getContactDataByID($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    public static function findByUsername($username){
        return self::find()->where(['login' => $username])->one();
    }

    public static function findAllByUsername($username){
        return self::find()->where(['login' => $username])->all();
    }

}
