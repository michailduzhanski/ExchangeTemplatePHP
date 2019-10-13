<?php

namespace common\modules\drole\registry;

use yii\data\SqlDataProvider;
use common\modules\drole\controllers\DefaultController;
use common\modules\drole\object\DBWorkConstructor;
use common\modules\drole\registry\droles\RegistryDescriptionRolesModel;

class DynamicRoleModelWithContacts extends DBWorkConstructor {

    //constants
    private static $registryTable = 'registry_drole_base';
    private static $companyObjectName = 'company';
    private static $serviceObjectName = 'service';
    private static $roleObjectName = 'role';
    private static $droleName = 'drole';
    private static $contactObjectName = 'contact';
    private static $objectObjectName = 'object';
    private $arrayOfParams = [];

    public function __construct($inputParams) {
        $this->arrayOfParams = $inputParams;
        $extSuffixName = "drole_base";
        parent::__construct("registry", $extSuffixName);
    }

    //function check dynrole for contact id
    public function getDynamicRoleForContactWithLocalization() {
        $params = $this->createParams();
        //
        //echo "[params: " . $this->createQuery() . "]";
        $provider = new SqlDataProvider([
            'sql' => $this->createQuery(),
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    private function createQuery() {
        //global $arrayOfParams;
        //for anonimous role has one assembly for all companies
        if ($this->arrayOfParams[DefaultController::$roleID] == RegistryDescriptionRolesModel::$anonimus) {
            return 'SELECT * FROM registry_drole_by_objects WHERE drole_id = '
                    . '(SELECT registry_drole_base.id FROM registry_drole_base WHERE company_id = \'\' '
                    . 'AND service_id = :service_id AND role_id = :role_id)';
        } else {
            return 'SELECT * FROM registry_drole_by_objects WHERE drole_id = '
                    . '(SELECT registry_drole_base.id FROM registry_drole_base WHERE company_id = :company_id '
                    . 'AND service_id = :service_id AND role_id = :role_id '
                    . 'AND id = (SELECT registry_drole_contacts.drole_id FROM registry_drole_contacts '
                    . 'WHERE registry_drole_contacts.contact_id = :contact_id limit 1))';
        }
    }

    private function createParams() {
        //echo "[" . DefaultController::$serviceID . "]";
        //die(print_r($this->arrayOfParams, true));
        if ($this->arrayOfParams[DefaultController::$roleID] == RegistryDescriptionRolesModel::$anonimus) {
            return $params = [':' .
                self::$serviceObjectName . '_id' => $this->arrayOfParams[DefaultController::$serviceID],
                ':' . self::$roleObjectName . '_id' => $this->arrayOfParams[DefaultController::$roleID]];
            ;
        } else {
            return $params = [':' . self::$companyObjectName . '_id' => $this->arrayOfParams[DefaultController::$companyID], ':' .
                self::$serviceObjectName . '_id' => $this->arrayOfParams[DefaultController::$serviceID], ':' . self::$roleObjectName . '_id' => $this->arrayOfParams[DefaultController::$roleID], ':' .
                self::$contactObjectName . '_id' => $this->arrayOfParams[DefaultController::$contactID]];
            ;
        }
    }

    public function checkPresentDroleForContact() {
        $params = [':' . self::$droleName . '_id' => $this->arrayOfParams['drole'], ':' .
            self::$contactObjectName . '_id' => $this->arrayOfParams[DefaultController::$contactID]];
        //echo "[params: " . $this->createQuery() . "]";
        $provider = new SqlDataProvider([
            'sql' => 'SELECT * FROM registry_drole_by_objects WHERE drole_id = '
            . '(SELECT registry_drole_contacts.drole_id FROM registry_drole_contacts WHERE '
            . 'contact_id = :contact_id AND drole_id = :drole_id)',
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

    public function getAssemblyForDroleAndObject($droleID) {
        $params = [':' . self::$droleName . '_id' => $droleID, ':' .
            self::$objectObjectName . '_id' => $this->arrayOfParams['object']];
        //echo "[params: " . $this->createQuery() . "]";
        $provider = new SqlDataProvider([
            'sql' => 'SELECT * FROM registry_drole_assembly WHERE object_id = :object_id AND drole_id = :drole_id AND active = \'1\'',
            'params' => $params
        ]);
        //print_r($provider);
        return $provider;
    }

}
