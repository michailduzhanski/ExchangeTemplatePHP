<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 7/16/2018
 * Time: 1:09 AM
 */

namespace common\modules\drole\models\gate;


class CheckIncomingWords
{
    public static function checkRequestNumber($textrequest)
    {
        //$result = self::checkrequest($textrequest);
        $result = preg_replace('~\D+\.\,~', '', ($textrequest));
        $result = str_replace(',', '.', $result);
        $result = str_replace('/', '.', $result);
        if (!is_numeric($result)) {
            self::setLog(date('Y/m/d H:i:s') . " - [" . $_SERVER['REMOTE_ADDR'] . "] " . $textrequest);
            die(APIHandler::getErrorArray(500, "Unexpected symbol.", true));
        }
        return $result;
    }

    public static function setLog($somecontent)
    {
        $sql = "select login from contact_data_use where id = (select site_contact_auth_log.uid from site_contact_auth_log where site_contact_auth_log.ip = '" . $_SERVER['REMOTE_ADDR'] . "' order by time desc limit 1)";
        $presentContact = \Yii::$app->db->createCommand($sql)->queryOne();
        //$filename = dirname(__FILE__) . '/../log/fieldinputhack.log';
        $filename = realpath(\Yii::$app->basePath . '/../data/log/fieldinputhack.log');
        if ($presentContact && count($presentContact) > 0)
            $somecontent .= "     " . $presentContact['login'] . "\n";
        else {
            $somecontent .= "     Not found\n";
        }
// Вначале давайте убедимся, что файл существует и доступен для записи.
        if (is_writable($filename)) {

            // В нашем примере мы открываем $filename в режиме "записи в конец".
            // Таким образом, смещение установлено в конец файла и
            // наш $somecontent допишется в конец при использовании fwrite().
            if (!$handle = fopen($filename, 'a')) {
                return -1;
            }
            // Записываем $somecontent в наш открытый файл.
            if (fwrite($handle, $somecontent) === FALSE) {
                return -2;
            }
            fclose($handle);
        } else {
            //die('can\'t open file: ' . $filename);
            return -3;
        }
        return 1;
    }

    public
    static function checkRequestString($textrequest, $isAddSlashes = false)
    {
// Удираем лишние пробелы
        $textrequest = trim($textrequest);
// Производим конвертацию в необходимую кодировку (нужно если у Вас проект использует кодировку отличную от UTF-8)
// $textrequest = iconv("UTF-8", "WINDOWS-1251", $textrequest);
// Фиксируем атаки
        if (strpos($textrequest, "https://") === false) {
            if (strpos($textrequest, "http://") === false) {
                //do nothing
            } else {
                $textrequest = substr($textrequest, 7);
            }
        } else {
            $textrequest = substr($textrequest, 8);
        }
        if (preg_match("/script|\*|<|>|<|>|\\\\|UPDATE|INSERT/i", $textrequest)) {
            self::setLog(date('Y/m/d H:i:s') . " - [" . $_SERVER['REMOTE_ADDR'] . "] " . $textrequest);
            die(APIHandler::getErrorArray(500, "Unexpected symbol.", true));
        }
// Если не число, то экранируем кавычки
        if ($isAddSlashes && !is_numeric($textrequest)) {
            $textrequest = addslashes($textrequest);
        }
        /*if (strlen($textrequest) > $length) {
            return substr($textrequest, 0, $length);
        }*/
        return $textrequest;
    }
}