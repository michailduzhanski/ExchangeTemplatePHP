<?php
/**
 * АнтиБот для форм
 */
namespace frontend\behaviors;

use Yii;
use yii\base\Model;

class AntiBotBehavior extends \yii\base\Behavior
{
    /**
     * Пустое скрытое поле в форме
     * @var string
     */
    public $botField;

    public function events()
    {
        return [
            Model::EVENT_BEFORE_VALIDATE => 'beforeValidate'
        ];
    }

    public function beforeValidate()
    {
        $data = $this->owner->{$this->botField};
        if(strlen($data) > 0){
            Yii::$app->controller->refresh();
            Yii::$app->end();
        }
    }
}