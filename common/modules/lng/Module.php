<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\lng;

use Yii;
use Codeception\Exception\ConfigurationException;
use common\models\SiteLang;
use common\models\SiteLangDictionary;
use common\modules\lng\models\LanguageKsl;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module implements BootstrapInterface {

    public $controllerNamespace = 'common\modules\lng\controllers';
    public $languages; //Языки используемые в приложении
    public $default_language; //основной язык (по-умолчанию)
    public $show_default; //показывать в URL основной язык

    public function init()
    {
        parent::init();
        //Получаем текущий язык пользователя из cookie
        if($this->default_language = Yii::$app->request->cookies->getValue('language')){
            Yii::$app->language = $this->default_language;
        }

        //Получаем текущий язык пользователя из данных авторизации
        if(!$this->default_language && !Yii::$app->user->isGuest){
            $userAuth = Yii::$app->user->identity->getContactAuth();
            Yii::$app->language = $userAuth->lang;
            $this->default_language = $userAuth->lang;
        }

        if(!$this->languages || !$this->default_language){
            $language = SiteLang::getAllArray();
            $this->languages = ArrayHelper::map($language, 'name', 'code');

            if(!$this->default_language){
                $default_lang = array_filter($language, function ($v, $k) {
                    if (isset($v['default_lang']) && $v['default_lang'] == true)
                        return true;
                }, ARRAY_FILTER_USE_BOTH);
                $default_lang = array_shift($default_lang);
                if($default_lang && isset($default_lang['code'])){
                    $this->default_language = $default_lang['code'];
                }
            }
        }
        if(!$this->languages || !$this->default_language)
            throw new ConfigurationException('Languages and default_languages must be set');
    }

    /*
     * Предзагрузка - выполнится до обработки входящего запроса.
     * Устанавливает язык приложения в зависимости от метки языка в URL,
     * а при ее отсутствии устанавливает в качестве метки текущий язык
     */

    public function bootstrap($app) {

        if (YII_ENV == 'test') {
            return; //для тестового приложения отключаем.
        }

        $url = $app->request->url;

        //Получаем список языков в виде строки
        $list_languages = LanguageKsl::list_languages();


        preg_match("#^/($list_languages)(.*)#", $url, $match_arr);

        //Если URL содержит указатель языка - сохраняем его в параметрах приложения и используем
        if (isset($match_arr[1]) && $match_arr[1] != '/' && $match_arr[1] != '') {

            /*
             * Если в настройках выбрано не показывать язык используемый по-умолчанию
             * убираем метку текущего языка из URL и перенаправляем на ту же страницу
             */
            if (!$this->show_default && $match_arr[1] == $this->default_language) {
                $url = $app->request->absoluteUrl; //Возвращает абсолютную ссылку
                $lang = $this->default_language; //язык используемый по-умолчанию
                //echo "1. try request to: " . $url;
                //exit;
                $app->response->redirect(['lng/default/index', 'lang' => $lang, 'url' => $url]);
            }

            $app->language = $match_arr[1];
            $app->formatter->locale = $match_arr[1];
            $app->homeUrl = '/' . $match_arr[1];

            /*
             * Если URL не содержит указатель языка и отключен показ основного языка в URL
             */
        } elseif (!$this->show_default) {

            $lang = $this->default_language; //язык используемый по-умолчанию

            $app->language = $lang;
            $app->formatter->locale = $lang;

            /*
             * Если URL не содержит указатель языка, а в настройках включен показ основного языка
             */
        } else {
            $url = $app->request->absoluteUrl; //Возвращает абсолютную ссылку

            $lang = $this->default_language;
            //echo "2. try request to: " . $url;
            //exit;
            $app->response->redirect(['lng/default/index', 'lang' => $lang, 'url' => $url], 301);
        }
    }

}
