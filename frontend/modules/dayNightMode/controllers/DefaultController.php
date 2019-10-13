<?php
namespace frontend\modules\dayNightMode\controllers;

use Yii;
use \common\modules\drole\models\auth\CompaniesContactDataUse;

class DefaultController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $this->enableCsrfValidation = false;
        $mode = Yii::$app->request->post('mode');

        CompaniesContactDataUse::setContactDataByID('nightmode', $mode);

        if(Yii::$app->request->referrer)
            return $this->redirect(Yii::$app->request->referrer);
        else
            return $this->goHome();
    }

}