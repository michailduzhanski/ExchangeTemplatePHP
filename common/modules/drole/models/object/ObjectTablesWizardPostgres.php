<?php

namespace common\modules\drole\models\object;

use common\modules\drole\models\registry\droles\RegistryDescriptionRolesModel;
use common\modules\drole\models\UUIDGenerator;
use Yii;
use yii\db\pgsql\Schema;

class ObjectTablesWizardPostgres
{

    public $objectName;

    public function __construct($extObjectName)
    {
        $this->objectName = $extObjectName;
    }

    public static function getTokensOfTables()
    {
        return [
            '_structure_fields',
            '_assembly_fields_use',
            '_implemented_records',
            '_implemented_records_objects',
            //'_data_drole_access',
            '_record_own',
            //'_record_creator',
            '_data_use',
            '_data_use_implemented',
            //'_data_use_fast',
            '_structure_use_fast',
            '_log',
            '_description',
            '_access_rules',
            '_sequence',
            '_filter_record',
            '_filter_group'
        ];
    }

    //tables create for postgresql

    public function createObjectTables($objectID)
    {
        $this->createStructureTable();
        $this->createAssemblyUseTable();
        //$this->createDataDroleAccessTable();
        //$this->createJsonFastDataTable();
        $this->createImplementedDataTable();
        $this->createJsonStructureFastDataTable();
        $this->createImplementedRecordsTable();
        $this->createImplementedRecordsObjectsTable();
        $this->createDataRecordOwnerTable();
        //$this->createDataRecordCreatorTable();
        $this->createAccessRulesTable();
        $this->createFiltersRecordsTable();
        $this->createFiltersGroupsTable();
        $this->createSequenceTable();
        $this->createLogTable();
        $this->createDescriptionTable();
        $this->createRecordInRegistry($objectID);
    }

    //structure fields

