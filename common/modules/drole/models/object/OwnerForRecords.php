<?php
namespace common\modules\drole\object;

use yii\data\SqlDataProvider;
use common\modules\drole\object\DBWorkConstructor;

class OwnerForRecords extends DBWorkConstructor
{

    public function __construct($extObjectName)
    {
        $extSuffixName = "record_own";
        parent::__construct($extObjectName, $extSuffixName);
    }

    public function getRecordByOwner($extParamsNamedArray)
    {
        if ($extParamsNamedArray['company_id'] === false) {
            //select all
        } else {
            foreach ($extParamsNamedArray as $line) {
                
            }
        }
    }
}
