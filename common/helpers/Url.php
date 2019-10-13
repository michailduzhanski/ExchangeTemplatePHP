<?php
namespace common\helpers;

use Yii;
class Url extends  \yii\helpers\Url
{
    public static function toWithoutLang($url = '', $scheme = false){
        $lang = Yii::$app->language;
        $url =  self::to($url, $scheme);
        $url = str_replace('/'.$lang.'/', '/', $url);

        return $url;
    }

}