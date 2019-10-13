<?php

namespace common\modules\imageStorage;

use Yii;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

        public function bootstrap($app)
        {
            $module = Yii::$app->getModule('image-storage');
            $webPath = $module->storageComponent->uploadDir;
            $cacheWebPath = $module->storageComponent->cacheDir;
            $app->getUrlManager()->addRules(
                [
                    $webPath.'/<path:.*>' => 'image-storage/default/open-image-dir',
                    $cacheWebPath.'/<path:.*>' => 'image-storage/default/open-image-cache',
                    'data/nophoto/<path:.*>' => 'image-storage/default/no-photo-image',
                    //'upload-test' => 'image-storage/default/upload-test'
                ], false
            );
        }

}