<?php
namespace frontend\modules\droleYii\models;

use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\UUIDGenerator;
use Yii;
use common\modules\drole\models\gate\StructureOperationHandler;
use common\modules\imageStorage\widgets\FileInput;
use yii\base\Exception;
use yii\bootstrap\ActiveField;
use common\modules\imageStorage\behaviors\ImageStorage;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use frontend\helpers\DroleHelper;
use common\modules\drole\models\gate\UpdateDataObjectHandler;
use yii\helpers\ArrayHelper;

class DroleYiiModel extends \yii\base\DynamicModel
{
    public $id;

    public $objectId;

    public $recordId;

    public $serviceId;

    public $dRole;

    /**
     * @var DroleStructure
     */
    protected $structure;

    protected $fields = [];

    protected $imageProfiles = [];

    protected $defaultValues = [];

    protected $defaultValuesFromField = [];

    protected $savedObjects = [];

    public $loadedData = [];

    public function __construct($objectId, $recorId = false, $options = [])
    {
        $this->objectId = $objectId;
        if(!$this->recordId)
            $this->recordId = UUIDGenerator::v4();
        if(!$this->serviceId)
            $this->serviceId = Yii::$app->params['service_id'];

        $this->dRole = Yii::$app->user->identity->getUserDroleID();
        $this->structure = new DroleStructure($objectId);
        $this->parseOptions($options);
        $this->fields = $this->structure->getAllowedFields();
        $this->createAttributes();
        $this->setRules();
        $this->attachImageBehavior();
    }

    protected function createAttributes()
    {
        $attributes = [];
        foreach ($this->fields as $key => $field){
            $attributes[$key] = '';
        }

        parent::__construct($attributes, []);
    }

    protected function parseOptions($options)
    {
        if(isset($options['imageProfile'])){
            $this->imageProfiles = $options['imageProfile'];
        }
        if(isset($options['order'])){
            $this->structure->setOrder($options['order']);
        }
        if(isset($options['disableFields'])){
            $this->structure->setDisableFields($options['disableFields']);
        }
        if(isset($options['defaultValues'])){
            $this->defaultValues = $options['defaultValues'];
        }
        if(isset($options['defaultValuesFromField'])){
            $this->defaultValuesFromField = $options['defaultValuesFromField'];
        }
    }

