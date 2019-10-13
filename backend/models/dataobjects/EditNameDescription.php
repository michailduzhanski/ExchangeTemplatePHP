<?php

namespace backend\models\dataobjects;

use common\modules\drole\models\auth\ContactData;
use common\modules\drole\models\gate\RegistryAPIHandler;
use common\modules\drole\models\UUIDGenerator;
use Yii;
use yii\base\Model;

class EditNameDescription extends Model
{

    public $id;
    public $name;
    public $description;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'name',
                    'description'
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

            RegistryAPIHandler::updateObjectNameDescription($this->id, $user->drole, $user->uid, $this->name, $this->description);

            return $this->id;
        }

        return false;
    }

}
