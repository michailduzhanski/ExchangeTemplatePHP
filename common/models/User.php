<?php

namespace common\models;

use common\modules\drole\models\auth\ContactAuth;
use common\modules\drole\models\gate\UpdateDataObjectHandler;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use frontend\helpers\DroleHelper;
use Yii;
use Codeception\Exception\ConfigurationException;
use common\modules\drole\models\gate\StructureOperationHandler;
use yii\base\Model;
use yii\db\Query;

class User extends Model {

    const STATUS_VERIFY = 100;

    const STATUS_VERIFED = 200;

    const ROLE_REGISTRANT = 'registrant';

    const ROLE_SUPERADMIN = 'superadmin';

    public $id;

    public $contactLinksId;

    public $login;

    public $firstname;

    public $secondname;

    public $password;

    public $email;

    public $sponsor;

    public $sponsorID;

    public $defaultSponsorId = '55901b1a-80ae-48e3-803d-e4b2ed418e39';

    public $emailVerificationToken;

    public $resetPasswordToken;

    public $dRole;

    public $dRoleId;

    public $contactObjectId = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';

    public $roles = [
        self::ROLE_SUPERADMIN => '62900a19-88a9-4655-a7ac-71488070b659',
        self::ROLE_REGISTRANT => 'f8547413-0af7-4679-aff1-b598f4f6d2e2'
    ];

    public $verification;

    public $serviceId;

    private $contactStructure;


    public function rules()
    {
        return [
            ['verification', 'in', 'range' => [self::STATUS_VERIFY, self::STATUS_VERIFED]]
        ];
    }

    public function configure()
    {
        if(!$this->dRoleId && !$this->dRole)
            throw  new ConfigurationException('The dRole or DRoleId must be set');

        if($this->dRole) $this->getRoleId();
        if(!$this->serviceId)
            $this->serviceId = Yii::$app->params['service_id'];
        if(!$this->verification)
            $this->verification = self::STATUS_VERIFY;
        if(!$this->emailVerificationToken)
            $this->emailVerificationToken = Yii::$app->security->generateRandomString();
        if(!$this->resetPasswordToken)
            $this->resetPasswordToken = Yii::$app->security->generateRandomString();

        $this->getContactStructure();
        if(!$this->id)
            $this->id = UUIDGenerator::v4();
        if(!$this->contactLinksId)
            $this->contactLinksId = UUIDGenerator::v4();
        if(!$this->sponsorID)
            $this->sponsorID = $this->defaultSponsorId;
    }


    public function save(){
        $this->configure();

        if(!$this->validate())
            return false;

        $contactData = [
            'id' => $this->id,            
            'contactlinks.id' => $this->contactLinksId,
            'login' => $this->login,
            'firstname' => $this->firstname,
            'secondname' => $this->secondname,
            'password' => Yii::$app->security->generatePasswordHash($this->password),
            'verification' => $this->verification,
            'emailverificationtoken' => $this->emailVerificationToken,
            'resetpasswordtoken' => $this->resetPasswordToken,
            'emailconversation' => $this->email
        ];

        $transaction = ContactAuth::getDb()->beginTransaction();
        try {
            if ($this->addContact($contactData)) {
                $auth['id'] = UUIDGenerator::v4();
                $auth['uid'] = $this->id;
                $auth['drole'] = $this->dRoleId;
                $auth['ip'] = Yii::$app->request->getUserIP();
                $auth['uagent'] = Yii::$app->request->getUserAgent();
                $auth['hash'] = Yii::$app->session->Id;
                $auth['page'] = Yii::$app->request->url;
                $auth['lang'] = Yii::$app->language;
                if(!ContactAuth::insertContactAuthArray($auth, microtime(true) + 3600 * 10)){
                    $transaction->rollBack();
                    return false;
                }

                $this->addSponsor();

                $transaction->commit();
                return true;
            }
        }  catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return false;
    }

    /*public function addSponsorLink($struct, $type, $value)
    {
        $linkId = UUIDGenerator::v4();
        //add ContactLinks
        $contactlinksObject = DroleHelper::getFieldInfoByName($struct, 'contactlinks');
        $contactlinksObject = $contactlinksObject['field'];
        $contactlinksObjectId = $contactlinksObject['object'];

        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $contactlinksObjectId,
            DroleHelper::createUpdateParams($contactlinksObject['nested'], [
                'id' => $linkId,
                'type' => $type,
                'value' => $value
            ])
        );
        $jsonIncomBody['permission']['service_id'] = $this->serviceId;
        $jsonIncomBody['permission']['contact_id'] = $this->id;
        $jsonIncomBody['permission']['drole_id'] = $this->dRoleId;

        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);

        if(isset($res['result']) && $res['result'] == 200){
            return $linkId;
        }
        //End add ContactLinks
    }*/

