<?php
/**
 * Меню в профиле
 *
 * @var $this \yii\web\View
 */

?>
<nav class="profile-nav">
    <a href=""><i class="fa fa-exchange" aria-hidden="true"></i> <?=Yii::t('frontend', 'Open trades')?></a>
    <a href=""><i class="fa fa-cog" aria-hidden="true"></i> <?=Yii::t('frontend', 'Settings')?></a>
    <a href=""><i class="fa fa-shield" aria-hidden="true"></i> <?=Yii::t('frontend', 'Security')?></a>
    <a href=""><i class="fa fa-envelope-open-o" aria-hidden="true"></i> <?=Yii::t('frontend', 'Messages')?></a>
    <a href=""><i class="fa fa-bell" aria-hidden="true"></i> <?=Yii::t('frontend', 'Market Items')?></a>
    <a href=""><i class="fa fa-bell" aria-hidden="true"></i> <?=Yii::t('frontend', 'Term deposite')?></a>
    <a href=""><i class="fa fa-bell" aria-hidden="true"></i> <?=Yii::t('frontend', 'Marketplace H.')?></a>
</nav>