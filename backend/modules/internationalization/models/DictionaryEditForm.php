<?php


namespace backend\modules\internationalization\models;

use Yii;
use common\models\SiteLang;
use common\models\SiteLangDictionary;
use common\models\SiteLangCategory;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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
class DictionaryEditForm extends ActiveRecord
{

    public $user_language;

    public $textItems = [];

    protected $default_language;

    public function init()
    {
        parent::init();
        $this->user_language = SiteLang::getUserLanguage();
        $this->language = Yii::$app->language;
        $this->default_language = Yii::$app->getModule('lng')->default_language;
        $this->text_group = SiteLangDictionary::generateTextGroup();
        $this->textItems = $this->getTextItems();
    }

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
            [['textItems'], 'required'],
            [['category', 'language', 'text'], 'required'],
            [['text_group'], 'default', 'value' => null],
            [['text_group'], 'integer'],
            [['text'], 'string'],
            [['category'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 3],
            [['language'], 'exist', 'skipOnError' => true, 'targetClass' => SiteLang::class, 'targetAttribute' => ['language' => 'code']],
            [['category'], 'exist', 'skipOnError' => true, 'targetClass' => SiteLangCategory::class, 'targetAttribute' => ['category' => 'code']],
            [['id', 'language', 'category', 'text_group', 'text'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category' => 'Category',
            'language' => 'Language',
            'text_group' => 'Text Group',
            'text' => 'Text ' . ((isset($this->user_language->name)) ? '('.$this->user_language->name.')' : ''),
            'textItems' => 'Text'
        ];
    }

    public function getTextItems()
    {
        if($siblings = SiteLangDictionary::getSiblings($this->language, $this->category, $this->text_group)){
            foreach ($siblings as $sibling){
                $this->textItems[$sibling->language] = $sibling->text;
            }
        } else {
            if ($languages = SiteLang::getNotCurrent()) {
                $codes = ArrayHelper::getColumn($languages, 'code');
                foreach ($codes as $code) {
                    $this->textItems[$code] = '';
                }
            }
        }

        return $this->textItems;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->textItems = $this->getTextItems();
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)){
            foreach ($this->textItems as $code => $value){
                if(!$dictionary = SiteLangDictionary::getItem($code, $this->category, $this->text_group))
                    $dictionary = new SiteLangDictionary();

                $dictionary->language = $code;
                $dictionary->category = $this->category;
                $dictionary->text_group = $this->text_group;
                $dictionary->text = $value;
                $dictionary->save();
            }

            return true;
        }

        return false;
    }

    public function afterDelete()
    {
        parent::afterDelete();
        static::deleteAll(['category' => $this->category, 'text_group' => $this->text_group]);
    }
}