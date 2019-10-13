<?php

namespace common\modules\drole\object\criterion;

use yii\data\SqlDataProvider;
use common\modules\drole\object\ObjectCriterionVerificationAbstract;
use common\modules\drole\registry\payment\RegistryDescriptionPaymentModel;

class PayCV extends ObjectCriterionVerificationAbstract {

    public function __construct($extObjectID, $objectName) {
        parent::__construct($extObjectID, $objectName);
    }

    public function checkCriterion($droleID, $objectID, $inputArray) {
        //
        $recordID = $inputArray[$this->selfObjectID];
        $arrayOfParams[':id'] = $recordID;
        //die('object: ' . $this->objectName . ', ' . print_r($arrayOfParams, true));
        $provider = new SqlDataProvider([
            'sql' => 'SELECT * FROM ' . $this->objectName . '_data_use where id = :id',
            'params' => $arrayOfParams
                //, 'totalCount' => $count
        ]);
        //die(print_r($provider->getModels()[0]['object_id'], true)); 
        $innerSubjectID = $provider->getModels()[0]['object_id'];
        if ($innerSubjectID != $objectID) {
            return '{"' . $this->selfObjectID . '":"200"}';
        }
        if ($provider->getModels()[0]['payment_status'] == RegistryDescriptionPaymentModel::$success) {
            return '{"' . $this->selfObjectID . '":"200"}';
        }
        if ($provider->getModels()[0]['payment_status'] == RegistryDescriptionPaymentModel::$test) {
            return '{"' . $this->selfObjectID . '":"301", "message":"used in test mode"}';
        }
        return '{"result":"440", "message":"not found module for dinrole: ' . $droleID . ' and record: ' . $recordID . '"}';
    }

}
