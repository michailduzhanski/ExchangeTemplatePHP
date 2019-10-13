<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 5/10/2018
 * Time: 6:12 PM
 */

namespace backend\models\assemblies;

use common\modules\drole\models\gate\RegistryAPIHandler;
use common\modules\drole\models\UUIDGenerator;
use Yii;
use yii\base\Model;

class AssemblyEditNameDescription extends Model
{
    public $id;
    public $objectid;
    public $name;
    public $description;
    public $type;

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
                    'type',
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
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            $user = Yii::$app->user->getIdentity()->getContactAuth();
            if ($this->id === null || $this->id === '') {
                $this->id = UUIDGenerator::v4();
            }
            $result = RegistryAPIHandler::updateAssemblyNameDescription($this->objectid, $user->drole, $user->uid, $this->id, $this->name, $this->description, $this->type);
            if (!is_array($result)) {
                $this->id = $result;
            }
        }
        return $result;
    }

}