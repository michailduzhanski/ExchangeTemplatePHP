<?php

namespace frontend\modules\auth\models;

use common\models\ResponsiveUserIdentity;
use frontend\behaviors\AntiBotBehavior;
use Yii;
use yii\base\Model;

/**
 * Модель формы входа
 * Class LoginForm
 * @package frontend\modules\auth\models
 */
class LoginForm extends Model
{
    const SCENARIO_LOGIN = 'login';

    //вход через форму с пинкодом
    const SCENARIO_SIGNIN = 'signin';

    public $login;

    public $password;

    public $reCaptcha;

    public $rememberMe = true;

    public $infoData;

    public $pincode;

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
            [['login', 'password', 'reCaptcha', 'infoData', 'pincode'], 'safe', 'on' => self::SCENARIO_DEFAULT],
            [['login', 'password'], 'safe', 'on' => self::SCENARIO_LOGIN],
            [['login', 'password'], 'required'],
            ['password', 'validatePassword'],
            //[['reCaptcha'], \himiklab\yii2\recaptcha\ReCaptchaValidator::class,  'uncheckedMessage' => Yii::t('frontend', 'Please confirm that you are not a bot.'), 'on' => self::SCENARIO_DEFAULT],
            [['pincode'], 'safe'],
            [['login', 'password', 'pincode'], 'safe', 'on' => self::SCENARIO_SIGNIN],
            [['login', 'password', 'pincode'], 'required', 'on' => self::SCENARIO_SIGNIN],
            ['pincode', 'validatePincode', 'on' => self::SCENARIO_SIGNIN],
        ];
    }

    public function attributeLabels()
    {
        return [
            'login' => Yii::t('login_page', 'Login'),
            'password' => Yii::t('login_page', 'Password'),
            'pincode' => Yii::t('login_page', 'Pin Code')
        ];
    }

    public function validatePincode($attribute, $params)
    {
        $user = $this->getUser();
        if($this->{$attribute} != $user->getPincode()){
            $this->addError($attribute, Yii::t('frontend', 'Invalid Pin Code'));
        }
    }

    public function validatePassword($attribute, $params) {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('frontend', 'Incorrect username or password.'));
            }
        }
    }

    public function signIn()
    {
        if($user = $this->getUser()){
            Yii::$app->session->set('reg', [
                'login' => $this->login,
                'password' => $this->password
            ]);

            $user->user->pincode = Yii::$app->security->generateRandomString(8);
            $user->user->save();
            Yii::$app->SendMail->pinCode(
                $user->user->emailconversation,
                $user->user->pincode,
                Yii::t('frontend', 'Pin code to login to your account: ')
            );

            return true;
        }

        return false;
    }

    public function login()
    {
        if($user = $this->getUser()){
            if($this->validate()) {
                return Yii::$app->user->login($user, $this->rememberMe ? 86400 : 0);
            }
        }

        return false;
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
        Yii::$app->session->setFlash('errors', Yii::t('app', $errors));
        return $errors;
    }

    protected function getUser() {
        if ($this->user === null) {
            $this->user = ResponsiveUserIdentity::findByAttribute('login', $this->login);
        }
        return $this->user;
    }

}