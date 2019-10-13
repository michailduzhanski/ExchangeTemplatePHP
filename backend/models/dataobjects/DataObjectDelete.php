<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 5/7/2018
 * Time: 5:27 PM
 */

namespace backend\models\dataobjects;

use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\gate\StructureOperationHandler;
use yii\base\Model;

class DataObjectDelete extends Model
{
    public $objectid;
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

        ];
    }

    public function save()
    {
        if ($this->validate()) {
            //$registryApiHandler = new RegistryAPIHandler();
            if ($this->objectid === null || $this->objectid === '') {
                return APIHandler::getErrorArray('404', 'Not found id object.');
            }
            $user = \Yii::$app->user->getIdentity()->getContactAuth();
            return StructureOperationHandler::deleteDataObject($this->objectid, $user->drole, $user->uid, null, $this->checkusage);
        }

        return false;
    }
}