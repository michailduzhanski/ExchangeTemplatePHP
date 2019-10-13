<?php

namespace common\modules\drole\models\object;

use Yii;
use common\modules\drole\models\object\DBWorkConstructor;
use yii\db\mysql\Schema;
use common\modules\drole\models\registry\RegistryObjects;
use common\modules\drole\models\registry\RegistryClasses;

class DataTableWizard extends DBWorkConstructor {

    //public static $arraySimpleClasses = ['uuid', 'string', 'boolean', 'integer', 'long', 'float', 'double', 'date'];

    public function __construct($extObjectName) {
        $extSuffixName = "data_use";
        parent::__construct($extObjectName, $extSuffixName);
    }

    public function createTable($objectStructureTable) {
        $currentModel = $objectStructureTable->getModels();
        if (count($currentModel) == 0) {
            return;
        }
        $columnsArray = array();
        $isCorrectWithID = false;
        foreach ($currentModel as $column) {
            $fieldName = '';
            if ($this->isUUIDToken($column['class'])) {
                $fieldName = RegistryObjects::getObjectNameByID($column['class'])->name;
            }
            
            if ($column['name'] == 'id') {
                $columnsArray[$column['name']] = Schema::TYPE_UUID . ' NOT NULL';
            } else if ($fieldName == '' || $fieldName == false) {
                $columnsArray[$column['name']] = $this->createColumnMysql($column);
            } else {
                $columnsArray[$column['name']] = Schema::TYPE_UUID . '';
            }
            if ($column['name'] == 'id') {
                $isCorrectWithID = true;
            }
        }
        $tableName = $this->getTableName();
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);
        if ($tableSchema === null && $isCorrectWithID) {
            Yii::$app->db->createCommand()->createTable($tableName, $columnsArray, '')->execute();
            Yii::$app->db->createCommand()->addPrimaryKey($tableName . '_id', $tableName, 'id')->execute();
        }
    }

    private function isUUIDToken($className) {
        $arrayValues = explode('-', $className);
        if (strlen($className) == 36 && count($arrayValues == 5)) {
            return true;
        } else {
            return false;
        }
    }

    //only for mysql!!!
    public function createColumnMysql($column) {
        $dataType = $column['class'];
        $fieldName = RegistryClasses::getObjectNameByID($dataType)->name;
        switch ($fieldName) {
            case 'String':
                return Schema::TYPE_STRING . '';
            case 'Uuid':
                return Schema::TYPE_UUID . '';
            case 'Integer':
                return Schema::TYPE_INTEGER . '';
            case 'Smallint':
                return Schema::TYPE_SMALLINT . '';
            case 'Long':
                return Schema::TYPE_BIGINT . '';
            case 'Float':
                return Schema::TYPE_FLOAT . '';
            case 'Double':
                return Schema::TYPE_DOUBLE . '';
            case 'Jsonb':
                return Schema::TYPE_JSONB . '';
            case 'TimeStamp':
                return Schema::TYPE_TIMESTAMP . '(2)';
            default :
                return Schema::TYPE_STRING . '(255)';
        }
    }

}
