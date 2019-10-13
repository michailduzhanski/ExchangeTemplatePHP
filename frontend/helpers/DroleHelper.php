<?php
namespace frontend\helpers;

use Yii;
use yii\base\Exception;
use yii\helpers\Json;

class DroleHelper
{

    private static $mapLvls = [];

    /**
     * Генерирует список ппараметров для JSONRegistryFactory::updateObject функции
     * @param $structure
     * @param $fields [['fieldName1' => 'fieldValue1'], ['FieldName2' => 'FieldValue2']]
     * @return array
     */
    public static function createUpdateParams($structure, $fields)
    {
        $params = [];
        foreach ($fields as $field => $value) {
            $params[] = self::createUpdateParam($structure, $field, $value);
        }

        return $params;
    }

    /**
     * Генерирует параметр для JSONRegistryFactory::updateObject функции
     * Например
     *   [
     *      'field' => string 'd2a47321-e0da-4ee5-bc76-110a4e67090c'
     *      'map' => '0',
     *      'value' => '4dc3e9c6-ef0c-49ef-b468-281c5a435d3a'
     *  ]
     * @param $structure Структура объекта
     * @param $fieldName  Можно использовать вложения. Например contactlinks.id
     * @param $value Значение
     * @return array
     * @throws Exception
     */
    public static function createUpdateParam($structure, $fieldName, $value)
    {
        $data = self::getFieldInfoByName($structure, $fieldName);
        if ($data !== false && isset($data['field']['id'])) {
            return ['field' => $data['field']['id'], 'map' => $data['map'], 'value' => $value];
        }

        throw new Exception('The ' . $fieldName . ' field was not found.');
    }

    /**
     * Получить путь к полю по его имени.
     * @param $structure
     * @param $fieldName Можно использовать вложения. Например contactlinks.id
     * @return array|bool
     */
    public static function getFieldMapPathByName($structure, $fieldName)
    {
        return self::getFieldInfo($structure, $fieldName, true);
    }

    /**
     * Получить путь и структуру поля по его имени.
     * @param $structure
     * @param $fieldName Можно использовать вложения. Например contactlinks.id
     * @param $onlyMapPath Вернуть только путь
     * @return bool|array
     */
    public static function getFieldInfoByName($structure, $fieldName, $onlyMapPath = false)
    {
        if (is_string($structure)) {
            if (!$structure = Json::decode($structure)) {
                return false;
            }
        }
        $names = explode('.', $fieldName);
        if (count($names) > 1) {
            $map = [];
            $field = [];
            foreach ($names as $name) {
                if ($info = self::getFieldInfo($structure, 'name', $name)) {
                    if (isset($info['field']['nested'])) {
                        $structure = $info['field']['nested'];
                        $field = $info['field'];
                        $map[] = $info['map'];
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            if ($onlyMapPath) {
                return implode('.', $map);
            } else {
                return ['field' => $field, 'map' => implode('.', $map)];
            }
        } else {
            return self::getFieldInfo($structure, 'name', $names[0]);
        }
    }

    /**
     * Получить путь к полю по его id.
     * @param $structure
     * @param $fieldId
     * @return bool|string
     * Например 3.0.1
     */
    public static function getMapPathToFieldById($structure, $fieldId)
    {
        return self::getMapPathToFieldByField($structure, 'id', $fieldId);
    }

    /**
     * @param $structure
     * @param $fieldName
     * @param $fieldValue
     * @param int $lvl
     * @return array|bool Возвращает массив в виде:
     *  [
     *  'field' => Структура
     *  'map' => Путь к полю
     * ]
     */
    public static function getFieldInfo($structure, $fieldName, $fieldValue, $lvl = 0)
    {
        if (is_string($structure)) {
            if (!$structure = Json::decode($structure)) {
                return false;
            }
        }
        if ($structure) {
            foreach ($structure as $key => $fields) {
                self::$mapLvls[$lvl] = $key;

                if (isset($fields[$fieldName]) && $fields[$fieldName] == $fieldValue) {
                    $map = implode('.', self::$mapLvls);
                    self::$mapLvls = [];

                    return ['field' => $fields, 'map' => $map];
                }

                if (isset($fields['nested']) && is_array($fields['nested'])) {
                    if ($result = self::getFieldInfo($fields['nested'], $fieldName, $fieldValue, ++$lvl)) {
                        return $result;
                    } else {
                        unset(self::$mapLvls[$lvl]);
                        $lvl--;
                    }
                }
            }
        }

        return false;
    }



    /**
     * Получить путь к полю по имени поля $fieldName со значением $fieldValue
     * @param $structure
     * @param $fieldName
     * @param $fieldValue
     * @param int $lvl
     * @return bool|string
     */
    public static function getMapPathToFieldByField($structure, $fieldName, $fieldValue, $lvl = 0)
    {
        foreach ($structure as $key => $fields) {
            self::$mapLvls[$lvl] = $key;

            if (isset($fields[$fieldName]) && $fields[$fieldName] == $fieldValue) {
                $map = implode('.', self::$mapLvls);
                self::$mapLvls = [];
                return $map;
            }

            if (isset($fields['nested']) && is_array($fields['nested'])) {
                if ($result = self::getMapPathToFieldByField($fields['nested'], $fieldName, $fieldValue, ++$lvl)) {
                    return $result;
                } else {
                    unset(self::$mapLvls[$lvl]);
                    $lvl--;
                }
            }
        }

        return false;
    }

    public static function getFieldById($structure, $fieldId)
    {
        if (is_string($structure)) {
            if (!$structure = Json::decode($structure)) {
                return null;
            }
        }
        foreach ($structure as $item) {
            if ($item['id'] == $fieldId) {
                return $item;
            }
        }

        return null;
    }

    public static function getFieldByName($structure, $fieldName)
    {
        if (is_string($structure)) {
            if (!$structure = Json::decode($structure)) {
                return null;
            }
        }
        foreach ($structure as $item) {
            if ($item['name'] == $fieldName) {
                return $item;
            }
        }

        return null;
    }



}