<?php
/**
 * Главная страница
 */

/* @var $this yii\web\View */

use yii\helpers\Url;
use common\modules\imageStorage\helpers\ImageStorageHelper;

$currentNewsID = \Yii::$app->request->get("id");

$langToken = Yii::$app->language;
//$dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);

$sql = "select newstopic_data_use.id, newstopic_data_use.date_change, newstopic_data_use.title" . $langToken .
    ", newstopic_data_use.short" . $langToken .
    ", newstopic_data_use.text" . $langToken . ", newstopic_data_use.titleimage from newstopic_data_use 
    join newstopic_record_own on newstopic_data_use.id = newstopic_record_own.id where 
    newstopic_record_own.company_id = 'af09ea17-d47c-452d-93de-2c89157b9d5b' and 
    newstopic_record_own.service_id = 'b56b99b6-2c6f-4103-849a-e914e8594869' order by newstopic_data_use.date_change desc limit 4";
$newsResult = \Yii::$app->db->createCommand($sql)->queryAll();
if (!$newsResult || count($newsResult) < 1) {
    //gohome
    echo "not found market";
    die(402);
}

$currentNewsArray = null;
if ($currentNewsID == null || $currentNewsID == '') {
    $currentNewsArray = $newsResult[0];
    array_splice($newsResult, 0, 1);
} else {
    for ($i = 0; $i < count($newsResult); $i++) {
        if ($newsResult[$i]['id'] == $currentNewsID) {
            $currentNewsArray = $newsResult[$i];
            array_splice($newsResult, $i, 1);
            break;
        }
    }
}

if ($currentNewsArray == null) {
    //gohome
    echo "not found market";
    die(402);
}

$allNews = '';
foreach ($newsResult as $nextNews) {
    $allNews .= '<div class="card news-card">
                    <img src="' . ImageStorageHelper::getWebPathFromObjectRecord(Yii::$app->ImageStorage, '655d85fa-2199-40fe-9836-295bf8a8a316',
        $nextNews['titleimage']) . '" class="img-responsive">
                    <div class="short-news-box">
                        <h3 class="card-title">' . $nextNews[('title' . $langToken)] . '</h3>
                        <p class="short-news">' . $nextNews[('short' . $langToken)] . '</p>
                        <a href="/news/topic?id=' . $nextNews['id'] . '">'.Yii::t('news_page', 'Read more').'</a>
                    </div>
                </div>';
}


$this->title = 'Hysiope';
?>

<section id="main-head">
    <div class="container">
        <div class="head-caption">
            <h1>Hysiope</h1>
            <div class="row">
                <p class="col-md-8 col-sm-8 col-xs-12 col-md-offset-2 col-sm-offset-2 subcaption-text">
                    <?=Yii::t('main_page', 'Start trading right now!')?><br>
                    <strong><?=Yii::t('main_page', 'Increase your wealth!')?></strong>
                </p>
            </div>
            <nav class="login-and-reg-btns-row">
                <a href="/login" class="btn btn-danger">
                    <?=Yii::t('main_page', 'Sign In')?>
                </a>
                <a href="/registration" class="btn btn-transperent btn-wtite-text">
                    <?=Yii::t('main_page', 'Registration')?>
                </a>
            </nav>
        </div>
    </div>
    <video class="bg" autoplay="" muted="muted" loop="loop" preload="auto"> 
        <source type="video/mp4" src="images/main-video-bg.mp4"> 
    </video>
</section>


<section id="top-categories">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a class="top-category-item" href="">
                    <img src="/images/top-categories-icons/analytics.svg">
                    <h3><?=Yii::t('main_page', 'Exchange') ?></h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a class="top-category-item" href="">
                    <img src="/images/top-categories-icons/pay-per-click.svg">
                    <h3><?=Yii::t('main_page', 'Marketplace') ?></h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a class="top-category-item" href="">
                    <img src="/images/top-categories-icons/tablet.svg">
                    <h3><?=Yii::t('main_page', 'Forum') ?></h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a class="top-category-item" href="">
                    <img src="/images/top-categories-icons/worldwide.svg">
                    <h3><?=Yii::t('main_page', 'HXP to crypto') ?></h3>
                </a>
            </div>
        </div>
    </div>
</section>

<section id="advantages">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <img src="/images/advantages-bg.png" class="img-responsive">
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="advantage-item">
                    <h3><img src="/images/icon1.png">
                        <?=Yii::t('main_page', 'You\'ll like it!') ?></h3>
                    <p><?=Yii::t('main_page', 'We have prepared a tempting bounty, airdrop and of course a referral system! We appreciate our user community and will constantly hold promotions, play prizes, crush discounts on commissions.') ?></p>
                </div>
                <div class="advantage-item">
                    <h3><img src="/images/icon2.png">
                        <?=Yii::t('main_page', 'Buy and sell easily!')?></h3>
                    <p><?=Yii::t('main_page', 'The Hysiope exchange interacts with numerous payment services, trading robots, financial companies and other exchanges through API. Having installed the services of our exchange on your website, you can easily promote your products and services to thousands of people.')?></p>
                </div>
                <div class="advantage-item">
                    <h3><img src="/images/icon3.png">
                        <?=Yii::t('main_page', 'How to launch your own exchange of cryptocurrencies')?>?
                    </h3>
                    <p><?=Yii::t('main_page', 'Team Hysiopе offers you a universal solution - rent your own exchange in the cloud! We offer a "white label" solution - a model of cooperation in which one company produces a product and another sells it under its own brand. In addition, you rent a solution in the cloud, and this saves you a lot of money invested when you start the exchange.')?></p>
                </div>
                <div class="advantage-item">
                    <h3><img src="/images/icon4.png">
                        <?=Yii::t('main_page', 'Advantages of trading on Hysiope.')?>
                    </h3>
                    <p>
                        <?=Yii::t('main_page', 'We guarantee profitable investment opportunities and conditions.<br>We provide training materials.<br>We employ an aggressive cold storage policy on all currencies in our system.<br>We guarantee instant transactions and immediate execution of market orders.<br>We provide fast and reliable 24/7 support.')?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="latest-news">
    <div class="container">
        <h3 class="section-title"><?=Yii::t('main_page', 'Latest news')?></h3>
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="card news-card latest-news">
                    <img src="<?php echo ImageStorageHelper::getWebPathFromObjectRecord(Yii::$app->ImageStorage, '655d85fa-2199-40fe-9836-295bf8a8a316', $currentNewsArray['titleimage']); ?>" class="img-responsive">
                    <div class="short-news-box">
                        <h3 class="card-title"><?= $currentNewsArray[('title' . $langToken)] ?></h3>
                        <p class="short-news"><?= $currentNewsArray[('short' . $langToken)] ?></p>
                        <a href="/news/topic?id=<?= $currentNewsArray['id'] ?>"><?=Yii::t('news_page', 'Read more')?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <?= $allNews ?>
            </div>
        </div>
    </div>
</section>