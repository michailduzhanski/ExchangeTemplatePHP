<?php

namespace common\modules\drole\models\auth;

use yii\db\ActiveRecord;
use common\modules\drole\models\UUIDGenerator;
use yii\helpers\VarDumper;

class ContactAuthLog extends ActiveRecord {

    public static function tableName() {
        return '{{site_contact_auth_log}}';
    }

    public static function getContactAuthByID($id) {
        return self::find()->where(['id' => $id])->one();
    }

    public static function insertContactAuthArray($array, $time) {
        $ca = new ContactAuthLog();
        $ca->id = UUIDGenerator::v4();
        $ca->uid = $array['uid'];
        $ca->drole = $array['drole'];
        $ca->ip = $array['ip'];
        $ca->uagent = $array['uagent'];
        $ca->hash = $array['hash'];
        $ca->page = $array['page'];
        $ca->time = $time;
        $ca->lang = $array['lang'];
        $ca->save();
    }

    public static function insertContactAuthByID($uid_uuid, $drole_uuid, $ip, $uagent, $hash, $time) {
        $id = UUIDGenerator::v4();
        $xy = [ 'id' => $id,
            'uid' => $uid_uuid,
            'drole' => $drole_uuid,
            'ip' => $ip,
            'uagent' => $uagent,
            'hash' => $hash,
            'time' => $time
        ];
        self::insertContactAuthArray($xy);
        return $id;
    }

}
