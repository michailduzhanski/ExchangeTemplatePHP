<?php
namespace frontend\modules\profile\models;

use Yii;
use common\modules\pinCode\traits\PinCodeTrait;
use common\modules\pinCode\behaviors\PincodeBehavior;
use yii\base\Model;

class Transfer extends Model
{
    use PinCodeTrait;

    public $amount;

    public $address;

    public $pincode;

    public $currency;

    public function behaviors()
    {
        return [
            'PincodeBehavior' => [
                'class' => PincodeBehavior::class,
                'codeAttribute' => 'pincode'
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'amount' => Yii::t('frontend', 'Amount'),
            'address' => Yii::t('frontend', 'Account ID'),
            'pincode' => Yii::t('frontend', 'Enter PIN')
        ];
    }

    public function rules()
    {
        return [
            [['pincode', 'address', 'amount', 'currency'], 'safe'],
            [['amount'], 'number', 'min' => 0],
            [['pincode', 'address','amount'], 'required'],
            ['address', 'addressValidate']
        ];
    }

    public function addressValidate($attribute, $params)
    {
        $length = strlen($this->$attribute);
        if ($length !== 32 ) {
            $this->addError($attribute, Yii::t('frontend', 'Account id is invalid'));
        }
    }

}