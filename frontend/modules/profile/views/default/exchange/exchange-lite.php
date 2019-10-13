<?php

use common\modules\drole\models\registry\DynamicRoleModel;
use common\modules\drole\models\webtools\JSONRegistryFactory;
use common\modules\imageStorage\helpers\ImageStorageHelper;
use frontend\assets\AppAsset;

AppAsset::register($this);

/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 7/17/2018
 * Time: 4:04 PM
 */
$dynamicRoleArray = DynamicRoleModel::getAnonymousDynamicArray('af09ea17-d47c-452d-93de-2c89157b9d5b',
    'b56b99b6-2c6f-4103-849a-e914e8594869');

$marketName = \common\modules\drole\models\gate\CheckIncomingWords::checkRequestString(Yii::$app->request->get('market'));
$sql = "SELECT coinmarkets_data_use.* FROM 
coinmarkets_data_use join coinmarkets_record_own on coinmarkets_record_own.id = coinmarkets_data_use.id WHERE 
name = '$marketName' and coinmarkets_record_own.company_id = '" . $dynamicRoleArray['company_id'] . "' and 
coinmarkets_record_own.service_id = '" . $dynamicRoleArray['service_id'] . "' limit 1";
$marketResult = \Yii::$app->db->createCommand($sql)->queryOne();

if (!$marketResult || count($marketResult) < 1) {
    echo "not found market";
    die(402);
}
//$marketCurrencyies = explode('_', $marketName);
$sql = "SELECT coin_data_use.name, coin_data_use.symbol, coin_data_use.image FROM coin_data_use join coin_record_own on 
coin_data_use.id = coin_record_own.id where coin_data_use.id = '" . $marketResult['currentcurrencyid'] . "' and 
coin_record_own.company_id = '" . $dynamicRoleArray['company_id'] . "' and coin_record_own.service_id = '" . $dynamicRoleArray['service_id'] . "'";
$currentCurrencyDescription = \Yii::$app->db->createCommand($sql)->queryOne();
if (!$currentCurrencyDescription || count($currentCurrencyDescription) < 1) {
    echo "not found current description";
    die(403);
}

$sql = "SELECT coin_data_use.name, coin_data_use.symbol, coin_data_use.image FROM coin_data_use join coin_record_own on 
coin_data_use.id = coin_record_own.id where coin_data_use.id = '" . $marketResult['basecurrencyid'] . "' and 
coin_record_own.company_id = '" . $dynamicRoleArray['company_id'] . "' and coin_record_own.service_id = '" . $dynamicRoleArray['service_id'] . "'";
$baseCurrencyDescription = \Yii::$app->db->createCommand($sql)->queryOne();
if (!$baseCurrencyDescription || count($baseCurrencyDescription) < 1) {
    echo "not found base description";
    die(404);
}

$listBaseCurrencies = "select coin_data_use.* from coin_data_use join coin_record_own on coin_data_use.id = 
coin_record_own.id where coin_data_use.id in (select DISTINCT basecurrencyid from (SELECT basecurrencyid, date_create FROM 
coinmarkets_data_use where status = '200' order by date_create) as foo) and coin_record_own.company_id = '" .
    $dynamicRoleArray['company_id'] . "' and coin_record_own.service_id = '" . $dynamicRoleArray['service_id'] . "' order by date_create";
$baseCurrencyList = \Yii::$app->db->createCommand($listBaseCurrencies)->queryAll();
if (!$baseCurrencyList || count($baseCurrencyList) < 1) {
    echo "not found coin description";
    die(404);
}

$currentStructure = \common\modules\drole\models\gate\StructureOperationHandler::getFastStructureWithCheck(
    '5c1a5894-f6df-4c96-a84d-6679f3375bb7', $dynamicRoleArray['id']);

$json = JSONRegistryFactory::getRecordsListFromObjectAnonymous('af09ea17-d47c-452d-93de-2c89157b9d5b',
    'b56b99b6-2c6f-4103-849a-e914e8594869', true, '5c1a5894-f6df-4c96-a84d-6679f3375bb7');

?>

<script src="/js/jquery.min.js"></script>
<script src="/js/highstock.min.js"></script>
<script src="/js/exporting.js"></script>
<script src="/js/default-chart/sand-signika.js"></script>

<script src="/js/socket.io.js"></script>
<script src="/js/bignumber.js"></script>


<div class="d-none" id="marketid"><?= $marketResult['id'] ?></div>

