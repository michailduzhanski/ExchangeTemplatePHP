<?php
namespace common\modules\pinCode\traits;

use Yii;

trait PinCodeTrait
{
    public function validatePincode($attribute, $params)
    {
        if($this->{$attribute} != Yii::$app->Pincode->getCode()){
            $this->addError($attribute, Yii::t('frontend', 'Invalid Pin Code'));
        }
    }
}