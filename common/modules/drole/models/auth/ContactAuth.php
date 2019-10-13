<?php

namespace common\modules\drole\models\auth;

use yii\db\ActiveRecord;
use common\modules\drole\models\UUIDGenerator;
use yii\helpers\VarDumper;

class ContactAuth extends ActiveRecord
{

    public static function tableName()
    {
        return '{{site_contact_auth}}';
    }

    public static function getContactAuthByID($id, $withTime = true)
    {
        if ($withTime)
            return self::find()->where(['uid' => $id])->andWhere(['>', 'time', round(microtime(true))])->one();
        else
            return self::find()->where(['uid' => $id])->one();
    }

    public static function getContactAuthByHash($hash)
    {
        return self::find()->where(['hash' => $hash])->andWhere(['>', 'time', round(microtime(true))])->one();
    }

    public static function updateContactAuthByID($id, $date)
    {
        $contact_auth = self::findOne($id);
        $contact_auth->time = $date;
        $contact_auth->update();
    }

    public static function updateContactAuthByHash($hash, $date, $page = '/api')
    {
        $contact_auth = self::getContactAuthByHash($hash);
        $contact_auth->time = $date;
        $contact_auth->page = $page;
        $contact_auth->update();
    }

    public static function deleteContactAuthByID($id)
    {
        $for_delete = self::find()->where(['uid' => $id])->all();
        foreach ($for_delete as $record) {
            $record->delete();
        }
    }

    public static function deleteContactAuthByHash($hash)
    {
        self::deleteAll(['hash' => $hash]);
    }

    public static function insertContactAuthArray($array, $time)
    {
        //echo 'insertContactAuthArray($array)';
        if (!isset($array['id'])) {
            $array['id'] = UUIDGenerator::v4();
        }
        $arrayByUID = self::getContactAuthByID($array['uid'], false);
        if (!$arrayByUID) {
            //do nothing
        } else {
            $arrayByHash = self::getContactAuthByHash($array['hash']);
            if (!$arrayByHash && $array['hash'] != 'API') {
                self::deleteContactAuthByID($array['uid']);
            } else {
                self::updateContactAuthByHash($arrayByUID['hash'], $array['time'], $array['page']);
                return;// false;
            }
        }
        //if (count($array) < 7) return NULL;
        $ca = new ContactAuth();
        $ca->id = $array['id'];
        $ca->uid = $array['uid'];
        $ca->drole = $array['drole'];
        $ca->ip = $array['ip'];
        $ca->uagent = $array['uagent'];
        $ca->hash = $array['hash'];
        $ca->time = $time;
        $ca->page = $array['page'];
        $ca->lang = $array['lang'];
        return $ca->save();
    }

    public static function insertContactAuthByID($uid_uuid, $drole_uuid, $ip, $uagent, $hash, $time, $page = '/api', $lang = 'en')
    {
        $id = UUIDGenerator::v4();
        $xy = ['id' => $id,
            'uid' => $uid_uuid,
            'drole' => $drole_uuid,
            'ip' => $ip,
            'uagent' => $uagent,
            'hash' => $hash,
            'page' => $page,
            'lang' => $lang,
            'time' => $time
        ];
        self::insertContactAuthArray($xy, $time);
        return $id;
    }

}