<div class="content-wrapper" id="exchange">
    <div class="main-coin-trade-info">
        <div class="row">
            <div class="col">
                <div class="trading-coin-name">
                    <div class="d-inline-block align-middle"><img
                                src="<?php echo ImageStorageHelper::getWebPathFromObjectRecord(Yii::$app->ImageStorage, '5cb705ea-6c8c-4dae-a620-248545acab14', $currentCurrencyDescription['image']); ?>">
                    </div>
                    <div class="d-inline-block align-middle">
                        <h4><?= $currentCurrencyDescription['name'] ?></h4>
                        <p class="grey-text"><?= $currentCurrencyDescription['symbol'] ?>
                            / <?= $baseCurrencyDescription['symbol'] ?></p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="change-price-block">
                    <h4 class="<?= ($marketResult['changeprice'] > 0 ? 'green-text' : 'red-text') ?>"><?= number_format($marketResult['lastprice'], 8, '.', '') ?></h4>
                    <p class="grey-text">24h Change: <strong><?= $marketResult['changeprice'] ?></strong></p>
                </div>
            </div>
            <div class="col">
                <div class="volume-block">
                    <h4><?= number_format($marketResult['volume24h'], 8, '.', '') ?></h4>
                    <p class="grey-text">Volume :
                        <strong><?= number_format($marketResult['base24h'], 8, '.', '') ?> <?= $baseCurrencyDescription['symbol'] ?></strong>
                    </p>
                </div>
            </div>
            <div class="col">
                <div class="high-price-block">
                    <h4><?= number_format($marketResult['high24h'], 8, '.', '') ?></h4>
                    <p class="grey-text">High price</p>
                </div>
            </div>
            <div class="col">
                <div class="low-price-block">
                    <h4><?= number_format($marketResult['low24h'], 8, '.', '') ?></h4>
                    <p class="grey-text">Low price</p>
                </div>
            </div>
        </div>
    </div>

    <div class="exchange-workspace">
        <div class="row">
            <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <div id="chartdiv" style="height: 300px;"></div>
                        <div id="nodatadiv" class="text-center cls-hidden"
                             style="min-height: 200px; width: 100%; padding-top:40px;">
                            <h2>Not found any data.</h2>
                        </div>
                        <div class="row justify-content-center">
                            <a href="javascript:void(0)" id="c6M" data-time="14515200" data-size="100"
                               class="btn btn-sm candleget btn-default">6M</a>
                            <a href="javascript:void(0)" id="c3M" data-time="4838400" data-size="100"
                               class="btn btn-sm candleget btn-default">3M</a>
                            <a href="javascript:void(0)" id="c1M" data-time="2419200" data-size="100"
                               class="btn btn-sm candleget btn-default">1M</a>
                            <a href="javascript:void(0)" id="c2W" data-time="1209600" data-size="100"
                               class="btn btn-sm candleget btn-default">2W</a>
                            <a href="javascript:void(0)" id="c1W" data-time="604800" data-size="100"
                               class="btn btn-sm candleget btn-default">1W</a>
                            <a href="javascript:void(0)" id="c2D" data-time="172800" data-size="100"
                               class="btn btn-sm candleget btn-default">2D</a>
                            <a href="javascript:void(0)" id="c1D" data-time="86400" data-size="100"
                               class="btn btn-sm candleget btn-default">1D</a>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                        <div class="card">
                            <div class="card-header white">
                                <div class="d-inline-block align-middle"><h4 class="card-title"><span class="red-text">Sell</span>
                                        orders</h4></div>
                                <div class="d-inline-block align-middle text-right">
                                    <div class="total-orders-sum grey-text">
                                        <span id="sell_sum">0.0</span>
                                        <span> <?= $currentCurrencyDescription['symbol'] ?></span></div>
                                </div>
                            </div>
                            <div class="card-body has-scroll">
                                <div class="stok-height">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped"
                                               id="table-sell-orders">
                                            <thead>
                                            <tr>
                                                <th>Price <span
                                                            class="orange-text"><?= $baseCurrencyDescription['symbol'] ?></span>
                                                </th>
                                                <th>Amount <span
                                                            class="orange-text"><?= $currentCurrencyDescription['symbol'] ?></span>
                                                </th>
                                                <th>Total</th>
                                            </tr>
                                            </thead>
                                            <tbody id="sellOrders">


                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                        <div class="card">
                            <div class="card-header white">
                                <div class="d-inline-block align-middle"><h4 class="card-title"><span
                                                class="green-text">Buy</span> orders</h4></div>
                                <div class="d-inline-block align-middle text-right">
                                    <div class="total-orders-sum grey-text">
                                        <span id="buy_sum">0.0</span>
                                        <span><?= $baseCurrencyDescription['symbol'] ?></span></div>
                                </div>
                            </div>
                            <div class="card-body has-scroll">
                                <div class="stok-height">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped"
                                               id="table-buy-orders">
                                            <thead>
                                            <tr>
                                                <th>Price <span
                                                            class="orange-text"><?= $baseCurrencyDescription['symbol'] ?></span>
                                                </th>
                                                <th>Amount <span
                                                            class="orange-text"><?= $currentCurrencyDescription['symbol'] ?></span>
                                                </th>
                                                <th>Total</th>
                                            </tr>
                                            </thead>
                                            <tbody id="buyOrders">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">

                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                <div class="card">
                    <div class="card-header white">
                        <div class="row">
                            <div class="col"><h4 class="card-title">Markets</h4></div>
                            <div class="col">
                                <button type="button" class="btn btn-transparent btn-show-favorite btn-sm-favorite"><i
                                            class="fa fa-star"></i> My favorite
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="market-header">
                            <p class="d-block grey-text mb-3px">
                                <small>Hold Shift and scroll</small>
                            </p>
                            <div class="h-scroll">
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
                            <div class="clearfix"></div>

                            <div class="row">
                                <div class="col-12">
                                    <label class="input-box"><input id="quick-search" type="text" class="form-control"
                                                                    placeholder="Search...." value=""></label>
                                </div>
                            </div>

                            <div class="relative">
                                <div class="market-table">
                                    <div class="">
                                        <table id="marketstable"
                                               class="table table-bordered table-hover table-course table-striped">
                                            <thead>
                                            <tr>
                                                <th class="marketsort">Coin <input type="hidden" name="" id=""> <i
                                                            class="fa fa-exchange"></i></th>
                                                <th class="marketsort">Price <input type="hidden" name="" id=""> <i
                                                            class="fa fa-exchange"></i></th>
                                                <th class="marketsort">Volume <input type="hidden" name="" id=""> <i
                                                            class="fa fa-sort-amount-desc"></i></th>
                                                <th class="marketsort">Change <input type="hidden" name="" id=""> <i
                                                            class="fa fa-exchange"></i></th>
                                            </tr>
                                            </thead>
                                            <tbody id="coinmarkets-table">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-min-history">
                    <div class="card-header white">
                        <div class="row">
                            <div class="col-auto"><h4 class="card-title">T.History</h4></div>
                            <div class="col">
                                <button type="button"
                                        class="btn d-inline-block float-right align-middle btn-transparent grey-text active">
                                    Market
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <div class="">
                                <table class="table table-bordered table-hover" id="market-history-table">
                                    <thead>
                                    <tr>
                                        <th>Price <span
                                                    class="orange-text"><?= $baseCurrencyDescription['symbol'] ?></span>
                                        </th>
                                        <th>Amount</th>
                                        <th>Volume</th>
                                        <th>Timestamp</th>
                                    </tr>
                                    </thead>
                                    <tbody id="marketHistory">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="basicmodal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type='text/javascript'>
    var socket;
    var tradechart;

    $(document).ready(function () {
        if (socket == null) {
            socket = io.connect('//' + document.domain + ':' + 3334, {
                pingInterval: 1000,
                'sync disconnect on unload': true
            });
        }
        var do_disconnect = setTimeout(do_disconnect_now, 4000000);

        function do_disconnect_now() {
            socket.disconnect();
        }

        var ping_pong_times = [];
        var start_time;
        var do_ping = setTimeout(do_ping_now, 500);

        function do_ping_now() {
            start_time = (new Date).getTime();
            socket.emit('my_ping', '{}');
            //socket.emit('my_ping', '{"log":"<?= \Yii::$app->session->id ?>", "market":"<?= $marketName ?>", "company":"<?= $dynamicRoleArray['company_id'] ?>", "service":"<?= $dynamicRoleArray['service_id'] ?>"}');
        }

        var do_ping1 = setTimeout(do_ping_now, 1000);

        window.setInterval(function () {
            start_time = (new Date).getTime();
            socket.emit('my_ping', '{}');
            //socket.emit('my_ping', '{"log":"<?= \Yii::$app->session->id ?>", "market":"<?= $marketName ?>", "company":"<?= $dynamicRoleArray['company_id'] ?>", "service":"<?= $dynamicRoleArray['service_id'] ?>"}');
        }, 10000);

        socket.on('my_pong', function () {
            var latency = (new Date).getTime() - start_time;
            ping_pong_times.push(latency);
            ping_pong_times = ping_pong_times.slice(-30); // keep last 30 samples
            var sum = 0;
            for (var i = 0; i < ping_pong_times.length; i++)
                sum += ping_pong_times[i];
            $('#ping-pong').text(Math.round(10 * sum / ping_pong_times.length) / 10 + " ms");
        });
        $(window).on('beforeunload', function () {
            socket.disconnect();
        });

        $('.btn-change-market.active').click();

        var groupingUnits = [[
            'day',                         // unit name
            [1]                             // allowed multiples
        ], [
            'month',
            [1, 2, 3, 4, 6]
        ]];

        (function (H) {
            // Pass error messages
            H.Axis.prototype.allowNegativeLog = true;

            // Override conversions
            H.Axis.prototype.log2lin = function (num) {
                var isNegative = num < 0,
                    adjustedNum = Math.abs(num),
                    result;
                if (adjustedNum < 10) {
                    adjustedNum += (10 - adjustedNum) / 10;
                }
                result = Math.log(adjustedNum) / Math.LN10;
                return isNegative ? 0 : result;
            };
            H.Axis.prototype.lin2log = function (num) {
                var isNegative = num < 0,
                    absNum = Math.abs(num),
                    result = Math.pow(10, absNum);
                if (result < 10) {
                    result = (10 * (result - 1)) / (10 - 1);
                }
                return (num < 0) ? 0 : result;
            };
        }(Highcharts));

        tradechart = Highcharts.stockChart('chartdiv', {

            exporting: {enabled: false},
            rangeSelector: {enabled: false},
            navigator: {
                type: 'logarithmic',
                format: "{point.y:.8f}",
                baseSeries: 1,
                height: 40,
                margin: 25,
                maskInside: !0,
                handles: {
                    backgroundColor: "#f2f2f2",
                    borderColor: "#999999"
                },
                outlineColor: "#cccccc",
                outlineWidth: 1
            },
            title: {
                text: ''
            },
            yAxis: [{
                labels: {
                    formatter: function () {
                        return this.value.toFixed(8);
                    },
                    align: 'right',
                    x: -3
                },
                title: {
                    text: ''
                },
                lineWidth: 1,
                type: 'logarithmic'
            }, {
                labels: {
                    enabled: false,
                    align: 'right',
                    x: -3
                },
                title: {
                    text: ''
                },
                lineWidth: 1,
                resize: {
                    enabled: true
                },
                type: 'logarithmic'
            }],
            tooltip: {
                useHTML: true,
                formatter: function () {
                    var index = 1;
                    if (this.points.length == 1) {
                        index = 0;
                    }
                    var volume = 0;
                    if (index == 1) {
                        volume = this.points[0].y;
                    }
                    /*console.log(tradechart.series)
                    console.log('[' + index + '] : ' + this.points[0].x)
                    console.log(this.points)*/
                    return ' <div class="chart-tooltip"> ' +
                        '<div class="tooltip-date-row">' + new Date(this.points[0].x).toLocaleString('en-GB', {timeZone: 'UTC'}) + '</div>' +
                        '<div class="tooltip-row">' + '<span>Open: </span>' + '<strong>' + this.points[index].point.open.toFixed(8) + '</strong></div>' +
                        '<div class="tooltip-row">' + '<span>Low: </span>' + '<strong>' + this.points[index].point.low.toFixed(8) + '</strong></div>' +
                        '<div class="tooltip-row">' + '<span>High: </span>' + '<strong>' + this.points[index].point.high.toFixed(8) + '</strong></div>' +
                        '<div class="tooltip-row">' + '<span>Close: </span>' + '<strong>' + this.points[index].point.close.toFixed(8) + '</strong></div>' +
                        '<div class="tooltip-row">' + '<span>Volume: </span>' + '<strong>' + volume.toFixed(8) + '</strong></div>' +
                        '</div>';
                },
                shared: false
            },
            series: [{
                name: 'Volume',
                type: 'column',
                yAxis: 1
            }, {
                name: 'Price',
                type: 'candlestick'
            }]
        });

        updateChart(lastIntervalType);
        setInterval(function () {
            updateChart(lastIntervalType);
        }, 5000);
    });
