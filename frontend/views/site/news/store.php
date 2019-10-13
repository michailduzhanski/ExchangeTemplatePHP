<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 9/18/2018
 * Time: 1:19 PM
 */

use common\modules\imageStorage\helpers\ImageStorageHelper;

$currentNewsID = \Yii::$app->request->get("id");

$langToken = Yii::$app->language;
//$dynamicRoleArray = DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);

$sql = "select newstopic_data_use.id, newstopic_data_use.date_change, newstopic_data_use.title" . $langToken .
    ", newstopic_data_use.short" . $langToken .
    ", newstopic_data_use.text" . $langToken . ", newstopic_data_use.titleimage from newstopic_data_use 
    join newstopic_record_own on newstopic_data_use.id = newstopic_record_own.id where 
    newstopic_record_own.company_id = 'af09ea17-d47c-452d-93de-2c89157b9d5b' and 
    newstopic_record_own.service_id = 'b56b99b6-2c6f-4103-849a-e914e8594869' order by newstopic_data_use.date_change desc";
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
    $allNews .= '<a class="news-item-short" href="/news/topic?id=' . $nextNews['id'] . '">
                    <img src="' . ImageStorageHelper::getWebPathFromObjectRecord(Yii::$app->ImageStorage, '655d85fa-2199-40fe-9836-295bf8a8a316',
            $nextNews['titleimage']) . '" alt="" class="img-responsive">
                    <h4 class="news-title">' . $nextNews[('title' . $langToken)] . '</h4>
                </a>
                ';
}


$this->title = 'Hysiope';
?>

<section id="secondary-work-space">
    <div class="container">
        <div class="row">
            <div class="col-sm-1 col-xs-12">
                <div class="news-date">
                    <div class="dd"><?= date("d", $currentNewsArray['date_change']) ?></div>
                    <div class="mm"><?= date("M", $currentNewsArray['date_change']) ?></div>
                    <div class="yyyy"><?= date("Y", $currentNewsArray['date_change']) ?></div>
                </div>
            </div>
            <div class="col-sm-8 col-xs-12">
                <div class="news-page-item">
                    <img src="<?php echo ImageStorageHelper::getWebPathFromObjectRecord(Yii::$app->ImageStorage, '655d85fa-2199-40fe-9836-295bf8a8a316', $currentNewsArray['titleimage']); ?>"
                         class="img-responsive" alt="">
                    <h1 class="news-title"><?= $currentNewsArray[('title' . $langToken)] ?></h1>
                    <?= $currentNewsArray[('text' . $langToken)] ?>
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <h3 class="section-title text-right with-border-bottom">Other news</h3>
                <?= $allNews ?>
            </div>
        </div>
    </div>
</section>
