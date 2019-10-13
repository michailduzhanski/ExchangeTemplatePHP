<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\lng\controllers;

use Yii;
use yii\web\Controller;
use common\modules\lng\models\LanguageKsl;

class DefaultController extends Controller {

    /**
     * Обрабатывает переход по ссылкам для смены языка
     * Перенаправляет на ту же страницу с новым URL.
     */
    public function actionIndex() {
        $language = Yii::$app->request->get('lang'); //язык на который будем менять
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new \yii\web\Cookie([
            'name' => 'language',
            'value' => $language,
        ]));

        /*
         * При перенаправлении сюда до разбора входящего запроса (метод run класса LanguageKsl)
         * передаем URL предыдущей страницы в get параметрах
         */
        $url_referrer = Yii::$app->request->get('url');
        /*
         * При перенаправлении сюда из виджета (нажатие по ссылке для смены языка)
         * получаем предыдущую страницу средствами Yii2
         */
        if (!$url_referrer)
            $url_referrer = Yii::$app->request->referrer; //предыдущая страница

            /*
             * Если все же предыдущая страница не получена - возвращаем на главную.
             */
        if (!$url_referrer)
            $url_referrer = Yii::$app->request->hostInfo . '/' . $language;

        //устанавливает/меняет метку языка
        $url = LanguageKsl::parsingUrl($language, $url_referrer);


        // перенаправление
        Yii::$app->response->redirect($url);
    }

}
