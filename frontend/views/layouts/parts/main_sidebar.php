<?php
/**
 * Боковое меню
 *
 * @var $this \yii\web\View
 */
use yii\helpers\Url;
?>

<nav class="bg-grey sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <?php if(!Yii::$app->user->isGuest): ?>
        <li class="nav-item active">            
            <a class="nav-link" href="<?=Url::to(['/profile/default/wlist'])?>">
                <img src="/images/icons/wallets.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'Wallets')?>
                </span>
            </a>
        </li> 
        <?php endif; ?>
        <?php if(!Yii::$app->user->isGuest): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?=Url::to(['/profile/default/dcrypto'])?>">
                <img src="/images/icons/dollar-down.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'Deposite')?>
                </span>
            </a>
        </li>
        <?php endif; ?>
        <?php if(!Yii::$app->user->isGuest): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?=Url::to(['/profile/default/wcrypto'])?>">
                <img src="/images/icons/dollar-up.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'Withdraw')?>
                </span>
            </a>
        </li>
        <?php endif; ?>
        <?php if(!Yii::$app->user->isGuest): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?=Url::to(['/profile/default/tcrypto'])?>">
                <img src="/images/icons/arrows.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'Transfers')?>
                </span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <ul class="nav">
        
        <li class="nav-item">
            <a class="nav-link" href="<?=Url::to(['/profile/default/coinlist'])?>">
                <img src="/images/icons/exchange.png" alt="">
                <span class="menu-title"><?=Yii::t('frontend', 'Exchange')?></span>
            </a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" href="#">
                <img src="/images/icons/marketplace.png" alt="">
                <span class="menu-title"><?=Yii::t('frontend', 'Marketplace')?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <img src="/images/icons/coininfo.png" alt="">
                <span class="menu-title"><?=Yii::t('frontend', 'CoinInfo')?></span>
            </a>
        </li> -->
        <?php if(!Yii::$app->user->isGuest): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?=Url::to(['/profile/default/ctransaction'])?>">
                <img src="/images/icons/history.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'History') ?>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?=Url::to(['/profile/default/htransaction'])?>">
                <img src="/images/icons/h-transfers.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'Market History') ?>
                </span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="<?=Url::to(['/news/default/index'])?>">
                <img src="/images/icons/newspaper.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'News')?>
                </span>
            </a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" href="#">
                <img src="/images/icons/badge.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'Awards')?>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <img src="/images/icons/dice.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'Lotto')?>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <img src="/images/icons/bar-chart.png" alt="">
                <span class="menu-title">
                    <?=Yii::t('frontend', 'Arbitrage')?>
                </span>
            </a>
        </li> -->
    </ul>
</nav>
