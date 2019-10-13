<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 5/7/2018
 * Time: 5:27 PM
 */

namespace backend\models\assemblies;

use common\modules\drole\models\gate\APIHandler;
use common\modules\drole\models\gate\StructureOperationHandler;
use yii\base\Model;

class AssemblyDelete extends Model
{
    public $objectid;
    public $assemblyid;
    public $checkusage;

    public function rules()
    {
        return [
            [
                [
                    'objectid',
                    'assemblyid',
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
            if ($this->objectid === null || $this->objectid === '' || $this->assemblyid === null || $this->assemblyid === '') {
                return APIHandler::getErrorArray('404', 'Not found id object.');
            }
            $user = \Yii::$app->user->getIdentity()->getContactAuth();
            return StructureOperationHandler::deleteAssembly($this->objectid, $user->drole, $user->uid, $this->assemblyid, null, $this->checkusage);
        }

        return false;
    }
}