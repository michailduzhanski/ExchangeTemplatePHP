<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "servicelinks_data_use".
 *
 * @property string $id
 * @property double $date_create
 * @property double $date_change
 * @property string $type
 * @property string $value
 */
class ServicelinksDataUse extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'servicelinks_data_use';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'value'], 'string'],
            [['date_create', 'date_change'], 'number'],
            [['type'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date_create' => 'Date Create',
            'date_change' => 'Date Change',
            'type' => 'Type',
            'value' => 'Value',
        ];
    }
}
