<?php
namespace common\modules\imageStorage\helpers;

use Yii;
use common\modules\imageStorage\components\ImageStorage;

class ImageStorageHelper
{

    /**
     * Получить информацию о файле по пути
     * @param $fullPath
     * @param bool $key
     * @return bool|mixed
     */
    public static function getFileInfo($fullPath, $key = false)
    {
        $info = pathinfo($fullPath);
        if(!$key)
            return $info;

        if(isset($info[$key]))
            return $info[$key];
        else
            return false;
    }

    /**
     * @param ImageStorage $storage
     * @param array $data
     * @return string
     */
    public static function encodeData($storage, $data)
    {
        $secretKey = $storage->secretKey;
        $data = utf8_encode(Yii::$app->security->encryptByKey(json_encode($data), $secretKey));
        return urlencode($data);
    }


    /**
     * @param $storage
     * @param string $data
     * @return array
     */
    public static function decodeData($storage, $data)
    {
        $secretKey = $storage->secretKey;
        $data = urldecode($data);
        $data = Yii::$app->security->decryptByKey(utf8_decode($data), $secretKey);
        $data = json_decode($data, true);

        return $data;
    }

    /**
     * @param $storage ImageStorage
     * @param $objectId
     * @param $fileName
     */
    public static function getFullPathFromObjectRecord($storage, $objectId, $fileName)
    {
        $storage->setPathTemplateKey('{object_id}', $objectId);
        $path = $storage->getPath();
        $path = $path . '/' . $fileName;

        return $path;
    }

    /**
     * @param $storage ImageStorage
     * @param $objectId
     * @param $fileName
     */
    public static function getWebPathFromObjectRecord($storage, $objectId, $fileName)
    {
        $storage->setPathTemplateKey('{object_id}', $objectId);
        $webPath = $storage->getWebPath() .'/'. $fileName;

        return $webPath;
    }


    public static function getImgUrl($objectId, $fileName, $owner, $size, $noPhoto = false)
    {
        Yii::$app->ImageStorage->setPathTemplateKey('{object_id}', $objectId);
        $webPath = Yii::$app->ImageStorage->getWebPath() .'/'. $fileName;
        $src = Yii::$app->ImageStorage->getSrc($webPath, $owner, $size);
        if(!$src && $noPhoto)
            return Yii::$app->ImageStorage->noPhotoUrl;

        return $src;
    }

}