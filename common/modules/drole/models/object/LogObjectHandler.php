<?php

namespace common\modules\drole\models\object;

use yii\data\SqlDataProvider;
use common\modules\drole\models\object\DBWorkConstructor;
use common\modules\drole\models\UUIDGenerator;

class LogObjectHandler extends DBWorkConstructor {

    public function __construct($extObjectName) {
        $extSuffixName = "log";
        parent::__construct($extObjectName, $extSuffixName);
    }

    public function setLogForModule($table_name, $record_id, $field, $value_old, $value_new, $date_change, $drole_id, $operator_id, $ip_address) {
        self::setLogModule($this->objectName, $table_name, $record_id, $field, $value_old, $value_new, $date_change, $drole_id, $operator_id, $ip_address);
    }

    public static function setLogModuleWithoutOldValue($object_title, $table_name, $record_id, $field, $value_old, $value_new, $date_change, $drole_id, $operator_id, $ip_address) {
        $insertQuery = 'INSERT INTO ' . $object_title . '_log (id, table_name, record_id, field, value_old, value_new, date_change, drole_id, operator_id, ip_address) VALUES (\'' .
                UUIDGenerator::v4() . '\', \'' . $table_name . '\', \'' . $record_id . '\', \'' . $field .
                '\', \'' . $value_old . '\', \'' . $value_new . '\', \'' . $date_change . '\', \'' . $drole_id .
                '\', \'' . $operator_id . '\', \'' . $ip_address . '\')';
        echo '[' . $insertQuery . ']';
        \Yii::$app->db->createCommand($insertQuery)
                ->execute();
    }

    public static function setLogModule($object_title, $table_name, $record_id, $field, $value_new, $date_change, $drole_id, $operator_id, $ip_address) {
        $value_old = getOldValueFromTable($table_name, $record_id, $field)[0][$field];
        setLogModuleWithoutOldValue($object_title, $table_name, $record_id, $field, $value_old, $value_new, $date_change, $drole_id, $operator_id, $ip_address);
    }

    public static function getOldValueFromTable($table_name, $record_id, $field) {
        $query = 'select ' . $field . ' from ' . $table_name . ' WHERE id=\'' . $record_id . '\'';
        $selectProvider = new SqlDataProvider([
            'sql' => $query
        ]);
        $row = $selectProvider->getModels();
        return $row;
    }

    public static function getOldValueFromTableWithLastChange($object_title, $table_name, $record_id, $field) {
        $query = 'select * from ' . $object_title . '_log WHERE table_name = \'' . $table_name . '\' and record_id=\'' . $record_id . '\' and field=\'' . $field . '\' order by desc';
        $selectProvider = new SqlDataProvider([
            'sql' => $query
            , 'totalCount' => 1
        ]);
        $row = $selectProvider->getModels();
        return $row;
    }

    public static function updateLogRecordForObject($objectName, $objectTable, $recordID, $fieldToUpdateID, $oldValue, $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0') {
        $insertQuery = 'INSERT INTO ' . $objectName . '_log (id, table_name, record_id, field, value_old, value_new, date_change, drole_id, operator_id, ip_address) VALUES (\'' . UUIDGenerator::v4() . '\', \'' . $objectTable . '\', \'' . $recordID . '\', \'' . $fieldToUpdateID . '\', \'' . $oldValue . '\', \'' . $newValue . '\', \'' . microtime(true) . '\', \'' . $droleID . '\', \'' . $contactID . '\', \'' . \Yii::$app->request->getRemoteIP() . '\')';
        \Yii::$app->db->createCommand($insertQuery)->execute();
    }

    public static function updateLogRecordForRegistry($objectTable, $recordID, $fieldToUpdateID, $oldValue, $newValue, $droleID, $contactID, $ipAddress = '0.0.0.0') {
        $insertQuery = 'INSERT INTO registry_log (id, table_name, record_id, field, value_old, value_new, date_change, drole_id, operator_id, ip_address) VALUES (\'' . UUIDGenerator::v4() . '\', \'' . $objectTable . '\', \'' . $recordID . '\', \'' . $fieldToUpdateID . '\', \'' . $oldValue . '\', \'' . $newValue . '\', \'' . microtime(true) . '\', \'' . $droleID . '\', \'' . $contactID . '\', \'' . \Yii::$app->request->getRemoteIP() . '\')';
        \Yii::$app->db->createCommand($insertQuery)->execute();
    }

}
