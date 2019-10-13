<?php
namespace frontend\modules\dayNightMode;

use Yii;
use common\modules\drole\models\auth\CompaniesContactDataUse;

class Widget extends \yii\base\Widget
{
    public $actionUrl;

    public $currentMode;

    public $currentModeText;

    public function init()
    {
        if(!$this->actionUrl)
            $this->actionUrl = \yii\helpers\Url::to(['/day-night-mode/default/index']);
        $this->currentMode = CompaniesContactDataUse::getContactDataByID('nightmode');
        if($this->currentMode)
            $this->currentModeText = Yii::t('frontend', 'Day');
        else
            $this->currentModeText = Yii::t('frontend', 'Night');
    }

    public function run()
    {
        return $this->render('widget', [
            'url' => $this->actionUrl,
            'modeText' => $this->currentModeText,
            'currentMode' => $this->currentMode
        ]);
    }
}