<?php

/**
 * Created by PhpStorm.
 * User: aleks
 * Date: 01.03.2018
 * Time: 17:01
 */

namespace common\models;

use common\modules\drole\models\auth\CompaniesContactDataUse;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;
use yii\web\IdentityInterface;
use yii\helpers\Url;
use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\auth\ContactAuth;

class ResponsiveUserIdentity implements IdentityInterface {

    /**
     * @var ActiveRecord $userModel
     * @var ActiveRecord $authModel
     * @var ActiveRecord $logModel
     */
    public static $userModel = 'common\modules\drole\models\auth\ContactData';
    public static $authModel = 'common\modules\drole\models\auth\ContactAuth';
    public static $logModel = 'common\modules\drole\models\auth\ContactAuthLog';
    public static $servicelinksModel = 'common\modules\drole\models\auth\ServicelinksDataUse';
    public static $timeExpired = 3600;

    public function getTimeExpired() {
        return self::$timeExpired;
    }

    /**
     * @var $user ActiveRecord
     * @var $auth ContactAuth
     */
    public $user;
    public $auth;
    public $serviceLinks;

    /**
     * ResponsiveUserIdentity constructor.
     * @param $id mixed ID of user model
     */
    public function __construct($id) {
        /* @var ActiveRecord $userModel */
        $userModel = self::$userModel;
        /* @var ActiveRecord $authModel */
        $authModel = self::$authModel;
        $this->user = $userModel::findOne($id);
        $auth = $authModel::findOne(['uid' => $this->user->getPrimaryKey()]);
        /*if ($auth['time'] != null && $auth['time'] < time()) {
            //$auth->delete();
            //$auth = null;
            $this->auth = null;
            $this->logout();
            $session = \Yii::$app->getSession();
            $session->remove($this->auth['id']);
            $session->remove($this->auth['hash']);
            //$session->remove($user->absoluteAuthTimeoutParam);
            return;
        }*/
        if ($auth === null) {
            $auth = new $authModel();
            $auth['id'] = UUIDGenerator::v4();
            $auth['uid'] = $this->user->getPrimaryKey();
            $auth['drole'] = DynamicRoleModel::getDynamicRoleWithParams($this->user->getPrimaryKey());
            $auth['ip'] = \Yii::$app->request->getUserIP();
            $auth['uagent'] = \Yii::$app->request->getUserAgent();
            $auth['hash'] = \Yii::$app->session->Id;
            $auth['page'] = \Yii::$app->request->url;
            $auth['lang'] = \Yii::$app->language;
        }
        //TODO Костыль, потому что registrant не записывает в registry_drole_base registry_drole_contacts
        if(!$auth['drole']){
            $auth['drole'] = 'f8547413-0af7-4679-aff1-b598f4f6d2e2';
        }
        /*if (!$auth['drole']) {
            \Yii::$app->getSession()->destroy();
            \Yii::$app->user->logout(true);
            echo 'not found drole for [' . $this->user->getPrimaryKey() . ']: ' . DynamicRoleModel::getDynamicRoleWithParams($this->user->getPrimaryKey()); exit;
            return;
        }*/
        $auth['time'] = microtime(true) + self::$timeExpired * 10;
        //$auth->save();
        $this->auth = $auth;
        //self::appendLog();
    }

    /**
     * @inheritDoc
     */
    public static function findIdentity($id) {
        return new self($id);
    }

    /**
     * @inheritDoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByAttribute($attribute, $value) {
        /* @var ActiveRecord $userModel */
        $userModel = self::$userModel;
        if($user = $userModel::findOne([$attribute => $value])){
            return new self($user->getPrimaryKey());
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getId() {
        return $this->user->getPrimaryKey();
    }

    /**
     * @inheritDoc
     */
    public function getAuthKey() {
        return $this->auth['hash'];
    }

    /**
     * @inheritDoc
     */
    public function validateAuthKey($authKey) {
        return $this->auth['hash'] === $authKey;
    }

    /**
     * Validate password hash
     * @param $password string Password string
     * @return boolean
     */
    public function validatePassword($password) {
        $data=  \Yii::$app->security->validatePassword($password, $this->user['password']);
        return $data;
    }

    public function validateResetPasswordKey($key)
    {
        $login = $this->getContact()->login;

    }

    public static function generateForgotPasswordKey($login)
    {
        return \Yii::$app->security->generatePasswordHash('#forgotpassword#' . $login);
    }

    public static function validateForgotPasswordKey($key, $login)
    {
        return \Yii::$app->security->validatePassword('#forgotpassword#' . $login, $key);
    }

    /**
     * This method used for logging user login
     */
    public function appendLog() {
        if (!$this->auth['drole']) {
            \Yii::$app->getSession()->destroy();
            \Yii::$app->user->logout(true);
            return;
        }
        $this->updateAuthRecords();
        /* @var ActiveRecord $logModel */
        //echo 'use appendLog() ' . print_r($this->formatLogAttributes(), true); exit;
        //if ($this->user->getPrimaryKey()) {

        //}
    }

    private function updateAuthRecords(){
        $authModel = self::$authModel;
        $authModel::insertContactAuthArray($this->auth, microtime(true) + self::$timeExpired);
        $logModel = self::$logModel;
        $logModel::insertContactAuthArray($this->auth, microtime(true) + self::$timeExpired);
    }

    /**
     * @return array Formatted attributes array for ActiveRecord
     */
    /*public function formatLogAttributes() {
        $log = [];

        $log['uid'] = $this->user->getPrimaryKey();
        $log['drole'] = $this->auth['drole'];
        $log['ip'] = \Yii::$app->request->getUserIP();
        $log['uagent'] = \Yii::$app->request->getUserAgent();
        $log['hash'] = $this->auth['hash'];
        $log['page'] = \Yii::$app->request->url;
        $log['time'] = microtime(true) + self::$timeExpired * 10;
        $log['lang'] = \Yii::$app->language;
        return $log;
    }*/

    /**
     * @return null|ContactData
     */
    public function getContact() {
        return $this->user;
    }

    /**
     * @return null|ContactAuth
     */
    public function getContactAuth() {
        return $this->auth;
    }

    /**
     * This method used for clear user_auth
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function logout() {
        /* @var ActiveRecord $authModel */
        $authModel = self::$authModel;
        $authModel::deleteContactAuthByID($this->user->getPrimaryKey());
    }

    public static function checkExpirationAuth(){
        $contactAuthRecord = ContactAuth::getContactAuthByID(\Yii::$app->user->getId());
        if(!$contactAuthRecord){
            return -1;
        }else{
            return $contactAuthRecord->time;
        }
    }

    public function getUserDroleID()
    {
        if(isset($this->auth['drole']))
            return $this->auth['drole'];

        return null;
    }

    public function getEmail()
    {
        if(isset($this->user->emailconversation))
            return $this->user->emailconversation;

        return null;
    }

    public function getPincode()
    {
        if(isset($this->user->pincode))
            return $this->user->pincode;

        return null;
    }


    public function getUserMD5()
    {
        if($id = $this->getId()){
            return md5($id);
        }
        return null;
    }

    public function getUserSponsor()
    {
        if($id = CompaniesContactDataUse::getContactDataByID('sponsorcontactid')){
            $sponsor = self::findIdentity($id);
            if(isset($sponsor->getContact()->login)){
                return $sponsor->getContact()->login;
            }
        }

        return '';
    }
}
