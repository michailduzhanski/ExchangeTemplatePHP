<?php
namespace common\modules\drole;

use yii\db\ActiveRecord;

class ContactStructureFields extends ActiveRecord
{

    public static function tableName()
    {
        return '{{contacts_structure_fields}}';
    }
}
