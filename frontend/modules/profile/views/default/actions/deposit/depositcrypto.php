<?php

use common\modules\drole\models\webtools\JSONRegistryFactory;

/**
 * withdrawal transactions history page
 *
 * @var $this \yii\web\View
 */
$usPMID = '00000000-430d-4a57-a7ec-ff125372ae09';

$objectID = '4438a6ab-db08-4421-a8bf-9221a8ca7e18';
$walletObjectID = '4bfe0dd7-9e54-4de5-b9fa-3e4882bcd82d';
$trunkObjectID = '62a15104-6ad9-4922-afd0-68e0b57ff87f';
$marketObjectID = '5c1a5894-f6df-4c96-a84d-6679f3375bb7';
$json = JSONRegistryFactory::getRecordsListFromObject(true, $objectID);
$jsonWallet = JSONRegistryFactory::getRecordsListFromObject(true, $walletObjectID);
$jsonTrunk = JSONRegistryFactory::getRecordsListFromObject(true, $trunkObjectID);
$jsonMarket = JSONRegistryFactory::getRecordsListFromObject(true, $marketObjectID);
//$json = JSONRegistryFactory::getRecordsListFromObject(true, '5c1a5894-f6df-4c96-a84d-6679f3375bb7');
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
$innerCoinHTML = "";

foreach ($marketResult as $marketRecord) {
    //if ($marketRecord['id'] != $usPMID)
    $marketAutocompliteSelects .= '{"id":"' . $marketRecord['id'] . '","title":"' . $marketRecord['name'] . '(' . $marketRecord['symbol'] . ')"}, ';
    //$innerCoinHTML .= '<div class="dropdown-item">' . $marketRecord['name'] . '(' . $marketRecord['symbol'] . ')<input value="' . $marketRecord['id'] . '" type="hidden"></div>';
}

