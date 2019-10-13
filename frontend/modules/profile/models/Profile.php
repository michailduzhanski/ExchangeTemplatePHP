<?php


namespace frontend\modules\profile\models;

use Yii;
use yii\base\DynamicModel;
use common\modules\imageStorage\behaviors\ImageStorage;
use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use frontend\helpers\DroleHelper;
use common\modules\drole\models\gate\UpdateDataObjectHandler;
use common\modules\drole\models\UUIDGenerator;

class Profile extends DynamicModel
{
    //fields
    public $photo;

    public $firstname;

    public $secondname;

    public $twitter;

    public $facebook;

    public $linkedin;


    //params
    protected $objectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';

    protected $user;

    protected $contactLinksId;

    protected $dRole = '88286f5e-ecd7-48d6-b2d1-69bed835a8c1';

    protected  $socials = ['facebook', 'twitter', 'linkedin'];

    protected $apiPermissions = [];

    protected $struct;


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

    public function attributeLabels()
    {
        return [
            'firstname' => Yii::t('frontend', 'Firstname'),
            'secondname' => Yii::t('frontend', 'Lastname')
        ];
    }

    public function rules()
    {
        return [
            [['firstname', 'lastname'], 'safe'],
            ['firstname', 'string', 'min' => 2, 'max' => 12],
            [['linkedin', 'twitter', 'facebook'], 'string', 'min' => 2],
            //[['linkedin', 'twitter', 'facebook'], 'url', 'defaultScheme' => 'https']

        ];
    }

    public function init()
    {
        parent::init();
        $this->user = Yii::$app->user->identity::findIdentity(Yii::$app->user->id);
        $this->contactLinksId = $this->user->getContact()->contactlinks;
        $this->apiPermissions['permission']['service_id'] = Yii::$app->params['service_id'];
        $this->apiPermissions['permission']['contact_id'] = Yii::$app->user->id;
        $this->apiPermissions['permission']['drole_id'] = $this->dRole;
    }

    public function getAttributeName($attribute)
    {
        $attribute = str_replace($this->formName().'[','', $attribute);
        $attribute = str_replace(']', '', $attribute);
        return $attribute;
    }

    public function loadAttribute($attribute, $value, $validate = true)
    {
        if(property_exists(self::class, $attribute)){
            $this->$attribute = $value;
            if($validate && $this->validate()){
                return true;
            } else {
                $this->$attribute = '';
            }
        }

        return false;
    }

    public function save($fieldId, $attribute, $value)
    {
        if($this->loadAttribute($attribute, $value)){
            if($this->isAttributeSocial($attribute)){
                return $this->saveServicelinkData($fieldId, $attribute, $value);
            } else {
                return $this->saveContactData($attribute, $value);
            }
        }
    }

    public function saveServicelinkData($fieldId, $attribute, $value)
    {
        if(!$value){
            return true;
        }

        switch ($attribute){
            case 'facebook' : {
                if(!$socname = preg_match('/(.+)facebook\.com(.+)/i', $value)){
                    $value = 'https://www.facebook.com/'.$value;
                }
                break;
            }
            case 'twitter' : {
                if(!$socname = preg_match('/(.+)twitter\.com(.+)/i', $value)){
                    $value = 'https://twitter.com/'.$value;
                }
                break;
            }
            case 'linkedin' : {
                if(!$socname = preg_match('/(.+)linkedin\.com(.+)/i', $value)){
                    $value = 'https://www.linkedin.com/'.$value;
                }
                break;
            }
        }

        if(!$httpPrefix = preg_match('/(^http:\/\/.+)|(^https:\/\/.+)/i', $value)){
            $attribute = 'https://'.$attribute;
        }

        if($fieldId == null)
            $fieldId = UUIDGenerator::v4();

        $this->struct = StructureOperationHandler::getFastStructureWithCheck($this->objectId, $this->dRole);

        $contactlinksObject = DroleHelper::getFieldInfoByName($this->struct, 'contactlinks');
        $contactlinksObject = $contactlinksObject['field'];
        $contactlinksObjectId = $contactlinksObject['object'];
        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $contactlinksObjectId,
            DroleHelper::createUpdateParams($contactlinksObject['nested'], [
                'id' => $fieldId,
                'type' => $attribute,
                'value' => $value
            ])
        );
        $jsonIncomBody = array_merge($this->apiPermissions, $jsonIncomBody);
        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);
        if(isset($res['result']) && $res['result'] == 200){
            $data = [
                'id' => Yii::$app->user->id,
                'contactlinks.id' => $fieldId
            ];
            $jsonIncomBody = JSONRegistryFactory::updateObject(false, $this->objectId,
                DroleHelper::createUpdateParams($this->struct, $data)
            );
            $jsonIncomBody = array_merge($this->apiPermissions, $jsonIncomBody);
            $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);
            if(isset($res['result']) && $res['result'] == 200){
                return true;
            }
        }

        return false;
    }

    public function saveContactData($attribute, $value)
    {
        $this->struct = StructureOperationHandler::getFastStructureWithCheck($this->objectId, $this->dRole);

        $data = [
            'id' => Yii::$app->user->id,
            $attribute => $value,
        ];

        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $this->objectId,
            DroleHelper::createUpdateParams($this->struct, $data)
        );
        $jsonIncomBody = array_merge($this->apiPermissions, $jsonIncomBody);
        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);

        if(isset($res['result']) && $res['result'] == 200){
            return true;
        }

        return false;
    }

    protected function isAttributeSocial($attribute)
    {
        return in_array($attribute, $this->socials);
    }
}