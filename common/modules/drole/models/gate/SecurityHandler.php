<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 12/11/2018
 * Time: 1:09 PM
 */

namespace common\modules\drole\models\gate;

use Yii;
class SecurityHandler
{
    public static function checkrequest($textrequest, $length = 100)
    {
        if(Yii::$app->request->isPost){
            $params = Yii::$app->request->post();
            self::checkArrayForHack($params, $length);
            return true;
        }

        if(Yii::$app->request->isGet) {
            $params = Yii::$app->request->get();
            if(isset($params['q']))
                unset($params['q']);
            self::checkArrayForHack($params, $length);
            return true;
        }

        if (strpos($textrequest, "https://") === false) {
            if (strpos($textrequest, "http://") === false) {
                //do nothing
            } else {
                $textrequest = substr($textrequest, 7);
            }
        } else {
            $textrequest = substr($textrequest, 8);
        }

        $params = Yii::$app->request->getQueryParams();
        self::checkArrayForHack($params);
        self::checkrequestFilter($textrequest);
    }

    public static function checkrequestFilter($textrequest, $length = 100)
    {
        if (preg_match("/script|\*|<|>|<|>|\\\\|SELECT|UNION|UPDATE|INSERT/i", $textrequest)) {
            self::setLog(date('Y/m/d H:i:s') . " - [" . $_SERVER['REMOTE_ADDR'] . "] " . $textrequest);
            //writelog('hack', date("y.m.d H:m:s") . "\t" . $_SERVER['REMOTE_ADDR'] . "\t" . $textrequest);
            $textrequest = '';
            die('You have ban.');
            //$textrequest = date("y.m.d H:m:s") . "\t" . $_SERVER['REMOTE_ADDR'] . "\t" . $textrequest;
        }
// Очищаем опасные запросы
        if (preg_match("/[^(\w)|(А-Яа-я&=.,@():;)|(\s)]/", $textrequest)) {
            //$textrequest = '';
        }
// Если не число, то экранируем кавычки
        if (!is_numeric($textrequest)) {
            //$textrequest = mysqli_real_escape_string($textrequest);
        }
        if (strlen($textrequest) > $length) {
            return substr($textrequest, 0, $length);
        }
        return $textrequest;
    }

    /**
     * Логирование
     * @param $somecontent
     * @return int
     */
    public static function setLog($somecontent)
    {
        $filename = \Yii::getAlias('@root') . '/data/log/fieldinputhack.log';
        $somecontent .= "\n";

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
            die('can\'t open file: ' . $filename);
            return -3;
        }
        return 1;
    }

    public function numberOnly($textrequest)
    {
        $result = $this->checkrequest($textrequest);
        $result = preg_replace('~\D+\.\,~', '', ($result));
        $result = str_replace(',', '.', $result);
        $result = str_replace('/', '.', $result);
        return $result;
    }

    public static function checkArrayForHack($array, $lenWrap = 100)
    {
        foreach ($array as $key => $value){
            self::checkrequestFilter($key, $lenWrap);

            if(is_array($value))
                self::checkArrayForHack($value, $lenWrap);
            else
                self::checkrequestFilter($value, $lenWrap);
        }
    }
}