<?php

use common\modules\drole\models\webtools\JSONRegistryFactory;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/**
 * transfer transactions history page
 *
 * @var $this \yii\web\View
 */
$usPMID = '00000000-430d-4a57-a7ec-ff125372ae09';
$currentCurrency = \Yii::$app->request->get("currency");

if (!\common\modules\drole\models\UUIDGenerator::isUUID($currentCurrency)) {
    \Yii::$app->response->redirect(\yii\helpers\Url::to('wcrypto'));
}

$objectID = '4438a6ab-db08-4421-a8bf-9221a8ca7e18';
$walletObjectID = '4bfe0dd7-9e54-4de5-b9fa-3e4882bcd82d';
$trunkObjectID = '62a15104-6ad9-4922-afd0-68e0b57ff87f';
$marketObjectID = '5c1a5894-f6df-4c96-a84d-6679f3375bb7';
$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectID);
$jsonWallet = JSONRegistryFactory::getRecordsListFromObject(true, $walletObjectID);
$jsonTrunk = JSONRegistryFactory::getRecordsListFromObject(true, $trunkObjectID);
$jsonMarket = JSONRegistryFactory::getRecordsListFromObject(true, $marketObjectID);
$dynamicRoleArray = \common\modules\drole\models\registry\DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
$sql = "SELECT coin_data_use.id, coin_data_use.name, coin_data_use.symbol FROM 
coin_data_use join coin_record_own on coin_record_own.id = coin_data_use.id WHERE coin_record_own.company_id = '" . $dynamicRoleArray['company_id'] . "' and 
coin_record_own.service_id = '" . $dynamicRoleArray['service_id'] . "' and coin_data_use.status = '200'";
$marketResult = \Yii::$app->db->createCommand($sql)->queryAll();
if (!$marketResult || count($marketResult) < 1) {
    echo "not found market";
    die(402);
}
$currenciesList = json_encode($marketResult);
$marketAutocompliteSelects = '';
foreach ($marketResult as $marketRecord) {
    $marketAutocompliteSelects .= '"' . $marketRecord['name'] . '",';
}
if (strlen($marketAutocompliteSelects) > 3) {
    $marketAutocompliteSelects = '[' . substr($marketAutocompliteSelects, 0, strlen($marketAutocompliteSelects) - 1) . ']';
}
$currentStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($objectID, \Yii::$app->user->getIdentity()->auth['drole']);
$walletStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($walletObjectID, \Yii::$app->user->getIdentity()->auth['drole']);
$trunkStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($trunkObjectID, \Yii::$app->user->getIdentity()->auth['drole']);
$marketStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($marketObjectID, \Yii::$app->user->getIdentity()->auth['drole']);
function getIndexFromArray($currentStructure, $fieldID)
{
    for ($i = 0; $i < count($currentStructure); $i++) {
        if ($currentStructure[$i]['id'] == $fieldID) {
            return $i;
        }
    }
    return null;
}

