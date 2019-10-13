<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/bootstrap.min.css',
        'css/font-awesome.min.css',
        'css/perfect-scrollbar.min.css',
        'css/dash.min.css',
        'css/animate.css',
        'css/jquery.dataTables.min.css',
        'css/simpleSelect.css',
        'css/star-rating.min.css',
        'css/style.css'
    ];
    public $js = [
        //'js/jquery.min.js',
        'js/popper.min.js',
        'js/bootstrap.min.js',
        'js/chart.min.js',
        'js/perfect-scrollbar.jquery.min.js',
        'js/off-canvas.js',
        'js/hoverable-collapse.js',
        'js/misc.js',
        'js/chart.js',
        'js/jquery.mousewheel.min.js',
        'js/clipboard.min.js',
        'js/objectfieldeditor.js',
        'js/imagestorage.js',
        'js/jquery.dataTables.min.js',
        'js/dataTables.fixedHeader.min.js',
        'js/star-rating.min.js',
        'js/jquery.isotope.min.js',
        'js/script.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'common\modules\spriteImage\assets\SpriteAsset'
    ];
}
