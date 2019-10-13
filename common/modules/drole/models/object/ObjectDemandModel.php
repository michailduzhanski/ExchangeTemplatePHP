<?php
namespace common\modules\drole\object;

use yii\data\SqlDataProvider;
use common\modules\drole\object\DBWorkConstructor;

class ObjectDemandModel extends DBWorkConstructor
{

    public function __construct($extObjectName)
    {
        $extSuffixName = "implemented_records";
        parent::__construct($extObjectName, $extSuffixName);
    }

    public function getValue($externalId)
    {
        //global $objectName;
        $arrayOfParams['assembly_id'] = $assemblyId;
        $provider = new SqlDataProvider([
            'sql' => 'SELECT ' . $this->getTableName() . '.*, (select ' . $this->objectName . '_structure_fields.name from ' . $this->objectName . '_structure_fields where ' . $this->objectName . '_structure_fields.id = ' . $this->getTableName() . '.field) as name FROM ' . $this->getTableName() . ' where id = :assembly_id and usef = \'1\'',
            'params' => $arrayOfParams
            //, 'totalCount' => $count
        ]);
        return $provider;
    }
}
