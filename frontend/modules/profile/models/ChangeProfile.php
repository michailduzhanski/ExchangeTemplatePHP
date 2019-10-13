<?php
namespace frontend\modules\profile\models;

use common\models\User;
use common\modules\drole\models\auth\ContactData;
use common\modules\drole\models\UUIDGenerator;
use Yii;
use yii\base\DynamicModel;
use yii\base\Model;
use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\gate\UpdateDataObjectHandler;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use frontend\helpers\DroleHelper;
use common\modules\imageStorage\behaviors\ImageStorage;

class ChangeProfile extends DynamicModel
{
    protected $objectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';

    public $photo;

    public $login;

    public $email;

    public $firstname;

    public $lastname;

    public $gender;

    public $birthday;

    public $twitter;

    public $facebook;

    public $linkedin;

    protected $isEmailChanged = false;

    protected $user;

    protected $dRole = '88286f5e-ecd7-48d6-b2d1-69bed835a8c1';

    protected $contactLinksId;

    protected  $socials = ['facebook', 'twitter', 'linkedin'];


    public function behaviors()
    {
        return [
            'imageStorage' => [
                'class' => ImageStorage::class,
                'uploadType' => 'POST',
                'uploadFields' => [
                    'photo' => 'user_photo',
                ],
                'tableFields' => [
                    'photo' => ['contact_data_use' => Yii::$app->user->id],
                ],
                'objects' => [
                    'photo' => '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24',
                ]
            ]
        ];
    }

    public function rules()
    {
        return [
            //[['login', 'email'], 'required'],
            //[['login'], 'required'],
            [[
                'firstname',
                'lastname',
                'twitter', 'facebook', 'linkedin'
            ], 'safe'],
            ['email', 'email'],
            ['login', 'match', 'pattern' => '/^[a-z0-9]\w*$/', 'message' => Yii::t('frontend', 'Only lowercase latin symbols and numbers')],
            ['login', 'string', 'min' => 6, 'max' => 12],
            ['login', 'validateLogin'],            
        ];
    }

    public function init()
    {
        parent::init();
        $this->user = Yii::$app->user->identity::findIdentity(Yii::$app->user->id);
        $this->contactLinksId = $this->user->getContact()->contactlinks;
    }

    public function validateLogin($attribute, $params)
    {
        $findUser = ContactData::find()->where(['login' => $this->{$attribute}])->one();
        $user = $this->user;
        if ($findUser && $findUser->id != $user->getContact()->id) {
            $this->addError($attribute, Yii::t('frontend', 'Login is already exist'));
        }
    }

    public function validateEmail($attribute, $params)
    {
        $findUser = ContactData::find()->where(['emailconversation' => $this->{$attribute}])->one();
        $user = $this->user;
        if ($findUser && $findUser->id != $user->getContact()->id) {
            $this->addError($attribute, Yii::t('frontend', 'Email is already exist'));
        }
    }

    public function save()
    {
/*        if(!$this->validate())
            return false;*/

        $contactObjectId = $this->objectId;

        $struct = StructureOperationHandler::getFastStructureWithCheck($contactObjectId, $this->dRole);

        foreach ($this->socials as $social){
            switch ($social){
                case 'facebook' : {
                    $value = $this->facebook;
                    break;
                }
                case 'twitter' : {
                    $value = $this->twitter;
                    break;
                }
                case 'linkedin' : {
                    $value = $this->linkedin;
                    break;
                }
            };
            $this->updateSocial($struct, $social, $value);
        }

        $data = [
            'id' => Yii::$app->user->id,
            //'login' => $this->login,
            'firstname' => $this->firstname,
            'secondname' => $this->lastname,
            //'emailconversation' => $this->email
            //'contactlinks.id' => $this->contactLinksId
        ];
        if($this->photo)
            $data = array_merge($data, ['photo' => $this->photo]);

        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $contactObjectId,
            DroleHelper::createUpdateParams($struct, $data)
        );
        $jsonIncomBody['permission']['service_id'] = Yii::$app->params['service_id'];
        $jsonIncomBody['permission']['contact_id'] = Yii::$app->user->id;
        $jsonIncomBody['permission']['drole_id'] = $this->dRole;        
        
        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);      

        if(isset($res['result']) && $res['result'] == 200){
            return true;
        }

        return false;
    }


    public function updateSocial($struct, $type, $value)
    {
        $contactlinksObject = DroleHelper::getFieldInfoByName($struct, 'contactlinks');
        $contactlinksObject = $contactlinksObject['field'];
        $contactlinksObjectId = $contactlinksObject['object'];
        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $contactlinksObjectId,
            DroleHelper::createUpdateParams($contactlinksObject['nested'], [
                'id' => UUIDGenerator::v4(),
                'type' => $type,
                'value' => $value
            ])
        );


        $jsonIncomBody['permission']['service_id'] = Yii::$app->params['service_id'];
        $jsonIncomBody['permission']['contact_id'] = Yii::$app->user->id;
        $jsonIncomBody['permission']['drole_id'] = $this->dRole;        


        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);

        if(isset($res['result']) && $res['result'] == 200){
            return true;
        }

        return false;
    }

    public function updateEmail($struct)
    {
        $contactlinksObject = DroleHelper::getFieldInfoByName($struct, 'contactlinks');
        $contactlinksObject = $contactlinksObject['field'];
        $contactlinksObjectId = $contactlinksObject['object'];


        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $contactlinksObjectId,
            DroleHelper::createUpdateParams($contactlinksObject['nested'], [
                'id' => $this->contactLinksId,
                'type' => 'email',
                'value' => $this->email
            ])
        );

        $jsonIncomBody['permission']['service_id'] = Yii::$app->params['service_id'];
        $jsonIncomBody['permission']['contact_id'] = Yii::$app->user->id;
        $jsonIncomBody['permission']['drole_id'] = $this->dRole;

        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);
        if(isset($res['result']) && $res['result'] == 200){
                return true;
        }

        return false;
    }




}