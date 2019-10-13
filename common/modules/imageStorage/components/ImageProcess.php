<?php

namespace common\modules\imageStorage\components;

use yii\helpers\FileHelper;

class ImageProcess
{
    public static $defaultQuality = 75;

    /**
     * @var \Imagick
     */
    public $item;

    /**
     * Создать превью
     * @param $width
     * @param $height
     * @return \Imagick
     */
    public function thumbnail($width, $height)
    {
        $this->item->cropThumbnailImage($width, $height);
        return $this->item;
    }

    /**
     * Создать превью не меняя пропорции
     * @param $width
     * @param $height
     * @return $this
     */
    public function smartResize($width, $height)
    {
        $imageWidth = $this->item->getImageWidth();
        $imageHeight = $this->item->getImageHeight();

        if($imageWidth < $width && $imageHeight < $height){
            return $this;
        }

        if($imageWidth < $imageHeight){
            $width = ($height / $imageHeight) * $imageWidth;
        } else {
            $height = ($width / $imageWidth) * $imageHeight;
        }
        $this->item->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);

        return $this;
    }

    /**
     * Сохранить изображение
     * @param $path
     * @param $name
     */
    public function save($path, $name)
    {
        if(!file_exists($path))
            FileHelper::createDirectory($path);
        $thumbPath = $path . DIRECTORY_SEPARATOR . $name;
        $this->item->writeImage($thumbPath);
        return $thumbPath;
    }

    /**
     * Оптимизировать изображение
     * @param $filePath
     * @param bool $quality
     * @return bool
     */
    public static function optimize($filePath, $quality = false)
    {
        if(!$quality)
            $quality = self::$defaultQuality;

        $imagick = new \Imagick();

        $rawImage = file_get_contents($filePath);

        $imagick->readImageBlob($rawImage);
        $imagick->stripImage();
        // Define image
        $width      = $imagick->getImageWidth();
        $height     = $imagick->getImageHeight();
        // Compress image

        //$imagick->setImageCompressionQuality($quality);

        $image_types = getimagesize($filePath);
        // Get thumbnail image
        $imagick->thumbnailImage($width, $height);
        // Set image as based its own type
        if ($image_types[2] === IMAGETYPE_JPEG)
        {
            $imagick->setImageFormat('jpeg');
            $imagick->setSamplingFactors(array('2x2', '1x1', '1x1'));
            $profiles = $imagick->getImageProfiles("icc", true);
            $imagick->stripImage();
            if(!empty($profiles)) {
                $imagick->profileImage('icc', $profiles['icc']);
            }
            $imagick->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
            $imagick->setColorspace(\Imagick::COLORSPACE_SRGB);
        }
        else if ($image_types[2] === IMAGETYPE_PNG)
        {
            $imagick->setImageFormat('png');
        }
        else if ($image_types[2] === IMAGETYPE_GIF)
        {
            $imagick->setImageFormat('gif');
        }
        // Get image raw data
        $rawData = $imagick->getImageBlob();

        $imagick->writeImage($filePath);

        // Destroy image from memory
        $imagick->destroy();

        return true;
        //return $rawData;
    }

    /**
     * Конвертировать изображение
     * @param $filePath
     * @param $config
     * @return bool|\Imagick|string
     */
    public static function convert($filePath, $config){
        if(isset($config['convertTo'])){
            switch (strtolower($config['convertTo'])) {
                case 'jpg' : {
                    return self::convertToJpg($filePath);
                }
            }
        }
        return $filePath;
    }

    /**
     * Конвертировать изображение в jpg
     * @param $filePath
     * @return bool|\Imagick|string
     */
    public static function convertToJpg($filePath)
    {
        if(!file_exists($filePath)){
            return false;
        }

        $info = pathinfo($filePath);
        if(isset($info['extension'])){
            switch (strtolower($info['extension'])){
                case 'png' : {
                    return self::convertPngToJpg($filePath);
                }
                default : {
                    return self::convertImageToJpg($filePath);
                }
            }
        }

        return $filePath;
    }

    /**
     * Конвертировать png в jpg
     * @param $filePath
     * @return bool|\Imagick|string
     */
    public static function convertPngToJpg($filePath)
    {
        if(!file_exists($filePath)){
            return false;
        }

        $fileInfo = pathinfo($filePath);

        $imagick = new \Imagick($filePath);

        if(strtolower($fileInfo['extension']) != 'png'){
            return $imagick;
        }

        if(isset($fileInfo['dirname']) && isset($fileInfo['filename'])){
            $newPath = $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['filename'] . '.jpg';
        } else {
            return false;
        }

        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        $white = new \Imagick();
        $white->newImage($width, $height, "white");
        $white->compositeimage($imagick, \Imagick::COMPOSITE_OVER, 0, 0);
        $white->setImageFormat('jpg');
        $white->writeImage($newPath);
        unlink($filePath);

        return $newPath;
    }

    /**
     * Конвертировать картинку в Jpg
     * @param $filePath
     * @return bool|string
     */
    public static function convertImageToJpg($filePath)
    {
        if(!file_exists($filePath)){
            return false;
        }

        $fileInfo = pathinfo($filePath);
        if(isset($fileInfo['dirname']) && isset($fileInfo['filename'])){
            $newPath = $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['filename'] . '.jpg';
        } else {
            return false;
        }
        if($fileInfo['extension'] == 'jpg')
            return $newPath;

        $imagick = new \Imagick($filePath);
        $imagick->setImageFormat('jpg');
        $imagick->writeImage($newPath);
        unlink($filePath);

        return $newPath;
    }
}