    public function createStructureTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'class' => Schema::TYPE_UUID . ' NOT NULL'
        ];
        $this->createTable($this->objectName . '_structure_fields', $columnsArray);
    }

    //assembly useful fields

    public function createTable($tableName, $columnsArray)
    {
        $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);
        if ($tableSchema === null) {
            Yii::$app->db->createCommand()->createTable($tableName, $columnsArray, '')->execute();
            //Yii::$app->db->createCommand()->addPrimaryKey('id', $tableName, 'id')->execute();
        }
    }

    //access to data records
    /* public function createDataDroleAccessTable() {
      $columnsArray = [
      'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
      'record_id' => Schema::TYPE_UUID . ' NOT NULL',
      'drole_id' => Schema::TYPE_UUID . ' NOT NULL'
      ];
      $this->createTable($this->objectName . '_data_drole_access', $columnsArray);
      } */

    //fast access to json data

    public function createAssemblyUseTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL',
            'field' => Schema::TYPE_UUID . ' NOT NULL',
            'turn' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT \'0\'',
            'usef' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\'',
            'visible' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\'',
            'edit' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\'',
            'delete' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\'',
            'insert' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\''
        ];
        $this->createTable($this->objectName . '_assembly_fields_use', $columnsArray);
        Yii::$app->db->createCommand()->setSql('ALTER TABLE "' . $this->objectName . '_assembly_fields_use"
ADD CONSTRAINT "' . $this->objectName . '_assembly_fields_use_id_field" PRIMARY KEY ("id", "field");')->execute();
    }



    /*public function createJsonFastDataTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'assembly_id' => Schema::TYPE_UUID . ' NOT NULL',
            'record_id' => Schema::TYPE_UUID . ' NOT NULL',
            'json_field' => Schema::TYPE_JSONB . ' NOT NULL'
        ];
        $this->createTable($this->objectName . '_data_use_fast', $columnsArray);
    }*/

    //implemented records table
    public function createImplementedDataTable()
    {
        $columnsArray = [
            'implemented_id' => Schema::TYPE_UUID . ' NOT NULL',
            'drole_id' => Schema::TYPE_UUID . ' NOT NULL',
            'assembly_id' => Schema::TYPE_UUID . ' NOT NULL',
            'json_field' => Schema::TYPE_JSONB . ' NOT NULL'
        ];
        $this->createTable($this->objectName . '_data_use_implemented', $columnsArray);
        Yii::$app->db->createCommand()->setSql('ALTER TABLE "' . $this->objectName . '_data_use_implemented"
ADD CONSTRAINT "' . $this->objectName . '_data_use_implemented_implemented_drole" PRIMARY KEY ("implemented_id", "drole_id");')->execute();
    }

    public function createJsonStructureFastDataTable()
    {
        $columnsArray = [
            //'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'drole_id' => Schema::TYPE_UUID . ' NOT NULL',
            'assembly_id' => Schema::TYPE_UUID . ' NOT NULL',
            'json_structure' => Schema::TYPE_JSONB . ' NOT NULL'
        ];
        $this->createTable($this->objectName . '_structure_use_fast', $columnsArray);
        Yii::$app->db->createCommand()->setSql('ALTER TABLE "' . $this->objectName . '_structure_use_fast"
ADD CONSTRAINT "' . $this->objectName . '_structure_use_fast_drole_assembly" PRIMARY KEY ("drole_id", "assembly_id");')->execute();
        //$this->createTable($this->objectName . '_structure_use_fast', $columnsArray);
    }

    //owner class table

    public function createImplementedRecordsTable()
    {
        $columnsArray = [
            'implemented_id' => Schema::TYPE_UUID . ' NOT NULL',
            'record_id' => Schema::TYPE_UUID . ' NOT NULL',
            'turn' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT \'0\''
        ];
        $this->createTable($this->objectName . '_implemented_records', $columnsArray);
        Yii::$app->db->createCommand()->setSql('ALTER TABLE "' . $this->objectName . '_implemented_records"
ADD CONSTRAINT "' . $this->objectName . '_implemented_records_implemented_id_record_id" PRIMARY KEY ("implemented_id", "record_id");')->execute();
    }

    public function createImplementedRecordsObjectsTable()
    {
        $columnsArray = [
            'implemented_id' => Schema::TYPE_UUID . ' NOT NULL',
            'object_id' => Schema::TYPE_UUID . ' NOT NULL',
            //'record_id' => Schema::TYPE_UUID . ' NOT NULL',
            'field_id' => Schema::TYPE_UUID . ' NOT NULL'
        ];
        $this->createTable($this->objectName . '_implemented_records_objects', $columnsArray);
        Yii::$app->db->createCommand()->setSql('ALTER TABLE "' . $this->objectName . '_implemented_records_objects"
ADD CONSTRAINT "' . $this->objectName . '_implemented_records_implemented_object_field" PRIMARY KEY ("implemented_id", "object_id", "field_id");')->execute();
        /*Yii::$app->db->createCommand()->setSql('CREATE INDEX "' . $this->objectName . '_implemented_records_objects_implemented_id" ON "public"."' .
            $this->objectName . '_implemented_records_objects" USING btree ("implemented_id");')->execute();*/
    }

    public function createDataRecordOwnerTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'company_id' => Schema::TYPE_UUID . ' NOT NULL',
            'service_id' => Schema::TYPE_UUID . ' NOT NULL',
            'contact_id' => Schema::TYPE_UUID . ' NOT NULL'
        ];
        $this->createTable($this->objectName . '_record_own', $columnsArray);
    }

    public function createAccessRulesTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'name' => Schema::TYPE_STRING . '(100) NOT NULL',
            'company_id' => Schema::TYPE_UUID . '',
            'accesslevel' => Schema::TYPE_SMALLINT . " DEFAULT '0' NOT NULL",
            'subjectclass' => Schema::TYPE_UUID . ' NOT NULL',
            'subjectvalue' => Schema::TYPE_UUID . ' NOT NULL',
            'accessclass' => Schema::TYPE_UUID . '',
            'accessfield' => Schema::TYPE_UUID . '',
            'accessrecord' => Schema::TYPE_UUID . '',
            'controlclass' => Schema::TYPE_UUID . '',
            'controlfield' => Schema::TYPE_UUID . '',
            'controlsubjectfield' => Schema::TYPE_UUID . '',
            'compareoperation' => Schema::TYPE_SMALLINT . " DEFAULT '0' NOT NULL",
            'typeaccess' => Schema::TYPE_SMALLINT . " DEFAULT '0' NOT NULL",
            'classaccessvalue' => Schema::TYPE_UUID . " DEFAULT 'f76812e6-9c87-45b3-956b-6196eb5bcaa7' NOT NULL",
            'priority' => Schema::TYPE_INTEGER . " DEFAULT '0' NOT NULL"
        ];
        $this->createTable($this->objectName . '_access_rules', $columnsArray);
    }

    public function createFiltersRecordsTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'name' => Schema::TYPE_STRING . '(100) NOT NULL',
            'company_id' => Schema::TYPE_UUID . ' NOT NULL',
            'accesslevel' => Schema::TYPE_SMALLINT . " DEFAULT '0' NOT NULL",
            'map' => Schema::TYPE_TEXT . " DEFAULT '0' NOT NULL",
            'compareoperation' => Schema::TYPE_SMALLINT . " DEFAULT '0' NOT NULL",
            'valueobject' => Schema::TYPE_UUID . '',
            'valuefield' => Schema::TYPE_UUID . '',
            'value' => Schema::TYPE_STRING . '(100) NOT NULL',
            'exvalueobjectname' => Schema::TYPE_STRING . '(100) NOT NULL',
            'exvaluefieldname' => Schema::TYPE_STRING . '(100) NOT NULL'
        ];
        $this->createTable($this->objectName . '_filter_record', $columnsArray);
    }

    public function createFiltersGroupsTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'name' => Schema::TYPE_STRING . '(100) NOT NULL',
            'company_id' => Schema::TYPE_UUID . ' NOT NULL',
            'accesslevel' => Schema::TYPE_SMALLINT . " DEFAULT '0' NOT NULL",
            'map' => Schema::TYPE_TEXT . ' DEFAULT \'\' NOT NULL'
        ];
        $this->createTable($this->objectName . '_filter_group', $columnsArray);
    }

    public function createSequenceTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'field' => Schema::TYPE_UUID . ' NOT NULL',
            'value' => Schema::TYPE_STRING . '(100) NOT NULL',
            'turn' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT \'0\''
        ];
        $this->createTable($this->objectName . '_sequence', $columnsArray);
    }

    //structure for fast access data

    public function createLogTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'table_name' => Schema::TYPE_STRING . '(100) NOT NULL',
            'record_id' => Schema::TYPE_UUID . ' NOT NULL',
            'field' => Schema::TYPE_STRING . '(100) NOT NULL',
            'value_old' => Schema::TYPE_TEXT . ' NOT NULL',
            'value_new' => Schema::TYPE_TEXT . ' NOT NULL',
            'date_change' => Schema::TYPE_DOUBLE . ' NOT NULL',
            'drole_id' => Schema::TYPE_UUID . ' NOT NULL',
            'operator_id' => Schema::TYPE_UUID . ' NOT NULL',
            'ip_address' => Schema::TYPE_STRING . '(50) NOT NULL'
        ];
        $this->createTable($this->objectName . '_log', $columnsArray);
    }

    //structure for fast access data

    public function createDescriptionTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'table_name' => Schema::TYPE_STRING . '(100) NOT NULL',
            'record_id' => Schema::TYPE_UUID . ' NOT NULL',
            'description' => Schema::TYPE_TEXT . ''
        ];
        $this->createTable($this->objectName . '_description', $columnsArray);
    }

    /* public function createAssemblySettingsTable()
      {
      global $objectName;
      $columnsArray = [
      'id' => Schema::TYPE_UUID . ' NOT NULL',
      'key' => Schema::TYPE_STRING . 'NOT NULL',
      'value' => Schema::TYPE_STRING . 'NOT NULL'
      ];
      $this->createTable($objectName . '_assembly_fields_settings', $columnsArray);
      } */

    /* public function createAssemblyRoleRelationshipTable()
      {
      global $objectName;
      $columnsArray = [
      'id' => Schema::TYPE_UUID . ' NOT NULL',
      'is_active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT \'0\'',
      'assembly_id' => Schema::TYPE_STRING . ' NOT NULL',
      'company_id' => Schema::TYPE_STRING . ' NOT NULL',
      'service_id' => Schema::TYPE_STRING . ' NOT NULL',
      'contact_id' => Schema::TYPE_STRING . ' NOT NULL'
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
      } */

    public function createRecordInRegistry($objectID)
    {
        $UUID = $objectID; //UUIDGenerator::v4();
        Yii::$app->db->createCommand()->insert('registry_objects', [
            'id' => $UUID,
            'name' => $this->objectName
        ])->execute();
    }

    //input

    public function checkPresentObjectName()
    {
        // вернёт одну строку (первую строку)
        // false, если ничего не будет выбрано
        $post = Yii::$app->db->createCommand('SELECT * FROM post WHERE name = \'' . $this->objectName . '\'')
            ->queryOne();
        if (!$post) {
            return false;
        } else {
            return true;
        }
    }

    public function createAdminAssemblyForObject($droleID, $objectID, $objectName, $contactID)
    {
        $idFieldID = UUIDGenerator::v4();
        $dateCreateFieldID = UUIDGenerator::v4();
        $dateChangeFieldID = UUIDGenerator::v4();
        $assemblyID = UUIDGenerator::v4();
        $sql = "insert into " . $objectName . "_structure_fields values ('" . $idFieldID . "', 'id', 'a1d5b5c0-de80-43b4-bcb8-8168a4fedbd4')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_structure_fields values ('" . $dateCreateFieldID . "', 'date_create', '00251e04-c11f-44a1-a5ee-c51e6834c3f3')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_structure_fields values ('" . $dateChangeFieldID . "', 'date_change', '00251e04-c11f-44a1-a5ee-c51e6834c3f3')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into registry_assembly values ('" . $assemblyID . "', '$objectID', '" . $objectName . "_admin')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into registry_description values ('" . UUIDGenerator::v4() . "','registry_assembly','" . $assemblyID . "', '$objectName default admin assembly')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into registry_drole_assembly values ('" . UUIDGenerator::v4() . "', '$droleID', '$assemblyID', '$objectID', '1')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_assembly_fields_use values ('" . $assemblyID . "', '$idFieldID', '0', 'true', 'false', 'false', 'false')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_assembly_fields_use values ('" . $assemblyID . "', '$dateCreateFieldID', '1', 'true', 'false', 'false', 'false')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_assembly_fields_use values ('" . $assemblyID . "', '$dateChangeFieldID', '2', 'true', 'false', 'false', 'false')"; //with uuid type
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_description values ('" . UUIDGenerator::v4() . "', 'structure_fields', '" . $idFieldID . "', 'The primary field for record. Will not change in structure.')";
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_description values ('" . UUIDGenerator::v4() . "', 'structure_fields', '" . $dateCreateFieldID . "', 'Default field that set date creating of record.')";
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "insert into " . $objectName . "_description values ('" . UUIDGenerator::v4() . "', 'structure_fields', '" . $dateChangeFieldID . "', 'Default field that set date last changing of record.')";
        Yii::$app->db->createCommand($sql)->execute();
        $this->createDataUseTable();
        $accessRulesSql = "INSERT INTO " . $objectName . "_access_rules (id, name, company_id, accesslevel, 
            subjectclass, subjectvalue, accessclass, accessfield, accessrecord, controlclass, controlfield, 
            controlsubjectfield, compareoperation, typeaccess, classaccessvalue, priority) VALUES
('" . UUIDGenerator::v4() . "',	'" . $objectName . "_superadmin',	'" . RegistryDescriptionRolesModel::getAdminCompany() .
            "',	0,	'7052a1e5-8d00-43fd-8f57-f2e4de0c8b24',	'$contactID',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	0,	0,	'11111111-1111-1111-1111-111111111111',	0)";
        Yii::$app->db->createCommand($accessRulesSql)->execute();
        //$structureFieldModel = new ObjectStructureModel($this->objectName);
        //$structureFields = $structureFieldModel->getDataFromTable();
        //$dataTableCreator = new DataTableWizard($this->objectName);
        //$dataTableCreator->createTable($structureFields);
        //return $idFieldID;
    }

    public function createDataUseTable()
    {
        $columnsArray = [
            'id' => Schema::TYPE_UUID . ' NOT NULL PRIMARY KEY',
            'date_create' => Schema::TYPE_DOUBLE . " DEFAULT date_part('epoch', now()) NOT NULL ",
            'date_change' => Schema::TYPE_DOUBLE . " DEFAULT date_part('epoch', now()) NOT NULL "
        ];
        $this->createTable($this->objectName . '_data_use', $columnsArray);
    }

}
