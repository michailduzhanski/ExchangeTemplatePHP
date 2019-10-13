<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 5/6/2018
 * Time: 10:39 PM
 */

namespace backend\models\dataobjects;

use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\gate\StructureOperationHandler;
use yii\base\Model;

class StructureFieldDelete extends Model
{
    public $objectid;
    public $id;
    public $checkusage;

    public function rules()
    {
        return [
            [
                [
                    'objectid',
                    'checkusage'
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
            //$registryApiHandler = new RegistryAPIHandler();
            if ($this->id === null || $this->id === '') {
                return APIHandler::getErrorArray('404', 'Not found id object.');
            }
            $user = \Yii::$app->user->getIdentity()->getContactAuth();
            return StructureOperationHandler::deleteStructureField($this->objectid, $user->drole, $user->uid, $this->id, null, $this->checkusage);
        }

        return false;
    }
}