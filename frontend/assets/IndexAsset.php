<?php


namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

class IndexAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'index/css/bootstrap.min.css',
        'index/css/font-awesome.min.css',
        'index/css/style.css'
    ];
    public $js = [
        'index/js/bootstrap.min.js',
        'js/objectfieldeditor.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}