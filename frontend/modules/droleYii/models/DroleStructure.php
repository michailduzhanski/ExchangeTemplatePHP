<?php


namespace frontend\modules\droleYii\models;

use common\modules\drole\models\registry\RegistryObjects;
use Yii;
use common\modules\drole\models\gate\StructureOperationHandler;
use frontend\helpers\DroleHelper;
use yii\helpers\BaseArrayHelper;

class DroleStructure
{
    private static $name = false;

    private static $currentObjectId = false;

    private $structure;

    private $fields;

    private $allowedFields = [];

    private $order = [];

    private $disableFields = [];

    private $objectId;

    public function __construct($objectId, $drole = false)
    {
        $this->objectId = $objectId;
        if(!$drole){
            $drole = Yii::$app->user->identity->getUserDroleID();
        }
        $structure = StructureOperationHandler::getFastStructureWithCheck($objectId, $drole);
        if($structure = json_decode($structure, true)){
            $this->structure = $structure;
        } else {
            throw new Exception('Structure not found');
        }

        $this->fillFields($this->structure);
    }

    private function fillFields($strucure, $lvl = 0)
    {
        foreach ($strucure as $key => $field){
            if(isset($field['nested']) && $field['nested'] == 'false'){
                if($lvl == 0){
                    $this->fields[$field['name']] = $field;
                    $this->fields[$field['name']]['order'] = 1;
                    $this->fields[$field['name']]['object_id'] = $this->objectId;
                    self::$name = false;
                } else {
                    $this->fields[self::$name .'__' . $field['name']] = $field;
                    $this->fields[self::$name .'__' . $field['name']]['order'] = 1;
                    $this->fields[self::$name .'__' . $field['name']]['object_id'] = self::$currentObjectId;
                }
            } else {
                self::$currentObjectId = $field['object'];
                $strucure = $field['nested'];
                self::$currentObjectId = $field['object'];

                if(!self::$name)
                    self::$name = $field['name'];
                else
                    self::$name = self::$name . '__' . $field['name'];

                $this->fillFields($strucure, ++$lvl);
                if($lvl!= 0) {
                    $lvl--;
                    self::$name = str_replace('__'.$field['name'], '', self::$name);
                }
            }
        }

    }

    public function getFields()
    {
        if(!$this->fields)
            $this->fillFields($this->structure);

        return $this->fields;
    }

    public function getArray()
    {
        return $this->structure;
    }

    public function getAllowedFields()
    {
        if($this->allowedFields)
            return $this->allowedFields;

        foreach ($this->getFields() as $key => $item){
            if(in_array($key, $this->disableFields))
                continue;
            if($item['perm'] > 2 && $item['perm'] != 16){
                if(isset($this->order[$key])){
                    $item['order'] = $this->order[$key];
                }
                $this->allowedFields[$key] = $item;
            }
        }

        BaseArrayHelper::multisort($this->allowedFields, ['order'], [SORT_ASC]);

        return $this->allowedFields;
    }

    public function setOrder($orderField)
    {
        $this->order = $orderField;
    }

    public function setDisableFields($fields)
    {
        $this->disableFields = $fields;
    }

    public function getObjectName($objectId)
    {
        if($obj = RegistryObjects::getObjectNameByID($objectId)){
            return $obj->name;
        }

        return false;
    }

}