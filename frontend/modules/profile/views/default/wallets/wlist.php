<?php

use common\modules\drole\models\webtools\JSONRegistryFactory;

/**
 * Wallets list
 *
 * @var $this \yii\web\View
 */
$objectID = '4bfe0dd7-9e54-4de5-b9fa-3e4882bcd82d';
$currencyObjectID = '5cb705ea-6c8c-4dae-a620-248545acab14';
$json = JSONRegistryFactory::getRecordsListFromObject(false, $objectID);
$json['work']['operation'] = 5;
$json = json_encode($json);
//$json = JSONRegistryFactory::getRecordsListFromObject(true, '5c1a5894-f6df-4c96-a84d-6679f3375bb7');
$dynamicRoleArray = \common\modules\drole\models\registry\DynamicRoleModel::getArrayOfDynamicRole(\Yii::$app->user->getIdentity()->auth['drole']);
$sql = "SELECT coin_data_use.id, coin_data_use.name, coin_data_use.symbol, coin_data_use.image FROM 
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
    $marketAutocompliteSelects .= '"' . $marketRecord['name'] . '(' . $marketRecord['symbol'] . ')",';
}
if (strlen($marketAutocompliteSelects) > 3) {
    $marketAutocompliteSelects = '[' . substr($marketAutocompliteSelects, 0, strlen($marketAutocompliteSelects) - 1) . ']';
}
$currentStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck($objectID, \Yii::$app->user->getIdentity()->auth['drole']);
$decodedStructure = json_decode($currentStructure, true);
function getIndexFromArray($currentStructure, $fieldID)
{
    for ($i = 0; $i < count($currentStructure); $i++) {
        if ($currentStructure[$i]['id'] == $fieldID) {
            return $i;
        }
    }
    return null;
}

