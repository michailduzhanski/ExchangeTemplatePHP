<?php

namespace common\modules\imageStorage\components;


use yii\base\Exception;
use yii\helpers\ArrayHelper;

class ImageItem
{
    /**
     * Название профиля
     * @var string
     */
    public $name;

    /**
     * Путь к кеш-дирректории картинок
     * @var string
     */
    public $cachePath;

    /**
     * Полный путь к картинкам
     * @var string
     */
    public $fullPath;

    /**
     * Класс библиотке с функциями обработки картинки
     * @var string
     */
    public $imageProcessClass = 'common\modules\imageStorage\components\ImageProcess';

    /**
     * Глобальный конфиг
     * @var array
     */
    public $config;

    /**
     * Текущая картинка на обработку
     * @var \Imagick
     */
    protected $item;

    /**
     * Открыть картинку для обработки
     * @param $fullPath
     */
    public function open($fullPath)
    {
        $this->item = new \Imagick($fullPath);
    }

    /**
     * Создать превьюшки
     * @param $config
     * @return mixed
     */
    public function createThumbs($config)
    {
        $response = [];
        foreach ($config as $thumbName => $item){
            if(isset($item['size'][0]) && isset($item['size'][1])){
                $pocess = \Yii::createObject([
                    'class' => $this->imageProcessClass,
                    'item' => $this->item
                ]);

                $operations = $this->getThumbOperation($item);
                if(in_array('smartResize', $operations)){
                    $pocess->smartResize($item['size'][0], $item['size'][1]);
                } else {
                    $pocess->thumbnail($item['size'][0], $item['size'][1]);
                }

                $info = pathinfo($this->fullPath);

                if(isset($info['extension'])){
                    $ext = strtolower($info['extension']);
                }

                $response[$thumbName] = $pocess->save($this->cachePath, $this->name . '_'. $thumbName .'.' . $ext);
            }
        }

        return $response;
    }

    /**
     * Получить список операций над картинкой
     * @param $thumbConfig
     * @return array|mixed
     */
    public function getThumbOperation($thumbConfig)
    {
        $data = ArrayHelper::getValue($thumbConfig, 'operations');
        if($data)
            return $data;
        else return [];
    }


    public function createNewThumb($filePath, $newFilePath, $thumbConfig, $size)
    {
        if(!isset($thumbConfig[$size])){
            throw new Exception('Not found "' . $size. '" size in "'. $this->name .'"');
        }
        if(!file_exists($filePath)){
            return false;
        }

        $this->open($filePath);

        $thumbSizeConfig = $thumbConfig[$size];
        $operations = $this->getThumbOperation($thumbSizeConfig);
        $pocess = $this->createThumb($thumbSizeConfig['size'][0], $thumbSizeConfig['size'][1], $operations);
        $pathInfo = pathinfo($newFilePath);

        return $pocess->save($pathInfo['dirname'], $pathInfo['basename']);
    }

    public function createThumb($width, $height, $operations)
    {
        $pocess = \Yii::createObject([
            'class' => $this->imageProcessClass,
            'item' => $this->item
        ]);

        if(in_array('smartResize', $operations)){
            $pocess->smartResize($width, $height);
        } else {
            $pocess->thumbnail($width, $height);
        }

        return $pocess;
    }

}