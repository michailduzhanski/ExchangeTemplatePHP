<?php


namespace frontend\modules\auth\models;

use common\models\ResponsiveUserIdentity;
use common\models\User;
use Yii;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $password;

    public $confirm_password;

    public $token;

    protected $forgotPasswordData;

    public function rules()
    {
        return [
            [['password', 'confirm_password', 'token'], 'safe'],
            [['password', 'confirm_password'], 'required'],
            ['password', 'string', 'min' => 8],
            [
                'password',
                'match',
                'pattern' => '/(?=.*[A-Z])/',
                'message' => Yii::t('frontend', 'Must have minimum one uppercase letter')
            ],
            [
                'password',
                'match',
                'pattern' => '/(?=.*[A-Z])/',
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
                'message' => Yii::t('frontend', 'Passwords don\'t match')]
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => Yii::t('frontend', 'New password'),
            'confirm_password' => Yii::t('frontend', 'Confirm password')
        ];
    }

    public function getForgotPasswordData()
    {
        if(!$this->forgotPasswordData){
            if($forgotPasswordData = Yii::$app->session->get('forgotPasswordData')){
                if($forgotPasswordData = Yii::$app->security->decryptByKey(utf8_decode($forgotPasswordData), $this->token)){
                    $this->forgotPasswordData = json_decode($forgotPasswordData);
                }
            }
        }

        return $this->forgotPasswordData;
    }

    /**
     * Обновить пароль
     * @return bool
     */
    public function updatePassword()
    {
        if($forgotPasswordData = $this->getForgotPasswordData()){
            if ($user = ResponsiveUserIdentity::findIdentity($forgotPasswordData->id)) {
                if ($forgotPasswordData->login == $user->getContact()->login && $user->getContact()->resetpasswordtoken == $this->token) {
                    $userModel = new User();
                    $userModel->id = $user->getId();
                    $userModel->password = $this->password;
                    $userModel->dRole = User::ROLE_SUPERADMIN;
                    return $userModel->updatePassword();
                }
            }
        }

        return false;
    }
}