<?php
namespace common\modules\pinCode\components;

use Yii;

class Pincode extends \yii\base\Component
{
    private $user;

    /**
     * @var int
     */
    public $sendInterval = 30;

    public function init()
    {
        $this->user = Yii::$app->user->identity::$userModel::findOne(Yii::$app->user->id);
    }

    public function getCode()
    {
        if($this->user && isset($this->user->pincode)){
            return $this->user->pincode;
        }

        return null;
    }

    public function generate()
    {
        return Yii::$app->security->generateRandomString(8);
    }

    public function regenerate($sendEmail = true, $canSendCheck = true)
    {
        $session = Yii::$app->session;

        if($canSendCheck && !$this->canSend()){
            return null;
        }

        $code = $this->generate();
        $this->user->pincode = $code;
        if($this->user->save()){
            if($sendEmail){
                Yii::$app->SendMail->pinCode($this->user->emailconversation, $code);
            }

            if($this->sendInterval !== 0){
                $time = strtotime('now') + $this->sendInterval;
                $session->set('pin_send_time', $time);
            }
            return $code;
        }

        return null;
    }

    public function canSend()
    {
        $pinSendTime = $this->getPinSendTime();
        if(!$pinSendTime)
            return true;
        if($this->sendInterval == 0)
            return true;
        if($pinSendTime){
            if($pinSendTime < strtotime('now')){
                return true;
            }
        }

        return false;
    }

    public function getPinSendTime()
    {
        return (int)Yii::$app->session->get('pin_send_time');
    }

    public function getTimeLeft()
    {
        if($pinSendTime = $this->getPinSendTime()){
            $time = (int)$pinSendTime - strtotime('now');
            return $time;
        }

        return 0;
    }

}