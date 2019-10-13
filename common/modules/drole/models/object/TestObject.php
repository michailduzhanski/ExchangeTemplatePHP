<?php
namespace common\modules\drole\object;

class TestObject
{

    public $firstParam;
    public $secondParam;

    function __construct($startParam1, $startParam2)
    {
        $this->init($startParam1, $startParam2);
    }
    
    public function init($startParam1, $startParam2){
        $this->firstParam = $startParam1;
        $this->secondParam = $startParam2;
    }

    public function getParams()
    {
        echo '[' . $this->firstParam . ', ' . $this->secondParam . ']';
    }
}
