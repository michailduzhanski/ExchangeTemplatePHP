<?php
namespace common\modules\drole\object\filters;

use common\modules\drole\object\DBWorkConstructor;

class SimpleObjectFilter extends DBWorkConstructor
{

    public function __construct($extObjectName)
    {
        $extSuffixName = "filter_use";
        parent::__construct($extObjectName, $extSuffixName);
    }
}
