<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 5/15/2018
 * Time: 10:33 AM
 */

namespace backend\models\assemblies;
use common\modules\drole\models\gate\RegistryAPIHandler;
use common\modules\drole\models\UUIDGenerator;
use common\modules\drole\models\gate\StructureOperationHandler;
use Yii;
use yii\base\Model;

class AssemblyStructureFieldValues extends Model
{
    public $id;
    public $objectid;
    public $assemblyid;
    public $turn;
    public $usef;
    public $visible;
    public $edit;
    public $delete;
    public $insert;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'objectid',
                    'assemblyid',
                    'turn',
                    'usef',
                    'visible',
                    'edit',
                    'delete',
                    'insert'
                ],
                'required'
            ],
            [
                'id',
                'safe'
            ],
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            $user = Yii::$app->user->getIdentity()->getContactAuth();
            $result = StructureOperationHandler::updatePositionAndPermission($this->objectid, $user->drole, $user->uid, $this->assemblyid, $this->id, $this->turn, $this->usef, $this->visible, $this->edit, $this->delete, $this->insert);
            return $result;
        }
        return false;
    }
    //
}
