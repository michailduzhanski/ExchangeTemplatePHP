<?php
namespace common\modules\imageStorage\widgets;

class FileInputAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@common/modules/imageStorage/widgets/src';

    public $css = [
        'fileinput.css'
    ];
}