$currentPageFilters = '[{"map":"' . getIndexFromArray(json_decode($currentStructure, true), 'af8e49fc-2a70-4143-9f59-69232bddfc69') . '","comp":"6","value":"2"}]'
?>
    <script src="/js/jquery.min.js"></script>
    <script src="/js/socket.io.js"></script>
    <script src="/js/bignumber.js"></script>
    <script src="/js/jquery-ui.js"></script>

    <div class="content-wrapper">
        <div class="row">
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                <div class="card">
                    <div class="card-header white">
                        <h3 class="card-title"><?=Yii::t('transfer_page', 'Transfer')?></h3>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'id' => 'wdone-form',
                            'action' => Url::to('/profile/default/tdone'),
                            'validateOnChange' => false,
                            'validateOnBlur' => false,
                            'validateOnSubmit' => true,
                            'enableAjaxValidation' => true,
                            'validationUrl' => Url::to('/profile/default/tdone-validate')
                        ]) ?>

                        <!-- <div class="alert-status">
                            <div class="alert alert-danger text-center d-none" role="alert">Incorrect PIN code</div>
                            <div class="alert alert-success text-center d-none" role="alert">Its Ok!</div>
                        </div> -->

                        <div class="day-limit-row">
                            <p class="grey-text"><?=Yii::t('withdraw_page', 'Daily limit')?></p>
                            <div class="progress">
                                <div class="progress-bar bg-danger" id="limitBar" style="width: 100%;"
                                     role="progressbar"
                                     aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"><?=Yii::t('withdraw_page', 'Use')?>: 2 BTC
                                </div>
                            </div>
                            <div class="row used-row-data grey-text">
                                <div class="col-4 text-left">0 BTC</div>
                                <div class="col-4 text-center">1 BTC</div>
                                <div class="col-4 text-right">2 BTC</div>
                            </div>
                        </div>
                        <label class="d-none grey-text"><?=Yii::t('withdraw_page', 'Currency')?>
                            <div class="autocomplete">
                                <input id="ht-auto-search" type="text" class="form-control" name="marketsAutocomplite"
                                       placeholder="Type currency">
                            </div>
                            <button type="button" onclick="location.reload()" class="btn btn-light btn-refresh"><i
                                        class="fa fa-times" aria-hidden="true"></i></button>
                        </label>
                        <p class="grey-text withdraw-balance-row"><?=Yii::t('withdraw_page', 'Balance')?>: <span class="green-text"><strong
                                        id="walletvalue" onclick="setAllSummValue()">0</strong></span>
                        </p>
                        <div class="row">
                            <div class="col-sm-6 col-12">
                                <?= $form->field($model, 'amount', [
                                    'template' => '{beginLabel}{labelTitle}{input}{endLabel}{error}',
                                    'labelOptions' => ['class' => 'd-block grey-text'],
                                ])->textInput([
                                    'id' => 'summamount',
                                    'class' => 'form-control',
                                    'placeholder' => "0.0000000"
                                ]) ?>

                                <?= $form->field($model, 'currency')->hiddenInput()->label(false) ?>


                            </div>
                            <div class="col-sm-6 col-12">
                                <!--<div class="total-block">
                                    <span class="grey-text">Fee: <span id="partfee">0.00000000</span></span>
                                    <p class="bold">Total: <span id="partvalue"
                                                                 class="total-amount orange-text">0.00000000</span>
                                    </p>
                                </div>-->
                            </div>
                        </div>
                        <div class="withdraw-to">
                            <?= $form->field($model, 'address', [
                                'template' => '
                                    {beginLabel}
                                        {labelTitle} 
                                        <span class="question-wrap">
                                            <i class="fa fa-question-circle"></i>
                                            <span class="question-wrap__text">
                                                <img src="/images/profile.jpg" />
                                            </span>
                                        </span>
                                        {input}
                                    {endLabel}{error}',
                                'labelOptions' => ['class' => 'd-block grey-text'],
                            ])->textInput([
                                'class' => 'form-control',
                                'placeholder' => Yii::t('frontend', 'User Acount ID'),
                                //'value' => "4sf6v1vsae6vga65sv16G5sad1v6asd5v1"
                            ]) ?>

                        </div>
                        <div class="transfer-verification row">
                            <div class="col">
                                <?= $form->field($model, 'pincode', [
                                    'template' => '{beginLabel}{labelTitle}{endLabel}{input}{error}',
                                    'labelOptions' => ['class' => 'd-block grey-text'],
                                ])->textInput([
                                    'id' => 'checkPIN',
                                    'class' => 'form-control',
                                    //'required' => 'required'
                                ]) ?>
                            </div>
                            <div class="col">
                                <label class="d-block grey-text invisible"><?=Yii::t('frontend','Status')?>:</label>
                                <!--<button type="submit" class="btn btn-warning btn-block btn-lg">Send e-mail code</button>-->
                                <?= \common\modules\pinCode\widgets\SendCode::widget(
                                    ['message' => Yii::t('frontend', 'Pin code for transfer')]
                                ) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?php $tcrypto = \yii\helpers\Url::to(['/profile/default/tcrypto']) ?>
                                <button onclick="location.href='<?= $tcrypto ?>'"
                                        class="btn btn-default btn-block btn-lg with-mr-t">
                                        <?=Yii::t('frontend', 'Cancel')?>
                                </button>
                            </div>
                            <div class="col">
                                <button id="wdone-submit" type="submit"
                                        class="btn btn-success btn-block btn-lg with-mr-t">
                                        <?=Yii::t('frontend', 'Done')?>
                                </button>
                            </div>
                        </div>

                        <?php ActiveForm::end() ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 col-12">
                <div class="card">
                    <div class="card-header white">
                        <h3 class="card-title"><?=Yii::t('transfer_page', 'Transfer history')?></h3>
                    </div>
                    <div class="card card-body">
                        <div class="row mr-b-20">
                            <div class="col-md-4 col-12">

                            </div>
                            <div class="col-md-4 col-12">
                                <select name="select_type_htransaction" id="select_type_htransaction"
                                        class="form-control"
                                        hidden>
                                    <option value="">
                                        <?=Yii::t('frontend', 'All')?></option>
                                    <option value="0"><?=Yii::t('frontend', 'Buy')?></option>
                                    <option value="1"><?=Yii::t('frontend', 'Sell')?></option>
                                </select>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="pagination-arrows text-right">
                                    <button type="button" class="btn btn-light btn-ht-maxprev"><i
                                                class="fa fa-step-backward"
                                                aria-hidden="true"></i></button>
                                    <button type="button" class="btn btn-light btn-ht-prev"><i class="fa fa-angle-left"
                                                                                               aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn btn-ht-current disabled" disabled>
                                        <?=Yii::t('frontend', 'Page') ?>
                                        <span
                                                id="htrans_page_number">1</span></button>
                                    <button type="button" class="btn btn-light btn-ht-next"><i class="fa fa-angle-right"
                                                                                               aria-hidden="true"></i>
                                    </button>
                                </div>
                                <label class="d-none input-box"><input id="quick-search" type="text"
                                                                       class="form-control"
                                                                       placeholder="<?=Yii::t('frontend', 'Search')?>...." value=""></label>
                            </div>
                        </div>
                        <div class="history_market-table">
                            <div class="table-responsive">
                                <table id="htransactiontable"
                                       class="table table-bordered table-hover table-course table-striped">

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        document.getElementById('select_type_htransaction').addEventListener("click", function (e) {
            paginationUpdate('htransactiontable');
        });

        function getFilterForMarketsToken(selectedMarketName) {
            if (selectedMarketName != '') {
                for (var i = 0; i < marketCurrencies.length; i++) {
                    if (marketCurrencies[i]['name'] == selectedMarketName) {
                        //var currentCurrencyMap = getStructureElementIndexByID(currentStructure, '97bebaa3-7687-4f3c-a85b-e5cc1fbcd605');
                        var currentCurrencyMap = getIndexFromStructureByID('97bebaa3-7687-4f3c-a85b-e5cc1fbcd605');
                        return JSON.parse('{"special":[{"map":"' + currentCurrencyMap + '","comp":"6","value":"' +
                            marketCurrencies[i].id + '"}]}');
                    }
                }
            } else {
                return {special: []};
            }
        }

        function getValuesFromFilters() {
            var currentCurrencyMap = getIndexFromStructureByID('97bebaa3-7687-4f3c-a85b-e5cc1fbcd605');
            return JSON.parse('{"special":[{"map":"' + currentCurrencyMap + '","comp":"6","value":"<?= $currentCurrency ?>"}]}');
        }
    </script>
