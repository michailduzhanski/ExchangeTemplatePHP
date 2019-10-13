<?php

use common\modules\drole\models\webtools\JSONRegistryFactory;

/**
 * withdrawal transactions history page
 *
 * @var $this \yii\web\View
 */
$objectID = '5c1a5894-f6df-4c96-a84d-6679f3375bb7';
$descriptionCoinObjectID = 'fd27729c-0f30-444b-a124-e3e16069e7d0';
$defaultCoinForView = '964940de-430d-4a57-a7ec-ff125372ae09';
$dynamicRoleArray = \common\modules\drole\models\registry\DynamicRoleModel::getAnonymousDynamicArray(
    'af09ea17-d47c-452d-93de-2c89157b9d5b', 'b56b99b6-2c6f-4103-849a-e914e8594869');
$json = JSONRegistryFactory::getRecordsListFromObjectAnonymous($dynamicRoleArray['company_id'],
    $dynamicRoleArray['service_id'], true, $objectID);

$descriptionJson = JSONRegistryFactory::getRecordsListFromObjectAnonymous($dynamicRoleArray['company_id'],
    $dynamicRoleArray['service_id'], false, $descriptionCoinObjectID);
//$json = JSONRegistryFactory::getRecordsListFromObject(true, '5c1a5894-f6df-4c96-a84d-6679f3375bb7');


$sql = "SELECT coin_data_use.id, coin_data_use.name, coin_data_use.symbol, coin_data_use.image FROM 
coin_data_use join coin_record_own on coin_record_own.id = coin_data_use.id WHERE coin_record_own.company_id = '" . $dynamicRoleArray['company_id'] . "' and 
coin_record_own.service_id = '" . $dynamicRoleArray['service_id'] . "' and coin_data_use.status = '200'";
$marketResult = \Yii::$app->db->createCommand($sql)->queryAll();
if (!$marketResult || count($marketResult) < 1) {
    echo "not found market";
    die(402);
}
$currenciesList = json_encode($marketResult);

$listBaseCurrencies = "select coin_data_use.* from coin_data_use join coin_record_own on coin_data_use.id = 
coin_record_own.id where coin_data_use.id in (select DISTINCT basecurrencyid from (SELECT basecurrencyid, date_create FROM 
coinmarkets_data_use where status = '200' order by date_create) as foo) and coin_record_own.company_id = '" .
    $dynamicRoleArray['company_id'] . "' and coin_record_own.service_id = '" . $dynamicRoleArray['service_id'] . "' order by date_create";
$baseCurrencyList = \Yii::$app->db->createCommand($listBaseCurrencies)->queryAll();
if (!$baseCurrencyList || count($baseCurrencyList) < 1) {
    echo "not found coin description";
    die(404);
}

$marketAutocompliteSelects = '';
$defaultCoin = null;
foreach ($marketResult as $marketRecord) {
    $marketAutocompliteSelects .= '"' . $marketRecord['name'] . '(' . $marketRecord['symbol'] . ')",';
}

foreach ($baseCurrencyList as $currentCoin) {
    if ($currentCoin['id'] == $defaultCoinForView) {
        $defaultCoin = $currentCoin;
        break;
    }
}
if (strlen($marketAutocompliteSelects) > 3) {
    $marketAutocompliteSelects = '[' . substr($marketAutocompliteSelects, 0, strlen($marketAutocompliteSelects) - 1) . ']';
}
$currentStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($objectID, $dynamicRoleArray['id']);
$descriptionStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($descriptionCoinObjectID, $dynamicRoleArray['id']);
function getIndexFromArray($currentStructure, $fieldID)
{
    for ($i = 0; $i < count($currentStructure); $i++) {
        if ($currentStructure[$i]['id'] == $fieldID) {
            return $i;
        }
    }
    return null;
}


$descriptionJson['permission']['contact_id'] = '00000000-0000-0000-0000-000000000000';
//echo json_encode(\common\modules\drole\models\gate\DataObjectAPIHandler::parseQuery($descriptionJson));

