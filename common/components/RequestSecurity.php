<?php


namespace common\components;

use common\modules\drole\models\gate\SecurityHandler;

class RequestSecurity extends \yii\base\Security
{

    public  function checkrequest($textrequest, $length = 100) {
        return SecurityHandler::checkrequest($textrequest, $length);
    }

    public  function setLog($somecontent) {
        return SecurityHandler::setLog($somecontent);
    }
}