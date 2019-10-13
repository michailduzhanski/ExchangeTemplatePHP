<?php


namespace common\modules\imageStorage\components;


use yii\helpers\ArrayHelper;

class ImageOwner
{
    /**
     * Конфиг профиля
     * @var array
     */
    public $ownerConfig;

    /**
     * Глобавльный конфиг
     * @var array
     */
    public $config;

    /**
     * Название профиля
     * @var string
     */
    public $name;

    /**
     * Конфиг превью
     * @var array
     */
    public $thumbs;

    /**
     * Конфиг изображения
     * @var array
     */
    public $imagick;

    /**
     * Правила валидации
     * @var array
     */
    public $rules;

    /**
     * Получить текущий owner config
     * @param $ownerName
     * @return $this|bool
     */
    public function open($ownerName)
    {
        if($owner = ArrayHelper::getValue($this->ownerConfig, $ownerName)){
            $this->name = $ownerName;
            $this->thumbs = ArrayHelper::getValue($owner, 'thumbs');
            $this->rules = ArrayHelper::getValue($owner, 'rules');
            $this->imagick = ArrayHelper::getValue($owner, 'imagick');
            return $this;
        }
        return false;
    }

    /**
     * Получить значение из конфига
     * @param $value
     * @return mixed
     */
    public function getValue($value)
    {
        return ArrayHelper::getValue($this->ownerConfig, $this->name . '.' .$value);
    }

    /**
     * Получить конфиг изображения
     * @return array
     */
    public function getConfig()
    {
        if(!$this->config) $this->config = [];
        if(!$this->imagick) $this->imagick = [];

        return array_merge($this->config, $this->imagick);
    }

}