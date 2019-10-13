<?php


namespace common\components;

use Yii;
use yii\base\Component;
use common\helpers\Url;

class SendMail extends Component
{
    public $from;

    public function init()
    {
        if(!$this->from)
            $this->from = Yii::$app->params['serviceEmail'];
    }

    public function pinCode($emailTo, $code, $message = null)
    {
        if(!$message)
            $message = Yii::t('frontend', 'Pin Code') . ': ';

        $data = [
            'image' => Url::toWithoutLang(['/images/messages/mes-login.png'], true),
            'title' => 'Hi!',
            'code' => $code,
        ];

        $flag =  Yii::$app->mailer->compose('default',[
            'title' => Yii::t('frontend', 'Hysiope Pin Code'),
            'message' => $message,
            'data' => $data
        ])->setFrom([$this->from => 'Hysiope'])->setTo($emailTo)
            ->setSubject(Yii::t('frontend', 'Hysiope Pin Code'))
            ->send();        

        return $flag;
    }

    /**
     * Письмо с ссылкой на верификацию пользователя
     * @param $emailTo
     * @param $code
     * @return bool
     */
    public function verificationCode($emailTo, $code)
    {
        $data = [
            'image' => Url::toWithoutLang(['/images/messages/mes-reset.png'], true),
            'title' => 'Hi'
        ];

        $message = Yii::t('frontend', 'Thank you for signing up to Hysiope, to get started you will need to verify your email address.');
        $message .= '<br/><a href="'.Url::to(['/auth/default/user-verification', 'key'=> $code], true).'">';
        $message .= Yii::t('frontend', 'Verify My Email Address');
        $message .= '</a>';

        $flag =  Yii::$app->mailer->compose('default',[
            'title' => Yii::t('frontend', 'Hysiope Account Registration Confirmation'),
            'message' => $message,
            'data' => $data
        ])->setFrom([$this->from => 'Hysiope'])->setTo($emailTo)
            ->setSubject(Yii::t('frontend', 'Hysiope Account Registration Confirmation'))
            ->send();

        return $flag;
    }

    /**
     * Письмо восстановления пароля
     * @param $emailTo
     * @param $code
     * @return bool
     */
    public function resetPasswordToken($emailTo, $token)
    {
        $data = [
            'image' => Url::toWithoutLang(['/images/messages/mes-reset.png'], true),
            'title' => 'Hi'
        ];

        $message = Yii::t('frontend', 'You recently requested to reset your Hysiope account password.');
        $message .= '<br/><a href="' . Url::to(['/auth/default/reset-password', 'token' => $token], true).'">Reset password</a>';
        $message .= '</a>';

        $flag =  Yii::$app->mailer->compose('default',[
            'title' => Yii::t('frontend', 'Hysiope Resetting Your Password'),
            'message' => $message,
            'data' => $data
        ])->setFrom([$this->from => 'Hysiope'])->setTo($emailTo)
            ->setSubject(Yii::t('frontend', 'Hysiope Resetting Your Password'))
            ->send();

        return $flag;
    }

}