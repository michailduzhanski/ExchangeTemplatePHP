<?php 
use yii\helpers\Url;
?>
<footer>
    <div class="top-footer-line">
        <div class="container">
            <div class="row">
                <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                    <p class="footer-menu-title">Menu</p>
                    <nav>
                        <a href="">Trading</a>
                        <a href="">Funding</a>
                        <a href="">White paper</a>
                        <a href="">Security</a>
                    </nav>
                </div> -->
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <p class="footer-menu-title invisible">
                        <?=Yii::t('frontend', 'Menu')?>
                    </p>
                    <nav>
                        <!-- <a href="">Customer Support</a> -->
                        <!-- <a href="">API Keys</a> -->
                        <a href="<?=Url::to(['/news/default/index'])?>">
                            <?=Yii::t('frontend', 'News')?>
                        </a>
                        <!-- <a href="">Referral Program</a> -->
                    </nav>
                </div>
                <div class="col-md-4 col-sm-12 col-xs-12">
                    <p class="footer-menu-title">
                        <?=Yii::t('frontend', 'Subscribe now!')?>
                    </p>
                    <nav class="socials">
                        <a href="https://www.facebook.com/haysiope.page" class="fb" target="_blanck"><i class="fa fa-facebook"></i></a>
                        <a href="https://twitter.com/hysiope/following" target="_blanck" class="twitter"><i class="fa fa-twitter"></i></a>
                        <a href="https://vk.com/id495205160" class="vk" target="_blanck"><i class="fa fa-vk"></i></a>
                        <!-- a href="" class="youtube" target="_blanck"><i class="fa fa-youtube"></i></a> -->
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright-row">
        <div class="container">
            &copy; 2018. All right reserved by <a href="">Hysiope</a>.
        </div>
    </div>
</footer>