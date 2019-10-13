<?php
namespace common\modules\spriteImage\assets;

use Yii;
use \yii\web\AssetBundle;
use yii\web\View;

class SpriteAsset extends AssetBundle
{
    public $sourcePath = '@root/data/sprites';

    public $css = [
        //'coins-sprite.css'
    ];

    public function init()
    {
    	$files = \yii\helpers\FileHelper::findFiles(Yii::getAlias($this->sourcePath),['only'=>['*.css']]);
    	$css = [];
    	foreach ($files as $key => $file) {
    		$css[] = basename($file);    		
    	}
    	$this->css = $css;

        $spriteObj =  Yii::$app->SpriteImage->getSpriteJsonObj();
        $js = <<<JS
        

function getIcon(category, id, size)
{
    if (size == undefined) size = 'small';
    id = id.replace(/\.[^.]+$/, "");    
    
    var spriteObj = $spriteObj;
    if(spriteObj[category] != undefined){
        id = category+'-icon-'+ id + '_' + size;
        if(spriteObj[category].indexOf(id) > -1){
            //return '<i class="icon '+id+'"></i>';
            return 'icon '+id;
        }      
    };
    
    return false;
}
JS;
        Yii::$app->view->registerJs($js, View::POS_HEAD);
    }
}