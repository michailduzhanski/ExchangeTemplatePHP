<?php


namespace frontend\modules\profile\models;

use common\modules\drole\models\auth\CompaniesContactDataUse;
use common\modules\drole\models\gate\UpdateDataObjectHandler;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use frontend\modules\droleYii\models\DroleStructure;
use frontend\modules\profile\behaviors\AddCoinImageStorage;
use frontend\helpers\DroleHelper;
use Yii;
use common\models\SiteTempdata;
use common\models\UUIDGenerator;
use common\modules\drole\models\gate\StructureOperationHandler;
use frontend\modules\droleYii\models\DroleYiiModel;
use yii\web\NotFoundHttpException;

class AddCoinModel extends DroleYiiModel
{
    public $mainObjectId = 'fd27729c-0f30-444b-a124-e3e16069e7d0';

    public $newRecord = true;

    public $tariff;

    public function __construct($objectId, $recorId = false, $options = [])
    {
        $this->objectId = $objectId;
        if(!$this->recordId)
            $this->recordId = UUIDGenerator::v4();
        else
            $this->newRecord = false;

        if(!$this->serviceId)
            $this->serviceId = Yii::$app->params['service_id'];

        $this->dRole = Yii::$app->user->identity->getUserDroleID();
        $this->structure = new DroleStructure($objectId);
        $this->parseOptions($options);
        $this->fields = $this->structure->getAllowedFields();
        $this->createAttributes();
        $this->setRules();
        $this->addRule('tariff', 'required');
        $this->addRule('tariff', 'match', ['pattern' => '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/i']);
        $this->addRule('tariff', 'exist',
            [
                'targetAttribute' => 'id',
                'targetClass' => CoinpricelistModel::class
            ]
        );
    }

    protected function apiSave($objectID, $structure, $objectFields)
    {
        if($objectID == $this->mainObjectId){
            $objectFields = array_merge($objectFields, ['price_adding' => CoinpricelistModel::getPrice($this->tariff)]);
        }

        return parent::apiSave($objectID, $structure, $objectFields);
    }

    protected function saveToTemp($objectID, $structure, $objectFields)
    {
        $companycontactId = CompaniesContactDataUse::getContactDataByID('id');
        if(!$model = SiteTempdata::findOne($objectFields['id'])){
            $model = new SiteTempdata();
            $model->id = $objectFields['id'];
        }
        $model->objectid = $objectID;
        $model->companycontactid = $companycontactId;
        $model->data = $objectFields;
        if($model->objectid == $this->mainObjectId){
            $this->id = $model->id;
            $model->tariff = $this->tariff;
        }
        $model->save();
    }

    /*public static function approve($id)
    {
        $obj = SiteTempdata::find()->where(['id' => $id])->one();
        if($obj){
            $objectFields = json_decode($obj->data, true);
            $objectID = $obj->objectid;
            $dRoleID = Yii::$app->user->identity->getUserDroleID();
            $serviceID = Yii::$app->params['service_id'];

            $structure = StructureOperationHandler::getFastStructureWithCheck($objectID, $dRoleID);

            $jsonIncomBody = JSONRegistryFactory::updateObject(
                false,
                $objectID,
                DroleHelper::createUpdateParams($structure, $objectFields)
            );
            $jsonIncomBody['permission']['service_id'] = $serviceID;
            $jsonIncomBody['permission']['contact_id'] = $objectID;
            $jsonIncomBody['permission']['drole_id'] = $dRoleID;
            $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);
            if (isset($res['result']) && $res['result'] == 200) {
                return true;
            }
            return false;
        }

        return false;
    }*/

    public function isNewRecord()
    {
        return $this->newRecord;
    }

    public function approve()
    {
        if($this->save()) {
            foreach ($this->savedObjects as $object) {
                if (isset($object['id'])) {
                    SiteTempdata::deleteAll(['id' => $object['id']]);
                }
            }
            return true;
        }
        return false;
    }

    public function loadByID($id)
    {
        $this->recordId = $id;
        $this->newRecord = false;
        $companycontactId = CompaniesContactDataUse::getContactDataByID('id');
        $model = SiteTempdata::find()->where([
            'id' => $id,
            'objectid' => $this->mainObjectId
        ])->one();
        if($model && $model->companycontactid == $companycontactId){
            $data = json_decode($model->data, true);
            if(!$this->tariff){
                $this->tariff = $model->tariff;
            }
            $data = $this->loadNestedData($data);
            foreach ($this->getAttributes() as  $property => $value){
                if(isset($data[$property]))
                    $this->{$property} = $data[$property];
            }
        } else {
            throw new NotFoundHttpException();
        }
    }

    protected function loadNestedData($data)
    {
        $newData = $data;
        foreach ($data as $fieldName => $value){
            $fields = explode('.', $fieldName);
            if(count($fields) > 1 && end($fields) == 'id'){
                unset($fields[count($fields)-1]);
                $nestedFieldName = implode('__', $fields);
                if($model = SiteTempdata::findOne($value)){
                    $nestedData = json_decode($model->data, true);
                    foreach ($nestedData as $nestedField => $nestedValue){
                        $newData[$nestedFieldName.'__'.$nestedField] = $nestedValue;
                    }
                }
            }
        }
        $this->loadedData = $newData;
        return $newData;
    }

    public function attachImageBehavior()
    {
        $uploadFields = [];
        $tableFields = [];
        $objects = [];

        foreach ($this->fields as $fieldName => $fieldInfo){
            if(strtolower($fieldInfo['type']) == 'image'){
                $objectId = $fieldInfo['object_id'];
                $objectTable = $this->structure->getObjectName($objectId);
                $objectTable = $objectTable.'_data_use';
                if(array_key_exists($fieldName, $this->imageProfiles)){
                    $uploadFields[$fieldName] = $this->imageProfiles[$fieldName];
                } else {
                    $uploadFields[$fieldName] = 'default';
                }
                $tableFields[$fieldName] = [$objectTable => $this->recordId];
                $objects[$fieldName] = $objectId;
            }
        }

        $this->attachBehavior('imageStorage',
            [
                'class' => AddCoinImageStorage::class,
                'uploadType' => 'POST',
                'uploadFields' => $uploadFields,
                'tableFields' => $tableFields,
                'objects' => $objects
            ]
        );
    }

    public function isCanPublish(){
        $arr = array_flip($this->getEditableFields());
        $this->addRule($arr, 'required');
        return $this->validate();
    }

    public function update()
    {
        return $this->save('saveToTemp');
    }
}