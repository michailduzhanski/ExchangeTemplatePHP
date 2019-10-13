<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 9/25/2018
 * Time: 4:10 PM
 */
function random_gen($length)
{
    $random = "";
    srand((double)microtime() * 1000000);
    $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $char_list .= "abcdefghijklmnopqrstuvwxyz";
    $char_list .= "1234567890";
    // Add the special characters to $char_list if needed

    for ($i = 0; $i < $length; $i++) {
        $random .= substr($char_list, (rand() % (strlen($char_list))), 1);
    }
    return $random;
}

$messageValue = '';
if (\Yii::$app->request->get('result', null) != null) {
    if (\Yii::$app->request->get('result', null) == 'success') {
        $messageValue = '<div class="alert alert-success" role="alert">
					  	<strong>Success!</strong> Replenishment action completed successfully.
					</div>';
    } else {
        $messageValue = '<div class="alert alert-danger" role="alert">
						<strong>Error!</strong> Something wrong...
					</div>';
    }
}

?>

<div class="content-wrapper">
    <div class="d-flex justify-content-center">
        <div class="">
            <div class="card">
                <div class="card-header white"><h3 class="card-title">Deposite USDpm</h3></div>
                <div class="card-body">
                    <?= $messageValue ?>
                    <form action="https://perfectmoney.is/api/step1.asp" method="POST"
                          class="form-inline form-perfect-money">
                        <div class="form-group">
                            <input type="hidden" name="PAYEE_ACCOUNT" value="U5840031">
                            <input type="hidden" name="PAYEE_NAME" value="Hysiope">
                            <input type="hidden" name="PAYMENT_ID" value="<?= random_gen(10) ?>">
                            <label>Enter sum</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input type="text" class="form-control" name="PAYMENT_AMOUNT" min="1" value="9.99">
                                <div class="input-group-append"><span class="input-group-text">.00</span></div>
                            </div>
                            <input type="hidden" name="PAYMENT_UNITS" value="USD">
                            <input type="hidden" name="STATUS_URL"
                                   value="<?= Yii::$app->urlManager->createAbsoluteUrl(['/']) ?>/drole/default/pmdeposit">
                            <input type="hidden" name="PAYMENT_URL"
                                   value="<?= Yii::$app->urlManager->createAbsoluteUrl(['/']) ?>/drole/default/pmdeposit">
                            <input type="hidden" name="PAYMENT_URL_METHOD" value="POST">
                            <input type="hidden" name="NOPAYMENT_URL"
                                   value="<?= Yii::$app->urlManager->createAbsoluteUrl(['/']) ?>/drole/default/pmdeposit">
                            <input type="hidden" name="NOPAYMENT_URL_METHOD" value="POST">
                            <input type="hidden" name="SUGGESTED_MEMO" value="<?= \Yii::$app->user->getId() ?>">
                        </div>
                        <input type="submit" class="btn btn-block btn-warning" name="PAYMENT_METHOD" value="Pay">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>