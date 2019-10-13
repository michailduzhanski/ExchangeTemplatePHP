<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\drole\object\criterion;

/**
 * Description of AccessRulesClass
 *
 * @author LILIYA
 */
class AccessRulesClass extends DBWorkConstructor
{

    public function __construct($extObjectID)
    {
        
        $extSuffixName = "filter_use";
        parent::__construct($extObjectName, $extSuffixName);
    }
}