    protected function attachImageBehavior()
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
                'class' => ImageStorage::class,
                'uploadType' => 'POST',
                'uploadFields' => $uploadFields,
                'tableFields' => $tableFields,
                'objects' => $objects
            ]
        );
    }

    public function getEditableFields()
    {
        $editableFields = [];

        $attributes = $this->getAttributes();
        foreach ($this->fields as $field => $info){
            if(key_exists($field, $attributes)){
                $editableFields[$field] = $attributes[$field];
            }
        }

        return $editableFields;
    }

    public function renderWidget($field, $name)
    {
        if(isset($this->fields[$name])){
            $type = $this->fields[$name]['type'];
        } else {
            throw new Exception('Attribute not found');
        }


        switch ($type){
            case 'image' : {
                return $field->widget(FileInput::class, [
                ]);
                break;
            }
            case 'Text' : {
                return $field->widget(
                    \dosamigos\ckeditor\CKEditor::class, [
                    'options' => [
                        'rows' => 6,
                    ],
                    'preset' => 'basic',
                ]);
                break;
            }
            default: {
                return $field->textInput();
            }

        }
    }

    protected function setRules()
    {
        $attr = [];
        foreach ($this->getEditableFields() as $key => $value){
            $attr[] = $key;
        }
        $this->addRule($attr, 'safe');

        foreach ($this->fields as $fieldName => $fieldData){
            if(isset($fieldData['type'])){
                $type = strtolower($fieldData['type']);
                if($type === 'text' || $type === 'character varying')
                    $this->addRule($fieldName, 'string');
                if($type === 'double precision')
                    $this->addRule($fieldName, 'double');
                if($type === 'integer')
                    $this->addRule($fieldName, 'integer');
                if($type === 'uuid')
                    $this->addRule($fieldName, 'match', ['pattern' => '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/i']);
            }
        }
    }

    public function createSavingObjects($attributes)
    {
        $treeDepth = 0;
        foreach ($attributes as $fieldName => $value){
            $depth = explode('__', $fieldName);
            $depth = count($depth);
            $treeDepth = ($depth > $treeDepth) ? $depth : $treeDepth;
        }

        $obj = [];
        $treeDepth--;
        for($currentDepth = $treeDepth; $currentDepth+1 > 0; $currentDepth--){
            foreach ($attributes as $fieldName => $value) {
                $map = explode('__', $fieldName);
                if (count($map) - 1 >= $currentDepth) {
                    if (count($map) > 1) {
                        $objectName = $map;
                        $objectName = array_slice($objectName, 0, $currentDepth);
                        $objectName = implode('__', $objectName);
                        if($objectName == '')
                            $objectName = 'main';
                    } else {
                        $objectName = 'main';
                    }

                    if(count($map)-1 > $currentDepth){
                        $curentField = explode('__', $fieldName);
                        $curentField = array_slice($curentField, 0, $currentDepth+1);
                        $curentField = implode('__', $curentField);
                    } else {
                        $curentField = $fieldName;
                    }

                    $field = explode('__', $fieldName);
                    $field = end($field);
                    if($value !=='') {
                        $obj[$objectName][$curentField] = ['value' => $value, 'field' => $field];
                    }
                }
            }
        }

        return $obj;
    }

    protected function getObjectIdByName($objectName)
    {
        $objectID = false;
        if($objectName != 'main'){
            $fieldName = str_replace('__', '.', $objectName);
            $field = DroleHelper::getFieldInfoByName($this->structure->getArray(), $fieldName);
            if($field && isset($field['field']['object'])){
                $objectID = $field['field']['object'];
            }
        } else {
            $objectID = $this->objectId;
        }
        if(!$objectID){
            throw new Exception('Object not found');
        }
        return $objectID;
    }

    protected function getObjectStructure($objectName, $objectID = false)
    {
        if($objectName == 'main'){
            $structure = $this->structure->getArray();
        } else {
            if(!$objectID)
                $objectID = $this->getObjectIdByName($objectName);
            $structure = StructureOperationHandler::getFastStructureWithCheck($objectID, $this->dRole);
            if(!is_array($structure))
                $structure = json_decode($structure, true);
        }

        return $structure;
    }

    protected function apiSave($objectID, $structure, $objectFields)
    {
        $jsonIncomBody = JSONRegistryFactory::updateObject(
            false,
            $objectID,
            DroleHelper::createUpdateParams($structure, $objectFields)
        );
        $jsonIncomBody['permission']['service_id'] = $this->serviceId;
        $jsonIncomBody['permission']['contact_id'] = $this->objectId;
        $jsonIncomBody['permission']['drole_id'] = $this->dRole;
        $res = UpdateDataObjectHandler::updateRecordValuesByID($jsonIncomBody);
        if (isset($res['result']) && $res['result'] == 200) {
            return true;
        }
        return false;
    }

    protected function setDefaultFields($objectFields, $objectName)
    {
        $newObjectFields['id'] = $objectFields['id'];
        unset($objectFields['id']);
        foreach ($objectFields as $fieldName => $value){
            $savedField = $objectName. '__' . $fieldName;
            if(array_key_exists($savedField, $this->defaultValues)){
                $newObjectFields[$fieldName] =  $this->defaultValues[$savedField];
            } else {
                $newObjectFields[$fieldName] = $value;
            }
        }

        return $newObjectFields;
    }

    protected function getObjectNameFieldNameFromPath($path)
    {
        $object = explode('__', $path);
        if(count($object) > 1){
            $toField = end($object);
            unset($object[count($object)-1]);
            $toObjectName = implode('__', $object);
        } else {
            $toObjectName = 'main';
            $toField = $path;
        }

        return [
            'objectName' => $toObjectName,
            'fieldName' => $toField
        ];
    }

    protected function setDefaultValuesFromField($objectFields, $objectName)
    {
        $defaulvalues = $this->defaultValuesFromField;

        foreach ($defaulvalues as $toField => $fromField){
            $data = $this->getObjectNameFieldNameFromPath($toField);
            $toObjectName = $data['objectName'];
            $toField = $data['fieldName'];

            if($toObjectName == $objectName){
                $data = $this->getObjectNameFieldNameFromPath($fromField);
                $fromObjectName = $data['objectName'];
                $fromField = $data['fieldName'];
                if(isset($this->savedObjects[$fromObjectName]) && isset($this->savedObjects[$fromObjectName][$fromField])){
                    $objectFields[$toField] = $this->savedObjects[$fromObjectName][$fromField];
                }
            }
        }

        return $objectFields;
    }

    public function save($saveFunc = false)
    {
        if($this->validate()) {

            $fields = $this->getEditableFields();

            $savingObjects = $this->createSavingObjects($fields);
            $objectIDs = [];

            foreach ($savingObjects as $objectName => $object){
                $objectFields = [];
                $objectID = $this->getObjectIdByName($objectName);

                foreach ($object as $fieldModelName => $field){
                    if(array_key_exists($fieldModelName, $savingObjects)){
                        $fieldKey = explode('__', $fieldModelName);
                        $fieldKey = end($fieldKey);
                        $objectFields[$fieldKey.'.id'] = $objectIDs[$fieldModelName];
                    } else {
                        $objectFields[$field['field']] = $field['value'];
                    }
                }

                //$objectFields['id'] = UUIDGenerator::v4();
                if($objectName == 'main'){
                    if(isset($this->loadedData['id'])){
                        $objectFields['id'] = $this->loadedData['id'];
                        $this->id = $objectFields['id'];
                    } else {
                        $objectFields['id'] = UUIDGenerator::v4();
                        $this->id = $objectFields['id'];
                    }
                } else {
                    if(isset($this->loadedData[$objectName.'__id'])){
                        $objectFields['id'] = $this->loadedData[$objectName.'__id'];
                    } else {
                        $objectFields['id'] = UUIDGenerator::v4();
                    }
                }

                $objectIDs[$objectName] = $objectFields['id'];

                $objectFields = $this->setDefaultFields($objectFields, $objectName);
                $objectFields = $this->setDefaultValuesFromField($objectFields, $objectName);

                $structure = $this->getObjectStructure($objectName, $objectID);

                $this->savedObjects[$objectName] =  $objectFields;
                if($saveFunc){
                    $this->{$saveFunc}($objectID, $structure, $objectFields);
                } else {
                    $this->apiSave($objectID, $structure, $objectFields);
                }
            }

            return $this->id;
        }

        return false;
    }
}