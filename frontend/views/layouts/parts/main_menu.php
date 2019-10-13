<?php
/**
 * Главное меню
 *
 * @var \yii\web\View $this
 */

use yii\helpers\Url;
?>
<li class="nav-item">
    <a class="nav-link" href="<?=Url::to(['/profile/default/coinlist'])?>"><?=Yii::t('frontend', 'Exchange')?></a>
</li>
<!-- <li class="nav-item">
    <a class="nav-link" href="#">
        <?=Yii::t('frontend', 'Marketplace')?>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="#">
        <?=Yii::t('frontend', 'CoinInfo')?>
    </a>
</li> -->
<li class="nav-item time-box"><div id="CurrentTime"></div></li>