//$currentPageFilters = '[{"map":"' . getIndexFromArray(json_decode($currentStructure, true), 'af8e49fc-2a70-4143-9f59-69232bddfc69') . '","comp":"6","value":"1"},{"map":"' . getIndexFromArray(json_decode($currentStructure, true), 'c79befba-e8bf-4ff0-b50f-0508f900fde3') . '","comp":"2","value":"100"}]'
?>
<script src="/js/jquery.min.js"></script>
<script src="/js/socket.io.js"></script>
<script src="/js/bignumber.js"></script>
<script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<div class="content-wrapper">
    <div class="row">
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-xs-12">
            <div class="main-info-of-currency">
                <div class="currency-info-header">
                    <div class="row">
                        <div class="col">
                            <div class="currency-info-name"><img id="dscrimg" src=""><span
                                        id="dscrsmbl">BTC</span></div>
                            <div id="dscrname" class="currency-info-fullname">Bitcoin</div>
                            <input type="text" class="kv-fa rating-loading" value="4" data-size="xs" data-step="1"
                                   title="">
                        </div>
                        <div class="col">
                            <div class="currency-course course-dollar"><i id="dscrchng"
                                                                          class="fa fa-caret-up"></i><span
                                        id="dscrlprc">0</span>
                            </div>
                            <div id="dscrpair" class="currency-course course-btc"><span id="dscrttl">0</span></div>
                            <a id="dscrlink"
                               href="">Start
                                trading <i class="fa fa-long-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="currency-info-body has-scroll">
                    <div id="mainlist" class="full-height">
                        <div class="info-title grey-text">Algorithm:</div>
                        <div class="info-value">Scrypt</div>
                        <div class="info-title grey-text">Network:</div>
                        <div class="info-value">POW</div>
                        <div class="info-title grey-text">Block Reward:</div>
                        <div class="info-value">50.00000000</div>
                        <div class="info-title grey-text">Total Coins:</div>
                        <div class="info-value">500000000.00000000</div>
                        <div class="info-title grey-text">Website:</div>
                        <div class="info-value"><a href="" target="_blanck"></a></div>
                        <div class="info-title grey-text">Explorer::</div>
                        <div class="info-value"><a href="" target="_blanck"></a></div>
                        <div class="info-title grey-text">Source Code:</div>
                        <div class="info-value"><a href="" target="_blanck"></a></div>
                        <div class="info-title grey-text">Launch Forum:</div>
                        <div class="info-value">N/A</div>
                        <div class="info-title grey-text">Trade Fee:</div>
                        <div class="info-value">20%</div>
                        <div class="info-title grey-text">Pool Fee:</div>
                        <div class="info-value">0.00%</div>
                        <!--h3 class="title">Rating</h3>
  <div class="row">
      <div class="col grey-text">
          <p>Windows Wallet:</p>
          <p>Linux Wallet:</p>
          <p>Mac Wallet:</p>
          <p>Mobile Wallet:</p>
          <p>Web/Paper Wallet:</p>
          <p>Premine:</p>
          <p>Website:</p>
          <p>Block Explorer:</p>
          <p>Haysiope Forum:</p>
      </div>
      <div class="col">
          <p>Yes</p>
          <p>Yes</p>
          <p>No</p>
          <p>No</p>
          <p>No</p>
          <p>60%</p>
          <p>Yes</p>
          <p>No</p>
          <p>No</p>
      </div>
  </div-->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title card-title-inline">Coin information</h3>
                    <div class="row mr-b-20">
                        <div class="col-md-3 col-12">
                            <div class="autocomplete">
                                <input id="ht-auto-search" type="text" class="form-control" name="marketsAutocomplite"
                                       placeholder="Coins filter">
                            </div>
                            <button type="button" onclick="location.reload()" class="btn btn-light btn-refresh"><i
                                        class="fa fa-times" aria-hidden="true"></i></button>
                        </div>
                        <div class="col-md-3 col-12">
                            <select name="select_type_htransaction" id="select_type_htransaction" class="form-control"
                                    hidden>
                                <option value="">All</option>
                                <option value="0">Buy</option>
                                <option value="1">Sell</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="pagination-arrows text-right">
                                <button type="button" class="btn btn-light btn-ht-maxprev"><i
                                            class="fa fa-step-backward"
                                            aria-hidden="true"></i></button>
                                <button type="button" class="btn btn-light btn-ht-prev"><i class="fa fa-angle-left"
                                                                                           aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-ht-current disabled" disabled>Page<span
                                            id="htrans_page_number">1</span></button>
                                <button type="button" class="btn btn-light btn-ht-next"><i class="fa fa-angle-right"
                                                                                           aria-hidden="true"></i>
                                </button>
                            </div>
                            <label class="d-none input-box"><input id="quick-search" type="text" class="form-control"
                                                                   placeholder="Search...." value=""></label>
                        </div>
                    </div>
                    <div class="h-scroll horizontal-scroll">
                        <div>
                            <?php
                            for ($i = 0; $i < count($baseCurrencyList); $i++) {
                                $activeClass = '';
                                if ($i == 0) {
                                    $activeClass = ' active';
                                }
                                echo '<button id="' . $baseCurrencyList[$i]['id'] . '" type="button" class="btn btn-secondary btn-change-market' . $activeClass . '">' . $baseCurrencyList[$i]['symbol'] . '</button>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tablecoinlist" class="table table-bordered table-hover table-course table-striped">

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    /* ============= AUTOCOMPLITE ============= */
    function autocomplete(inp, arr) {
        /*the autocomplete function takes two arguments,
        the text field element and an array of possible autocompleted values:*/
        var currentFocus;
        /*execute a function when someone writes in the text field:*/
        inp.addEventListener("input", function (e) {
            var a, b, i = this.value;
            var val = this.value.toUpperCase();
            /*close any already open lists of autocompleted values*/
            closeAllLists();
            if (!val) {
                return false;
            }
            currentFocus = -1;
            /*create a DIV element that will contain the items (values):*/
            a = document.createElement("DIV");
            a.setAttribute("id", this.id + "autocomplete-list");
            a.setAttribute("class", "autocomplete-items");
            /*append the DIV element as a child of the autocomplete container:*/
            this.parentNode.appendChild(a);
            /*for each item in the array...*/
            for (i = 0; i < arr.length; i++) {
                /*check if the item starts with the same letters as the text field value:*/
                //if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                if (arr[i].toUpperCase().search(`${val}`) >= 0) {
                    /*create a DIV element for each matching element:*/
                    b = document.createElement("DIV");
                    /*make the matching letters bold:*/
                    //b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                    //b.innerHTML += arr[i].substr(val.length);
                    b.innerHTML = arr[i];
                    /*insert a input field that will hold the current array item's value:*/
                    b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                    /*execute a function when someone clicks on the item value (DIV element):*/
                    b.addEventListener("click", function (e) {
                        /*insert the value for the autocomplete text field:*/
                        inp.value = this.getElementsByTagName("input")[0].value;
                        /*close the list of autocompleted values,
                        (or any other open lists of autocompleted values:*/
                        paginationUpdate('tablecoinlist');
                        closeAllLists();
                    });
                    a.appendChild(b);
                }
            }
        });
        /*execute a function presses a key on the keyboard:*/
        inp.addEventListener("keydown", function (e) {
            var x = document.getElementById(this.id + "autocomplete-list");
            if (x) x = x.getElementsByTagName("div");
            if (e.keyCode == 40) {
                /*If the arrow DOWN key is pressed,
                increase the currentFocus variable:*/
                currentFocus++;
                /*and and make the current item more visible:*/
                addActive(x);
            } else if (e.keyCode == 38) { //up
                /*If the arrow UP key is pressed,
                decrease the currentFocus variable:*/
                currentFocus--;
                /*and and make the current item more visible:*/
                addActive(x);
            } else if (e.keyCode == 13) {
                /*If the ENTER key is pressed, prevent the form from being submitted,*/
                e.preventDefault();
                if (currentFocus > -1) {
                    /*and simulate a click on the "active" item:*/
                    if (x) x[currentFocus].click();
                }
            }
        });

        function addActive(x) {
            /*a function to classify an item as "active":*/
            if (!x) return false;
            /*start by removing the "active" class on all items:*/
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (x.length - 1);
            /*add class "autocomplete-active":*/
            x[currentFocus].classList.add("autocomplete-active");
        }

        function removeActive(x) {
            /*a function to remove the "active" class from all autocomplete items:*/
            for (var i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active");
            }
        }

        function closeAllLists(elmnt) {
            /*close all autocomplete lists in the document,
            except the one passed as an argument:*/
            var x = document.getElementsByClassName("autocomplete-items");
            for (var i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                    x[i].parentNode.removeChild(x[i]);
                }
            }
        }

        /*execute a function when someone clicks in the document:*/
        document.addEventListener("click", function (e) {
            closeAllLists(e.target);
        });

        /*inp.addEventListener("change", function (e) {
            paginationUpdate('tablecoinlist');
        });*/
    }

    document.getElementById('select_type_htransaction').addEventListener("click", function (e) {
        paginationUpdate('tablecoinlist');
    });

    function getFilterForMarketsToken(selectedMarketName) {
        if (selectedMarketName != '') {
            for (var i = 0; i < marketCurrencies.length; i++) {
                if ((marketCurrencies[i]['name'] + '(' + marketCurrencies[i]['symbol'] + ')') == selectedMarketName) {
                    //var currentCurrencyMap = getStructureElementIndexByID(currentStructure, '97bebaa3-7687-4f3c-a85b-e5cc1fbcd605');
                    var currentCurrencyMap = getIndexFromStructureByID('b9c877df-5e27-45eb-a625-775d57e19b72');
                    return JSON.parse('{"special":[{"map":"' + currentCurrencyMap + '","comp":"6","value":"' +
                        marketCurrencies[i].id + '"}]}');
                }
            }
        } else {
            return {special: []};
        }
    }

    function getValuesFromFilters() {
        var result = getFilterForMarketsToken(document.getElementById('ht-auto-search').value);
        /*var e = document.getElementById("select_type_htransaction");
        var currentType = e.options[e.selectedIndex].value;
        if (currentType != '') {
            /!*result.special.push(JSON.parse('{"map":"' +
                getStructureElementIndexByID(currentStructure, '7e27659f-4a38-479f-a17d-0f70425b4942') + '","comp":"6","value":"' +
                currentType + '"}'));*!/
            result.special.push(JSON.parse('{"map":"' +
                getIndexFromStructureByID('b9c877df-5e27-45eb-a625-775d57e19b72') + '","comp":"6","value":"' +
                currentType + '"}'));
        }*/
        return result;
    }
