<?php
namespace common\modules\pinCode\behaviors;

use yii\base\Behavior;
use yii\base\Model;
use yii\validators\Validator;

class PincodeBehavior extends Behavior
{
    public $codeAttribute = [];

    public function init()
    {
        parent::init();
        if($this->codeAttribute){
            if(is_string($this->codeAttribute))
                $this->codeAttribute = [$this->codeAttribute];
        }
    }

    public function attach($owner)
    {
        parent::attach($owner);
        $owner->on(Model::EVENT_BEFORE_VALIDATE, [$this, 'beforeValidate']);
    }

    public function beforeValidate()
    {
        $validators = $this->owner->validators;

        foreach ($this->codeAttribute as $field){

            $validator = Validator::createValidator('safe', $this->owner, [$field]);
            $validators->append($validator);

            $validator = Validator::createValidator('validatePincode', $this->owner, [$field]);
            $validators->append($validator);

        }
    }

}