<?php

namespace common\modules\spriteImage\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
    public function init()
    {
        parent::init();
    }

    public function actionIndex()
    {
        if(Yii::$app->SpriteImage->create('coins')){
            echo 'generated';
        } else {
            echo 'not generated';    
        }
        
        exit;
    }

    public function actionOpenImage($path)
    {
        $path = Yii::getAlias('@root/data/sprites/'.$path);
        if (file_exists($path)) {
            $imginfo = getimagesize($path);
            if (isset($imginfo['mime'])) {
                header("Content-type: " . $imginfo['mime']);
                echo readfile($path);
                exit;
            }
        }

        throw new NotFoundHttpException();
    }
}