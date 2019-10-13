<?php
namespace frontend\modules\droleYii\widgets;

class FormFields extends \yii\base\Widget
{
    public  $model;

    public $viewTemplate;

    public $action;

    public function init()
    {
        if(!$this->viewTemplate)
            $this->viewTemplate = 'index';
        if(!$this->action)
            $this->action = '/';
    }

    public function run()
    {
        return $this->render($this->viewTemplate, [
            'model' => $this->model,
            'action' => $this->action
        ]);
    }
}