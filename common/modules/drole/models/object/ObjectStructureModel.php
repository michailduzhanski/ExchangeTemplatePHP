<?php
namespace common\modules\drole\models\object;

use yii\data\SqlDataProvider;
use common\modules\drole\models\object\DBWorkConstructor;

class ObjectStructureModel extends DBWorkConstructor
{

    public function __construct($extObjectName)
    {
        $extSuffixName = "structure_fields";
        parent::__construct($extObjectName, $extSuffixName);
    }

    public function getDataFromTable()
    {
        /* $count = Yii::$app->db->createCommand('
          SELECT COUNT(*) FROM ' . $this->getTableName() . '
          ')->queryScalar();
         */
        $provider = new SqlDataProvider([
            'sql' => 'SELECT * FROM ' . $this->getTableName() . ''
            //, 'totalCount' => $count
        ]);
        //print_r(json_encode($provider->getModels()));
// возвращает массив данных
        //return json_encode($provider);
        return $provider;
    }

    public function getValueForFieldFromTable($fieldName)
    {
        /* $count = Yii::$app->db->createCommand('
          SELECT COUNT(*) FROM ' . $this->getTableName() . '
          ')->queryScalar();
         */
        $provider = new SqlDataProvider([
            'sql' => 'SELECT * FROM ' . $this->getTableName() . ' where name = \'' . $fieldName . '\''
            //, 'totalCount' => $count
        ]);
        //print_r(json_encode($provider->getModels()));
// возвращает массив данных
        //return json_encode($provider);
        return $provider;
    }

    public function getDataAnotherObjectsFromTable()
    {
        $provider = new SqlDataProvider([
            'sql' => 'SELECT * FROM ' . $this->getTableName() . ' where class in (select registry_objects.id from registry_objects)'
            //, 'totalCount' => $count
        ]);
        return $provider;
    }
}