<?php 
    $textDate = Yii::t('frontend', 'Date');
    $textCoin = Yii::t('frontend', 'Coin');
    $textWallet = Yii::t('frontend', 'Wallet');
    $textAmount = Yii::t('frontend', 'Amount');
    $textStatus = Yii::t('frontend', 'Status');

    $textNotFound = Yii::t('frontend', 'Not found');
    $textAccounted = Yii::t('frontend', 'Accounted');
    $textInProgress = Yii::t('frontend', 'In Progress');
    $textForbidden = Yii::t('frontend', 'Forbidden');             
?>
    <script>
        var marketCurrencies = <?= $currenciesList ?>;
        var currentSortValue = null;
        var marketsPairs = <?= $marketAutocompliteSelects ?>;

        $(function () {
            $('#htransactiontable').on('click', '.fieldsort', function () {
                /*console.log(currentStructure[$(this).index()])
                console.log($(this).index())*/
                var key = $(this).attr('id');
                var indexes = getIndexFromStructureByID($(this).attr('id'));
                if ($($(this).children(0)[1]).hasClass('fa-exchange')) {
                    if (currentSortValue != null) {
                        $($(currentSortValue).children(0)[1]).removeClass('fa-sort-amount-asc');
                        $($(currentSortValue).children(0)[1]).removeClass('fa-sort-amount-desc');
                        $($(currentSortValue).children(0)[1]).addClass('fa-exchange');
                    }
                    currentSortValue = $(this);
                    $($(currentSortValue).children(0)[1]).removeClass('fa-exchange');
                    $($(currentSortValue).children(0)[1]).addClass('fa-sort-amount-asc');
                } else if ($($(this).children(0)[1]).hasClass('fa-sort-amount-asc')) {
                    $($(currentSortValue).children(0)[1]).removeClass('fa-sort-amount-asc');
                    $($(currentSortValue).children(0)[1]).addClass('fa-sort-amount-desc');
                } else if ($($(this).children(0)[1]).hasClass('fa-sort-amount-desc')) {
                    $($(currentSortValue).children(0)[1]).removeClass('fa-sort-amount-desc');
                    $($(currentSortValue).children(0)[1]).addClass('fa-sort-amount-asc');
                }
                currentPage = 0;
                paginationUpdate('htransactiontable');
                /*var localQuery = < ?= $json ?>;

                localQuery.work.operation = 4;
                localQuery.filters[1] = {"special": []};
                //localQuery.filters[1] = JSON.parse('{"special":[{"map":"4","comp":"6","value":"' + currentPressedMarket + '"},{"map":"6","comp":"6","value":"200"}]}');
                localQuery['filters'][2] = JSON.parse('{"sorting":[{"map":"' + indexes + '","field":"' + key + '","sort":"' + ($($(this).children(0)[1]).hasClass('fa-sort-amount-asc') ? '0' : '1') + '"}]}');
                localQuery['filters'][3] = JSON.parse('{"limit":{"lmt":"10","off":"10","prev":"10","asc":"-1"}}');
                //console.log(JSON.stringify(localQuery.filters))
                /!*if (document.getElementById("quick-search").value.length > 0)
                    localQuery.filters[1].special.push(JSON.parse('{"map":"3","comp":"7","value":"_' + document.getElementById("quick-search").value + '"}'))*!/
                //getMarketPair('/en/drole/default/get-info', localQuery);
                defaultUpdateTable('htransactiontable', '/en/drole/default/get-info', localQuery);*/
            });
        });

        // var maps = [{'81426e36-34e5-47c2-a91a-95791aa47ac3': ["7.0.4"]}, {'3c6bd221-c53e-4637-9225-c23d2b701e7d': ["9"]}, {'cc024f97-efe0-4821-b540-c75c0aee89d5': ["8"]}, {'8076c2b1-4eaa-444f-92c8-bc86e1eb65d7': ["10"]}];

        /*function getValueFromRowData(row, indexesArray) {
            var result = row;
            var index = 1;
            while (index < indexesArray.length) {
                //console.log(result[indexesArray[index]])
                if (result[indexesArray[index]] != null && result[indexesArray[index]] != undefined) {
                    result = result[indexesArray[index]];
                }
                index++;
            }
            return result;
        }*/

        /*function getMarketPair(url, data) {

            $.post(url, {json: JSON.stringify(data)}).done(function (data) {
                console.log(JSON.stringify(data))
                //return;
                //console.log("start query")
                var rowTemplate = '<tr class="coin-ankor"><td class="token-str">{81426e36-34e5-47c2-a91a-95791aa47ac3}</td><td>{3c6bd221-c53e-4637-9225-c23d2b701e7d}</td><td>{cc024f97-efe0-4821-b540-c75c0aee89d5}</td><td class="{view-class}">{8076c2b1-4eaa-444f-92c8-bc86e1eb65d7}%</td></tr>';

                var resultHTML = '';
                //var maps = [{'81426e36-34e5-47c2-a91a-95791aa47ac3': ["7.0.4"]}, {'3c6bd221-c53e-4637-9225-c23d2b701e7d': ["9"]}, {'cc024f97-efe0-4821-b540-c75c0aee89d5': ["8"]}, {'8076c2b1-4eaa-444f-92c8-bc86e1eb65d7': ["10"]}];
                $.each(data['data']['data'], function () {
                    var htmlRow = rowTemplate;
                    $.each(this, function (index, value) {
                        for (var i = 0; i < maps.length; i++) {
                            var key = Object.keys(maps[i])[0];
                            var indexes = maps[i][key][0].split(".");
                            if (indexes[0] == index) {
                                //
                                var resultRow = '';
                                if (value != null) {
                                    resultRow = getValueFromRowData(value, indexes);
                                }
                                if (key == '8076c2b1-4eaa-444f-92c8-bc86e1eb65d7') {
                                    var colorClass = 'green-text';
                                    if (resultRow == null || resultRow == 0) {
                                        colorClass = 'orange-text';
                                    } else if (resultRow < 0) {
                                        colorClass = 'red-text';
                                    }
                                    htmlRow = htmlRow.split(`{view-class}`).join(colorClass);
                                } else if (key == '3c6bd221-c53e-4637-9225-c23d2b701e7d' || key == 'cc024f97-efe0-4821-b540-c75c0aee89d5') {
                                    resultRow = resultRow.toFixed(8);
                                }
                                //console.log(resultRow)
                                htmlRow = htmlRow.split(`{${key}}`).join(resultRow);
                            }
                        }
                    });
                    resultHTML += htmlRow;
                });
                $("#coinmarkets-table").html(resultHTML);
            });
        }*/

        /*$('#quick-search').keyup(function () {
            var localQuery = < ?= $json ?>;
            localQuery.work.operation = 4;
            //var key = Object.keys(sortMaps[$(currentSortValue).index()])[0];
            //var indexes = sortMaps[$(currentSortValue).index()][key][0];
            //localQuery.filters[1] = JSON.parse('{"special":[{"map":"4","comp":"6","value":"' + currentPressedMarket + '"},{"map":"6","comp":"6","value":"200"}]}');
            //localQuery.filters[2] = JSON.parse('{"sorting":[{"map":"' + indexes + '","field":"' + key + '","sort":"' + ($($(currentSortValue).children(0)[1]).hasClass('fa-sort-amount-asc') ? '0' : '1') + '"}]}');
            /!*if (document.getElementById("quick-search").value.length > 0)
                localQuery.filters[1].special.push(JSON.parse('{"map":"3","comp":"7","value":"_' + document.getElementById("quick-search").value + '"}'))*!/
            //getMarketPair('/en/drole/default/get-info', localQuery);
            defaultUpdateTable('htransactiontable', '/en/drole/default/get-info', localQuery);
        }).keyup();*/

        /*function getMarketByCurrency(currentField, currentValue, baseField, baseValue) {
            for (var i = 0; i < marketCurrencies.len; i++) {
                //if(marketCurrencies[i][])
            }
        }

        function getMapForField(currentField) {

        }

        function createMapFromStructure() {

        }*/

        var currentStructure = <?= $currentStructure ?>;
        var currentData = null;

        function defaultUpdateTable(tableID, url, data) {
            $.post(url, {json: JSON.stringify(data)}).done(function (data) {
                if (data.data.structure == undefined) {
                    return;
                }
                currentStructure = data['data']['structure']['data'];
                indexedTableFields = setMap(currentStructure);
                //console.log(indexedTableFields)
                var x = document.getElementById(tableID);
                var editorsHTML = '';
                if (x.tHead) {
                    $("#" + tableID + " > tbody").html("");
                }
                if (!x.tHead) {
                    var recordCount = $('#modal-add-value').attr('datacount');
                    var header = x.createTHead();
                    var row = header.insertRow(0);
                    x.createTBody();
                    /*var blankField = document.createElement('th');
                    blankField.innerHTML =
                        '<p>Add</p>';
                    row.appendChild(blankField);*/
                    $.each(currentStructure, function (sindex) {
                        //console.log(sindex)
                        if (presentInTableFields.includes(currentStructure[sindex].id)) {
                            var currentHeadFunction = getHeadFunctionByID(data['data']['structure']['data'][sindex].id);
                            row.appendChild(currentHeadFunction(sindex));
                        }
                    });
                    // return;

                }
                $.each(data['data']['data'], function (r, rowValue) {
                    var body = x.getElementsByTagName('tbody')[0];
                    var row = body.insertRow();
                    var fieldIndex = 0;
                    var viewIndex = 1;
                    zeroFieldID = data['data']['structure']['data'][fieldIndex].id;
                    var record = this;
                    //row.appendChild(baseDataFunction(this, f));
                    $.each(this, function (f, fieldValue) {
                        if (presentInTableFields.includes(currentStructure[f].id)) {
                            var currentFunction = getDataFunctionByID(data['data']['structure']['data'][f].id);
                            //console.log(currentFunction(record, f))
                            row.appendChild(currentFunction(record, f));
                        }
                    });
                    //var cell = row.insertCell(0);
                    //cell.innerHTML = '<input type="number" placeholder="position" value="' + recordCount + '" min="0" max="' + recordCount + '" class="form-control"><button type="button" onclick="addValueToInnerObject(this)" id="' + this[0] + '" class="btn btn-icon btn-add"><i class="pe-7s-plus"></i></button><button type="button" onclick="addValueToInnerObject(this)" id="' + this[0] + '" class="btn btn-icon btn-add"><i class="pe-7s-pen"></i></button>';
                });
                //$("#values-list").html(editorsHTML)
                //alert(editorsHTML);
                //console.log(editorsHTML)
            });
        }

        var presentInTableFields = ['c2256403-7b16-469a-88ca-4d0c676022ef', '8b9762f1-d914-467e-885b-0749fd463364',
            '97bebaa3-7687-4f3c-a85b-e5cc1fbcd605', 'c106cbc6-34f8-4656-84f2-7fd3fac5d617',
            'c79befba-e8bf-4ff0-b50f-0508f900fde3', '0ab2df79-d2b3-4e45-a4f4-26c2b68fe18d'];
        var presentInTablesFunctions = [];
        var sortMaps = {
            '8b9762f1-d914-467e-885b-0749fd463364': '<?=$textDate?>',
            'c2256403-7b16-469a-88ca-4d0c676022ef': 'ID',
            '97bebaa3-7687-4f3c-a85b-e5cc1fbcd605': '<?=$textCoin?>',
            '0ab2df79-d2b3-4e45-a4f4-26c2b68fe18d': '<?=$textWallet?>',
            'c106cbc6-34f8-4656-84f2-7fd3fac5d617': '<?=$textAmount?>',
            'c79befba-e8bf-4ff0-b50f-0508f900fde3': '<?=$textStatus?>'
        };
        var indexedTableFields = null;
        var currentTableLimit = 20;
        var currentPage = 0;
        var pageRecordsCount = 0;
        var pageOperation = 0;

        function getNameByIndex(index) {
            var result = sortMaps[currentStructure[index].id];
            if (result == null || result == 'undefined') {
                return currentStructure[index].name;
            }
            return result;
        }

        //pagination

        $('.btn.btn-ht-next').click(function () {
            if (document.getElementById('htransactiontable').getElementsByTagName('tbody') != null && document.getElementById('htransactiontable').getElementsByTagName('tbody') != 'undefined' &&
                document.getElementById('htransactiontable').getElementsByTagName('tbody').length > 0) {
                if (document.getElementById('htransactiontable').getElementsByTagName('tbody')[0].getElementsByTagName('tr').length < currentTableLimit) return;
            } else {
                return;
            }
            pageOperation = 1;
            paginationUpdate('htransactiontable');
        });

        $('.btn.btn-ht-prev').click(function () {
            if (currentPage <= 0) return;
            pageOperation = -1;
            //console.log('button prev')
            //defaultUpdateTable('htransactiontable', '/en/drole/default/get-info', localQuery);
            paginationUpdate('htransactiontable');
        });

        $('.btn.btn-ht-maxprev').click(function () {
            //console.log('button maxprev')
            //defaultUpdateTable('htransactiontable', '/en/drole/default/get-info', localQuery);
            currentPage = 0;
            paginationUpdate('htransactiontable');
        });

        function paginationUpdate(tableID) {
            var localQuery = <?= $json ?>;
            var specialPagesFilters = <?= $currentPageFilters ?>;
            localQuery.filters = [];
            localQuery.filters[0] = {"common": ""};
            localQuery.filters[1] = getValuesFromFilters();
            localQuery.filters[1].special.push(specialPagesFilters[0]);
            localQuery.filters[1].special.push(specialPagesFilters[1]);
            if (currentSortValue != null) {
                var key = $(currentSortValue).attr('id');
                var indexes = getIndexFromStructureByID($(currentSortValue).attr('id'));
                localQuery['filters'][2] = JSON.parse('{"sorting":[{"map":"' + indexes + '","field":"' + key + '","sort":"' + ($($(currentSortValue).children(0)[1]).hasClass('fa-sort-amount-asc') ? '0' : '1') + '"}]}');
            } else {
                localQuery['filters'][2] = JSON.parse('{"sorting":[]}');
            }

            if (document.getElementById(tableID).getElementsByTagName('tbody') != null && document.getElementById(tableID).getElementsByTagName('tbody') != 'undefined' &&
                document.getElementById(tableID).getElementsByTagName('tbody').length > 0)
                pageRecordsCount = document.getElementById(tableID).getElementsByTagName('tbody')[0].getElementsByTagName('tr').length;
            if (pageOperation == 0 && currentPage == 0) {
                pageRecordsCount = currentTableLimit;
            }
            localQuery['filters'][3] = JSON.parse('{"limit":{"lmt":"' + currentTableLimit + '","off":"' + (currentPage * currentTableLimit) + '","prev":"' +
                pageRecordsCount + '","asc":"' + pageOperation + '"}}');
            defaultUpdateTable('htransactiontable', '/en/drole/default/get-info', localQuery);
            document.getElementById('htrans_page_number').innerText = ' ' + (currentPage + pageOperation + 1);
            currentPage = currentPage + pageOperation;
            pageOperation = 0;
        }

        function setMap(instantStructure) {
            var resultMap = [];
            for (var instantStructureIndex = 0; instantStructureIndex < instantStructure.length; instantStructureIndex++) {
                //console.log('start with['+instantStructureIndex+']: ' +instantStructure[instantStructureIndex].id)
                for (var instantFieldsIndex = 0; instantFieldsIndex < presentInTableFields.length; instantFieldsIndex++) {
                    if (presentInTableFields[instantFieldsIndex] == instantStructure[instantStructureIndex].id) {
                        var idField = instantStructure[instantStructureIndex].id;
                        var currentObject = {};
                        currentObject[instantStructure[instantStructureIndex].id] = instantStructureIndex;
                        //console.log(currentObject)
                        resultMap.push(currentObject);
                        break;
                    }
                }
            }
            return resultMap;
        }

        function getStructureElementIndexByID(instantStructure, fieldID) {
            if (instantStructure == null || instantStructure == undefined) {
                return null;
            }
            for (var instantStructureIndex = 0; instantStructureIndex < Object.keys(instantStructure).length; instantStructureIndex++) {
                if (instantStructure[instantStructureIndex].id == fieldID) {
                    return instantStructureIndex;
                }
            }
            return null;
        }

        var baseHeadFunction = function (index = -1) {
            if (index == -1) {
                return '';
            }
            var cell = document.createElement('th');
            var sorting = '';
            if (currentStructure[index].perm == 1 || currentStructure[index].perm == 16) {
                cell.classList.add('hidden')
            } else if (!currentStructure[index].nested || currentStructure[index].nested == "false") {
                cell.setAttribute('id', currentStructure[index].id);
            }
            var title =
                cell.innerHTML =
                    '<span>' + getNameByIndex(index) + '</span>';
            return cell;
        }

        var baseSortingHeadFunction = function (index = -1) {
            if (index == -1) {
                return '';
            }
            var cell = document.createElement('th');
            var sorting = '';

            if (currentStructure[index].perm == 1 || currentStructure[index].perm == 16) {
                cell.classList.add('hidden')
            } else if (!currentStructure[index].nested || currentStructure[index].nested == "false") {
                //console.log(currentStructure[index].nested)
                cell.classList.add('fieldsort');
                cell.setAttribute('id', currentStructure[index].id);
            }
            cell.innerHTML =
                '<span>' + getNameByIndex(index) + '</span><i class="fa fa-exchange"></i>';
            return cell;
        }

        var baseDataFunction = function (row, index = -1) {
            if (index == -1) {
                return '';
            }
            var fieldHeader = currentStructure[index];
            if (typeof(fieldHeader) != "undefined") {
                var cell = document.createElement('td');//row.insertCell(index);
                cell.innerHTML = '<p>' + row[index] + '</p>';
                if ((fieldHeader.perm == 1 || fieldHeader.perm == 16)) {
                    cell.classList.add('hidden');
                }
                return cell;
            }
        }

        function setFunctionsByID(fieldID, headFunction = null, dataFunction = null) {
            var currentObject = {};
            if (headFunction == null) {
                currentObject['head_f'] = baseHeadFunction;
            } else {
                currentObject['head_f'] = headFunction;
            }
            if (dataFunction == null) {
                currentObject['data_f'] = baseDataFunction;
            } else {
                currentObject['data_f'] = dataFunction;
            }
            var resultObject = {};
            resultObject[fieldID] = currentObject;
            presentInTablesFunctions.push(resultObject);
        }

        function getFunctionByID(fieldID) {
            for (var i = 0; i < presentInTablesFunctions.length; i++) {
                if (presentInTablesFunctions[i].hasOwnProperty(fieldID)) {
                    return presentInTablesFunctions[i];
                }
            }
            return null;
        }

        function getHeadFunctionByID(fieldID) {
            var resultObject = getFunctionByID(fieldID);
            if (resultObject == null) {
                return baseHeadFunction;
            } else {
                return resultObject[fieldID]['head_f'];
            }
        }

        function getDataFunctionByID(fieldID) {
            var resultObject = getFunctionByID(fieldID);
            if (resultObject == null) {
                return baseDataFunction;
            } else {
                return resultObject[fieldID]['data_f'];
            }
        }

        function getIndexFromStructureByID(fieldID) {
            for (var i = 0; i < Object.keys(currentStructure).length; i++) {
                if (currentStructure[i].id == fieldID) {
                    return i;
                }
            }
            return -1;
        }

        //special functions
        var currencyDataFunction = function (fieldValue, index = -1) {
            if (index == -1) {
                return '';
            }
            var fieldHeader = currentStructure[index];
            if (typeof(fieldHeader) != "undefined") {
                //var currentCurrencyID = fieldValue[getStructureElementIndexByID(currentStructure, '97bebaa3-7687-4f3c-a85b-e5cc1fbcd605')];
                var currentCurrencyID = fieldValue[getIndexFromStructureByID('97bebaa3-7687-4f3c-a85b-e5cc1fbcd605')];
                var cell = document.createElement('td');
                var result = 'undefined';
                for (var currencyIndex = 0; currencyIndex < marketCurrencies.length; currencyIndex++) {
                    if (marketCurrencies[currencyIndex].id == currentCurrencyID) {
                        result = marketCurrencies[currencyIndex]['name'];
                        break;
                    }
                }
                cell.innerHTML = '<span>' + result + '</span>';
                if ((fieldHeader.perm == 1 || fieldHeader.perm == 16)) {
                    cell.classList.add('hidden');
                }
                return cell;
            }
        }

        var moneyViewDataFunction = function (row, index = -1) {
            if (index == -1) {
                return '';
            }
            var fieldHeader = currentStructure[index];
            if (typeof(fieldHeader) != "undefined") {
                var cell = document.createElement('td');//row.insertCell(index);
                cell.innerHTML = '<p>' + row[index].toFixed(8) + '</p>';
                if ((fieldHeader.perm == 1 || fieldHeader.perm == 16)) {
                    cell.classList.add('hidden');
                }
                return cell;
            }
        }

        var dateHeadFunction = function (index = -1) {
            if (index == -1) {
                return '';
            }
            var cell = document.createElement('th');
            var sorting = '';
            cell.innerHTML =
                '<span>' + getNameByIndex(index) + '</span><i class="fa fa-exchange"></i>';
            cell.classList.add('fieldsort');
            cell.setAttribute('id', currentStructure[index].id);
            return cell;
        }

        var dateDataFunction = function (row, index = -1) {
            if (index == -1) {
                return '';
            }
            var fieldHeader = currentStructure[index];
            if (typeof(fieldHeader) != "undefined") {
                var cell = document.createElement('td');//row.insertCell(index);
                cell.innerHTML = '<p>' + new Date(row[index] * 1000).toUTCString() + '</p>';
                cell.classList.add('fieldsort');
                return cell;
            }
        }

        var statusDataFunction = function (row, index = -1) {
            if (index == -1) {
                return '';
            }
            var fieldHeader = currentStructure[index];
            if (typeof(fieldHeader) != "undefined") {
                var cell = document.createElement('td');//row.insertCell(index);
                var result = "<?=$textNotFound?>";
                switch (row[index]) {
                    case 200:
                        result = '<?=$textAccounted?>';
                        break;
                    case 100:
                        result = '<?=$textInProgress?>';
                        break;
                    case 300:
                        result = '<?=$textForbidden?>';
                        break;
                }
                cell.innerHTML = '<p>' + result + '</p>';
                return cell;
            }
        }

        setFunctionsByID('8b9762f1-d914-467e-885b-0749fd463364', dateHeadFunction, dateDataFunction);
        setFunctionsByID('97bebaa3-7687-4f3c-a85b-e5cc1fbcd605', null, currencyDataFunction);
        setFunctionsByID('c106cbc6-34f8-4656-84f2-7fd3fac5d617', baseSortingHeadFunction, moneyViewDataFunction);
        setFunctionsByID('c79befba-e8bf-4ff0-b50f-0508f900fde3', null, statusDataFunction);
        paginationUpdate('htransactiontable');
        updateWalletAmount();

        //
        function updateWalletAmount() {
            var jsonWallet = <?= $jsonWallet ?>;
            jsonWallet.filters = [];
            jsonWallet.filters[0] = {"common": ""};
            jsonWallet.filters[1] = {"special": []};
            var walletStructure = <?= $walletStructure ?>;
            var indexOfWalletIDField = getStructureElementIndexByID(walletStructure, '5b296714-e069-457e-b606-3a40bea5b2f2');
            var filter = JSON.parse('{"map":"' + indexOfWalletIDField + '","comp":"6","value":"<?= $currentCurrency ?>"}');
            jsonWallet.filters[1].special.push(filter);
            indexOfWalletIDField = getStructureElementIndexByID(walletStructure, '8bfee8c9-c297-4124-a43b-909748e243a6');
            filter = JSON.parse('{"map":"' + indexOfWalletIDField + '","comp":"6","value":"<?= \Yii::$app->user->getIdentity()->auth['uid'] ?>"}');
            jsonWallet.filters[1].special.push(filter);
            //console.log(JSON.stringify(jsonWallet))
            $.post('/en/drole/default/get-info', {json: JSON.stringify(jsonWallet)}).done(function (data) {
                if (data.data.data[0] != undefined && data.data.data[0] != null) {
                    indexOfWalletIDField = getStructureElementIndexByID(walletStructure, 'd8a9e95a-2fc4-474a-8cd7-6f2f3a7d54e5');
                    document.getElementById('walletvalue').innerText = data.data.data[0][indexOfWalletIDField];
                    updateWalletTrunkSettings();
                    updateWalletMarketSettings();
                }
            });
        }

        var dataTrunk = null;
        var dataMarket = null;
        var currency = null;
        var maxAmount = null;

        function updateWalletTrunkSettings() {
            var trunkWallet = <?= $jsonTrunk ?>;
            trunkWallet.filters = [];
            trunkWallet.filters[0] = {"common": ""};
            trunkWallet.filters[1] = {"special": []};
            var trunkStructure = <?= $trunkStructure ?>;
            var indexOfWalletIDField = getStructureElementIndexByID(trunkStructure, 'c300a61d-5cbd-44a3-a677-04bf9fbbe4a2');
            var filter = JSON.parse('{"map":"' + indexOfWalletIDField + '","comp":"6","value":"<?= $currentCurrency ?>"}');
            trunkWallet.filters[1].special.push(filter);
            $.post('/en/drole/default/get-info', {json: JSON.stringify(trunkWallet)}).done(function (data) {
                if (data.data.data[0] != undefined && data.data.data[0] != null) {
                    var feeIndex = getStructureElementIndexByID(trunkStructure, 'd7157859-937b-498f-8f14-b5f0dcf92bf0');
                    var minFeeIndex = getStructureElementIndexByID(trunkStructure, '6a04ccf9-e64e-463b-a50c-a3b4dbec5af9');
                    var typeFeeIndex = getStructureElementIndexByID(trunkStructure, '3a3b89b9-5c73-4bde-b62c-9ab13299f24d');
                    dataTrunk = {
                        "typeFee": data.data.data[0][typeFeeIndex],
                        "fee": data.data.data[0][feeIndex],
                        "minFee": data.data.data[0][minFeeIndex]
                    };
                    maxAmount = {
                        "unauthorised": data.data.data[0][getStructureElementIndexByID(trunkStructure, '8bbd9d38-3ace-42d0-8128-3d26f55177ac')],
                        "authorised": data.data.data[0][getStructureElementIndexByID(trunkStructure, 'c12aeb46-ea63-4e8c-bf1a-3e17aab9b42e')]
                    };
                }
            });
        }

        function updateWalletMarketSettings() {
            var marketJson = <?= $jsonMarket ?>;
            marketJson.filters = [];
            marketJson.filters[0] = {"common": ""};
            marketJson.filters[1] = {"special": []};
            var marketStructure = <?= $marketStructure ?>;
            var indexOfCurrentCurrencyField = getStructureElementIndexByID(marketStructure, 'b9c877df-5e27-45eb-a625-775d57e19b72');
            var indexOfBaseCurrencyField = getStructureElementIndexByID(marketStructure, '5cfe2e58-b96a-4c66-88d4-fc5143c5c5a3');
            var filter = JSON.parse('{"map":"' + indexOfBaseCurrencyField + '","comp":"6","value":"<?= $usPMID ?>"}');
            marketJson.filters[1].special.push(filter);
            filter = JSON.parse('{"map":"' + indexOfCurrentCurrencyField + '","comp":"6","value":"<?= $currentCurrency ?>"}');
            marketJson.filters[1].special.push(filter);
            $.post('/en/drole/default/get-info', {json: JSON.stringify(marketJson)}).done(function (data) {
                if (data == null || data.data == null || data.data.data == null) {
                    return;
                }
                if (data.data.data[0] != undefined && data.data.data[0] != null) {
                    dataMarket = data.data.data[0];
                    currency = dataMarket[getStructureElementIndexByID(marketStructure, '8e6b1492-f288-4010-bbb3-53766c6a2294')].substring(5);
                }
            });
        }

        $('#summamount').keyup(function () {
            if (dataTrunk != null) {
                var walletValue = document.getElementById('walletvalue').innerText.replace(/[^\d.]/g, '');
                var presentValue = this.value.replace(/[^\d.]/g, '');
                console.log('present value: ' + presentValue + ', walletValue: ' + (presentValue > parseFloat(walletValue)))
                if (this.value != presentValue || presentValue > parseFloat(walletValue)) {
                    if (presentValue > parseFloat(walletValue))
                        this.value = parseFloat(walletValue);
                    else if (this.value != presentValue) this.value = presentValue;
                }
                /*var parts = updateAmountParts(presentValue);
                if (parts != null) {
                    document.getElementById('partfee').innerText = parts.partfee;
                    document.getElementById('partvalue').innerText = parts.partvalue;
                    $('#wdone-total').val(parts.partvalue);
                } else {
                    document.getElementById('partfee').innerText = '0.00000000';
                    document.getElementById('partvalue').innerText = '0.00000000';
                    $('#wdone-total').val(0);
                }*/

            } else {
                this.value = '';
            }
        }).keyup();

        /*function updateAmountParts(summ) {
            if (summ > 0 && dataTrunk != null) {
                var fee = dataTrunk.fee;
                if (dataTrunk.typeFee == 1) {
                    fee = summ * dataTrunk.fee;
                }
                var partValue = summ - fee;
                if (partValue < dataTrunk.minFee * 2) {
                    return null;
                }
                return {"partvalue": partValue.toFixed(8), "partfee": fee.toFixed(8)};
            } else {
                console.log("dataTrunk is null!!!")
                return null;
            }
        }*/

        function setAllSummValue() {
            var walletValue = document.getElementById('walletvalue').innerText.replace(/[^\d.]/g, '');
            if (walletValue > 0) {
                document.getElementById('summamount').value = walletValue;
                /*var parts = updateAmountParts(walletValue);
                if (parts != null) {
                    document.getElementById('partfee').innerText = parts.partfee;
                    document.getElementById('partvalue').innerText = parts.partvalue;
                } else {
                    document.getElementById('partfee').innerText = '0.00000000';
                    document.getElementById('partvalue').innerText = '0.00000000';
                }*/
            }
        }


    </script>

<?php
/*$js = <<<JS
    $(document).on('beforeSubmit', "#wdone-form", function(){                
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(data){
                if(data.status == 'success'){
                    $('#wdone-form').html('<div class="alert-status"><div class="alert alert-success text-center" role="alert">'+data.message+'</div></div>');
                    setTimeout(function(){
                        location.href="/";    
                    }, 1000);                    
                }
                if(data.status == 'error'){
                    $('#wdone-form').prepend('<div class="alert-status"><div class="alert alert-danger text-center" role="alert">'+data.message+'</div></div>');
                }
            }
        });
        return false;
    });
JS;
$this->registerJs($js);
*/?>