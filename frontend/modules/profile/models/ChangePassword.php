<?php


namespace frontend\modules\profile\models;

use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\gate\UpdateDataObjectHandler;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use frontend\helpers\DroleHelper;
use Yii;
use yii\base\Model;

class ChangePassword extends Model
{
    public $oldPassword;

    public $newPassword;

    public $confirmPassword;

    public function rules()
    {
        return [
            [['oldPassword', 'newPassword', 'confirmPassword'], 'safe'],
            [['oldPassword', 'newPassword', 'confirmPassword'], 'required'],
            [['oldPassword', 'newPassword', 'confirmPassword'], 'string', 'min' => 8],
            [['oldPassword', 'newPassword', 'confirmPassword'], 'match', 'pattern' => '/(?=.*[A-Z])/', 'message' => Yii::t('frontend', 'Must have minimum one uppercase letter')],
            [['oldPassword', 'newPassword', 'confirmPassword'], 'match', 'pattern' => '/(?=.*[a-z])/', 'message' => Yii::t('frontend', 'Must have minimum one lovercase lowercase')],
            [['oldPassword', 'newPassword', 'confirmPassword'], 'match', 'pattern' => '/(?=.*\d)/', 'message' => Yii::t('frontend', 'Must have minimum one number')],
            [['oldPassword', 'newPassword', 'confirmPassword'], 'match', 'pattern' => '/(?=.*[$@$!%*?&])/', 'message' => Yii::t('frontend', 'Must have minimum one special symbol $@$!%*?&')],
            ['confirmPassword', 'compare', 'compareAttribute' => 'newPassword', 'message' => Yii::t('frontend', 'Passwords don\'t match')],
            ['oldPassword', 'validatePassword']

        ];
    }

    public function attributeLabels()
    {
        return [
            'oldPassword' => Yii::t('profile_page', 'Old password'),
            'newPassword' => Yii::t('profile_page', 'New password'),
            'confirmPassword' => Yii::t('profile_page', 'Confirm password')
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if($this->{$attribute}){
            if($user = Yii::$app->user->identity::findIdentity(Yii::$app->user->id)){
                $userPassword = $user->getContact()->password;
                if(!Yii::$app->security->validatePassword($this->{$attribute}, $userPassword)){
                    $this->addError($attribute, Yii::t('frontend', 'Invalid password'));
                }
            } else {
                $this->addError($attribute, Yii::t('frontend', 'Invalid password'));
            }
        }
    }

    public function save()
    {
        if(!$this->validate())
            return false;

        $contactObjectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';
        $dRole = '88286f5e-ecd7-48d6-b2d1-69bed835a8c1';
        $struct = StructureOperationHandler::getFastStructureWithCheck($contactObjectId, $dRole);

        $data = [
            'id' => Yii::$app->user->id,
            'password' => Yii::$app->security->generatePasswordHash($this->newPassword)
        ];
        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $contactObjectId,
            DroleHelper::createUpdateParams($struct, $data)
        );
        $jsonIncomBody['permission']['service_id'] = Yii::$app->params['service_id'];
        $jsonIncomBody['permission']['contact_id'] = Yii::$app->user->id;
        $jsonIncomBody['permission']['drole_id'] = $dRole;

        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);

        if(isset($res['result']) && $res['result'] == 200){
            return true;
        }

        return false;
    }
}