<?php
namespace common\modules\drole\registry\droles;

class SimpleAccessToDroleElements{
    
    private $key;
    private $value;
    //SELECT drole_id, count(drole_id) FROM `registry_droles` WHERE (meta_key like 'objectid03' and `value` LIKE 'companyid01') or (meta_key like 'objectid01' and `value` LIKE 'roleid01') GROUP BY drole_id HAVING COUNT(drole_id) = 2 
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
    
    public function getValue(){
        return $this->value;
    }
}