if (strlen($marketAutocompliteSelects) > 3) {
    $marketAutocompliteSelects = '[' . substr($marketAutocompliteSelects, 0, strlen($marketAutocompliteSelects) - 1) . ']';
    //$innerCoinHTML = substr($innerCoinHTML, 0, strlen($innerCoinHTML) - 1);
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

$currentPageFilters = '[{"map":"' . getIndexFromArray(json_decode($currentStructure, true), 'af8e49fc-2a70-4143-9f59-69232bddfc69') . '","comp":"6","value":"0"}]';

?>
<script src="<?= \common\helpers\Url::toWithoutLang(['/js/jquery.min.js'], true) ?>"></script>
<script src="<?= \common\helpers\Url::toWithoutLang(['/js/socket.io.js'], true) ?>"></script>
<script src="<?= \common\helpers\Url::toWithoutLang(['/js/simpleSelect.js'], true) ?>"></script>
<script src="<?= \common\helpers\Url::toWithoutLang(['/js/jquery-ui.js'], true) ?>"></script>

<div class="content-wrapper">
    <div class="row">
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
            <div class="card">
                <div class="card-header white">
                    <h3 class="card-title float-left"><?=Yii::t('deposite_page', 'Deposite')?></h3>
                    <a href="/profile/default/perfdone" class="float-right right-btn">
                        $<?=Yii::t('deposite_page', 'Perfect')?> <i class="fa fa-download"
                                                                                                  aria-hidden="true"></i></a>
                </div>
                <div class="card-body">
                    <div>
                        <select id="ht-auto-search" class="form-control" name="segmentation">

                        </select>
                        <div class="row">
                            <div class="col">
                                <a href="" class="btn btn-default btn-block btn-lg with-mr-t"><?=Yii::t('frontend', 'Cancel')?></a>
                            </div>
                            <div class="col">
                                <a id="donehref" href="" class="btn btn-success btn-block btn-lg with-mr-t"><?=Yii::t('frontend', 'Done')?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 col-12">
            <div class="card">
                <div class="card-header white">
                    <h3 class="card-title">                    
                    <?=Yii::t('deposite_page', 'Withdrawal history')?>
                    </h3>
                </div>
                <div class="card card-body">
                    <div class="row mr-b-20">
                        <div class="col-md-4 col-12">

                        </div>
                        <div class="col-md-4 col-12">
                            <select name="select_type_htransaction" id="select_type_htransaction" class="form-control"
                                    hidden>
                                <option value=""><?=Yii::t('frontend', 'All')?></option>
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
                                <button type="button" class="btn btn-ht-current disabled" disabled><?=Yii::t('frontend', 'Page')?><span
                                            id="htrans_page_number">1</span></button>
                                <button type="button" class="btn btn-light btn-ht-next"><i class="fa fa-angle-right"
                                                                                           aria-hidden="true"></i>
                                </button>
                            </div>
                            <label class="d-none input-box"><input id="quick-search" type="text" class="form-control"
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
<?php 
$notFoundMessageText = Yii::t('frontend', 'No Results');
$selectText = Yii::t('frontend', 'Select');
?>
<script>
    var options = {
        terms: <?= $marketAutocompliteSelects ?>,
        notFoundMessage: '<?=$notFoundMessageText?>.',
        defaultSelected: '<?=$selectText?> ...'
    }

    $('#ht-auto-search').simpleSelect(options);
    $('#ht-auto-search').on("change", function (event) {
        paginationUpdate('htransactiontable');
        if ($(document.getElementById('ht-auto-search')).val() != '00000000-430d-4a57-a7ec-ff125372ae09') {
            $('#donehref').attr("href", ("/en/profile/default/ddone?currency=" + $(document.getElementById('ht-auto-search')).val()));
        } else $('#donehref').attr("href", ("/en/profile/default/perfdone"));
    })

    document.getElementById('select_type_htransaction').addEventListener("click", function (e) {
        console.log('select_type_htransaction\').addEventListener')
        paginationUpdate('htransactiontable');
    });

    function getFilterForMarketsToken(selectedMarketName) {
        if (selectedMarketName != '' && selectedMarketName != 'undefined') {
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
        if ($(document.getElementById('ht-auto-search')).val() != 'undefined') {
            var currentCurrencyMap = getIndexFromStructureByID('97bebaa3-7687-4f3c-a85b-e5cc1fbcd605');
            return JSON.parse('{"special":[{"map":"' + currentCurrencyMap + '","comp":"6","value":"' +
                $(document.getElementById('ht-auto-search')).val() + '"}]}');
        } else return JSON.parse('{"special":[]}');

    }
</script>

<?php 
$textDate = Yii::t('frontend', 'Date');
$textCoin = Yii::t('frontend', 'Coin');
$textWallet = Yii::t('frontend', 'Wallet');
$textAmount = Yii::t('frontend', 'Amount');
$textStatus = Yii::t('frontend', 'Status');
$textActions = Yii::t('frontend', 'Actions');
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
        });
    });

    var currentStructure = <?= $currentStructure ?>;
    var currentData = null;

    function defaultUpdateTable(tableID, url, data) {
        console.log(JSON.stringify(data))
        $.post(url, {json: JSON.stringify(data)}).done(function (data) {
            if (data.data.structure == undefined) {
                console.log('not found data!')
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
            });
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
        //localQuery.filters[1].special.push(specialPagesFilters[1]);
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
            var result = "Not found";
            switch (row[index]) {
                case 200:
                    result = 'Accounted';
                    break;
                case 100:
                    result = 'In Progress';
                    break;
                case 300:
                    result = 'Forbidden';
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

    ////
    function updateWalletAmount() {
        var jsonWallet = <?= $jsonWallet ?>;
        jsonWallet.filters = [];
        jsonWallet.filters[0] = {"common": ""};
        jsonWallet.filters[1] = {"special": []};
        var walletStructure = <?= $walletStructure ?>;
        var indexOfWalletIDField = getStructureElementIndexByID(walletStructure, '5b296714-e069-457e-b606-3a40bea5b2f2');
        var filter = JSON.parse('{"map":"' + indexOfWalletIDField + '","comp":"6","value":"' +
            getValuesFromFilters().special[0].value + '"}');
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
        var filter = JSON.parse('{"map":"' + indexOfWalletIDField + '","comp":"6","value":"' +
            getValuesFromFilters().special[0].value + '"}');
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
                console.log(JSON.stringify(maxAmount))
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
        filter = JSON.parse('{"map":"' + indexOfCurrentCurrencyField + '","comp":"6","value":"' +
            getValuesFromFilters().special[0].value + '"}');
        marketJson.filters[1].special.push(filter);
        $.post('/en/drole/default/get-info', {json: JSON.stringify(marketJson)}).done(function (data) {
            if (data == null || data.data == null || data.data.data == null) {
                return;
            }
            if (data.data.data[0] != undefined && data.data.data[0] != null) {
                dataMarket = data.data.data[0];
                currency = dataMarket[getStructureElementIndexByID(marketStructure, '8e6b1492-f288-4010-bbb3-53766c6a2294')].substring(5);
                console.log(JSON.stringify(marketStructure))
            }
        });
    }

    $('#summamount').keyup(function () {
        if (dataTrunk != null) {
            var walletValue = document.getElementById('walletvalue').innerText.replace(/[^\d.]/g, '');
            var presentValue = this.value.replace(/[^\d.]/g, '')
            if (this.value != presentValue || presentValue > walletValue) {
                if (presentValue > walletValue)
                    this.value = walletValue;
                else this.value = presentValue;
            }
            var parts = updateAmountParts(presentValue);
            if (parts != null) {
                document.getElementById('partfee').innerText = parts.partfee;
                document.getElementById('partvalue').innerText = parts.partvalue;
            } else {
                document.getElementById('partfee').innerText = '0.00000000';
                document.getElementById('partvalue').innerText = '0.00000000';
            }

        } else {
            this.value = '';
        }
    }).keyup();

    function updateAmountParts(summ) {
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
    }

    function setAllSummValue() {
        var walletValue = document.getElementById('walletvalue').innerText.replace(/[^\d.]/g, '');
        if (walletValue > 0) {
            document.getElementById('summamount').value = walletValue;
            var parts = updateAmountParts(walletValue);
            if (parts != null) {
                document.getElementById('partfee').innerText = parts.partfee;
                document.getElementById('partvalue').innerText = parts.partvalue;
            } else {
                document.getElementById('partfee').innerText = '0.00000000';
                document.getElementById('partvalue').innerText = '0.00000000';
            }
        }
    }

</script>