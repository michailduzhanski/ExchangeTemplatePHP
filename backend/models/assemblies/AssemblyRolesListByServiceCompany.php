<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 6/7/2018
 * Time: 5:36 PM
 */

namespace backend\models\assemblies;
use common\modules\drole\models\gate\RegistryAPIHandler;
use common\modules\drole\models\UUIDGenerator;
use common\modules\drole\models\gate\AssemblyManagerHandler;
use Yii;
use yii\base\Model;

class AssemblyRolesListByServiceCompany extends Model
{
    public $objectid;
    public $assemblyid;
    public $companyid;
    public $serviceid;

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
                    'companyid',
                    'serviceid'
                ],
                'required'
            ],
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            $user = Yii::$app->user->getIdentity()->getContactAuth();
            $result = AssemblyManagerHandler::getRolesArrayValuesForObjectAndAssembly($this->objectid, $this->assemblyid, $this->companyid, $this->serviceid);
            return $result;
        }
        return false;
    }
    //
}