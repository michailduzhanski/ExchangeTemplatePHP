<?php
namespace common\models;

use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "site_sprite".
 *
 * @property string $name
 * @property string $data
 *
 */
class SiteSprite extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site_sprite';
    }

    /*public function afterFind()
    {
        $this->data = json_decode($this->data, false);
    }*/

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)){
            //$this->data = json_encode($this->data);
            return true;
        }
        return false;
    }
}