<?php
namespace common\modules\drole\object;

use yii\data\SqlDataProvider;
use common\modules\drole\object\DBWorkConstructor;

class ObjectDataRecords extends DBWorkConstructor
{

    public function __construct($extObjectName)
    {
        $extSuffixName = "data_use";
        parent::__construct($extObjectName, $extSuffixName);
    }
    
    public function getRecords($usedFieldsByAssembly, $arrayOfID){
        //global $objectName;
        $arrayOfParams['assembly_id'] = $usedFieldsByAssembly;
        $provider = new SqlDataProvider([
            'sql' => 'SELECT ' . $this->getTableName() . '.* FROM ' . $this->getTableName() . ' where id = :assembly_id and usef = \'1\'',
            'params' => $arrayOfParams
            //, 'totalCount' => $count
        ]);
        return $provider;
    }
}