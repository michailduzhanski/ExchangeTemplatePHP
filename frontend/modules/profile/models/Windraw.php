<?php
namespace frontend\modules\profile\models;

use Yii;
use common\modules\pinCode\traits\PinCodeTrait;
use common\modules\pinCode\behaviors\PincodeBehavior;
use yii\base\Model;

class Windraw extends Model
{
    use PinCodeTrait;

    public $amount;

    public $address;

    public $pincode;

    public $total;

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
            'address' => Yii::t('frontend', 'Currency address'),
            'pincode' => Yii::t('frontend', 'Enter PIN')
        ];
    }

    public function rules()
    {
        return [
            [['pincode', 'address', 'amount', 'total', 'currency'], 'safe'],
            [['amount', 'total'], 'number', 'min' => 0],
            [['pincode', 'address','amount'], 'required'],
        ];
    }

}