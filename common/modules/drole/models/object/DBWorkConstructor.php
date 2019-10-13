<?php
namespace common\modules\drole\models\object;

use Yii;
use yii\db\ActiveRecord;
use common\modules\drole\controllers\DefaultController;
use yii\data\SqlDataProvider;

class DBWorkConstructor
{

    public $objectName;
    public $suffixName;

    public function __construct($extObjectName, $extSuffixName)
    {
        $this->objectName = $extObjectName;
        $this->suffixName = $extSuffixName;
    }

    public function getTableName()
    {
        $table = $this->objectName;
        $separator = '';
        if($this->objectName != ''){
            $separator = '_';
        }
        return '' . $table . $separator . $this->suffixName . '';
    }
}
