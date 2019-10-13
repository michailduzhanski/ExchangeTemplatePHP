<?php
namespace common\modules\drole\object;

use yii\data\SqlDataProvider;
use common\modules\drole\object\DBWorkConstructor;

class ObjectAssemblyUseModel extends DBWorkConstructor
{

    public function __construct($extObjectName)
    {
        $extSuffixName = "assembly_fields_use";
        parent::__construct($extObjectName, $extSuffixName);
    }

    public function getAssemblyUsingFieldsObject($assemblyId, $using = 1, $visible = 1, $edit = 1)
    {
        //global $objectName;
        $arrayOfParams['assembly_id'] = $assemblyId;
        /*$query = 'SELECT ' . $this->getTableName() . '.*, (select ' . $this->objectName . '_structure_fields.name from ' . $this->objectName . '_structure_fields where ' . $this->objectName . '_structure_fields.id = ' . $this->getTableName() . '.field) as name FROM ' . $this->getTableName() . ' where id = \'' . $assemblyId . '\' and usef = \'1\'';
        if ($this->objectName == 'address') {
            echo '[get assembly: ' . $query . ']';
        }*/
        $provider = new SqlDataProvider([
            'sql' => 'SELECT ' . $this->getTableName() . '.*, (select ' . $this->objectName . 
            '_structure_fields.name from ' . $this->objectName . '_structure_fields where ' . 
            $this->objectName . '_structure_fields.id = ' . $this->getTableName() . '.field) as name FROM ' . 
            $this->getTableName() . ' where id = :assembly_id and usef = \'1\'',
            'params' => $arrayOfParams
            //, 'totalCount' => $count
        ]);
        return $provider;
    }
}
