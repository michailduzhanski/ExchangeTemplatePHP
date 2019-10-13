<?php

$altHash = strtoupper(md5('4K2461GflddLfbtXWZBJQnGXX'));
define('ALTERNATE_PHRASE_HASH', $altHash);

$string = Yii::$app->request->post('PAYMENT_ID', '') . ':' . Yii::$app->request->post('PAYEE_ACCOUNT', '') . ':' .
    Yii::$app->request->post('PAYMENT_AMOUNT', '') . ':' . Yii::$app->request->post('PAYMENT_UNITS', '') . ':' .
    Yii::$app->request->post('PAYMENT_BATCH_NUM', '') . ':' .
    Yii::$app->request->post('PAYER_ACCOUNT', '') . ':' . ALTERNATE_PHRASE_HASH . ':' .
    Yii::$app->request->post('TIMESTAMPGMT', '');

$hash = strtoupper(md5($string));

if ($hash == Yii::$app->request->post('V2_HASH', '')) {
    \common\modules\drole\models\wactions\PerfectDeposit::setPerfectMoney($_POST['PAYMENT_BATCH_NUM'],
        'af09ea17-d47c-452d-93de-2c89157b9d5b', 'b56b99b6-2c6f-4103-849a-e914e8594869',
        '00000000-430d-4a57-a7ec-ff125372ae09', $_POST['PAYMENT_AMOUNT'], $_POST['PAYER_ACCOUNT']);

    //sendMailToUserFromAdmin("adm.matrix.coin@gmail.com", $siteName . '. Receiving a payment.', "Hello, on your account was credited with money: " . $delta . " " . $_POST['PAYMENT_UNITS']);
    \Yii::$app->response->redirect(\yii\helpers\Url::to('/profile/default/perfdone?result=success'))->send();
    return true;
} else {
    \Yii::$app->response->redirect(\yii\helpers\Url::to('/profile/default/perfdone?result=wrong'))->send();
    return true;
}