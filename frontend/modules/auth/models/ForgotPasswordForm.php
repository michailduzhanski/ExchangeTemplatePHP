<?php
namespace frontend\modules\auth\models;
use common\models\ResponsiveUserIdentity;
use common\models\User;
use frontend\behaviors\AntiBotBehavior;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Модель формы входа
 * Class LoginForm
 * @package frontend\modules\auth\models
 */
class ForgotPasswordForm extends \yii\base\Model
{
    public $login;

    public $reCaptcha;

    public $infoData;

    protected $user;

    public function behaviors()
    {
        return [
            'antiBot' => [
                'class' => AntiBotBehavior::class,
                'botField' => 'infoData'
            ]
        ];
    }

    public function rules()
    {
        return [
            [['login', 'reCaptcha', 'infoData'], 'safe'],
            ['login', 'validateLogin'],
            [['reCaptcha'], \himiklab\yii2\recaptcha\ReCaptchaValidator::class,  'uncheckedMessage' => Yii::t('frontend', 'Please confirm that you are not a bot.')]
        ];
    }

    public function attributeLabels()
    {
        return [
            'login' => Yii::t('forgot_password_page', 'Login'),
            'email' => Yii::t('forgot_password_page', 'Email'),
        ];
    }

    public function validateLogin($attribute, $params)
    {
        if(!$user = $this->getUser()){
            $this->addError($attribute, Yii::t('forgot_password_page', 'Incorrect login or email'));
        }
    }

    public function getError()
    {
        if($this->errors){
            $errors = '';
            foreach ($this->errors as $errorsGroup){
                foreach ($errorsGroup as $error){
                    $errors = $error . '<br/>';
                }
            }
        } else {
            $errors = Yii::t('app', 'Something wrong! Please try again!');
        }
        Yii::$app->session->setFlash('forgot_errors', $errors);
        return $errors;
    }

    protected function getUser()
    {
        if(!$this->user && $user = ResponsiveUserIdentity::findByAttribute('login', $this->login))
            $this->user = $user;

        return $this->user;
    }

    /**
     * Отправить ссылку с токеном на восстановление
     * @return bool
     * @throws BadRequestHttpException
     */
    public function sendResetToken()
    {
        if($user = $this->getUser()) {
            Yii::$app->session->set(
                'forgotPasswordKey',
                ResponsiveUserIdentity::generateForgotPasswordKey($user->getContact()->login)
            );
            Yii::$app->session->set(
                'forgotPasswordData',
                utf8_encode(Yii::$app->security->encryptByKey(json_encode([
                    'id' => $user->getId(),
                    'login' => $user->getContact()->login,
                ]), $user->getContact()->resetpasswordtoken))
            );
            $resetPasswordToken = $user->getContact()->resetpasswordtoken;
            $userEmail = $user->getContact()->emailconversation;
            if(Yii::$app->SendMail->resetPasswordToken($userEmail, $resetPasswordToken)){
                return $resetPasswordToken;
            }

            return false;
        } else {
            throw new BadRequestHttpException('Something wrong!');
        }

    }

}