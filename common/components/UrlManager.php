<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

/**
 * Description of UrlManager
 *
 * @author LILIYA
 */
use Yii;

class UrlManager extends \yii\web\UrlManager {

    public function createUrl($params) {

        //Получаем сформированную ссылку(без идентификатора языка)
        $url = parent::createUrl($params);
        //echo "start url: " . $url . "                                              ";
        if (empty($params['lang'])) {
            //текущий язык приложения
            $curentLang = Yii::$app->language;

            //Добавляем к URL префикс - буквенный идентификатор языка
            if ($url == '/') {
                return '/' . $curentLang;
            } else {
                return '/' . $curentLang . $url;
            }
        };
        //echo "end url: " . $url; exit;
        return $url;
    }

}
