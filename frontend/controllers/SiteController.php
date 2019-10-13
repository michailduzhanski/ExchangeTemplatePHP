<?php

namespace frontend\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{

    public $layout = 'index';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'denyCallback' => function ($rule, $action) {
                    return $this->goHome();
                },
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['?', '@'],
                        'actions' => ['clisting']
                    ]
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'layout' => 'auth'
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionClisting()
    {
        return $this->render('partnership/coinlisting');
    }


    /**
     * Главная страница
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
