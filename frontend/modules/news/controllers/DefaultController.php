<?php
namespace frontend\modules\news\controllers;
use Yii;
use \yii\web\Controller;

class DefaultController extends Controller
{
    public $layout = '/index';

    public function actionIndex()
    {
		return $this->render('coinpage');
    }

    public function actionTopic()
    {
        return $this->render('store');
    }
}