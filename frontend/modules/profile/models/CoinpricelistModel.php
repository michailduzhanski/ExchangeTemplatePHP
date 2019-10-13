<?php
namespace frontend\modules\profile\models;


use yii\db\ActiveRecord;

class CoinpricelistModel extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'coinpricelist_data_use';
    }

    public static function getPrice($id)
    {
        if($model = static::findOne($id)){
            return $model->price;
        }

        return 0;
    }

}