<?php

namespace common\modules\drole\object\criterion;

use yii\data\SqlDataProvider;
use common\modules\drole\object\ObjectCriterionVerificationAbstract;
use common\modules\drole\registry\payment\RegistryDescriptionPaymentModel;

class CommonCV extends ObjectCriterionVerificationAbstract {

    public function __construct($extObjectID) {
        //parent::__construct($extObjectID);
    }

    public function checkCriterion($droleID, $objectID, $inputArray) {
        return '{"' . $this->selfObjectID . '":"200"}';
    }

}
