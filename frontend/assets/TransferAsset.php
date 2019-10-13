<?php


namespace frontend\assets;

use yii\web\AssetBundle;

class TransferAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [

    ];
    public $js = [
        'socket.io.js',
        'simpleSelect.js',
        'bignumber.js',
        'jquery-ui.js',
    ];
}