$currencyIDMap = getIndexFromArray($decodedStructure, '5b296714-e069-457e-b606-3a40bea5b2f2');
$currentPageFilters = '[{"map":"' . getIndexFromArray($decodedStructure, '8bfee8c9-c297-4124-a43b-909748e243a6') . '","comp":"6","value":"' . \Yii::$app->user->getId() . '"}]';
?>
<script src="<?= \common\helpers\Url::toWithoutLang(['/js/jquery.min.js'], true) ?>"></script>
<script src="<?= \common\helpers\Url::toWithoutLang(['/js/socket.io.js'], true) ?>"></script>
<script src="<?= \common\helpers\Url::toWithoutLang(['/js/bignumber.js'], true) ?>"></script>
<script src="<?= \common\helpers\Url::toWithoutLang(['/js/jquery-ui.js'], true) ?>"></script>
<div class="content-wrapper">
    <div class="card card-body">
        <h3 class="card-title">
        <?=Yii::t('wallets_page', 'Wallets') ?>
        </h3>
        <div class="row mr-b-20">
            <div class="col-md-3 col-12">
                <div class="autocomplete">
                    <input id="ht-auto-search" type="text" class="form-control" name="marketsAutocomplite"
                           placeholder="<?=Yii::t('wallets_page', 'Coins filter') ?>">
                </div>
                <button type="button" onclick="location.reload()" class="btn btn-light btn-refresh"><i
                            class="fa fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="col-md-3 col-12">
                <div class="filter-check">
                    <div class="form-check">
                        <label class="line-btn">
                            <input id="nonzero" type="checkbox" class="form-check-input" checked
                                                       onclick="checkBoxUpdate()">
                            <?=Yii::t('wallets_page', 'Hide zero balances') ?><i class="input-helper"></i></label>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="pagination-arrows text-right">
                    <button type="button" class="btn btn-light btn-ht-maxprev"><i class="fa fa-step-backward"
                                                                                  aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-light btn-ht-prev"><i class="fa fa-angle-left"
                                                                               aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn btn-ht-current disabled" disabled>
                        <?=Yii::t('frontend', 'Page')?>
                        <span id="htrans_page_number">1</span></button>
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
                <table id="htransactiontable" class="table table-bordered table-hover table-course table-striped">

                </table>
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
                        paginationUpdate('htransactiontable');
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
            paginationUpdate('htransactiontable');
        });*/
    }

    /*document.getElementById('select_type_htransaction').addEventListener("click", function (e) {
        paginationUpdate('htransactiontable');
    });*/

    function getFilterForMarketsToken(selectedMarketName) {
        if (selectedMarketName != '') {
            for (var i = 0; i < marketCurrencies.length; i++) {
                if ((marketCurrencies[i]['name'] + '(' + marketCurrencies[i]['symbol'] + ')') == selectedMarketName) {
                    var currentCurrencyMap = getIndexFromStructureByID('5b296714-e069-457e-b606-3a40bea5b2f2');
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

            result.special.push(JSON.parse('{"map":"' +
                getIndexFromStructureByID('7e27659f-4a38-479f-a17d-0f70425b4942') + '","comp":"6","value":"' +
                currentType + '"}'));
        }*/
        return result;
    }
</script>
<?php 
$textDate = Yii::t('frontend', 'Date');
$textCoin = Yii::t('frontend', 'Coin');
$textBalance = Yii::t('frontend', 'Balance');
$textWallet = Yii::t('frontend', 'Wallet');
$textEstimate = Yii::t('frontend', 'Estimate');
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

                if (Object.keys(additionalFunctions).length > 0) {
                    $.each(additionalFunctions, function (sindex) {
                        if (this.position == 0) {
                            var currentHeadFunction = this['head_f'];
                            //console.log(currentHeadFunction())
                            row.appendChild(currentHeadFunction());
                        }
                    });
                }
                $.each(currentStructure, function (sindex) {
                    //console.log(sindex)
                    if (presentInTableFields.includes(currentStructure[sindex].id)) {
                        var currentHeadFunction = getHeadFunctionByID(data['data']['structure']['data'][sindex].id);
                        row.appendChild(currentHeadFunction(sindex));
                    }
                });
                if (Object.keys(additionalFunctions).length > 0) {
                    $.each(additionalFunctions, function (sindex) {
                        if (this.position == 1) {
                            var currentHeadFunction = this['head_f'];
                            //console.log(currentHeadFunction())
                            row.appendChild(currentHeadFunction());
                        }
                    });
                }
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
                if (Object.keys(additionalFunctions).length > 0) {
                    $.each(additionalFunctions, function (sindex) {
                        if (this.position == 0) {
                            var currentDataFunction = this['data_f'];
                            //console.log(currentDataFunction(record, 0))
                            row.appendChild(currentDataFunction(record, 0));
                        }
                    });
                }
                $.each(this, function (f, fieldValue) {
                    if (presentInTableFields.includes(currentStructure[f].id)) {
                        var currentFunction = getDataFunctionByID(data['data']['structure']['data'][f].id);
                        //console.log(currentFunction(record, f))
                        row.appendChild(currentFunction(record, f));
                    }
                });
                if (Object.keys(additionalFunctions).length > 0) {
                    $.each(additionalFunctions, function (sindex) {
                        if (this.position == 1) {
                            var currentDataFunction = this['data_f'];
                            //console.log(currentDataFunction(record, 0))
                            row.appendChild(currentDataFunction(record, 0));
                        }
                    });
                }
                //var cell = row.insertCell(0);
                //cell.innerHTML = '<input type="number" placeholder="position" value="' + recordCount + '" min="0" max="' + recordCount + '" class="form-control"><button type="button" onclick="addValueToInnerObject(this)" id="' + this[0] + '" class="btn btn-icon btn-add"><i class="pe-7s-plus"></i></button><button type="button" onclick="addValueToInnerObject(this)" id="' + this[0] + '" class="btn btn-icon btn-add"><i class="pe-7s-pen"></i></button>';
            });
            //$("#values-list").html(editorsHTML)
            //alert(editorsHTML);
            //console.log(editorsHTML)
        });
    }

    var presentInTableFields = ['a3ba8d4a-246d-4e69-b546-944cbd31446f', 'ce2c74a8-831d-421d-8956-9c8ddfca9192',
        '5b296714-e069-457e-b606-3a40bea5b2f2', 'd8a9e95a-2fc4-474a-8cd7-6f2f3a7d54e5',
        '8edc7ce0-6e01-47b2-b797-a3fc89bc7499', '1db747bd-0828-4572-b6e5-33be5bc031e2'];
    var presentInTablesFunctions = [];
    var additionalFunctions = [];
    var sortMaps = {
        'ce2c74a8-831d-421d-8956-9c8ddfca9192': '<?=$textDate?>',
        'a3ba8d4a-246d-4e69-b546-944cbd31446f': 'ID',
        '5b296714-e069-457e-b606-3a40bea5b2f2': '<?=$textCoin?>',
        'd8a9e95a-2fc4-474a-8cd7-6f2f3a7d54e5': '<?=$textBalance?>',
        '1db747bd-0828-4572-b6e5-33be5bc031e2': '<?=$textWallet?>',
        '8edc7ce0-6e01-47b2-b797-a3fc89bc7499': '<?=$textEstimate?>'
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
        if ($('#nonzero').is(':checked')) {
            localQuery.filters[1].special.push(JSON.parse('{"map":"' + getIndexFromStructureByID('d8a9e95a-2fc4-474a-8cd7-6f2f3a7d54e5') + '","comp":"2","value":"0"}'));
        }
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
        console.log(localQuery);
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

    var baseAddHeadFunction = function () {
        var cell = document.createElement('th');
        return cell;
    }

    var baseAddDataFunction = function (row, index = -1) {
        if (index == -1) {
            return '';
        }
        var fieldHeader = currentStructure[index];
        if (typeof(fieldHeader) != "undefined") {
            var cell = document.createElement('td');//row.insertCell(index);
            return cell;
        }
    }

    var actionsAddHeadFunction = function () {
        var cell = document.createElement('th');
        cell.innerHTML =
            '<span><?=$textActions?></span>';
        return cell;
    }

    var actionsAddDataFunction = function (row, index = -1) {
        if (index == -1) {
            return '';
        }
        var fieldHeader = currentStructure[index];
        if (typeof(fieldHeader) != "undefined") {
            var cell = document.createElement('td');//row.insertCell(index);
            if (row[<?= $currencyIDMap ?>] != '00000000-430d-4a57-a7ec-ff125372ae09')
                cell.innerHTML = '<td>' +
                    '<a href="/profile/default/ddone?currency=' + row[<?= $currencyIDMap ?>] + '" class="btn btn-icon icon-only"><img src="/images/icons/dollar-down-dark.png"></a>' +
                    '<a href="/profile/default/wdone?currency=' + row[<?= $currencyIDMap ?>] + '" class="btn btn-icon icon-only"><img src="/images/icons/withdraw.png"></a>' +
                    '<a href="/profile/default/tdone?currency=' + row[<?= $currencyIDMap ?>] + '" class="btn btn-icon icon-only"><img src="/images/icons/arrows.png"></a>' +
                    '</td>';
            else
                cell.innerHTML = '<td>' +
                    '<a href="/profile/default/perfdone" class="btn btn-icon icon-only"><img src="/images/icons/dollar-down-dark.png"></a>' +
                    '<a href="/profile/default/wdone?currency=' + row[<?= $currencyIDMap ?>] + '" class="btn btn-icon icon-only"><img src="/images/icons/withdraw.png"></a>' +
                    '<a href="/profile/default/tdone?currency=' + row[<?= $currencyIDMap ?>] + '" class="btn btn-icon icon-only"><img src="/images/icons/arrows.png"></a>' +
                    '</td>';
                return cell;
        }
    }

    function setAdditionalFunction(position = 1, headFunction = null, dataFunction = null) {
        var currentObject = {};
        currentObject['position'] = position;
        if (headFunction == null) {
            currentObject['head_f'] = baseAddHeadFunction;
        } else {
            currentObject['head_f'] = headFunction;
        }
        if (dataFunction == null) {
            currentObject['data_f'] = baseAddDataFunction;
        } else {
            currentObject['data_f'] = dataFunction;
        }
        //var resultObject = {};
        //resultObject[Object.keys(additionalFunctions).length] = currentObject;
        additionalFunctions.push(currentObject);
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
            var currentCurrencyID = fieldValue[getIndexFromStructureByID('5b296714-e069-457e-b606-3a40bea5b2f2')];
            var cell = document.createElement('td');
            var result = 'undefined';
            var image = '00000000-0000-0000-0000-000000000000.png';
            for (var currencyIndex = 0; currencyIndex < marketCurrencies.length; currencyIndex++) {
                if (marketCurrencies[currencyIndex].id == currentCurrencyID) {
                    result = marketCurrencies[currencyIndex]['name'];
                    if (marketCurrencies[currencyIndex]['image'] != null)
                        image = marketCurrencies[currencyIndex]['image'];
                    break;
                }
            }

            var icon = getIcon('coins', image, 'small');     
            if(icon){      
                icon = '<i class="coin-img-name '+icon+'"></i>';
            } else {
                icon = '<img src="/cache/objects/<?= $currencyObjectID ?>/img/' + image + '" class="coin-img-name">';
            }

            image = image.replace(/\.jpg/i, '_small.jpg');                        
            cell.innerHTML = icon +
                ' <span>' + result + '</span>';
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
            var amount = (row[index] == null ? 0 : row[index]);
            cell.innerHTML = '<p>' + amount.toFixed(8) + '</p>';
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
    setFunctionsByID('5b296714-e069-457e-b606-3a40bea5b2f2', null, currencyDataFunction);
    setFunctionsByID('d8a9e95a-2fc4-474a-8cd7-6f2f3a7d54e5', baseSortingHeadFunction, moneyViewDataFunction);
    setFunctionsByID('8edc7ce0-6e01-47b2-b797-a3fc89bc7499', null, moneyViewDataFunction);
    setAdditionalFunction(1, actionsAddHeadFunction, actionsAddDataFunction);
    paginationUpdate('htransactiontable');
    autocomplete(document.getElementById("ht-auto-search"), marketsPairs);

    function checkBoxUpdate() {
        paginationUpdate('htransactiontable');
    }

</script>