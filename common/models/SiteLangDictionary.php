<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "site_lang_dictionary".
 *
 * @property int $id
 * @property string $category
 * @property string $language
 * @property int $text_group
 * @property string $text
 *
 * @property SiteLang $language0
 * @property SiteLangCategory $category0
 */
class SiteLangDictionary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site_lang_dictionary';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'language', 'text'], 'required'],
            [['text_group'], 'default', 'value' => null],
            [['text_group'], 'integer'],
            [['text'], 'string'],
            [['category'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 3],
            [['language'], 'exist', 'skipOnError' => true, 'targetClass' => SiteLang::className(), 'targetAttribute' => ['language' => 'code']],
            [['category'], 'exist', 'skipOnError' => true, 'targetClass' => SiteLangCategory::className(), 'targetAttribute' => ['category' => 'code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category' => Yii::t('app', 'Category'),
            'language' => Yii::t('app', 'Language'),
            'text_group' => Yii::t('app', 'Text Group'),
            'text' => Yii::t('app', 'Text'),
        ];
    }

    public static function generateTextGroup()
    {
        if($text_group = static::find()->max('text_group')){
            return $text_group + 1;
        }

        return 1;
    }

    public static function getItem($language, $category, $text_group)
    {
        return static::find()->where([
            'language' => $language,
            'category' => $category,
            'text_group' => $text_group
        ])->one();
    }

    public static function getSiblings($language, $category, $text_group)
    {
        return static::find()->where([
            'category' => $category,
            'text_group' => $text_group
        ])->andWhere(['not', ['language' => $language]])->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage0()
    {
        return $this->hasOne(SiteLang::class, ['code' => 'language']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory0()
    {
        return $this->hasOne(SiteLangCategory::class, ['code' => 'category']);
    }

}
