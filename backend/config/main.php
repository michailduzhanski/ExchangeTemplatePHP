<?php
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

$params = array_merge(require __DIR__ . '/../../common/config/params.php', require __DIR__ . '/../../common/config/params-local.php', require __DIR__ . '/params.php', require __DIR__ . '/params-local.php');

return [
    'id' => 'app-backend',
    'name' => 'Hysiope.com',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'sourceLanguage' => 'en',
    'language' => 'en',
    'homeUrl' => '/',
    'as beforeRequest' => [
        'class' => AccessControl::class,
        'rules' => [
            [
                'allow' => true,
                'roles' => ['@'],
            ],
            [
                'actions' => ['signup', 'login'],
                'allow' => true,
                'roles' => ['?'],
            ],
        ],
    ],
    'modules' => [
        'internationalization' => [
            'class' => 'backend\modules\internationalization\Internationalization',
        ],
        'textfield' => [
            'class' => 'backend\modules\textfield\TextField',
        ],
        'lng' => [
            'class' => 'common\modules\lng\Module',
            //Языки используемые в приложении
            'languages' => [
                'English' => 'en',
                'Русский' => 'ru',
                'Українська' => 'uk',
            ],
            'default_language' => 'en', //основной язык (по-умолчанию)
            'show_default' => true, //true - показывать в URL основной язык, false - нет
        ],
    ],
    'bootstrap' => [
        'log',
        'lng',
    ],
    'components' => [
		'assetManager' => [
			'bundles' => [
				/* 'yii\web\JqueryAsset' => [
					'js'=>[]
				], */
				'yii\bootstrap\BootstrapPluginAsset' => [
					'js'=>[]
				],
				'yii\bootstrap\BootstrapAsset' => [
					'css' => [],
				],
			],
		],
        'reCaptcha' => [
            'name' => 'reCaptcha',
            'class' => 'himiklab\yii2\recaptcha\ReCaptcha',
            'siteKey' => '6LdAJFIUAAAAAGyYcSgQA-DYrEzo2O4gvS15H8_w',
            'secret' => '6LdAJFIUAAAAAGegbBHC2XgHH_mYDHTd304ifp84',
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'baseUrl' => '',
            'class' => 'common\components\Request',
        ],
        'user' => [
            'identityClass' => 'common\models\ResponsiveUserIdentity',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity-backend',
                'httpOnly' => true
            ],
            'on afterLogin' => function($event) {
        Yii::$app->user->identity->appendLog($event);
    },
            'on beforeLogout' => function($event) {
        Yii::$app->user->identity->logout();
    }
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => [
                        'error',
                        'warning'
                    ],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'class' => 'common\components\UrlManager',
            'rules' => [
                'lng' => 'lng/default/index',
                '' => 'site/index',
                '<action>' => 'site/<action>',
                '<controller>/<action>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];
