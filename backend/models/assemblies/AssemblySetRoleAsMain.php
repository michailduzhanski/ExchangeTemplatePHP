<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 6/15/2018
 * Time: 3:06 PM
 */

namespace backend\models\assemblies;

use common\modules\drole\models\gate\RegistryAPIHandler;
use Yii;
use yii\base\Model;

class AssemblySetRoleAsMain extends Model
{
    public $objectid;
    public $assemblyid;
    public $roleid;
    public $companyid;
    public $serviceid;
    public $deletevalue;

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
                    'roleid',
                    'companyid',
                    'serviceid',
                    'deletevalue'
                ],
                'required'
            ]
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            $user = Yii::$app->user->getIdentity()->getContactAuth();
            $result = RegistryAPIHandler::setRoleAsMain($this->objectid, $user->drole, $user->uid, $this->assemblyid, $this->roleid, $this->companyid, $this->serviceid, $this->deletevalue);
            return $result;
        }
        return false;
    }
    //
}