<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\lng\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class ListWidget extends Widget {

    public $array_languages;

    public function init() {
        $language = Yii::$app->language; //текущий язык
        //Создаем массив ссылок всех языков с соответствующими GET параметрами
        $array_lang = [];
        foreach (Yii::$app->getModule('lng')->languages as $key => $value) {
            $array_lang += [$value => Html::a($key, ['/lng/default/index', 'lang' => $value])];
        }

        //ссылку на текущий язык не выводим
        if (isset($array_lang[$language]))
            unset($array_lang[$language]);
        $this->array_languages = $array_lang;
    }

    public function run() {
        return $this->render('list', [
                    'array_lang' => $this->array_languages
        ]);
    }

}
