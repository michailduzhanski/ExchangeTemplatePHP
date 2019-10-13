<?php
namespace common\modules\pinCode\widgets;

use yii\helpers\Url;

class SendCode extends  \yii\base\Widget
{
    public $sendUrlAction;

    public $message;


    public function init()
    {
        parent::init();
        if(!$this->sendUrlAction){
            $this->sendUrlAction = Url::to(['/pincode/default/send-code']);
        }
    }

    public function run()
    {
        return $this->render('index', [
            'id' => 'pinCode-'.$this->id,
            'url' => $this->sendUrlAction,
            'message' => $this->message,
            'widgetId' => $this->id,
            'timeLeft' => \Yii::$app->Pincode->getTimeLeft()
        ]);
    }
}