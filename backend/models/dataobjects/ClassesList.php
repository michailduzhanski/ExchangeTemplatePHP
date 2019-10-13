<?php

namespace backend\models\dataobjects;

use common\modules\drole\models\registry\RegistryClasses;

class ClassesList {
    public static function getClassesListForSelect($currentID = null) {
        $classesList = RegistryClasses::getAllObjects();
        $resultString = '';
        foreach ($classesList as $class) {
            if($currentID && $currentID == $class->id){
                $resultString = '<option value="' . $class->id . '">' . $class->name . '</option>' . $resultString;
            }else {
                $resultString .= '<option value="' . $class->id . '">' . $class->name . '</option>';
            }
        }
        return $resultString;
    }
}
