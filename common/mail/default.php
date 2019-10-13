<?php
use common\helpers\Url;
?>
<div style="max-width:600px; background: #f1f4f9; padding-bottom: 20px; margin: 0 auto;">
	<div style="background: #000000; padding: 20px 20px;">
		<div style="width: calc(50% - 10px); display: inline-block; vertical-align: middle; border-right: 2px solid #fff;">
			<div style="display: inline-block; vertical-align: middle;">
				<img src="<?=Url::toWithoutLang(['/images/logo-mini.png'], true) ?>"></div>
			<div style="display: inline-block; vertical-align: middle; color: #fff;">
				<h4 style="font-family: arial; font-size: 20px; margin: 0;">Hysiope</h4>
				<h5 style="font-family: arial; font-size: 13px; margin: 0; opacity: .7">Exchange</h5>
			</div>
		</div>
		<div style="width: calc(50% - 10px); display: inline-block; vertical-align: middle; font-family: arial; font-size: 22px; color:#fff; text-align: right;">
			<?=$title?>
		</div>
	</div>
	<div style="margin: 0 auto 0; margin-top: -60px; background: #fff; padding: 15px; width: 85%; border-radius: 8px; ">
		<div style="display: inline-block;vertical-align: middle; width: 35%; min-height: 200px; background: #ffa93a; border-radius: 8px; text-align: center;">
			<?php if(isset($data['image'])): ?>
				<img src="<?=$data['image']?>" alt="" style="margin-top: 35px;">
			<?php endif; ?>
		</div>
		<div style="display: inline-block; vertical-align: middle; font-family: arial; width: 60%; margin-left: 4%;">
			<?php if(isset($data['title'])): ?>
				<h4><?=$data['title']?></h4>
			<?php endif; ?>
			<p style="opacity: .7; font-size: 14px;">
				<?=$message ?>
			</p>
			<?php if(isset($data['code'])): ?>
			<div style="background: #f3da2b; display: inline-block; padding: 10px 15px; margin: 0 auto;">
				<?=$data['code']?>
			</div>
            <p>
                <?=Yii::t('frontend', 'Date')?>: <?=date("l, d-m-Y H:i:s") ?>
            </p>
			<?php endif; ?>
			
		</div>
	</div>
	<div style="text-align: center; margin-top: 15px; color: #393939; font-family: arial; font-size: 14px;">		
		Support: <a href="mailto:help@hysiope.com" style="color: #000; font-weight: bold;">help@hysiope.com</a>
	</div>
</div>