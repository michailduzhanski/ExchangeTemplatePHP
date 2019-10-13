<?php

$params = array_merge(require __DIR__ . '/../../common/config/params.php', require __DIR__ . '/../../common/config/params-local.php', require __DIR__ . '/params.php', require __DIR__ . '/params-local.php');

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'sourceLanguage' => 'en',
    'language' => 'en',
    'homeUrl' => '/',
    'as access' => [
        'class' => \yii\filters\AccessControl::class,
        'rules' => [
            [
                'controllers' => ['site'],
                'actions' => ['clisting'],
                'roles' => ['?', '@'],
                'allow' => true,
            ],
            [
                'controllers' => ['profile/default'],
                'actions' => ['exchange', 'coinlist'],
                'roles' => ['?', '@'],
                'allow' => true,
            ],
            [
                'controllers' => ['news/default'],
                'roles' => ['?', '@'],
                'allow' => true,
            ],
            [
                'controllers' => ['drole/default'],
                'roles' => ['?', '@'],
                'actions' => ['exchange', 'get-info'],
                'allow' => true,
            ],
            [
                'controllers' => ['drole/default'],
                'roles' => ['?', '@'],
                'actions' => ['trunks', 'chart', 'pmdeposit', 'getmarketpair'],
                'allow' => true,
            ],
            [
                'controllers' => ['lng/default'],
                'allow' => true,
            ],
            [
                'actions' => ['error'],
                'allow' => true,
            ],
            [
                'controllers' => ['site'],
                'roles' => ['?'],
                'allow' => true,
            ],
            [
                'controllers' => ['auth/default'],
                'actions' => ['registration', 'login', 'forgot-password', 'reset-password'],
                'roles' => ['?'],
                'allow' => true,
            ],
            [
                'controllers' => ['auth/default'],
                'actions' => ['user-verification', 'logout'],
                'roles' => ['@'],
                'allow' => true,
            ],
            [
                'controllers' => ['image-storage/default'],
                'actions' => ['open-image-dir', 'open-image-cache', 'no-photo-image'],
                'roles' => ['@', '?'],
                'allow' => true,
            ],
            [
                'controllers' => ['sprite-image/default'],
                //'actions' => ['open-image-dir', 'open-image-cache', 'no-photo-image'],
                'roles' => ['@', '?'],
                'allow' => true,
            ],
            [
                'allow' => true,
                'roles' => ['@'],
                'matchCallback' => function ($rule, $action) {
                    $user = Yii::$app->user->identity;
                    Yii::$app->errorHandler->errorAction = '/profile/default/error';
                    if ($user->getContact()->verification != \common\models\User::STATUS_VERIFED) {
						
                        Yii::$app->session->setFlash('error', 'verification');

                        //TODO удалить отображение ссылки
                        Yii::$app->session->setFlash('activate_link', \yii\helpers\Html::a('Activate', ['/auth/default/user-verification', 'key' => $user->getContact()->emailverificationtoken]));
                        $email = (Yii::$app->session->get('registration_email')) ?
                            Yii::$app->session->get('registration_email') :
                            Yii::$app->user->identity->getContact()->emailconversation;
						
                        throw new \yii\web\ForbiddenHttpException(
                            Yii::t('frontend', 'An email has been sent to {email} please click the activation link in the email to complete your registration process.', [
                                'email' => $email, 
                            ])
                        );

                    }
                    return true;
                }
            ],
        ],
    ],
    'on beforeRequest' => function () {
        $request = Yii::$app->request->getAbsoluteUrl();
        $request .= Yii::$app->request->getQueryString();
        Yii::$app->requestSecurity->checkrequest($request);

        if(!Yii::$app->user->isGuest){
            Yii::$app->homeUrl = \yii\helpers\Url::to([Yii::$app->params['homeUrl']]);
            Yii::$app->errorHandler->errorAction = '/profile/default/error';
        }
        $pathInfo = Yii::$app->request->pathInfo;        
            $query = Yii::$app->request->queryString;
            if (!empty($pathInfo) && substr($pathInfo, -1) === '/') {
                $url = '/' . substr($pathInfo, 0, -1);
                if ($query) {
                    $url .= '?' . $query;
                }
                Yii::$app->response->redirect($url, 301)->send();
                Yii::$app->end();
            }        
    },
    'modules' => [
        'lng' => [
            'class' => 'common\modules\lng\Module',
            //Языки используемые в приложении
           /* 'languages' => [
                'English' => 'en',
                'Русский' => 'ru',
                'Українська' => 'uk',
            ],
            'default_language' => 'en', //основной язык (по-умолчанию)
           */
            'show_default' => true, //true - показывать в URL основной язык, false - нет
        ],
        'auth' => [
            'class' => 'frontend\modules\auth\Auth',
        ],
        'profile' => [
            'class' => 'frontend\modules\profile\Profile',
        ],
        'news' => [
            'class' => 'frontend\modules\news\News'
        ],
        'day-night-mode' => [
            'class' => 'frontend\modules\dayNightMode\Module'
        ],
        'droleYii' => [
            'class' => 'frontend\modules\droleYii\droleYii'
        ],
        'sprite-image' => [
            'class' => 'common\modules\spriteImage\Module'
        ]
    ],
    'bootstrap' => [
        'log',
        'lng',
    ],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'reCaptcha' => [
            'name' => 'reCaptcha',
            'class' => 'himiklab\yii2\recaptcha\ReCaptcha',
            'siteKey' => '6LdAJFIUAAAAAGyYcSgQA-DYrEzo2O4gvS15H8_w',
            'secret' => '6LdAJFIUAAAAAGegbBHC2XgHH_mYDHTd304ifp84',
        ],
        'request' => [
            'baseUrl' => '',
            'class' => 'common\components\Request',
        ],
        'user' => [
            'identityClass' => 'common\models\ResponsiveUserIdentity',
            'enableAutoLogin' => true,
            'loginUrl' => ['/auth/default/login'],
            'identityCookie' => [
                'name' => '_identity-frontend',
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
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
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
                '' => 'site/index',
                'lng' => 'lng/default/index',
                'login' => 'auth/default/login',
                'forgot-password' => 'auth/default/forgot-password',
                'reset-password' => 'auth/default/reset-password',
                'registration' => 'auth/default/registration',
                'logout' => 'auth/default/logout',
                'profile' => 'profile/default/index',
                'news/<action>' => 'news/default/<action>',
                '<action>' => 'site/<action>',
                '<controller>/<action>' => '<controller>/<action>',
            ],
        ],
        'assetManager' => [
        	'forceCopy' => false,
            'appendTimestamp' => true,
            'linkAssets' => true,
            'bundles' => [
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js'=>[]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [],
                ]
            ],
        ],
        'requestSecurity' => [
            'class' => 'common\components\RequestSecurity'
        ]
    ],
    'params' => $params,
];
