<?php 
use yii\helpers\Url;
?>
<!--<li><a href="#"><?=Yii::t('frontend', 'CoinInfo')?></a></li>-->
<li><a href="<?=Url::to(['/news/default/index'])?>"><?=Yii::t('frontend', 'News')?></a></li>
<li><a href="<?=Url::to(['/profile/default/coinlist'])?>"><?=Yii::t('frontend', 'Exchange')?></a></li>
<!--li><a href="#"><?=Yii::t('frontend', 'Forum')?></a></li--> 