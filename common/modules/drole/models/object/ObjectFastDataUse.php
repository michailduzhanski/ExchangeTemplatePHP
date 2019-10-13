<?php
namespace common\modules\drole\object;

use yii\data\SqlDataProvider;
use common\modules\drole\object\DBWorkConstructor;

class ObjectFastDataUse extends DBWorkConstructor
{

    public function __construct($extObjectName)
    {
        $extSuffixName = "data_use_fast";
        parent::__construct($extObjectName, $extSuffixName);
    }

    public function getAllDataFromTable($inputQueryPermissions, $assemblyID, $arrayOfAssemblyPermissions)
    {
        $query = 'select json_field from ' . $this->getTableName() . ' where assembly_id = \'' . $assemblyID . '\'';
        $fastProvider = new SqlDataProvider([
            'sql' => $query
        ]);
        $recordsPresent = $fastProvider->getModels();
        if (count($recordsPresent) > 0) {
            return $recordsPresent;
        } else {
            return [];
        }
    }
}
