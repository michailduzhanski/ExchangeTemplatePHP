<?php
namespace common\models;

/**
 * This is the model class for table "site_tempdata".
 *
 * @property string $id
 * @property string $objectid
 * @property string $companycontactid
 * @property double $date_create
 * @property double $date_change
 * @property string $data
 *
 */
class SiteTempdata extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site_tempdata';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'objectid', 'companycontactid', 'data'], 'safe'],
            [['id','objectid','companycontactid'], 'match', 'pattern' => '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/i'],
            //['objectid', 'match', 'pattern' => '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/i'],
            //['companycontactid', 'match', 'pattern' => '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/i'],
            //, 'objectid', 'companycontactid']
        ];
    }

    public function afterFind()
    {
        $this->data = json_decode($this->data, true);
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)){
            $this->data = json_encode($this->data);
            $this->date_change = microtime(true);
            if($this->isNewRecord){
                if(!$this->id)
                    $this->id = UUIDGenerator::v4();
                $this->date_create = microtime(true);
            }
            return true;
        };

        return false;
    }
}