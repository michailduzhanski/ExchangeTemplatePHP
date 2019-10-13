<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 5/10/2018
 * Time: 10:10 AM
 */

namespace common\modules\drole\models\registry\droles;


class AssemblyDescriptionModel
{
    public static function getDescriptionOfAssemblyType(){
        return [
            0 => "Type of assembly used to display data",
            1 => "Type of assembly is used only in management"
        ];
    }
}