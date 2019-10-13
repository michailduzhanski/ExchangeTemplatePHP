<?php
namespace frontend\modules\auth\models;

use common\models\ResponsiveUserIdentity;
use common\models\ServiceDataUse;
use common\models\User;
use common\modules\drole\models\auth\ContactData;
use frontend\behaviors\AntiBotBehavior;
use Yii;
use yii\base\Model;

/**
 * Модель формы регистрации
 * Class RegisterForm
 * @package frontend\modules\auth\models
 */
Class RegisterForm extends Model{

    public $login;

    public $email;

    public $password;

    public $confirm_password;

    public $sponsor;

    public $agree;

    public $reCaptcha;

    public $infoData;

    protected $user;

    protected $sponsorID;

    public function init()
    {
        $this->sponsor = Yii::$app->request->get('sponsor');
    }

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
            [['login', 'sponsor', 'email', 'password', 'confirm_password', 'agree', 'reCaptcha', 'infoData'], 'safe'],
            [['login', 'email', 'password', 'confirm_password'], 'required'],
            [
                ['agree'], 'compare', 'compareValue' => 1,
                'message' => Yii::t('frontend', 'You must accept the terms and conditions to register.')
            ],
            ['login', 'match', 'pattern' => '/^[a-z0-9]\w*$/', 'message' => Yii::t('frontend', 'Only lowercase latin symbols and numbers')],
            ['login', 'unique', 'targetClass' => ContactData::class],
            ['login', 'string', 'min' => 6, 'max' => 12],
            ['password', 'string', 'min' => 8],
            ['email', 'email'],
            [
                'password',
                'match',
                'pattern' => '/(?=.*[A-Z])/',
                'message' => Yii::t('frontend', 'Must have minimum one uppercase letter')
            ],
            [
                'password',
                'match',
                'pattern' => '/(?=.*[a-z])/',
                'message' => Yii::t('frontend', 'Must have minimum one lovercase lowercase')
            ],
            [
                'password',
                'match',
                'pattern' => '/(?=.*\d)/',
                'message' => Yii::t('frontend', 'Must have minimum one number')
            ],
            [
                'password',
                'match',
                'pattern' => '/(?=.*[$@$!%*?&])/',
                'message' => Yii::t('frontend', 'Must have minimum one special symbol $@$!%*?&')
            ],
            ['confirm_password', 'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t('frontend', 'Passwords don\'t match')],
            [['reCaptcha'], \himiklab\yii2\recaptcha\ReCaptchaValidator::class,  'uncheckedMessage' => Yii::t('frontend', 'Please confirm that you are not a bot.')],
            ['sponsor', 'validateSponsor']
        ];
    }

    public function validateSponsor($attribute, $params)
    {
        if($this->{$attribute}){
            $sponsor = ContactData::find()->where(['login' => $this->{$attribute}])->one();
            if(!$sponsor){
                $this->addError($attribute, 'Sponsor not found');
            } else {
                $this->sponsorID = $sponsor->id;
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'login' => Yii::t('registration_page', 'Username'),
            'email' => Yii::t('registration_page', 'Email'),
            'password' => Yii::t('registration_page', 'Password'),
            'confirm_password' => Yii::t('registration_page', 'Confirm password'),
            'sponsor' => Yii::t('registration_page', 'Sponsor'),
        ];
    }


    /**
     * Зарегистрировать пользователя
     * @return null
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        if(Yii::$app->session->isActive) {
            Yii::$app->session->regenerateID(true);
        }

        $user = new User();
        $user->email = $this->email;
        $user->sponsor = $this->sponsor;
        $user->sponsorID = $this->sponsorID;
        $user->login = $this->login;
        $user->password = $this->password;
        $user->dRole = User::ROLE_REGISTRANT;
        if($user->save()){
            $verificationCode = $user->emailVerificationToken;
            Yii::$app->SendMail->verificationCode($user->email, $verificationCode);
            Yii::$app->session->set('registration_email', $this->email);
            return true;
        }

        return false;
    }

    protected function getUser() {
        if ($this->user === null) {
            $this->user = ResponsiveUserIdentity::findByAttribute('login', $this->login);
        }
        return $this->user;
    }

    public function getSponsorID()
    {
        return $this->sponsorID;
    }
}