</script>
<script type="text/javascript" charset="utf-8">

    $(document).ready(function () {

        function seconds_since_epoch() {
            return Math.floor(Date.now() / 1000)
        }

        BigNumber.config(8, 5);
        BigNumber.config({ERRORS: false})

        socket.on('connect', function () {
            socket.emit('subscribe', '{"log":"<?= \Yii::$app->session->id ?>", "market":"<?= $marketName ?>", "company":"<?= $dynamicRoleArray['company_id'] ?>", "service":"<?= $dynamicRoleArray['service_id'] ?>"}');
        });

        socket.on('trade_history', function (data) {
            if (activeHistoryOrdersBtn.attr('id') == 'yours-orders') {
                return;
            }
            var result = JSON.parse(data).data
            if (result.status == "ok") {
                $("#marketHistory").html("");
                if (Object.keys(result.history).length > 0) {
                    for (var key in result.history) {
                        var row = result.history[key];
                        if (isNaN(parseFloat(row.basevolume))) {
                            btcvalue = "Not available";
                        } else {
                            btcvalue = row.basevolume;
                        }
                        if (row.tradetype == "0") {
                            classColor = "sell-text";
                        } else {
                            classColor = "buy-text";
                        }
                        var adderClass = 't-sell';
                        if (row.tradetype == "0") {
                            tradeType = "SELL";
                        } else {
                            tradeType = "BUY";
                            adderClass = 't-buy';
                        }

                        html = "";
                        html = html + "<tr class='ordertext " + adderClass + "'>";
                        html = html + "<td class='text-right amount pricerow'><span class='" + classColor + "'>";
                        html = html + row.price.toFixed(8);
                        html = html + "</span></td>";

                        /*html = html + "<td class='text-center price pricerow'><span class='" + classColor + "'>";
                        html = html + tradeType;
                        html = html + "</span></td>";*/

                        html = html + "<td class='text-right total pricerow'><span class='" + classColor + "'>";
                        html = html + row.volume.toFixed(8);
                        html = html + "</span></td>";
                        html = html + "<td class='text-right total pricerow'><span class='" + classColor + "'>";
                        html = html + row.basevolume;
                        html = html + "</span></td>";
                        html = html + "<td class='text-center price pricerow'><span class=''>";
                        html = html + row.ticker;
                        html = html + "</span></td>";
                        /*
                         html = html + "<td class='text-right total pricerow'><span class='" + classColor + "'>";
                         html = html + btcvalue;*/
                        //html = html + "</span></td><td></td>";
                        html = html + "</tr>";
                        $("#marketHistory").append(html);
                    }
                } else {
                    $("#marketHistory").append("<tr><td colspan='5'>No market history found, make a trade to fix this.</td></tr>");
                }
            }
        });


        socket.on('sell_orders', function (data) {
            result = JSON.parse(data).data
            if (result.status == "ok") {
                if (result.orders.length > 0) {
                    var selfHash = "<?= md5(\Yii::$app->user->id) ?>";
                    //start update result orders
                    var sellCount = 0;
                    var sellBaseAmount = 0;
                    var sellCurrAmount = 0;
                    var arrayResult = [];
                    var sellResult = '';
                    //console.log(result.orders.length);
                    for (var i = 0; i < result.orders.length; i++) {
                        var countRepeats = checkCountElementsWithPrice(result.orders, result.orders[i]['price']);
                        //console.log('found ' + countRepeats + ' records for price: ' + rows[i]['price'].toFixed(8));
                        var amountRep = 0;
                        var priceRep = result.orders[i]['price'];
                        var basevalueRep = 0;
                        var hashesArray = [];
                        for (var repeat = i; repeat < (i + countRepeats); repeat++) {
                            amountRep += result.orders[repeat]['amount'];
                            basevalueRep += result.orders[repeat]['basevalue'];
                            hashesArray.push(result.orders[repeat]['hashid']);
                            //hashIndex++;
                        }
                        arrayResult.push({
                            amount: amountRep,
                            price: priceRep,
                            baseamount: basevalueRep,
                            hashes: hashesArray
                        })
                        i += countRepeats - 1;
                    }
                    for (var i = 0; i < arrayResult.length; i++) {
                        sellCount++;
                        var numberValAmount = parseFloat(arrayResult[i]['baseamount'].toFixed(8));
                        var numberValCurrent = parseFloat(arrayResult[i]['amount'].toFixed(8));
                        sellBaseAmount += numberValAmount;
                        sellCurrAmount += numberValCurrent;
                        var hashesResult = '';
                        for (var j = 0; j < arrayResult[i]['hashes'].length; j++) {
                            hashesResult += '"' + arrayResult[i]['hashes'][j] + '", ';
                        }
                        hashesResult = '[' + hashesResult.substring(0, hashesResult.length - 2) + ']';
                        sellResult += '{' +
                            '"hashid": ' + hashesResult + ', ' +
                            '"rowtotalbase": "' + sellBaseAmount.toFixed(8) + '", ' +
                            '"amount": "' + arrayResult[i]['amount'].toFixed(8) + '", ' +
                            '"price": "' + arrayResult[i]['price'].toFixed(8) + '", ' +
                            '"rowtotal": "' + sellCurrAmount.toFixed(8) + '", ' +
                            '"basevalue": "' + arrayResult[i]['baseamount'] + '",' +
                            '"total": "' + arrayResult[i]['baseamount'].toFixed(8) + '"' +
                            '}, ';
                        //console.log(sellResult);
                    }
                    if (sellCount > 0) {
                        sellResult = "[" + sellResult.substring(0, sellResult.length - 2) + "]";
                    }
                    //console.log(JSON.parse(sellResult))
                    var sellOrdersPars = JSON.parse(sellResult);
                    $("#sellOrders").html("");
                    var sellSum = 0;
                    for (var i = 0; i < sellOrdersPars.length; i++) {
                        //if (!sellOrdersPars.hasOwnProperty(key)) continue;
                        var row = sellOrdersPars[i];
                        if (isNaN(parseFloat(row.total))) {
                            total = "Not available";
                        } else {
                            total = row.total;
                        }
                        if (isNaN(parseFloat(row.basevalue)) || parseFloat(row.basevalue) == 0) {
                            btcvalue = "Not available";
                        } else {
                            btcvalue = row.basevalue;
                        }
                        var selfRowClass = "";
                        if (row.hashid.indexOf(selfHash) != -1) {
                            selfRowClass = " my-order-row";
                        }
                        sellSum = parseFloat(row.amount) + sellSum;
                        html = "";
                        html = html + "<tr class='orderrow ordertext" + selfRowClass + "'>";
                        html = html + "<td class='text-right pricerow-2 price'><span class='maxAmount value'>";
                        html = html + row.price;
                        html = html + "</span></td>";
                        html = html + "<td class='text-right pricerow-2 amount'><span class='maxAmount value'>";
                        html = html + row.amount;
                        /*html = html + "</span></td>";
                        html = html + "<td class='text-right pricerow-2 total'><span class='maxAmount value'>";
                        html = html + row.total;*/
                        html = html + "</span></td>";
                        html = html + "<td class='text-right pricerow-2 total'><input type='hidden' class='rowtotal' value='" + row.rowtotal + "'><input type='hidden' class='rowtotalbase' value='" + row.rowtotalbase + "'>";
                        html = html + row.rowtotalbase;
                        //html = html + "</td><td></td>";
                        html = html + "</tr>";
                        $("#sellOrders").append(html);
                    }
                    $("#sell_sum").text(sellSum.toFixed(8));
                } else {
                    $("#sellOrders").html("");
                    $("#sellOrders").append("<tr><td colspan='3'>No open orders found, will refresh shortly.</td></tr>");
                    $("#sell_sum").text("0.00000000");
                }
            }
        });

        socket.on('buy_orders', function (data) {
            result = JSON.parse(data).data
            //console.log(result)
            if (result.status == "ok") {
                var selfHash = "<?= md5(\Yii::$app->user->id) ?>";
                if (result.orders.length > 0) {
                    //start update result orders
                    var buyCount = 0;
                    var buyBaseAmount = 0;
                    var buyCurrAmount = 0;
                    var arrayResult = [];
                    var buyResult = '';
                    //console.log(result.orders.length);
                    for (var i = 0; i < result.orders.length; i++) {
                        var countRepeats = checkCountElementsWithPrice(result.orders, result.orders[i]['price']);
                        //console.log('found ' + countRepeats + ' records for price: ' + rows[i]['price'].toFixed(8));
                        var amountRep = 0;
                        var priceRep = result.orders[i]['price'];
                        var basevalueRep = 0;
                        var hashesArray = [];
                        for (var repeat = i; repeat < (i + countRepeats); repeat++) {
                            amountRep += result.orders[repeat]['amount'];
                            basevalueRep += result.orders[repeat]['basevalue'];
                            hashesArray.push(result.orders[repeat]['hashid']);
                            //hashIndex++;
                        }
                        arrayResult.push({
                            amount: amountRep,
                            price: priceRep,
                            baseamount: basevalueRep,
                            hashes: hashesArray
                        })
                        i += countRepeats - 1;
                    }
                    for (var i = 0; i < arrayResult.length; i++) {
                        buyCount++;
                        var numberValAmount = parseFloat(arrayResult[i]['baseamount'].toFixed(8));
                        var numberValCurrent = parseFloat(arrayResult[i]['amount'].toFixed(8));
                        buyBaseAmount += numberValAmount;
                        buyCurrAmount += numberValCurrent;
                        var hashesResult = '';
                        for (var j = 0; j < arrayResult[i]['hashes'].length; j++) {
                            hashesResult += '"' + arrayResult[i]['hashes'][j] + '", ';
                        }
                        hashesResult = '[' + hashesResult.substring(0, hashesResult.length - 2) + ']';
                        buyResult += '{' +
                            '"hashid": ' + hashesResult + ', ' +
                            '"rowtotalbase": "' + buyBaseAmount.toFixed(8) + '", ' +
                            '"amount": "' + arrayResult[i]['amount'].toFixed(8) + '", ' +
                            '"price": "' + arrayResult[i]['price'].toFixed(8) + '", ' +
                            '"rowtotal": "' + buyCurrAmount.toFixed(8) + '", ' +
                            '"basevalue": "' + arrayResult[i]['baseamount'] + '",' +
                            '"total": "' + arrayResult[i]['baseamount'].toFixed(8) + '"' +
                            '}, ';
                        //console.log(buyResult);
                    }
                    if (buyCount > 0) {
                        buyResult = "[" + buyResult.substring(0, buyResult.length - 2) + "]";
                    }
                    //console.log(JSON.parse(buyResult))
                    var buyOrdersPars = JSON.parse(buyResult);
                    $("#buyOrders").html("");
                    var buySum = 0;
                    for (var i = 0; i < buyOrdersPars.length; i++) {
                        //if (!buyOrdersPars.hasOwnProperty(key)) continue;
                        var row = buyOrdersPars[i];
                        if (isNaN(parseFloat(row.total))) {
                            total = "Not available";
                        } else {
                            total = row.total;
                        }
                        if (isNaN(parseFloat(row.basevalue)) || parseFloat(row.basevalue) == 0) {
                            btcvalue = "Not available";
                        } else {
                            btcvalue = row.basevalue;
                        }
                        var selfRowClass = "";
                        if (row.hashid.indexOf(selfHash) != -1) {
                            selfRowClass = " my-order-row";
                        }
                        buySum += parseFloat(row.basevalue);
                        html = "";
                        html = html + "<tr class='orderrow ordertext" + selfRowClass + "'>";
                        html = html + "<td class='text-right pricerow-2 price'><span class='maxAmount value'>";
                        html = html + row.price;
                        html = html + "</span></td>";
                        html = html + "<td class='text-right pricerow-2 amount'><span class='maxAmount value'>";
                        html = html + row.amount;
                        /*html = html + "</span></td>";
                        html = html + "<td class='text-right pricerow-2 total'><span class='maxAmount value'>";
                        html = html + row.total;*/
                        html = html + "</span></td>";
                        html = html + "<td class='text-right pricerow-2 total'><input type='hidden' class='rowtotal' value='" + row.rowtotal + "'><input type='hidden' class='rowtotalbase' value='" + row.rowtotalbase + "'>";
                        html = html + row.rowtotalbase;
                        //html = html + "</td><td></td>";
                        html = html + "</tr>";
                        $("#buyOrders").append(html);
                    }
                    $("#buy_sum").text(buySum.toFixed(8));
                } else {
                    $("#buyOrders").html("");
                    $("#buyOrders").append("<tr><td colspan='3'>No open orders found, will refresh shortly.</td></tr>");
                    $("#buy_sum").text("0.00000000");
                }
            }
        });

        socket.on('operation_result', function (data) {
            result = data;//JSON.parse(data);
            var modal = $("#myModal");
            modal.find('.modal-header').html("<h3>Trade details</h3>");
            html = "";
            if (result.result == "200") {
                if (result.type == 0) {
                    if (Object.keys(result.tradeinfo).length > 0) {
                        for (var key in result.tradeinfo) {
                            if (!result.tradeinfo.hasOwnProperty(key)) continue;
                            var row = result.tradeinfo[key];
                            html = html + "<h5 class='bold'>#" + key + " | Bought " + row.toamount_tot + " " + row.tocurrency + " with " + row.fromamount + " " + row.fromcurrency + "</h5>";
                            html = html + "<p>&nbsp;&nbsp;<strong>Price:</strong> " + row.price + " <strong>Fee:</strong> " + row.fee + " " + row.tocurrency + "<br>";
                            html = html + "&nbsp;&nbsp;<strong>Net Total:</strong> " + row.toamount + " " + row.tocurrency + "</p>";
                            if (row.myownorder == true) {
                                html = html + "<span class='text-danger bold'>WARNING! You just traded against your own order, this will not create a ticker and count to the volume. However the trade was still accounted in your trade history.</span>";
                            }
                            html = html + "<hr>";
                        }
                    }
                    if (result.message.created == "yes") {
                        html = html + "<h5 class='bold'>Created Buy order #" + result.message.orderid + "</h5>"
                        html = html + "<p><strong>Buying " + result.message.toamount_tot + " " + result.message.tocurrency + " with " + result.message.fromamount + " " + result.message.fromcurrency + "</strong><br>";
                        html = html + "&nbsp;&nbsp;<strong>Price:</strong> " + result.message.price + " <strong>Fee:</strong> " + result.message.fee + " " + result.message.tocurrency + "</p>";
                        html = html + "&nbsp;&nbsp;<strong>Net Total:</strong> " + result.message.toamount + " " + result.message.tocurrency + "</p>";
                    }
                    else {
                        html = html + "<h5 class='bold'>Order was filled.</h5>"
                    }
                    modal.find('.modal-body').html(html);
                    $("#btnBuy").prop("disabled", false);
                    $("#btnBuy").html("BUY")
                } else if (result.type == 1) {
                    if (Object.keys(result.tradeinfo).length > 0) {
                        for (var key in result.tradeinfo) {
                            if (!result.tradeinfo.hasOwnProperty(key)) continue;
                            var row = result.tradeinfo[key];
                            html = html + "<h5 class='bold'>#" + key + " | Sold " + row.toamount_tot + " " + row.tocurrency + " with " + row.fromamount + " " + row.fromcurrency + "</h5>";
                            html = html + "<p>&nbsp;&nbsp;<strong>Price:</strong> " + row.price + " <strong>Fee:</strong> " + row.fee + " " + row.fromcurrency + "<br>";
                            html = html + "&nbsp;&nbsp;<strong>Net Total:</strong> " + row.toamount + " " + row.fromcurrency + "</p>";
                            if (row.myownorder == true) {
                                html = html + "<span class='text-danger bold'>WARNING! You just traded against your own order, this will not create a ticker and count to the volume. However the trade was still accounted in your trade history.</span>";
                            }
                            html = html + "<hr>";
                        }
                    }
                    if (result.message.created == "yes") {
                        html = html + "<h5 class='bold'>Created Sell order #" + result.message.orderid + "</h5>";
                        html = html + "<p><strong>Selling " + result.message.toamount_tot + " " + result.message.tocurrency + " with " + result.message.fromamount + " " + result.message.fromcurrency + "</strong><br>";
                        html = html + "&nbsp;&nbsp;<strong>Price:</strong> " + result.message.price + " <strong>Fee:</strong> " + result.message.fee + " " + result.message.fromcurrency + "</p>";
                        html = html + "&nbsp;&nbsp;<strong>Net Total:</strong> " + result.message.toamount + " " + result.message.fromcurrency + "</p>";
                    }
                    else {
                        html = html + "<h5 class='bold'>Order was filled.</h5>"
                    }
                    modal.find('.modal-body').html(html);
                    $("#btnSell").prop("disabled", false);
                    $("#btnSell").html("SELL")
                } else if (result.type == 2) {
                    modal.find('.modal-body').html(result.message);
                }
            } else {
                html = html + "<p>" + result.message + "</p>";
                modal.find('.modal-body').html(html);
                $("#btnBuy").prop("disabled", false);
                $("#btnBuy").html("BUY")
                $("#btnSell").prop("disabled", false);
                $("#btnSell").html("SELL")
            }
            modal.modal('show');
            setTimeout(function () {
                modal.modal('hide')
            }, 8000);
        })

        $(document).on('click', '.orderrow', function () {
            d = BigNumber
            var amount = new d($(this).children(".total").children(".rowtotal").val());
            var price = new d($(this).children(".price").children(".value").html());
            var total = new d($(this).children(".total").children(".rowtotalbase").val());

            var what = $(this).parent().attr("id");
            $("#buyPrice").val(price.toFixed(8));
            $("#sellPrice").val(price.toFixed(8));
            if (what == "sellOrders") {
                //var fee = <  market settings >;
                var myamount = new d($("#buyCurrencyAmount").html());
                if (myamount < amount) {
                    amount = myamount;
                }
                $("#buyAmount").val(amount.toFixed(8));
                $("#buyTotal").val((amount * price).toFixed(8));
                $("#buyFee").val((amount * price * 0.002).toFixed(8));

            }
            if (what == "buyOrders") {
                $("#sellAmount").val(amount.toFixed(8));
            }
            $("#sellAmount").blur();
        })
        ;

        $('body').on('hidden.bs.modal', '.modal', function () {
            $(this).children(".modal-header").html("");
            $(this).children(".modal-body").html("");
        })
        ;

        $(document).on('keyup', '#sellAmount', function () {
            calcBox("sell", "amount", 0);
        });
        $(document).on('keyup', '#sellPrice', function () {
            calcBox("sell", "price", 0);
        });
        $(document).on('keyup', '#sellTotal', function () {
            calcBox("sell", "total", 1);
        });
        $(document).on('keyup', '#buyAmount', function () {
            calcBox("buy", "amount", 0);
        });
        $(document).on('keyup', '#buyPrice', function () {
            calcBox("buy", "price", 0);
        });
        $(document).on('keyup', '#buyTotal', function () {
            calcBox("buy", "total", 1);
        });


        $(document).on('blur', '#sellAmount', function () {
            calcBox("sell", "all", 0);
        });
        $(document).on('blur', '#sellPrice', function () {
            calcBox("sell", "all", 0);
        });
        $(document).on('blur', '#sellTotal', function () {
            calcBox("sell", "all", 1);
        });
        $(document).on('blur', '#buyAmount', function () {
            calcBox("buy", "all", 0);
        });
        $(document).on('blur', '#buyPrice', function () {
            calcBox("buy", "all", 0);
        });
        $(document).on('blur', '#buyTotal', function () {
            calcBox("buy", "all", 1);
        });

        function checkCountElementsWithPrice(array, price) {
            var check = 0;
            var result = 0;
            for (var i = 0; i < array.length; i++) {
                if (array[i]['price'] === price) {
                    check = 1;
                    result++;
                } else if (check === 1) {
                    return result;
                }
            }
            return result;
        }

        function calcBox(action, item, what) {
            d = BigNumber
            var error = false;
            var tohigh = false;
            var maxamount = new d("0");
            var maxtotal = new d("0");
            var fee = new d("0");
            var nettotal = new d("0");
            var myamount = new d($("#" + action + "CurrencyAmount").html());
            var amount = new d($("#" + action + "Amount").val());

            var price = new d($("#" + action + "Price").val());
            var total = new d($("#" + action + "Total").val());
//see calcBox("buy", "all", 1);
            if ((!amount.isNaN() && !price.isNaN() && !total.isNaN()) || (!amount.isZero() && !price.isZero() && !total.isZero()) || (!amount.isNeg() && !price.isNeg() && !total.isNeg())) {
                if (action == "buy") {
                    maxamount = myamount.div(price);
                    maxtotal = myamount;
                    if (what == 1) {
                        if (total.gt(maxtotal)) {
                            tohigh = true;
                            total = d(maxtotal);
                        }
                        amount = total.div(price);
                    } else {
                        if (amount.gt(maxamount)) {
                            amount = maxamount;
                        }
                        total = amount.times(price);
                    }
                    fee = total.times(0.002);
                    nettotal = total.sub(fee);
                }
                else if (action == "sell") {
                    maxamount = myamount;
                    maxtotal = myamount.times(price);
                    if (what == 0) {
                        if (amount.gt(maxamount)) {
                            tohigh = true;
                            amount = maxamount;
                        }
                        total = amount.times(price);
                    } else {
                        if (total.gt(maxtotal)) {
                            tohigh = true;
                            total = maxtotal;
                        }
                        amount = total.div(price);
                    }
                    fee = total.times(0.002);
                    nettotal = total.sub(fee);
                }
            }
            else {
                if (amount.isNaN()) {
                    $("#" + action + "Amount").val("0.00000000");
                    calcBox(action, "all", what);
                    return;
                }
                if (price.isNaN()) {
                    $("#" + action + "Price").val("0.00000000");
                    calcBox(action, "all", what);
                    return;
                }
                if (total.isNaN()) {
                    $("#" + action + "Total").val("0.00000000");
                    calcBox(action, "all", what);
                    return;
                }
            }
            if (item == "all") {
                $("#" + action + "Amount").val(amount.toFixed(8));
                $("#" + action + "Price").val(price.toFixed(8));
                $("#" + action + "Total").val(total.toFixed(8));
            }
            else if (item == "amount") {
                $("#" + action + "Total").val(total.toFixed(8));
                $("#" + action + "Price").val(price.toFixed(8));
            }
            else if (item == "price") {
                $("#" + action + "Amount").val(amount.toFixed(8));
                $("#" + action + "Total").val(total.toFixed(8));
            }
            else if (item == "total") {
                $("#" + action + "Amount").val(amount.toFixed(8));
                $("#" + action + "Price").val(price.toFixed(8));
            }
            $("#" + action + "Fee").html(fee.toFixed(8));
            $("#" + action + "NetTotal").html(nettotal.toFixed(8));
        }

        $(document).on('click', '#btnBuy', function () {
            d = BigNumber
            calcBox("buy", "all", 1)
            error = false;
            var modal = $("#myModal");
            $("#btnBuy").html("Please wait...");
            $("#btnBuy").prop("disabled", true);
            var fee = new d($("#buyFee").html());
            var amount = new d($("#buyAmount").val());
            var price = new d($("#buyPrice").val());
            var totalValue = new d($("#buyTotal").val());

            if (fee.isNaN() || fee.lt("0.00000020")) {
                fee = false;
                error = true;
            }
            if (amount.isNaN() || amount.lte("0")) {
                amount = false;
                error = true;
            }
            if (price.isNaN() || price.lte("0")) {
                price = false;
                error = true;
            }
            if (totalValue.isNaN() || totalValue.lte("0")) {
                price = false;
                error = true;
            }

            if (!error) {
                //var modal = $("#myModal");
                values = {
                    'type': "0",
                    'basecurrencyid': "<?= $marketResult['basecurrencyid'] ?>",
                    'currentcurrencyid': "<?= $marketResult['currentcurrencyid'] ?>",
                    'amount': amount.toFixed(8),
                    'price': price.toFixed(8)
                }
                socket.emit('order_operation', values);

            }
            else {
                modal.find('.modal-header').html("<h3 class='text-danger'>Error</h3>");
                html = ""
                if (fee == false) {
                    html = html + "<div class='text-center'><p>Fee must add up to 0.030.</p></center>";
                }
                if (amount == false) {
                    html = html + "<div class='text-center'><p>Amount must be bigger than zero (0).</p></center>";
                }
                if (price == false) {
                    html = html + "<div class='text-center'><p>Price cannot be zero (0).</p></center>";
                }
                modal.find('.modal-body').html(html);
                modal.modal('show');
                $("#btnBuy").prop("disabled", false);
                $("#btnBuy").html(buttontext)
            }
        })
        ;

        $(document).on('click', '#btnSell', function () {
            d = BigNumber
            calcBox("sell", "all", 0);
            error = false;
            var modal = $("#myModal");
            $("#btnSell").html("Please wait...");
            $("#btnSell").prop("disabled", true);
            var fee = new d($("#sellFee").html());
            var amount = new d($("#sellAmount").val());
            var price = new d($("#sellPrice").val());

            if (fee.isNaN() || fee.lt("0.00000020")) {
                fee = false;
                error = true;
            }
            if (amount.isNaN() || amount.lte("0")) {
                amount = false;
                error = true;
            }
            if (price.isNaN() || price.lte("0")) {
                price = false;
                error = true;
            }

            if (!error) {
                values = {
                    'type': "1",
                    'basecurrencyid': "<?= $marketResult['basecurrencyid'] ?>",
                    'currentcurrencyid': "<?= $marketResult['currentcurrencyid'] ?>",
                    'amount': amount.toFixed(8),
                    'price': price.toFixed(8)
                }
                socket.emit('order_operation', values);

            }
            else {
                modal.find('.modal-header').html("<h3 class='text-danger'>Error</h3>");
                html = ""
                if (fee == false) {
                    html = html + "<div class='text-center'><p>Fee must add up to 0.00000020 .</p></center>";
                }
                if (amount == false) {
                    html = html + "<div class='text-center'><p>Amount must be bigger than zero (0).</p></center>";
                }
                if (price == false) {
                    html = html + "<div class='text-center'><p>Price cannot be zero (0).</p></center>";
                }
                modal.find('.modal-body').html(html);
                modal.modal('show');
                $("#btnSell").prop("disabled", false);
                $("#btnSell").html(buttontext)
            }
        })
        ;

        socket.on('cancel_order', function (data) {
            console.log("Refresh orders...")
        })
        ;

        $(document).on('click', '.cancelorder', function () {
            var parent = $(this).parent();
            var orderid = $(this).attr('data-orderid');
            parent.html("Canceling...");

            values = {
                'type': "2",
                'orderid': orderid
            }
            socket.emit('order_operation', values);
            parent.html("Removing...");
        });

        socket.on('my_orders', function (data) {
            var classColor = "";
            var tradeType = "";
            var fieldColor = "";
            //console.log(data)
            result = JSON.parse(data).data
            if (result.status == "ok") {
                $("#myOrders").html("");
                if (Object.keys(result.orders).length > 0) {
                    for (var key in result.orders) {
                        if (!result.orders.hasOwnProperty(key)) continue;
                        var row = result.orders[key];
                        if (isNaN(parseFloat(row.total))) {
                            total = "Not available";
                        } else {
                            total = row.total;
                        }
                        if (isNaN(parseFloat(row.basevalue))) {
                            btcvalue = "Not available";
                        } else {
                            btcvalue = row.basevalue;
                        }
                        var viewClass = ' t-sell';
                        if (row.ordertype == "0") {
                            var ordertype = "SELL";
                        } else {
                            viewClass = ' t-buy';
                            var ordertype = "BUY";
                        }
                        if (row.ordertype == "0") {
                            classColor = "sell-bg";
                        } else {
                            classColor = "buy-bg";
                        }
                        if (row.ordertype == "0") {
                            fieldColor = "sell-text";
                        } else {
                            fieldColor = "buy-text";
                        }
                        html = "";
                        html = html + "<tr class='ordertext " + viewClass + "'>";
                        html = html + "<td class='text-center pricerow orderid " + fieldColor + "'>";
                        html = html + ordertype;
                        html = html + "</td>";
                        html = html + "<td class='text-center pricerow price " + fieldColor + "'>";
                        html = html + row.price;
                        html = html + "</td>";
                        html = html + "<td class='text-center pricerow amount " + fieldColor + "'>";
                        html = html + row.amount;
                        html = html + "</td>";
                        html = html + "<td class='text-center pricerow btcvalue " + fieldColor + "'>";
                        html = html + btcvalue;
                        html = html + "</td>";
                        html = html + "<td class='text-center pricerow amount " + fieldColor + "'>";
                        html = html + row.amount;
                        html = html + "</td>";
                        html = html + "<td class='text-center pricerow datestamp'>";
                        html = html + row.datestamp;
                        html = html + "</td>";
                        /*html = html + "<td class='text-center pricerow orderid " + fieldColor + "'>";
                        html = html + row.orderid;
                        html = html + "</td>";
                        html = html + "<td class='text-center pricerow total " + fieldColor + "'>";
                        html = html + total;
                        html = html + "</td>";
                        */
                        html = html + "<td class='text-center pricerow cancel'>";
                        html = html + "<a href='javascript:void(0)' class='cancelorder' data-orderid='" + row.orderid + "'>Close</a>"
                        html = html + "</td>";
                        html = html + "</tr>";
                        $("#myOrders").append(html);
                    }
                } else {
                    $("#myOrders").append("<tr><td colspan='3'>No open orders found, will refresh shortly.</td></tr>");
                }
            }
        })
        ;

    });
