<?php

use \frontend\containers\usercard\UserCard;
use \frontend\containers\profilecard\ProfileCard;
use yii\helpers\Url;

/**
 * Страница профиля
 *
 * @var $this \yii\web\View
 */

?>


<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                    <?=UserCard::widget() ?>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-xs-12">
                    <div class="current-status">
                        <!--<h3 class="user-lvl lvl-icon lvl-active"><?/*=Yii::t('frontend', 'Lv.')*/?> 2</h3>-->
                        <!--<p class="lvl-limit"><?/*=Yii::t('frontend', 'Limit:')*/?> 100 BitCoin</p>-->
<!--                         <div class="verify-status verify-error verify-success">
    <?=Yii::t('profile_page', 'Unverified')?>
</div> -->
                        <div class="verify-status verify-success">
                            <?=Yii::t('profile_page', 'Verified')?>
                        </div>
                    </div>
                </div>
                <!--<div class="col-xl-6 col-lg-6 d-none d-xl-block d-lg-block lvls-row">
                    <div class="">
                        <div class="d-inline-block align-middle">
                            <h3 class="user-lvl lvl-icon lvl-active"><?/*=Yii::t('frontend', 'Lv.')*/?> 1</h3>
                            <p class="lvl-limit">2 BitCoin</p>
                        </div>
                        <div class="d-inline-block align-middle"><hr class="lvl-line line-active"></div>
                        <div class="d-inline-block align-middle">
                            <h3 class="user-lvl lvl-icon lvl-active">Lv. 2</h3>
                            <p class="lvl-limit">100 BitCoin</p>
                        </div>
                        <div class="d-inline-block align-middle"><hr class="lvl-line"></div>
                        <div class="d-inline-block align-middle">
                            <h3 class="user-lvl lvl-icon lvl-inactive">Lv. 3</h3>
                            <p class="lvl-limit">Unlimit BitCoin</p>
                        </div>
                    </div>
                </div>-->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-5 col-lg-5 col-md-6 col-sm-12 col-12">
            <?=ProfileCard::widget()?>
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
<!--             <div class="card" id="wallets-user-data">
    <div class="card-header white">
        <h5 class="card-title float-left">
            <?=Yii::t('profile_page', 'Balance')?>
        </h5>
        <a href="" class="btn btn-link-with-arrow float-right">
            <?=Yii::t('profile_page', 'Wallets')?>
            <i class="fa fa-long-arrow-right"></i></a>
        <div class="clearfix"></div>
    </div>
    <div class="card-body">
        <div class="main-card-data">
            <p class="sub-title small-title">
                <?=Yii::t('profile_page', 'Estimated value')?>
            </p>
            <p class="course orange-text">0.00248011</p>
        </div>
        <div class="has-scroll">
            <div class="stok-height">
                <div class="table-responsive">
                    <table class="table table-striped td-bold">
                        <thead>
                        <tr class="">
                            <th><?=Yii::t('profile_page', 'Currency')?></th>
                            <th><?=Yii::t('profile_page', 'Available')?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Matrix Coin</td>
                            <td>1000.00078</td>
                            <td>
                                <form action="" method="post">
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/dollar-down-dark.png"></button>
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/withdraw.png"></button>
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/arrows.png"></button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td>Ethereum</td>
                            <td>2.00078</td>
                            <td>
                                <form action="" method="post">
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/dollar-down-dark.png"></button>
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/withdraw.png"></button>
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/arrows.png"></button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td>BitCoin</td>
                            <td>0.00078132</td>
                            <td>
                                <form action="" method="post">
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/dollar-down-dark.png"></button>
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/withdraw.png"></button>
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/arrows.png"></button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td>LiteCoin</td>
                            <td>1.78132</td>
                            <td>
                                <form action="" method="post">
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/dollar-down-dark.png"></button>
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/withdraw.png"></button>
                                    <button type="submit" class="btn btn-icon icon-only"><img src="/images/icons/arrows.png"></button>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> -->
        </div>
        <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12">
            <!-- <div class="card">
                <?=$this->render('_profile_card_menu')?>
            </div> -->
        </div>
    </div>

    <div class="row">
        <div class="col-xl-5 col-lg-5 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-header white">
                    <h5 class="card-title float-left">
                        <?=Yii::t('profile_page', 'Referal system')?>
                    </h5>
                    <span  class="btn btn-link-with-arrow float-right">                        
                    <?=Yii::t('profile_page', 'Sponsor') ?>:
                    <?=Yii::$app->user->identity->getUserSponsor() ?>
                    </span>
                    <div class="clearfix"></div>
                </div>
                <div class="card-body">
                    <label class="d-block">
                        <?=Yii::t('profile_page', 'Your referal link')?> <button type="button" data-clipboard-target="#referal_to_copy" class="float-right btn btn-link btn-clipboard">
                            <?=Yii::t('profile_page', 'Copy link')?>
                        </button>
                        <div id="referal_to_copy" class="form-control disabled">
                            <?=Url::to(['/auth/default/registration', 'sponsor' => Yii::$app->user->identity->getContact()->login], true)?>
                        </div>
                    </label>
                    <p class="grey-text">
                        <?=Yii::t('profile_page', 'The Haysiope referral system rewards users for referring customers to the site, for each referred user you will receive 10% of that users trade fees converted to MATRIX Please provide the link below to the user you like, if he registers an account using your link you will start being rewarded for their activity.')?>
                    </p>
                </div>
            </div>
        </div>
