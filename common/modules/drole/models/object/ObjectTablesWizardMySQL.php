<?php
namespace common\modules\drole\object;

use Yii;
use common\modules\drole\UUIDGenerator;
use yii\db\mysql\Schema;

class ObjectTablesWizardMySQL
{

    public $objectName;

    public function __construct($extObjectName)
    {
        global $objectName;
        $objectName = $extObjectName;
    }

    //tables create for MySQL
    public function createTable($tableName, $columnsArray)
    {
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);
        if ($tableSchema === null) {
            Yii::$app->db->createCommand()->createTable($tableName, $columnsArray, 'ENGINE = MyIsam')->execute();
            Yii::$app->db->createCommand()->addPrimaryKey('id', $tableName, 'id')->execute();
        }
    }

    //structure fields
    public function createStructureTable()
    {
        global $objectName;
        $columnsArray = [
            'id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'class' => Schema::TYPE_STRING . ' NOT NULL'
        ];
        $this->createTable($objectName . 'structure_fields', $columnsArray);
    }

    //assembly useful fields
    public function createAssemblyUseTable()
    {
        global $objectName;
        $columnsArray = [
            'id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'field' => Schema::TYPE_STRING . ' NOT NULL',
            'visible' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\'',
            'edit' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\''
        ];
        $this->createTable($objectName . '_assembly_fields_use', $columnsArray);
    }

    public function createAssemblySettingsTable()
    {
        global $objectName;
        $columnsArray = [
            'id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'key' => Schema::TYPE_STRING . 'NOT NULL',
            'value' => Schema::TYPE_STRING . 'NOT NULL'
        ];
        $this->createTable($objectName . '_assembly_fields_settings', $columnsArray);
    }

    public function createAssemblyRoleRelationshipTable()
    {
        global $objectName;
        $columnsArray = [
            'id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'is_active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\'',
            'assembly_id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'company_id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'service_id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'contact_id' => Schema::TYPE_STRING . '(37) NOT NULL'
        ];
        $this->createTable($objectName . 'structure_fields', $columnsArray);
    }

    //owner class table
    public function createAssemblyOwnerTable()
    {
        global $objectName;
        $columnsArray = [
            'id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'assembly_id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'owner_class_id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'owner_assembly_id' => Schema::TYPE_STRING . '(37) NOT NULL'
        ];
        $this->createTable($objectName . '_assembly_class_owner', $columnsArray);
    }

    //owner class table
    public function createDataRecordOwnerTable()
    {
        global $objectName;
        $columnsArray = [
            'id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'record_id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'owner_class_id' => Schema::TYPE_STRING . '(37) NOT NULL',
            'owner_record_id' => Schema::TYPE_STRING . '(37) NOT NULL'
        ];
        $this->createTable($objectName . '_data_class_owner', $columnsArray);
    }

    public function createObjectTables()
    {
        $this->createStructureTable();
        $this->createAssemblyUseTable();
        $this->createAssemblySettingsTable();
        $this->createAssemblyOwnerTable();
        $this->createDataRecordOwnerTable();
    }

    //input
    public function createRecordInRegistry()
    {
        global $objectName;
        $UUID = UUIDGenerator::v4();
        Yii::$app->db->createCommand()->insert('registry_objects', [
            'id' => $UUID,
            'name' => $objectName
        ])->execute();
    }

    public function checkPresentObjectName()
    {
        global $objectName;
        // вернёт одну строку (первую строку)
        // false, если ничего не будет выбрано
        $post = Yii::$app->db->createCommand('SELECT * FROM post WHERE name = \'' . $objectName . '\'')
            ->queryOne();
        if (!$post) {
            return false;
        } else {
            return true;
        }
    }
}
