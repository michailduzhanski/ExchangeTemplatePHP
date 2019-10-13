<?php
namespace common\modules\pinCode\controllers;
use Yii;
use \yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionSendCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $json = [];

        if(!Yii::$app->Pincode->canSend()){
            $json['time_left'] = Yii::$app->Pincode->getTimeLeft();
            $json['message'] = Yii::t('frontend', 'Sent') .
                ' (<span class="time-left">'.$json['time_left'].'</span>)';
            $json['message_end'] = Yii::t('frontend', 'Send e-mail code');
            $json['status'] = 'error';
            return $json;
        }

        $code = Yii::$app->Pincode->regenerate(false);
        $email = Yii::$app->user->identity->getEmail();
        $message = Yii::$app->request->post('message');

        if($code && $email){
            if(Yii::$app->SendMail->pinCode($email, $code, $message)){
                $json['time_left'] = Yii::$app->Pincode->getTimeLeft();
                $json['status'] = 'success';
                $json['message'] = Yii::t('frontend', 'Sent') .
                    ' (<span class="time-left">'.Yii::$app->Pincode->sendInterval.'</span>)';
                $json['message_end'] = Yii::t('frontend', 'Send e-mail code');

                return $json;
            }
        }

        $json['status'] = 'error';


        return $json;
    }
}