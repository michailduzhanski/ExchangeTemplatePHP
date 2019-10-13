<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "site_lang".
 *
 * @property string $code
 * @property string $locale
 * @property string $name
 * @property bool $default_lang
 *
 * @property SiteLangMessage[] $siteLangMessages
 */
class SiteLang extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site_lang';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'locale', 'name'], 'required'],
            [['default_lang'], 'boolean'],
            [['code'], 'string', 'max' => 3],
            [['locale', 'name'], 'string', 'max' => 255],
            [['code', 'locale'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('app', 'Code'),
            'locale' => Yii::t('app', 'Locale'),
            'name' => Yii::t('app', 'Name'),
            'default_lang' => Yii::t('app', 'Default Language'),
        ];
    }

    /**
     * @param $code 'en', 'ru', 'uk'
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getByCode($code)
    {
        return self::find()->where(['code' => $code])->one();
    }

    /**
     * Получить исходный язык
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getSource()
    {
        return self::getByCode(Yii::$app->sourceLanguage);
    }

    /**
     * Получить язык по умолчанию
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDefaultLanguage()
    {
        return self::find()->where(['default_lang' => 1])->all();
    }

    /**
     * Получить текущий язык пользователя
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getUserLanguage()
    {
        return self::getByCode(Yii::$app->language);
    }

    /**
     * Получить все языки
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAll()
    {
        return self::find()->orderBy(['default_lang' => SORT_DESC])->all();
    }

    /**
     * Получить все языки в виде массива
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllArray()
    {
        return self::find()->orderBy(['default_lang' => SORT_DESC])->asArray()->all();
    }

    /**
     * Получить языки все кроме текущего
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getNotCurrent()
    {
        return self::find()->where(['not', ['code' => Yii::$app->language]])->all();
    }

    /**
     * Получить все языки кроме языка по умолчанию
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getNotDefault()
    {
        return self::find()->where(['default_lang' => 0])->all();
    }

    /**
     * Получить название языка по коду
     * @param $code
     * @return mixed|string
     */
    public static function getNameByCode($code)
    {
        if($one = self::getByCode($code)){
            return $one->name;
        }

        return '';
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSiteLangDictionaries()
    {
        return $this->hasMany(SiteLangDictionary::class, ['language' => 'code']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        //Только один язык может быть по умолчанию
        if($this->default_lang == true) {
            static::updateAll(['default_lang' => 0], ['not', ['code' => $this->code]]);
        } else {
            static::updateAll(['default_lang' => 0]);
            $first = static::find()->one();
            static::updateAll(['default_lang' => 1], ['code' => $first->code]);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        //Если удален язык по умолчанию, поставить новый
        if($this->default_lang == true){
            $first = static::find()->one();
            static::updateAll(['default_lang' => 1], ['code' => $first->code]);
        }
    }
}
