<?php

namespace backend\models\dataobjects;

use common\modules\drole\models\auth\ContactData;
use common\modules\drole\models\gate\RegistryAPIHandler;
use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\drole\models\UUIDGenerator;
use Yii;
use yii\base\Model;

class StructureFieldValues extends Model
{

    public $objectid;
    public $id;
    public $name;
    public $description;
    public $classid;
    //public $typeClass;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'name',
                    'description',
                    'classid',
                    'objectid'
                ],
                'required'
            ],
            [
                'id',
                'safe'
            ],
            [
                'name',
                'string',
                'max' => 100
            ],
            [
                'description',
                'string',
                'max' => 500
            ],
            [
                'classid',
                'string',
                'max' => 40
            ],
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            //$registryApiHandler = new RegistryAPIHandler();

            /** @var ContactData $user */
            $user = Yii::$app->user->getIdentity()->getContactAuth();
            if ($this->id === null || $this->id === '') {
                $this->id = UUIDGenerator::v4();
            }
            StructureOperationHandler::updateStructureFieldNameDescription($this->objectid, $user->drole, $user->uid, $this->id, $this->name, $this->classid, $this->description);
            return $this->id;
        }

        return false;
    }

}
