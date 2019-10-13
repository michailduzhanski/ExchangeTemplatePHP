<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "site_lang_categories".
 *
 * @property string $code
 * @property string $name
 *
 * @property SiteLangSourceMessage[] $siteLangSourceMessages
 */
class SiteLangCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site_lang_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code', 'name'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSiteLangDictionaries()
    {
        return $this->hasMany(SiteLangDictionary::class, ['category' => 'code']);
    }

    public static function getAllItems()
    {
        return ArrayHelper::map(static::find()->all(), 'code', 'name');
    }
}
