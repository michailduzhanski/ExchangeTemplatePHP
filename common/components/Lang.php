<?php

namespace common\components;

use Yii;
use yii\base\Component;

class Lang extends Component
{
    public $langModel = 'common\models\SiteLang';

    protected $sourceLanguage;

    protected $default;

    protected $current;

    public function getSourceLanguage()
    {
        if($this->sourceLanguage)
            return $this->sourceLanguage;
        return $this->sourceLanguage = Yii::$app->sourceLanguage;
    }

    public function setSourceLanguage($value)
    {
        $this->sourceLanguage = $value;
        Yii::$app->sourceLanguage = $value;
    }
}