</script>

<script>
    var marketCurrencies = <?= $currenciesList ?>;
    var currentSortValue = null;
    var marketsPairs = <?= $marketAutocompliteSelects ?>;

    $(function () {
        $('#tablecoinlist').on('click', '.fieldsort', function () {
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
            paginationUpdate('tablecoinlist');
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
            defaultUpdateTable('tablecoinlist', '/en/drole/default/get-info', localQuery);*/
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
        defaultUpdateTable('tablecoinlist', '/en/drole/default/get-info', localQuery);
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
            data = JSON.parse(data);
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
                    if (presentInTableFields.includes(currentStructure[sindex].id)) {
                        var currentHeadFunction = getHeadFunctionByID(data['data']['structure']['data'][sindex].id);
                        row.appendChild(currentHeadFunction(sindex));
                    }
                });
                // return;

            }
            var isUpdated = false;
            $.each(data['data']['data'], function (r, rowValue) {
                var body = x.getElementsByTagName('tbody')[0];
                var row = body.insertRow();
                row.className = "rowdiv";
                var fieldIndex = 0;
                var viewIndex = 1;
                zeroFieldID = data['data']['structure']['data'][fieldIndex].id;
                var record = this;

                $.each(this, function (f, fieldValue) {
                    if (presentInTableFields.includes(currentStructure[f].id)) {
                        var currentFunction = getDataFunctionByID(data['data']['structure']['data'][f].id);
                        //console.log(currentFunction(record, f))
                        row.appendChild(currentFunction(record, f));
                    }

                });
                //update first element to
                if(!isUpdated){
                    currentMarketRow = $(row);
                    updateDescription(currentMarketRow);
                    isUpdated = true;
                }
                //var cell = row.insertCell(0);
                //cell.innerHTML = '<input type="number" placeholder="position" value="' + recordCount + '" min="0" max="' + recordCount + '" class="form-control"><button type="button" onclick="addValueToInnerObject(this)" id="' + this[0] + '" class="btn btn-icon btn-add"><i class="pe-7s-plus"></i></button><button type="button" onclick="addValueToInnerObject(this)" id="' + this[0] + '" class="btn btn-icon btn-add"><i class="pe-7s-pen"></i></button>';
            });

        });
    }

    function defaultUpdateDescription(url, data) {
        $.post(url, {json: JSON.stringify(data)}).done(function (data) {
                data = JSON.parse(data);
                if (data.data.structure == undefined) {
                    return;
                }
                console.log($($(currentMarketRow).children(0)[getIndexFromHeader('3c6bd221-c53e-4637-9225-c23d2b701e7d')]).children(0)[0])
                {
                }
                var isPresent = undefined;
                var marketName = "NOT_NOT";
                if ($($(currentMarketRow).children(0)[getIndexFromHeader('3c6bd221-c53e-4637-9225-c23d2b701e7d')]).children(0)[0] != undefined) {
                    $('#dscrlprc').html($($(currentMarketRow).children(0)[getIndexFromHeader('3c6bd221-c53e-4637-9225-c23d2b701e7d')]).children(0)[0].innerText);
                    isPresent = $($(currentMarketRow).children(0)[getIndexFromHeader('8076c2b1-4eaa-444f-92c8-bc86e1eb65d7')]).children(0)[0].innerText;
                    marketName = $($(currentMarketRow).children(0)[getIndexFromHeader('8e6b1492-f288-4010-bbb3-53766c6a2294')]).children(0)[0].innerText;
                }
                $('#dscrchng').removeClass('fa-caret-up');
                $('#dscrchng').removeClass('fa-caret-down');
                if (isPresent > 0) {
                    $('#dscrchng').addClass('fa-caret-up')
                } else {
                    $('#dscrchng').addClass('fa-caret-down')
                }

                $('#dscrlink').attr('href', ('/profile/default/exchange?market=' + marketName));
                marketName = marketName.split('_');
                $('#dscrsmbl').html(marketName[1]);
                if ($($(currentMarketRow).children(0)[getIndexFromHeader('cc024f97-efe0-4821-b540-c75c0aee89d5')]).children(0)[0] != undefined)
                    $('#dscrpair').html(marketName[0] + ': <span id="dscrttl">' +
                        $($(currentMarketRow).children(0)[getIndexFromHeader('cc024f97-efe0-4821-b540-c75c0aee89d5')]).children(0)[0].innerText + '</span>')
                var x = document.getElementById('mainlist');
                var result = '';
                result += '<div class="info-title grey-text">Algorithm:</div> <div class="info-value">' + data.data.data[0][getLocalIndexFromStructureByID('0a69508e-2c22-4c41-86f6-84ca790a9c81', data.data.structure.data)] + '</div>';
                result += '<div class="info-title grey-text">Network:</div> <div class="info-value">' + data.data.data[0][getLocalIndexFromStructureByID('b1cbc386-69a8-44b6-b230-e480549c06ff', data.data.structure.data)] + '</div>';
                result += '<div class="info-title grey-text">Block Time:</div> <div class="info-value">' + data.data.data[0][getLocalIndexFromStructureByID('95f9ce69-8236-46e3-a7f9-e871a0c29070', data.data.structure.data)] + '</div>';
                result += '<div class="info-title grey-text">Block Rewards:</div> <div class="info-value">' + data.data.data[0][getLocalIndexFromStructureByID('b76f8a66-6ad4-4e1e-9227-21dc5c0ceebb', data.data.structure.data)] + '</div>';
                result += '<div class="info-title grey-text">Total coins:</div> <div class="info-value">' + data.data.data[0][getLocalIndexFromStructureByID('8929c20e-95bf-4b50-9e3d-9b97bcc7aa51', data.data.structure.data)] + '</div>';
                result += '<div class="info-title grey-text">Description:</div> <div class="info-value">' + getDescriptionCoinByLang(data) + '</div>';
                x.innerHTML = result;
            }
        )
    }

    function getDescriptionCoinByLang(data) {
        var lang = '<?= \Yii::$app->language ?>';
        if (lang == 'en') {
            return data.data.data[0][getLocalIndexFromStructureByID('d119e83d-24f3-4b87-8424-fba545a478c3', data.data.structure.data)]
        }
        if (lang == 'ru') {
            return data.data.data[0][getLocalIndexFromStructureByID('b33f0e31-1b7f-48a5-a157-fe64b104fede', data.data.structure.data)]
        }
    }

    var presentInTableFields = ['9cc6a6f0-7714-407a-a893-8d1493c96fdd', 'ef9bba25-acd3-44b9-932f-59a960c0e908',
        '8e6b1492-f288-4010-bbb3-53766c6a2294', 'cc024f97-efe0-4821-b540-c75c0aee89d5',
        '3c6bd221-c53e-4637-9225-c23d2b701e7d', '8076c2b1-4eaa-444f-92c8-bc86e1eb65d7',
        '96ed4513-f007-4b0c-8d0e-b155e62b618d', '5b3c05c3-a766-45e1-8401-1a1b28f6b3d3',
        '33b924f5-12f6-4f99-a546-1f47a531a8ad', 'b9c877df-5e27-45eb-a625-775d57e19b72'];
    var presentInTablesFunctions = [];
    var sortMaps = {
        '9cc6a6f0-7714-407a-a893-8d1493c96fdd': 'Date',
        'ef9bba25-acd3-44b9-932f-59a960c0e908': 'ID',
        'b9c877df-5e27-45eb-a625-775d57e19b72': 'Coin',
        '8e6b1492-f288-4010-bbb3-53766c6a2294': 'Market',
        'cc024f97-efe0-4821-b540-c75c0aee89d5': 'Base24h',
        '3c6bd221-c53e-4637-9225-c23d2b701e7d': 'Last price',
        '8076c2b1-4eaa-444f-92c8-bc86e1eb65d7': 'Change',
        '96ed4513-f007-4b0c-8d0e-b155e62b618d': 'Low',
        '5b3c05c3-a766-45e1-8401-1a1b28f6b3d3': 'High',
        '33b924f5-12f6-4f99-a546-1f47a531a8ad': 'Volume'
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
        if (document.getElementById('tablecoinlist').getElementsByTagName('tbody') != null && document.getElementById('tablecoinlist').getElementsByTagName('tbody') != 'undefined' &&
            document.getElementById('tablecoinlist').getElementsByTagName('tbody').length > 0) {
            if (document.getElementById('tablecoinlist').getElementsByTagName('tbody')[0].getElementsByTagName('tr').length < currentTableLimit) return;
        } else {
            return;
        }
        pageOperation = 1;
        paginationUpdate('tablecoinlist');
    });

    $('.btn.btn-ht-prev').click(function () {
        if (currentPage <= 0) return;
        pageOperation = -1;
        //console.log('button prev')
        //defaultUpdateTable('tablecoinlist', '/en/drole/default/get-info', localQuery);
        paginationUpdate('tablecoinlist');
    });

    $('.btn.btn-ht-maxprev').click(function () {
        //console.log('button maxprev')
        //defaultUpdateTable('tablecoinlist', '/en/drole/default/get-info', localQuery);
        currentPage = 0;
        paginationUpdate('tablecoinlist');
    });

    function paginationUpdate(tableID) {
        var localQuery = <?= $json ?>;
        //var specialPagesFilters = < ?= $currentPageFilters ?>;
        localQuery.filters = [];
        localQuery.filters[0] = {"common": ""};
        localQuery.filters[1] = getValuesFromFilters();
        //localQuery.filters[1].special.push(specialPagesFilters[0]);
        //localQuery.filters[1].special.push(specialPagesFilters[1]);
        if (currentPressedMarket == null || currentPressedMarket == undefined) {
            currentPressedMarket = '964940de-430d-4a57-a7ec-ff125372ae09';
        }
        localQuery.filters[1].special.push(JSON.parse('{"map":"' + getIndexFromStructureByID('5cfe2e58-b96a-4c66-88d4-fc5143c5c5a3') + '","comp":"6","value":"' + currentPressedMarket + '"}'));
        localQuery.filters[1].special.push(JSON.parse('{"map":"' + getIndexFromStructureByID('48e6c567-99dd-4c23-9574-a53e5c23607e') + '","comp":"6","value":"200"}'));
        //localQuery.filters[1] = JSON.parse('{"special":[{"map":"6","comp":"6","value":"200"}]}');
        localQuery.filters[2] = JSON.parse('{"sorting":[{"map":"' + getIndexFromStructureByID('cc024f97-efe0-4821-b540-c75c0aee89d5') + '","field":"cc024f97-efe0-4821-b540-c75c0aee89d5","sort":"1"}]}');
        if (currentSortValue != null) {
            var key = $(currentSortValue).attr('id');
            var indexes = getIndexFromStructureByID($(currentSortValue).attr('id'));
            localQuery['filters'][2] = JSON.parse('{"sorting":[{"map":"' + indexes + '","field":"' + key + '","sort":"' + ($($(currentSortValue).children(0)[1]).hasClass('fa-sort-amount-asc') ? '0' : '1') + '"}]}');
        } else {

            localQuery['filters'][2] = JSON.parse('{"sorting":[{"map":"' + getIndexFromStructureByID('33b924f5-12f6-4f99-a546-1f47a531a8ad') + '","field":"33b924f5-12f6-4f99-a546-1f47a531a8ad","sort":"0"}]}');
        }

        if (document.getElementById(tableID).getElementsByTagName('tbody') != null && document.getElementById(tableID).getElementsByTagName('tbody') != 'undefined' &&
            document.getElementById(tableID).getElementsByTagName('tbody').length > 0)
            pageRecordsCount = document.getElementById(tableID).getElementsByTagName('tbody')[0].getElementsByTagName('tr').length;
        if (pageOperation == 0 && currentPage == 0) {
            pageRecordsCount = currentTableLimit;
        }
        localQuery['filters'][3] = JSON.parse('{"limit":{"lmt":"' + currentTableLimit + '","off":"' + (currentPage * currentTableLimit) + '","prev":"' +
            pageRecordsCount + '","asc":"' + pageOperation + '"}}');
        defaultUpdateTable('tablecoinlist', '/en/drole/default/getmarketpair', localQuery);
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

    /*function getStructureElementIndexByID(instantStructure, fieldID) {
        if (instantStructure == null || instantStructure == undefined) {
            console.log('error')
            return null;
        } else {
            console.log('start find fieldID: ' + fieldID)
        }
        for (var instantStructureIndex = 0; instantStructureIndex < instantStructure.length; instantStructureIndex++) {
            console.log('[' + instantStructure[instantStructureIndex].id + '] == ' + fieldID + ' ? ' + (instantStructure[instantStructureIndex].id == fieldID))
            if (instantStructure[instantStructureIndex].id == fieldID) {
                return instantStructureIndex;
            }
        }
        return null;
    }*/

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

    function getLocalIndexFromStructureByID(fieldID, structure) {
        for (var i = 0; i < Object.keys(structure).length; i++) {
            if (structure[i].id == fieldID) {
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
            var currentCurrencyID = fieldValue[getIndexFromStructureByID('b9c877df-5e27-45eb-a625-775d57e19b72')];
            var cell = document.createElement('td');
            var result = 'undefined';
            for (var currencyIndex = 0; currencyIndex < marketCurrencies.length; currencyIndex++) {
                if (marketCurrencies[currencyIndex].id == currentCurrencyID) {
                    result = marketCurrencies[currencyIndex]['name'] + '(' + marketCurrencies[currencyIndex]['symbol'] + ')';
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

    var marketNameDataFunction = function (row, index = -1) {
        if (index == -1) {
            return '';
        }
        var fieldHeader = currentStructure[index];
        if (typeof(fieldHeader) != "undefined") {
            var cell = document.createElement('td');
            cell.innerHTML = '<a href="/profile/default/exchange?market=' + row[index] + '">' +
                row[index] + '</a>';
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

    //setFunctionsByID('9cc6a6f0-7714-407a-a893-8d1493c96fdd', dateHeadFunction, dateDataFunction);
    setFunctionsByID('8e6b1492-f288-4010-bbb3-53766c6a2294', baseSortingHeadFunction, marketNameDataFunction);
    setFunctionsByID('b9c877df-5e27-45eb-a625-775d57e19b72', null, currencyDataFunction);
    setFunctionsByID('cc024f97-efe0-4821-b540-c75c0aee89d5', baseSortingHeadFunction, moneyViewDataFunction);
    setFunctionsByID('3c6bd221-c53e-4637-9225-c23d2b701e7d', baseSortingHeadFunction, moneyViewDataFunction);
    setFunctionsByID('96ed4513-f007-4b0c-8d0e-b155e62b618d', baseSortingHeadFunction, moneyViewDataFunction);
    setFunctionsByID('5b3c05c3-a766-45e1-8401-1a1b28f6b3d3', baseSortingHeadFunction, moneyViewDataFunction);
    setFunctionsByID('33b924f5-12f6-4f99-a546-1f47a531a8ad', baseSortingHeadFunction, moneyViewDataFunction);
    setFunctionsByID('c79befba-e8bf-4ff0-b50f-0508f900fde3', null, statusDataFunction);
    paginationUpdate('tablecoinlist');
    autocomplete(document.getElementById("ht-auto-search"), marketsPairs);
    getStartDescription();

    //market update
    var myActiveBaseCurrency = $('.btn-change-market.active');
    var activeHistoryOrdersBtn = $('.d-inline-block.float-right.align-middle.btn-transparent.grey-text.active');
    var currentPressedMarket = '964940de-430d-4a57-a7ec-ff125372ae09';

    $('.btn-change-market').click(function () {
        updateMarket(this);
    });

    var currentMarketRow = null;

    function updateMarket(button) {
        if (!button.classList.contains('active')) {
            myActiveBaseCurrency.removeClass("active");
            myActiveBaseCurrency = $(button);
            myActiveBaseCurrency.addClass("active");
        }
        currentPressedMarket = button.id;
        paginationUpdate('tablecoinlist');
    }

    function getIndexFromHeader(fieldID) {
        var thead = document.getElementById(fieldID)
        return $(thead).index();
    }

    $(document).on('click', '.rowdiv', function () {
        currentMarketRow = $(this);
        updateDescription($(this))
    })

    function getStartDescription() {
        var descriptionQuery = <?= json_encode($descriptionJson) ?>;
        descriptionQuery.filters[1] = JSON.parse('{"special":[{"map":"' + getLocalIndexFromStructureByID('6b5922e9-54c7-46df-bc18-5f9d45a02aaf', <?= $descriptionStructure ?>) + '","comp":"6","value":"<?= $defaultCoin['id'] ?>"}]}');
        //console.log(JSON.stringify(descriptionQuery))
        $('#dscrimg').attr('src', '/data/objects/5cb705ea-6c8c-4dae-a620-248545acab14/img/<?= $defaultCoin['image'] ?>');
        $('#dscrname').html('<?= $defaultCoin['name'] ?>');
        $('#dscrsmbl').html('<?= $defaultCoin['symbol'] ?>');
        //defaultUpdateDescription('/en/drole/default/getmarketpair', descriptionQuery);
    }


    function updateDescription() {
        var index = marketsPairs.indexOf($($(currentMarketRow).children(0)[getIndexFromHeader('b9c877df-5e27-45eb-a625-775d57e19b72')]).children(0)[0].innerText);
        //console.log(marketCurrencies[index].id)
        var descriptionQuery = <?= json_encode($descriptionJson) ?>;
        descriptionQuery.filters[1] = JSON.parse('{"special":[{"map":"' + getLocalIndexFromStructureByID('6b5922e9-54c7-46df-bc18-5f9d45a02aaf', <?= $descriptionStructure ?>) + '","comp":"6","value":"' + marketCurrencies[index].id + '"}]}');
        //console.log(JSON.stringify(descriptionQuery))
        var currencyIndex = marketsPairs.indexOf($($(currentMarketRow).children(0)[getIndexFromHeader('b9c877df-5e27-45eb-a625-775d57e19b72')]).children(0)[0].innerText);
        $('#dscrimg').attr('src', '/data/objects/5cb705ea-6c8c-4dae-a620-248545acab14/img/' + marketCurrencies[currencyIndex].image);
        $('#dscrname').html(marketCurrencies[currencyIndex].name);
        //$('#dscrsmbl').html(marketCurrencies[currencyIndex].symbol);
        defaultUpdateDescription('/en/drole/default/getmarketpair', descriptionQuery);
    }

</script>