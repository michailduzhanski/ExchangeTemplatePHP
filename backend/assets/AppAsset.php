<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/bootstrap.min.css',
        'css/animate.min.css',
        'css/perfect-scrollbar.min.css',
        'css/light-bootstrap-dashboard.css',
        'css/font-awesome.min.css',
        'css/pe-icon-7-stroke.css',
        'css/style.css',
    ];
    public $js = [
		//'js/jquery.3.2.1.min.js',
		'js/bootstrap.min.js',
		'js/jquery-ui.min.js',
		'js/perfect-scrollbar.jquery.min.js',
		'js/bootstrap-notify.js',
		'js/light-bootstrap-dashboard.js',
		'js/clipboard.min.js',
        'js/objectfieldeditor.js',
        'js/jquery.waterfall.js',
        'js/autocomplete_custom.js',
        'js/jquery.androidSpinner.js'
		//'js/scripts.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