</script>

<script type='text/javascript'>
    $(document).ready(function () {

        var minidiceBase = "<?= $baseCurrencyDescription['symbol'] ?>";
        var minidiceCurr = "<?= $currentCurrencyDescription['symbol'] ?>";
        var buttonlocked = false;
        var resetvar = null;
        var buttonreset = null;

        $(document).on('click', '.minidicemaxCurrency', function () {
            //var currency = $(this).text();
            //if (currency == minidiceBase) {
            //    console.log("Base Currency");
            //    $("#minidicebetBTC").val($("#buyCurrencyAmount").text());
            //}
            //else {
            //    $("#minidicebetMATRIX").val($("#sellCurrencyAmount").text());
            //}
            return;
        });

        function sleep(ms) {
            var unixtime_ms = new Date().getTime();
            while (new Date().getTime() < unixtime_ms + ms) {
            }
        }

        function displayDiceError(currency, errorText) {
            $("#minidiceresult" + currency).removeClass('bggreen bgred bold text-danger text-success');
            $("#minidiceresult" + currency).html("<span class='bold text-danger'>" + errorText + "</span>");
        }

        function resetDiceError(currency) {
            $("#minidiceresult" + currency).removeClass('bggreen bgred bold text-danger text-success');
            $("#minidiceresult" + currency).html("");
        }

        function setButtonClass(item, text, removec, addc) {
            $("#" + item).removeClass(removec).addClass(addc);
            $("#" + item).text(text);
        }

        function resetButton(item, text, removec, addc) {
            $("#" + item).removeClass(removec).addClass(addc);
            $("#" + item).text(text);
            $(".roll").prop('disabled', false);
        }

        $(".roll").click(function () {

            alert('Need to be logged in to roll the dice.');
            return;

        });

        var minidice_hidden = localStorage['minidice_hidden'];
        if (minidice_hidden == null) {
            //$("#minidicewrapper").show();
            //$("#minidicewrapper").removeClass('hidden');
            minidice_hidden = "false"
            localStorage['minidice_hidden'] = 'true';
        }

        function toggleMinidice() {
            if (minidice_hidden == 'true') {
                $("#minidicewrapper").show();
                $("#minidicewrapper").removeClass('hidden');
                $("#minidicetoggle").html("Hide Mini Dice");
                minidice_hidden = 'false';
                localStorage['minidice_hidden'] = 'true';
            }
            else {
                $("#minidicewrapper").hide();
                $("#minidicewrapper").addClass('hidden');
                $("#minidicetoggle").html("Show Mini Dice");
                minidice_hidden = 'true';
                localStorage['minidice_hidden'] = 'false';
            }
        }

        setTimeout(function () {
            toggleMinidice();
        }, 250);

        $(document).on('click', '#minidicetoggle', function () {
            toggleMinidice();
        });

    });

