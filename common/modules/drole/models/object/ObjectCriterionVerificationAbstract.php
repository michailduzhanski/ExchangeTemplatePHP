<?php

namespace common\modules\drole\object;

use Yii;
use yii\db\ActiveRecord;
use common\modules\drole\controllers\DefaultController;
use yii\data\SqlDataProvider;

class ObjectCriterionVerificationAbstract {

    public $objectName;
    public $selfObjectID = '';

    public function __construct($extObjectID, $objectName) {
        $this->selfObjectID = $extObjectID;
        $this->objectName = $objectName;
        //$this->objectName = RegistryObjects::getObjectNameByID($extObjectID)->name;
    }

    public function checkCriterion($droleID, $objectID, $inputArray) {
        return '{"NaN":"440","message":"not found module for dinrole: ' . $droleID . ' and object: ' . $objectID . '"}';
    }

}
