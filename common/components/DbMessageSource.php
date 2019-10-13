<?php

namespace common\components;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class DbMessageSource extends \yii\i18n\DbMessageSource
{

    public $dictionary_table = '{{site_lang_dictionary}}';

    public function loadMessagesFromDb($category, $language)
    {
        $subquery1 = (new Query())->select('*')
            ->from($this->dictionary_table)
            ->where(['category' => $category, 'language' => $this->sourceLanguage]);
        $subquery2  = (new Query())->select('*')
            ->from($this->dictionary_table)
            ->where(['category' => $category, 'language' => $language]);

        $mainQuery = (new Query())->select(['message' => 's.text', 'translation' => 't.text'])
            ->from(['s' => $subquery1, 't' => $subquery2])
            ->where('s.text_group = t.text_group');

        $messages = $mainQuery->createCommand($this->db)->queryAll();

        return ArrayHelper::map($messages, 'message', 'translation');
    }
}