<!--         <div class="col-xl-7 col-lg-7 col-md-12 col-sm-12 col-xs-12">
    <div class="card">
        <div class="card-header white">
            <h5 class="card-title"><?=Yii::t('frontend', 'Login history')?></h5>
        </div>
        <div class="card-body">
            <div class="row grey-text like-table">
                <div class="col">09:41:23 2018/03/11</div>
                <div class="col">192.168.133.104</div>
                <div class="col">Ukraine, Kharkiv</div>
            </div>
            <div class="row grey-text like-table">
                <div class="col">09:41:23 2018/03/11</div>
                <div class="col">192.168.133.104</div>
                <div class="col">Ukraine, Kharkiv</div>
            </div>
            <div class="row grey-text like-table">
                <div class="col">09:41:23 2018/03/11</div>
                <div class="col">192.168.133.104</div>
                <div class="col">Ukraine, Kharkiv</div>
            </div>
            <div class="row grey-text like-table">
                <div class="col">09:41:23 2018/03/11</div>
                <div class="col">192.168.133.104</div>
                <div class="col">Ukraine, Kharkiv</div>
            </div>
        </div>
    </div>
</div> -->
    </div>

<!--     <div class="card">
    <div class="card-header white">
        <div class="row">
            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-5">
                <h5 class="card-title"><?=Yii::t('frontend', 'History:')?></h5>
            </div>
            <div class="col-xl-8 col-lg-8 col-md-9 col-sm-6 col-7">
                <button type="button" id="navHistoryTypeTableBtn" data-toggle="collapse" data-target="#navHistoryTypeTable" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="navHistoryTypeTable">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>
                <div class="collapse select-type-row" id="navHistoryTypeTable">
                    <button type="button" id="historyDeposite" class="btn btn-filter active">
                        <?=Yii::t('frontend', 'Deposite')?>
                    </button>
                    <button type="button" id="historyWithdraw" class="btn btn-filter">
                        <?=Yii::t('frontend', 'Withdraw')?>
                    </button>
                    <button type="button" id="historyTrade" class="btn btn-filter">
                        <?=Yii::t('frontend', 'Trade')?>
                    </button>
                    <button type="button" id="historyTransfer" class="btn btn-filter">
                        <?=Yii::t('frontend', 'Transfer')?>
                    </button>
                    <button type="button" id="historyMineshaft" class="btn btn-filter">
                        <?=Yii::t('frontend', 'Mineshaft')?>
                    </button>
                    <button type="button" id="historyMarketplace" class="btn btn-filter">
                        <?=Yii::t('frontend', 'Marketplace') ?>
                    </button>
                    <button type="button" class="btn btn-filter btn-only-icons" data-toggle="collapse" href="#moreFiltersTrigger" role="button" aria-expanded="false" aria-controls="moreFiltersTrigger">Other filters<i class="fa fa-caret-down"></i>
                    </button>
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12"><a href="" class="btn btn-link ogange-text card-header-right-link">Full history</a></div>
        </div>
        <div class="collapse" id="moreFiltersTrigger">
            <form action="" class="form-row">
                <div class="col">
                    <label class="d-block"><?=Yii::t('frontend', 'Choice currencies')?>
                        <select class="custom-select" multiple>
                            <option value="matrix">MATRIX</option>
                            <option value="bitcoin">BitCoin</option>
                            <option value="litecoin">LiteCoin</option>
                            <option value="matrix">MATRIX</option>
                            <option value="bitcoin">BitCoin</option>
                            <option value="litecoin">LiteCoin</option>
                            <option value="matrix">MATRIX</option>
                            <option value="bitcoin">BitCoin</option>
                            <option value="litecoin">LiteCoin</option>
                        </select>
                    </label>
                </div>
                <div class="col-auto">
                    <label class="d-block"><?=Yii::t('frontend', 'Choice type:')?></label>
                    <div class="custom-control custom-checkbox d-block">
                        <input type="checkbox" class="custom-control-input" id="checkType_Buy">
                        <label class="custom-control-label" for="checkType_Buy">
                            <?=Yii::t('frontend', 'Buy')?>
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox d-block">
                        <input type="checkbox" class="custom-control-input" id="checkType_Sell">
                        <label class="custom-control-label" for="checkType_Sell">
                            <?=Yii::t('frontend', 'Sell')?>
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox d-block">
                        <input type="checkbox" class="custom-control-input" id="checkType_Favorite">
                        <label class="custom-control-label" for="checkType_Favorite">
                            <?=Yii::t('frontend', 'Favorite')?>
                        </label>
                    </div>
                </div>
                <div class="col-auto">
                    <label class="d-block">
                        <?=Yii::t('frontend', 'Choice status:')?>
                    </label>
                    <div class="custom-control custom-checkbox d-block">
                        <input type="checkbox" class="custom-control-input" id="checkStatus_Done">
                        <label class="custom-control-label" for="checkStatus_Done">
                            <?=Yii::t('frontend', 'Done')?>
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox d-block">
                        <input type="checkbox" class="custom-control-input" id="checkStatus_InProcess">
                        <label class="custom-control-label" for="checkStatus_InProcess">
                            <?=Yii::t('frontend', 'In process')?>
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox d-block">
                        <input type="checkbox" class="custom-control-input" id="checkStatus_Cancel">
                        <label class="custom-control-label" for="checkStatus_Cancel">
                            <?=Yii::t('frontend', 'Cancel')?>
                        </label>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-12">
                    <p class="current-filters-row"><?=Yii::t('frontend', 'Current filters:')?>
                        <span class="badge badge-warning">MATRIX<button type="button" class="btn btn-sm btn-delete-barge"><i class="fa fa-times"></i></button></span>
                        <span class="badge badge-warning">BitCoin<button type="button" class="btn btn-sm btn-delete-barge"><i class="fa fa-times"></i></button></span>
                    </p>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered td-bold">
                    <thead>
                    <tr>
                        <th><?=Yii::t('frontend', 'ID')?></th>
                        <th><?=Yii::t('frontend', 'Type')?></th>
                        <th><?=Yii::t('frontend', 'Amount')?></th>
                        <th><?=Yii::t('frontend', 'Coin')?></th>
                        <th><?=Yii::t('frontend', 'Status')?></th>
                        <th><?=Yii::t('frontend', 'Begins')?></th>
                        <th><?=Yii::t('frontend', 'Ends')?></th>
                        <th><?=Yii::t('frontend', 'TransferID')?></th>
                        <th><?=Yii::t('frontend', 'RefundID')?></th>
                        <th><?=Yii::t('frontend', 'Time')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>234</td>
                        <td class="type buy">Buy</td>
                        <td>2000000.00000001</td>
                        <td>MATRIX</td>
                        <td class="status done">Done</td>
                        <td>*******</td>
                        <td>*******</td>
                        <td>6541acsasc451</td>
                        <td>64aac454awf1</td>
                        <td>09:41:23 2018/03/11</td>
                    </tr>
                    <tr>
                        <td>24</td>
                        <td class="type sell">Sell</td>
                        <td>2000000.00000001</td>
                        <td>MATRIX</td>
                        <td class="status process">In process</td>
                        <td>*******</td>
                        <td>*******</td>
                        <td>6541acsasc451</td>
                        <td>64aac454awf1</td>
                        <td>09:41:23 2018/03/11</td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td class="type buy">Buy</td>
                        <td>2000000.00000001</td>
                        <td>MATRIX</td>
                        <td class="status cancel">Cancel</td>
                        <td>*******</td>
                        <td>*******</td>
                        <td>6541acsasc451</td>
                        <td>64aac454awf1</td>
                        <td>09:41:23 2018/03/11</td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td class="type buy">Buy</td>
                        <td>2000000.00000001</td>
                        <td>MATRIX</td>
                        <td class="status cancel">Cancel</td>
                        <td>*******</td>
                        <td>*******</td>
                        <td>6541acsasc451</td>
                        <td>64aac454awf1</td>
                        <td>09:41:23 2018/03/11</td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td class="type buy">Buy</td>
                        <td>2000000.00000001</td>
                        <td>MATRIX</td>
                        <td class="status cancel">Cancel</td>
                        <td>*******</td>
                        <td>*******</td>
                        <td>6541acsasc451</td>
                        <td>64aac454awf1</td>
                        <td>09:41:23 2018/03/11</td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td class="type buy">Buy</td>
                        <td>2000000.00000001</td>
                        <td>MATRIX</td>
                        <td class="status cancel">Cancel</td>
                        <td>*******</td>
                        <td>*******</td>
                        <td>6541acsasc451</td>
                        <td>64aac454awf1</td>
                        <td>09:41:23 2018/03/11</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> -->

</div>

