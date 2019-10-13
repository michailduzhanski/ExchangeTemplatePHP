<?php

use common\modules\drole\models\webtools\JSONRegistryFactory;
use common\modules\imageStorage\helpers\ImageStorageHelper;

$currentCurrency = \Yii::$app->request->get("currency");

if (!\common\modules\drole\models\UUIDGenerator::isUUID($currentCurrency)) {
    \Yii::$app->response->redirect(\yii\helpers\Url::to('/profile/default/dcrypto'))->send();
    return true;
}

$objectID = '4bfe0dd7-9e54-4de5-b9fa-3e4882bcd82d';
$coinObject = '5cb705ea-6c8c-4dae-a620-248545acab14';
$json = json_decode(JSONRegistryFactory::getRecordsListFromObject(true, $objectID), true);
$coinJson = $json;
$coinJson['permission']['object_id'] = $coinObject;
//delete
/*$testJson = $json;
$testJson['permission']['object_id'] = '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24';
echo json_encode($testJson);
$testValues = \common\modules\drole\models\gate\DataObjectAPIHandler::parseQuery($testJson);
echo json_encode($testValues); exit;*/

$walletStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($objectID, \Yii::$app->user->getIdentity()->auth['drole']);
function getIndexFromArray($currentStructure, $fieldID)
{
    for ($i = 0; $i < count($currentStructure); $i++) {
        if ($currentStructure[$i]['id'] == $fieldID) {
            return $i;
        }
    }
    return null;
}

$json['filters'][1] = json_decode('{"special":[{"map":"' . getIndexFromArray(json_decode($walletStructure, true), '5b296714-e069-457e-b606-3a40bea5b2f2') . '","comp":"6","value":"' . $currentCurrency . '"},{"map":"' . getIndexFromArray(json_decode($walletStructure, true), '8bfee8c9-c297-4124-a43b-909748e243a6') . '","comp":"6","value":"' . \Yii::$app->user->getId() . '"}]}', true);
$coinJson['filters'][1] = json_decode('{"special":[{"map":"0","comp":"6","value":"' . $currentCurrency . '"}]}', true);

$resultValues = \common\modules\drole\models\gate\DataObjectAPIHandler::parseQuery($json);
if ($resultValues['data']['data'][0] == null) {
    $droleArray = \common\modules\drole\models\registry\DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
    \common\modules\drole\models\wactions\CreateAllWallets::createWalletsForContact($droleArray['company_id'], $droleArray['service_id'], \Yii::$app->user->getId(), false);
    $resultValues = \common\modules\drole\models\gate\DataObjectAPIHandler::parseQuery($json);
}

$resultCoin = \common\modules\drole\models\gate\DataObjectAPIHandler::parseQuery($coinJson);

$resultValues = $resultValues['data']['data'][0];
$resultDataCoin = $resultCoin['data']['data'][0];
//echo json_encode($resultCoin);
//echo '==========================' . getIndexFromArray(json_decode($walletStructure, true), '1db747bd-0828-4572-b6e5-33be5bc031e2');
$walletID = $resultValues[getIndexFromArray(json_decode($walletStructure, true), '1db747bd-0828-4572-b6e5-33be5bc031e2')];
$coinName = $resultDataCoin[getIndexFromArray($resultCoin['data']['structure']['data'], 'd68f2806-79b7-47f9-b805-5b5fd459aeb8')];
$coinImage = $resultDataCoin[getIndexFromArray($resultCoin['data']['structure']['data'], '7596f220-bc61-46e4-b6aa-83eb08d5f804')];
/*echo json_encode($json);
exit;*/
if ($walletID == null || $walletID == '') {
    $walletID = 'Not found';
}
$qrCode = (new \Da\QrCode\QrCode($walletID))
    ->setSize(300)
    ->setMargin(5)
    ->useForegroundColor(0, 0, 0);
//echo '<img src="' . $qrCode->writeDataUri() . '">';

?>

<div class="content-wrapper">
    <div class="d-flex justify-content-center">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <h4 class="deposite-final-data black-text"><img
                                src="<?php echo ImageStorageHelper::getWebPathFromObjectRecord(Yii::$app->ImageStorage, $coinObject, $coinImage); ?>"
                                alt=""><strong><?= $coinName ?></strong></h4>
                    <div class="row">
                        <div class="col-md-5 col-sm-12 col-12">
                            <div class="qr-code-block">
                                <img src="<?= $qrCode->writeDataUri() ?>" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-md-7 col-sm-12 col-12">
                            <div class="deposite-steps">
                                <div class="step">
                                    <h5><?=Yii::t('frontend', 'Step')?> #1</h5>
                                    <p><?=Yii::t('deposite_page', 'Scan QR code or copy deposit address')?></p>
                                </div>
                                <div class="step">
                                    <h5><?=Yii::t('frontend', 'Step')?> #2</h5>
                                    <p><?=Yii::t('deposite_page', 'Pay whis your wallet')?></p>
                                </div>
                                <div class="step">
                                    <h5><?=Yii::t('frontend', 'Step')?> #3</h5>
                                    <p>
                            <?=Yii::t('deposite_page', 'Click on <span class="green-text">"Done"</span> button')?></p>
                                </div>
                                <div class="step">
                                    <h5><?=Yii::t('frontend', 'Step')?> #4</h5>
                                    <p><?=Yii::t('deposite_page','Congratulations! Your wallet is replenished!')?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="copy-address-row">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button class="btn btn-orange btn-clipboard" type="button"
                                        data-clipboard-target="#deposite_address_row"><i class="fa fa-copy"></i>
                                </button>
                            </div>
                            <div class="form-control disabled" id="deposite_address_row"><?= $walletID ?>
                            </div>
                        </div>
                    </div>
                    <a class="btn btn-success btn-block btn-lg with-mr-t"><?=Yii::t('deposite_page', 'Done')?></a></div>
            </div>
        </div>
    </div>
</div>
</div>