<?php
namespace common\modules\spriteImage\components;

use common\models\SiteSprite;
use Yii;
use common\modules\spriteImage\components\lib\Spriter;
use \yii\base\Component;
use yii\base\Exception;

class SpriteImage extends Component
{

    public  $config;

    public $dir;

    public function init()
    {
        if(!$this->dir)
            $this->dir = Yii::getAlias('@root').'/data/sprites';
        parent::init();
    }

    protected function getCurrentConfig($name)
    {
        if(array_key_exists($name, $this->config)){
            $currentConfig = $this->config[$name];
            if(array_key_exists('objectId', $currentConfig) && array_key_exists('field', $currentConfig)){
                return $this->config[$name];
            }

            throw new Exception($name . 'not found "objectId" or "field" in ' . self::class . 'config' .' ' . $name);
        }
        throw new Exception($name . 'not found in ' . self::class . 'config');
    }

    protected function createSpriteConfig($name)
    {
        $currentConfig = $this->getCurrentConfig($name);
        $objId = $currentConfig['objectId'];
        $fieldName = $currentConfig['field'];

        $rootPath = Yii::getAlias('@root');
        $config = [
            "forceGenerate" => true,                 // set to true if you want to force the CSS and sprite generation.

            "srcDirectory" => $rootPath . "/cache/objects/".$objId."/img/", // folder that contains the source pictures for the sprite.

            "spriteDirectory" => $this->dir,   // folder where you want the sprite image file to be saved (folder has to be writable by your webserver)

            "spriteFilepath" => "/images/sprites",     // path to the sprite image for CSS rule.
            "spriteFilename" => $name."-sprite",        // name of the generated CSS and PNG file.
            "cssDirectory" => $this->dir,

            "tileMargin" => 0,                        // margin in px between tiles in the highest 'retina' dimension (default is 0) - if you generate different 'retina' dimensions, take a common multiple of the selected variants.
            //"retina" => array(2, 1),                  // defines the desired 'retina' dimensions, you want.
            "retinaDelimiter" => "@",                 // delimiter inside the sprite image filename.
            "namespace" => $name."-icon-",                   // namespace for your icon CSS classes

            "ignoreHover" => false,                   // set to true if you don't need hover icons
            "hoverSuffix" => "-hover"
        ];

        return $config;
    }

    public function create($name)
    {
        $spriteConfig = $this->createSpriteConfig($name);
        $spriter = new Spriter($spriteConfig);

        if ($spriter->hasGenerated) {            
            $icons = $spriter->getIcons();
            if(!$model = SiteSprite::findOne($name)){
                $model = new SiteSprite();
            }
            $model->name = $name;
            $model->data = $icons;
            $model->save();

            return true;
        }

        return false;
    }



    public function getSpriteJsonObj()
    {
        $spriteObj = [];
        $models = SiteSprite::find()->all();
        foreach ($models as $model){
            $spriteObj[$model->name] = $model->data;
        }
        $spriteObj = json_encode($spriteObj);

        return $spriteObj;
    }
}