    /**
     * Добавить спонсора
     */
    public function addSponsor()
    {
        $companiesContactId = '77206e5e-5dad-4f4b-880d-ad4976942cdd';
        $contactlinkId = $this->contactLinksId;

        $companiesId = UUIDGenerator::v4();

        $this->dRole = self::ROLE_REGISTRANT;
        $this->configure();

        $struct = StructureOperationHandler::getFastStructureWithCheck($companiesContactId, $this->dRoleId);

        if($contactlinkId){
            $data = [
                'id' => $companiesId,
                'contactlinks' => $contactlinkId,
                'sponsorcontactid' => $this->sponsorID
            ];
            $jsonIncomBody = JSONRegistryFactory::updateObject(false, $companiesContactId,
                DroleHelper::createUpdateParams($struct, $data)
            );
            $jsonIncomBody['permission']['service_id'] = $this->serviceId;
            $jsonIncomBody['permission']['contact_id'] = $this->id;
            $jsonIncomBody['permission']['drole_id'] = $this->dRoleId;

            $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);

            if(isset($res['result']) && $res['result'] == 200){
                return true;
            }
        }

        return false;
    }

    /**
     * Добавить пользователя
     * @param $data
     * @return bool
     */
    public function addContact($data)
    {
        $struct = $this->getContactStructure();
        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $this->contactObjectId,
            DroleHelper::createUpdateParams($struct, $data)
        );

        $jsonIncomBody['permission']['service_id'] = $this->serviceId;
        $jsonIncomBody['permission']['contact_id'] = $this->id;
        $jsonIncomBody['permission']['drole_id'] = $this->dRoleId;

        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);

        if(isset($res['result']) && $res['result'] == 200){
            return true;
        }

        return false;
    }

    /**
     * Добавить сontactlink
     * @param $type
     * @param $value
     * @return bool
     */
/*    public function addContactLink($type, $value){
        $struct = $this->getContactStructure();
        $contactlinksObject = DroleHelper::getFieldInfoByName($struct, 'contactlinks');
        $contactlinksObject = $contactlinksObject['field'];
        $contactlinksObjectId = $contactlinksObject['object'];

        $jsonIncomBody = JSONRegistryFactory::updateObject(false, $contactlinksObjectId,
            DroleHelper::createUpdateParams($contactlinksObject['nested'], [
                'id' => $this->contactLinksId,
                'type' => $type,
                'value' => $value
            ])
        );

        $jsonIncomBody['permission']['service_id'] = $this->serviceId;
        //$jsonIncomBody['permission']['contact_id'] = $this->contactObjectId;
        //here I inserted the UID, which we create
        $jsonIncomBody['permission']['contact_id'] = $this->id;
        $jsonIncomBody['permission']['drole_id'] = $this->dRoleId;


        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);
        if(isset($res['result']) && $res['result'] == 200){
            return true;
        }

        return false;
    }*/

    /**
     * Получить id роли по имени
     * @return mixed
     */
    public function getRoleId(){
        if(array_key_exists($this->dRole, $this->roles)){
            $this->dRoleId = $this->roles[$this->dRole];
            return $this->dRoleId;
        }
    }

    /**
     * Поулчить структура Contact
     * @return bool|string
     */
    public function getContactStructure()
    {
        if($this->contactStructure)
            return $this->contactStructure;
        $this->contactStructure = StructureOperationHandler::getFastStructureWithCheck($this->contactObjectId, $this->dRoleId);

        return $this->contactStructure;
    }


    /**
     * Обновить статус верификации
     * @return bool
     */
    public function updateVerificationStatus(){
        $this->configure();

        $data = [
            'id' => $this->id,
            'verification' => $this->verification
        ];

        return $this->addContact($data);
    }


    /**
     * Обновить пароль
     * @return bool
     */
    public function updatePassword()
    {
        $this->configure();
        $data = [
            'id' => $this->id,
            'password' => Yii::$app->security->generatePasswordHash($this->password),
            'resetpasswordtoken' => Yii::$app->security->generateRandomString()
        ];
        return $this->addContact($data);
    }
}