</script>
<script>
    $(function () {
        $('form').submit(function () {
            socket.emit('chat message', $('#m').val());
            $('#m').val('');
            return false;
        });
        socket.on('chat message', function (msg) {
            $('#messages').append($('<li>').text(msg));
        })
        ;
    })
    ;
</script>

<script>
    var json = <?= $json ?>;
    var maps = [{'81426e36-34e5-47c2-a91a-95791aa47ac3': ["7.0.4"]}, {'3c6bd221-c53e-4637-9225-c23d2b701e7d': ["9"]}, {'cc024f97-efe0-4821-b540-c75c0aee89d5': ["8"]}, {'8076c2b1-4eaa-444f-92c8-bc86e1eb65d7': ["10"]}];

    function getMarketPair(url, data) {
        $.post(url, {json: JSON.stringify(data)}).done(function (data) {
            data = JSON.parse(data);
            var rowTemplate = '<tr class="coin-ankor"><td class="token-str">{81426e36-34e5-47c2-a91a-95791aa47ac3}</td><td>{3c6bd221-c53e-4637-9225-c23d2b701e7d}</td><td>{cc024f97-efe0-4821-b540-c75c0aee89d5}</td><td class="{view-class}">{8076c2b1-4eaa-444f-92c8-bc86e1eb65d7}%</td></tr>';

            var resultHTML = '';
            //var maps = [{'81426e36-34e5-47c2-a91a-95791aa47ac3': ["7.0.4"]}, {'3c6bd221-c53e-4637-9225-c23d2b701e7d': ["9"]}, {'cc024f97-efe0-4821-b540-c75c0aee89d5': ["8"]}, {'8076c2b1-4eaa-444f-92c8-bc86e1eb65d7': ["10"]}];
            $.each(data['data']['data'], function () {
                var htmlRow = rowTemplate;
                $.each(this, function (index, value) {
                    console.log("row value["+index+"]: ")
                    console.log(value)
                    for (var i = 0; i < maps.length; i++) {
                        var key = Object.keys(maps[i])[0];
                        var indexes = maps[i][key][0].split(".");
                        if (indexes[0] == index) {
                            //
                            var resultRow = '';
                            if (value != null) {
                                resultRow = getValueFromRowData(value, indexes);
                            }
                            console.log(resultRow)
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
    }

    function getValueFromRowData(row, indexesArray) {
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
    }

    var myActiveBaseCurrency = $('.btn-change-market.active');
    var activeHistoryOrdersBtn = $('.d-inline-block.float-right.align-middle.btn-transparent.grey-text.active');
    var currentPressedMarket = '<?= $marketResult['basecurrencyid'] ?>';

    $('.d-inline-block.float-right.align-middle.btn-transparent.grey-text').click(function () {
        if (!this.classList.contains('active')) {
            activeHistoryOrdersBtn.removeClass("active");
            activeHistoryOrdersBtn = $(this);
            activeHistoryOrdersBtn.addClass("active");
        }
        if (activeHistoryOrdersBtn.attr('id') == 'yours-orders') {
            socket.emit('yours_history');
        } else {
            socket.emit('trade_history');
        }

        //console.log(activeHistoryOrdersBtn.attr('id'))
    });

    $('.btn-change-market').click(function () {
        updateMarket(this);
    });

    function updateMarket(button) {

        if (!button.classList.contains('active')) {
            myActiveBaseCurrency.removeClass("active");
            myActiveBaseCurrency = $(button);
            myActiveBaseCurrency.addClass("active");
        }
        currentPressedMarket = button.id;
        var localQuery = <?= $json ?>;
        localQuery.filters[1] = JSON.parse('{"special":[{"map":"' + getIndexFromStructureByID('5cfe2e58-b96a-4c66-88d4-fc5143c5c5a3') + '","comp":"6","value":"' + currentPressedMarket + '"},' +
            '{"map":"' + getIndexFromStructureByID('48e6c567-99dd-4c23-9574-a53e5c23607e') + '","comp":"6","value":"200"}]}');
        localQuery.filters[2] = JSON.parse('{"sorting":[{"map":"' + getIndexFromStructureByID('cc024f97-efe0-4821-b540-c75c0aee89d5') + '","field":"cc024f97-efe0-4821-b540-c75c0aee89d5","sort":"1"}]}');
        getMarketPair('/en/drole/default/getmarketpair', localQuery);
    }

    $(function () {
        $('#coinmarkets-table').on('click', '.coin-ankor', function () {
            window.location.href = window.location.href.slice(0, window.location.href.indexOf('\?')) + "?market=" + myActiveBaseCurrency.text() + "_" + $(this).find('.token-str').html();
        });
    });
    var lastIntervalType = null;
    var requestIntervalType = 86400;
    var startPeriodNavigator = null;
    var endPeriodNavigator = null;
    var currentSortValue = $($('#marketstable').find("thead").find('tr').find('.fa-sort-amount-desc')).parent();
    var currentStructure = <?= $currentStructure ?>;
    var sortMaps = [{'8e6b1492-f288-4010-bbb3-53766c6a2294': ["3"]}, {'3c6bd221-c53e-4637-9225-c23d2b701e7d': ["9"]}, {'cc024f97-efe0-4821-b540-c75c0aee89d5': ["8"]}, {'8076c2b1-4eaa-444f-92c8-bc86e1eb65d7': ["10"]}];

    $('#quick-search').keyup(function () {
        var localQuery = <?= $json ?>;
        var key = Object.keys(sortMaps[$(currentSortValue).index()])[0];
        var indexes = sortMaps[$(currentSortValue).index()][key][0];
        localQuery.filters[1] = JSON.parse('{"special":[{"map":"' + getIndexFromStructureByID('5cfe2e58-b96a-4c66-88d4-fc5143c5c5a3') + '","comp":"6","value":"' + currentPressedMarket + '"},' +
            '{"map":"' + getIndexFromStructureByID('48e6c567-99dd-4c23-9574-a53e5c23607e') + '","comp":"6","value":"200"}]}');
        localQuery.filters[2] = JSON.parse('{"sorting":[{"map":"' + indexes + '","field":"' + key + '","sort":"' + ($($(currentSortValue).children(0)[1]).hasClass('fa-sort-amount-asc') ? '0' : '1') + '"}]}');
        if (document.getElementById("quick-search").value.length > 0)
            localQuery.filters[1].special.push(JSON.parse('{"map":"' + getIndexFromStructureByID('8e6b1492-f288-4010-bbb3-53766c6a2294') + '","comp":"7","value":"_' + document.getElementById("quick-search").value + '"}'))
        getMarketPair('/en/drole/default/getmarketpair', localQuery);
    }).keyup();

    $('#c1D').click(function () {
        if ($(this).hasClass('btn-success')) {
            return;
        }
        updatePeriodBtns(this);
    });

    $('.btn.btn-sm.candleget.btn-default').click(function () {
        //lastIntervalType = $(this).attr('data-time');
        updatePeriodBtns(this);
    });

    /*$('#c1d').click(function () {
        if ($(this).hasClass('btn-success')) {
            return;
        }
        updatePeriodBtns(this);
    });
*/
    function updateChart(period) {
        //console.log("start update chart " + period)
        var url = "/en/drole/default/chart";
        $.post(url, {
            'companyid': '<?= $dynamicRoleArray['company_id'] ?>',
            'serviceid': '<?= $dynamicRoleArray['service_id'] ?>',
            'currentcurrencyid': '<?= $marketResult['currentcurrencyid'] ?>',
            'basecurrencyid': '<?= $marketResult['basecurrencyid'] ?>',
            'type': period
        }).done(function (data) {
            //console.log(data)
            data = JSON.parse(data).data
            //
            //data = JSON.parse(data)
            //console.log(data.type)

            if (data.status != 'ok' || requestIntervalType != data.type) {
                return;
            }
            var candleArray = data.chartdata;

            if (candleArray != null && candleArray != undefined) {
                var ohlc = [],
                    volume = [],
                    dataLength = candleArray.length;
                if (candleArray[0][1] == 0) {
                    candleArray[0][1] = 0.00000000001;
                }
                for (i = 0; i < candleArray.length; i += 1) {
                    //console.log(parseFloat(candleArray[i][4]).toFixed(8))
                    ohlc.push([
                        candleArray[i][0] + 10800000, // the date
                        candleArray[i][1], // open
                        candleArray[i][2], // high
                        candleArray[i][3], // low
                        candleArray[i][4] // close
                    ]);
                    //var currentVolume = (candleArray[i][5] == 0 ? 0.00000000001 : candleArray[i][5]);
                    volume.push([
                        ohlc[i][0], // the date
                        candleArray[i][5] // the volume
                    ]);

                }
                /*console.log(JSON.stringify(volume[volume.length - 2]))
                console.log(JSON.stringify(volume[volume.length - 1]))
                console.log(JSON.stringify(ohlc[ohlc.length - 2]))
                console.log(JSON.stringify(ohlc[ohlc.length - 1]))
                if (volume.length > 0 && volume[volume.length - 1][0] == volume[volume.length - 2][0]) {
                    //volume[volume.length - 1][1] = volume[volume.length - 2][1];
                    volume.splice(volume.length - 2, 1);
                    //var lastElement = [(volume[volume.length - 2][0] + (volume[1][0] - volume[0][0])), 1000];
                    //volume.splice(volume.length - 1, 1, lastElement);
                    //ohlc.splice(ohlc.length - 2, 1);
                }
                if (ohlc.length > 0 && ohlc[ohlc.length - 1][0] == ohlc[ohlc.length - 2][0]) {
                    //volume[volume.length - 1][1] = volume[volume.length - 2][1];
                    ohlc.splice(ohlc.length - 2, 1);
                    //ohlc.splice(ohlc.length - 1, 1);
                    var lastElement = [ohlc[ohlc.length - 1][0], parseFloat(ohlc[ohlc.length - 1][1]), ohlc[ohlc.length - 1][2], ohlc[ohlc.length - 1][3], parseFloat(ohlc[ohlc.length - 1][4])];
                    ohlc.splice(ohlc.length - 1, 1, lastElement);
                }*/

                /*console.log('=')
                console.log((volume[1][0] - volume[0][0]) + ' <> ' + (volume[volume.length - 1][0] - volume[volume.length - 2][0]))
                console.log(JSON.stringify(volume[volume.length - 2]))
                console.log(JSON.stringify(volume[volume.length - 1]))
                console.log(JSON.stringify(ohlc[ohlc.length - 2]))
                console.log(JSON.stringify(ohlc[ohlc.length - 1]))*/
                /*var endPeriodIndex = 189;
                if(endPeriodIndex >= ohlc.length){
                    endPeriodIndex = ohlc.length - 1;
                }*/
                /*startPeriodNavigator = ohlc[ohlc.length - endPeriodIndex][0];
                endPeriodNavigator = ohlc[ohlc.length - 1][0];*/
                var lastElement = [ohlc[ohlc.length - 1][0], parseFloat(ohlc[ohlc.length - 1][1]), ohlc[ohlc.length - 1][2], ohlc[ohlc.length - 1][3], parseFloat(ohlc[ohlc.length - 1][4])];
                ohlc.splice(ohlc.length - 1, 1, lastElement);
                // create the chart
                tradechart.series[0].setData(volume);
                //console.log(JSON.stringify(ohlc))
                tradechart.series[1].setData(ohlc);
                tradechart.series[2].setData(ohlc);
                if (data.type != lastIntervalType) {
                    lastIntervalType = data.type;
                    updateXAxisInterval(lastIntervalType, dataLength);
                }
            }
        });
    }

    function updatePeriodBtns(currentBtn) {

        var activeBtn = $('.btn.btn-sm.candleget.btn-success');
        activeBtn.removeClass("btn-success");
        //activeBtn.removeClass("candleget");
        activeBtn.addClass("btn-default");
        //activeBtn.addClass("candleget");
        $(currentBtn).removeClass("btn-default");
        //$(currentBtn).removeClass("candleget");
        $(currentBtn).addClass("btn-success");
        //$(currentBtn).addClass("candleget");
        updateChart($(currentBtn).attr('data-time'));
        requestIntervalType = $(currentBtn).attr('data-time');
    }

    function updateXAxisInterval(dataTime, datalength) {
        var firstPeriod = 101;
        switch (dataTime) {
            case '172800':
                firstPeriod = 80;
                break;
            case '604800':
            case '1209600':
                firstPeriod = 168;
                break;
            case '2419200':
                firstPeriod = 159;
                break;
            case '4838400':
                firstPeriod = 159;
                break;
            case '14515200':
                firstPeriod = 159;
                break;
        }
        var arrayLength = datalength;
        if (firstPeriod > arrayLength) {
            firstPeriod = arrayLength - 1;
        }
        tradechart.xAxis[0].update({
            //range: (endPeriodNavigator - startPeriodNavigator)
            range: (tradechart.series[0].xData[arrayLength - 1] - tradechart.series[0].xData[arrayLength - firstPeriod])
            //max:tradechart.series[0].xData[arrayLength - firstPeriod], min: 0
        });
    }

    function getIndexFromStructureByID(fieldID) {
        for (var i = 0; i < Object.keys(currentStructure).length; i++) {
            if (currentStructure[i].id == fieldID) {
                return i;
            }
        }
        return -1;
    }

    $(function () {
        $('#marketstable').on('click', '.marketsort', function () {
            var key = Object.keys(sortMaps[$(this).index()])[0];
            var indexes = sortMaps[$(this).index()][key][0];
            if ($($(this).children(0)[1]).hasClass('fa-exchange')) {
                $($(currentSortValue).children(0)[1]).removeClass('fa-sort-amount-asc');
                $($(currentSortValue).children(0)[1]).removeClass('fa-sort-amount-desc');
                $($(currentSortValue).children(0)[1]).addClass('fa-exchange');
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
            var localQuery = <?= $json ?>;
            localQuery.filters[1] = JSON.parse('{"special":[{"map":"' + getIndexFromStructureByID('5cfe2e58-b96a-4c66-88d4-fc5143c5c5a3') + '","comp":"6","value":"' + currentPressedMarket + '"},' +
                '{"map":"' + getIndexFromStructureByID('48e6c567-99dd-4c23-9574-a53e5c23607e') + '","comp":"6","value":"200"}]}');
            localQuery.filters[2] = JSON.parse('{"sorting":[{"map":"' + indexes + '","field":"' + key + '","sort":"' + ($($(this).children(0)[1]).hasClass('fa-sort-amount-asc') ? '0' : '1') + '"}]}');
            if (document.getElementById("quick-search").value.length > 0)
                localQuery.filters[1].special.push(JSON.parse('{"map":"' + getIndexFromStructureByID('8e6b1492-f288-4010-bbb3-53766c6a2294') + '","comp":"7","value":"_' + document.getElementById("quick-search").value + '"}'))
            getMarketPair('/en/drole/default/getmarketpair', localQuery);
        });
    });

</script>
