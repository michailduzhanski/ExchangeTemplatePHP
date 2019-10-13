<?php
namespace common\modules\spriteImage;

use Yii;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules(
            [
                'images/sprites/<path:.*.png>' => 'sprite-image/default/open-image',
            ], false
        );
    }

}