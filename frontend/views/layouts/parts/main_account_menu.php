<?php
/**
 * Меню акаунта
 *
 * @var \yii\web\View $this
 */

use \yii\helpers\Url;
use \yii\helpers\Html;
use \yii\bootstrap\ActiveForm;
?>

<li class="nav-item">
    <a class="nav-link" href="<?=Url::to(['/profile/default/wlist'])?>">
        <img src="/images/icons/wallets.png" alt="">
        <span class="menu-title">
                            <?=Yii::t('frontend', 'Wallets')?>
                        </span>
    </a>
</li>
<li class="nav-item">
<?=\frontend\modules\dayNightMode\Widget::widget() ?>
</li>
<li class="nav-item dropdown hidden-sm hidden-xs">
    <?=\frontend\containers\usermenu\UserMenu::widget()?>
</li>
<!-- <li class="nav-item dropdown">
    <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
        <i class="fa fa-bell" aria-hidden="true"></i>
        <span class="count">4</span>
    </a>
    <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
        <a class="dropdown-item">
            <p class="mb-0 font-weight-normal">You have 4 new notifications</p>
        </a>

        <a class="dropdown-item preview-item">
            <div class="preview-item-content">
                <h6 class="preview-subject font-weight-medium">Application Error</h6>
                <p class="font-weight-light small-text">Just now</p>
            </div>
        </a>

        <a class="dropdown-item preview-item">
            <div class="preview-item-content">
                <h6 class="preview-subject font-weight-medium">New user registration</h6>
                <p class="font-weight-light small-text">1 day ago</p>
            </div>
        </a>

    </div>
</li> -->