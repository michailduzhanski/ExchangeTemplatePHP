var selectedChart = 'chart'
var enableChartAnimations = false;
var candlestickChart = true;
var stockPriceChart = false;
var volumeChart = true;
var macdChart = false;
var signalChart = false;
var histogramChart = false;
var smaChart = false;
var smaChartValue = 50;
var ema1Chart = false;
var ema1ChartValue = 30;
var ema2Chart = false;
var ema2ChartValue = 20;
var fibonacciChart = false;
var orderBookChartPercent = 0;
var distributionChartCount = 100;

var actionSubmitTrade = '/Exchange/SubmitTrade';
var actionTradeChart = '/Exchange/GetTradePairChart';
var actionTradePairData = '/Exchange/GetTradePairData';
var actionUserOpenTrades = '/Exchange/GetUserOpenTrades';
var actionCurrencySummary = '/Exchange/GetCurrencySummary';
var actionTradePairUserData = '/Exchange/GetTradePairUserData';
var actionDistributionChart = '/Exchange/GetCurrencyDistribution';
var actionHideZeroBalances = '/UserSettings/UpdateBalanceHideZero';
var actionUpdateChartSettings = '/UserSettings/UpdateChartSettings';
var actionShowFavoriteBalances = '/UserSettings/UpdateBalanceFavoritesOnly';
var actionTradeBalances = '/UserBalance/GetTradeBalance';
var actionSetFavoriteBalance = '/UserBalance/SetFavorite';
var actionTradePairBalance = '/UserBalance/GetTradePairBalance';


var selectedMarket = 'MATRX/BTC';
var currentBaseMarket = 'BTC';
var selectedTradePair = {
    BaseFee: 0.20000000,
    BaseMinTrade: 0.00050000,
    TradePairId:  5803,
};

var fullChart = false;
var marketSummaryView = false;
var showZeroBalances = true;
var showFavoriteBalances = false;
var isAuthenticated = false;

$(function () {
    $('#carousel-example-generic').carousel();
})

var Resources = Resources || {};
Resources.Exchange = {
    MarketPageTitle: 'Market',
    MarketsLoadingMessage: 'Loading markets...',
    MarketsEmptyListMessage: 'No markets found.',
    MarketsSearchPlaceholder: 'Search markets...',
    BalanceEmptyListMessage: 'No balances found.',
    OrdersEmptyListMessage: 'You have no open orders.',
    HistorySellOrdersEmptyList: 'No sell orders.',
    HistoryBuyOrdersEmptyList: 'No buy orders.',
    HistoryMarketEmtpyList: 'No market history.',
    HistoryMyOpenOrdersEmtpyList: 'You have no open orders.',
    HistoryMyOrdersEmtpyList: 'You have no order history.',
    TradeNotificationTitle: 'Trade Notification',
    TradeMinPriceError: 'Invalid trade price, minimum price is {0}',
    TradeMinTotalError: 'Your trade total must be at least {0} {1}',
    TradeInsufficientFundsError: 'Insufficient {0} funds.',
    TradeBuyOrderSubmittedMessage: 'Buy order submitted',
    TradeSellOrderSubmittedMessage: 'Sell order submitted',
    InfoSettingsSavedMessage: 'Settings Saved',
    InfoSettingsFailedMessage: 'Save Failed'
};

var tradechart, orderbookChart, orderBookChartThrottle = 250,
    distributionChart, selectedChart = "trade",
    selectedSeriesRange = 1,
    selectedCandleGrouping = 60,
    chartTextColor = "#666666",
    chartBorderColor = "#000000",
    chartCrossHairColor = "#000000",
    candlestickLineColor = "#000000",
    candlestickChartUpColor = "#5cb85c",
    candlestickChartDownColor = "#ee5f5b",
    stockPriceChartColor = "#4286f4",
    volumeChartColor = "rgba(0, 0, 0, 0.2)",
    macdChartColor = "#3b7249",
    signalChartColor = "#d8ae13",
    histogramChartUpColor = "#5cb85c",
    histogramChartDownColor = "#ee5f5b",
    smaChartColor = "#4a788c",
    ema1ChartColor = "orange",
    ema2ChartColor = "purple",
    fibonacciChartColor = "#91353e",
    orderTemplate = $("#orderTemplate").html(),
    tradeHistoryTemplate = $("#tradeHistoryTemplate").html(),
    orderbookTooltipTemplate = $("#orderbookTooltipTemplate").html(),
    marketDataSet = [],
    marketSummaryTables = {},
    isSideMenuOpen = !0;
    /*favoriteMarkets = store.get("favorite-market") || [],
    showFavoriteMarkets = store.get("favorite-market-enabled") || !1,
    marketTableSortColumn = store.get("market-sort-col") || 5,
    marketTableSortDirection = store.get("market-sort-dir") || "desc",
    balanceTableSortColumn = store.get("balance-sort-col") || 1,
    balanceTableSortDirection = store.get("balance-sort-dir") || "asc",
    disableTradeConfirmationModal = store.get("disable-trade-confirmation") || !1;*/

function createTradeChart() {
    tradechart = new Highcharts.StockChart({
        chart: {
            height: fullChart ? 554 : 354,
            backgroundColor: "transparent",
            renderTo: "chartdata",
            animation: enableChartAnimations,
            panning: !1,
            margin: [0, 0, 15, 0],
            alignTicks: !1,
            events: {
                redraw: function() {}
            }
        },
        credits: {
            enabled: !1
        },
        navigator: {
            adaptToUpdatedData: !1
        },
        scrollbar: {
            liveRedraw: !1
        },
        exporting: {
            enabled: !1
        },
        xAxis: {
            tickPosition: "inside",
            endOnTick: !0,
            startOnTick: !0,
            crosshair: {
                snap: !0,
                width: 1,
                zIndex: 100
            },
            events: {
                afterSetExtremes: function() {
                    var n = tradechart.yAxis[0].getExtremes(),
                        i, t;
                    n.dataMax != n.dataMin && (i = n.dataMax - n.dataMin, t = i / 20, tradechart.yAxis[0].update({
                        floor: n.dataMin - t,
                        ceiling: n.dataMax + t
                    }, !0), setTimeout(function() {
                        toggleFibonacci(fibonacciChart)
                    }, 100))
                }
            }
        },
        yAxis: [{
            labels: {
                format: "{value:.8f}",
                align: "right",
                x: -2
            },
            title: {
                text: "",
                enabled: !1
            },
            height: fullChart ? 300 : 225,
            offset: 0,
            lineWidth: .5,
            allowDecimals: !0,
            endOnTick: !1,
            startOnTick: !1,
            showLastLabel: !0,
            showFirstLabel: !0,
            tickPosition: "inside",
            events: {
                afterSetExtremes: function() {}
            }
        }, {
            labels: {
                format: "{value:.8f}",
                align: "right",
                x: -3,
                enabled: !1
            },
            title: {
                text: "Volume",
                enabled: !1
            },
            offset: 0,
            endOnTick: !1,
            startOnTick: !1,
            height: fullChart ? 300 : 225,
            lineWidth: 1,
            tickPosition: "inside",
            gridLineWidth: 0
        }, {
            labels: {
                format: "{value:.8f}",
                align: "right",
                x: -2
            },
            title: {
                text: "MACD",
                enabled: !1
            },
            top: 360,
            height: fullChart ? 100 : 0,
            offset: 0,
            maxPadding: 0,
            minPadding: 0,
            lineWidth: 1,
            gridLineWidth: 1,
            tickPosition: "inside"
        }],
        series: [{
            name: "StockPrice",
            type: "line",
            color: stockPriceChartColor,
            id: "primary",
            yAxis: 0,
            showInLegend: !1,
            lineWidth: stockPriceChart ? 1 : 0,
            animation: enableChartAnimations,
            turboThreshold: 100,
            showInNavigator: !0,
            dataGrouping: {
                enabled: !1
            },
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            states: {
                hover: {
                    enabled: !1
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-price").html(this.y.toFixed(8))
                }
            }
        }, {
            type: "candlestick",
            name: selectedMarket,
            yAxis: 0,
            color: candlestickChartDownColor,
            upColor: candlestickChartUpColor,
            upLineColor: candlestickLineColor,
            lineColor: candlestickLineColor,
            showInLegend: !1,
            lineWidth: .5,
            animation: enableChartAnimations,
            turboThreshold: 100,
            showInNavigator: !1,
            visible: candlestickChart,
            dataGrouping: {
                enabled: !1
            },
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            states: {
                hover: {
                    enabled: !1
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-open").html(this.open.toFixed(8));
                    $("#chart-info-high").html(this.high.toFixed(8));
                    $("#chart-info-low").html(this.low.toFixed(8));
                    $("#chart-info-close").html(this.close.toFixed(8));
                    $("#chart-info-date").html(moment.utc(this.x).local().format("D/MM hh:mm"))
                }
            }
        }, {
            type: "column",
            color: volumeChartColor,
            name: "",
            yAxis: 1,
            zIndex: 0,
            showInLegend: !1,
            animation: enableChartAnimations,
            turboThreshold: 0,
            showInNavigator: !1,
            visible: volumeChart,
            dataGrouping: {
                enabled: !1
            },
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-volume").html(this.y.toFixed(8));
                    $("#chart-info-basevolume").html((+this.basev || 0).toFixed(8))
                }
            }
        }, {
            name: "SMA",
            linkedTo: "primary",
            showInLegend: !0,
            type: "trendline",
            algorithm: "SMA",
            color: smaChartColor,
            periods: smaChartValue,
            visible: smaChart,
            showInLegend: !1,
            lineWidth: .5,
            animation: enableChartAnimations,
            turboThreshold: 100,
            showInNavigator: !1,
            enableMouseTracking: !1,
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-SMA").html(this.y.toFixed(8))
                }
            }
        }, {
            name: "EMA 1",
            linkedTo: "primary",
            showInLegend: !0,
            type: "trendline",
            algorithm: "EMA",
            color: ema1ChartColor,
            periods: ema1ChartValue,
            visible: ema1Chart,
            showInLegend: !1,
            lineWidth: .5,
            turboThreshold: 100,
            animation: enableChartAnimations,
            turboThreshold: 0,
            showInNavigator: !1,
            enableMouseTracking: !1,
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-EMA1").html(this.y.toFixed(8))
                }
            }
        }, {
            name: "EMA 2",
            linkedTo: "primary",
            showInLegend: !0,
            type: "trendline",
            algorithm: "EMA",
            color: ema2ChartColor,
            periods: ema2ChartValue,
            visible: ema2Chart,
            showInLegend: !1,
            turboThreshold: 100,
            enableMouseTracking: !1,
            lineWidth: .5,
            animation: enableChartAnimations,
            turboThreshold: 0,
            showInNavigator: !1,
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-EMA2").html(this.y.toFixed(8))
                }
            }
        }, {
            name: "MACD",
            linkedTo: "primary",
            yAxis: 2,
            showInLegend: !0,
            type: "trendline",
            algorithm: "MACD",
            color: macdChartColor,
            showInLegend: !1,
            lineWidth: .5,
            turboThreshold: 100,
            animation: enableChartAnimations,
            turboThreshold: 1e3,
            showInNavigator: !1,
            visible: macdChart,
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-macd").html(this.y.toFixed(8))
                }
            }
        }, {
            name: "Signal line",
            linkedTo: "primary",
            yAxis: 2,
            showInLegend: !0,
            type: "trendline",
            algorithm: "signalLine",
            color: signalChartColor,
            showInLegend: !1,
            lineWidth: .5,
            turboThreshold: 100,
            animation: enableChartAnimations,
            turboThreshold: 1e3,
            showInNavigator: !1,
            visible: signalChart,
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-signal").html(this.y.toFixed(8))
                }
            }
        }, {
            name: "Histogram",
            linkedTo: "primary",
            yAxis: 2,
            color: histogramChartUpColor,
            negativeColor: histogramChartDownColor,
            showInLegend: !0,
            type: "histogram",
            showInLegend: !1,
            lineWidth: .5,
            turboThreshold: 100,
            animation: enableChartAnimations,
            turboThreshold: 1e3,
            showInNavigator: !1,
            visible: histogramChart,
            marker: {
                enabled: !1,
                states: {
                    hover: {
                        enabled: !1
                    }
                }
            },
            tooltip: {
                pointFormatter: function() {
                    $("#chart-info-histogram").html(this.y.toFixed(8))
                }
            }
        }],
        tooltip: {
            animation: enableChartAnimations,
            style: {
                display: "none"
            }
        },
        rangeSelector: {
            inputEnabled: !1,
            allButtonsEnabled: !1,
            buttons: [{
                type: "day",
                count: 1,
                text: "",
                dataGrouping: {
                    forced: !0,
                    enabled: !0
                }
            }, {
                type: "day",
                count: 2,
                text: "",
                dataGrouping: {
                    forced: !0,
                    enabled: !0
                }
            }, {
                type: "week",
                count: 1,
                text: "",
                dataGrouping: {
                    forced: !0,
                    enabled: !0
                }
            }, {
                type: "week",
                count: 2,
                text: "",
                dataGrouping: {
                    forced: !0,
                    enabled: !0
                }
            }, {
                type: "month",
                text: "",
                count: 1,
                dataGrouping: {
                    forced: !0,
                    enabled: !0,
                    units: [
                        ["hour", [1]]
                    ]
                }
            }, {
                type: "month",
                text: "",
                count: 3,
                dataGrouping: {
                    forced: !0,
                    enabled: !0,
                    units: [
                        ["hour", [1]]
                    ]
                }
            }, {
                type: "month",
                text: "",
                count: 6,
                dataGrouping: {
                    forced: !0,
                    enabled: !0,
                    units: [
                        ["hour", [1]]
                    ]
                }
            }, {
                type: "all",
                text: "",
                dataGrouping: {
                    forced: !0,
                    enabled: !0,
                    units: [
                        ["hour", [1]]
                    ]
                }
            }],
            buttonTheme: {
                width: 0,
                height: 0
            },
            labelStyle: {
                fontSize: "1px"
            },
            selected: 0,
            inputStyle: {
                background: "red"
            }
        }
    })
}

function updateTradeChart() {
    createTradeChart();
    updateSeriesRange(selectedSeriesRange)
}

function updateSeriesRange(n) {
    tradechart && ($(".chart-candles-group > .btn-default").removeClass("active").attr("disabled", "disabled"), n == 0 ? $(".chart-candles-btn15, .chart-candles-btn30, .chart-candles-btn60, .chart-candles-btn120").removeAttr("disabled") : n == 1 ? $(".chart-candles-btn15, .chart-candles-btn30, .chart-candles-btn60, .chart-candles-btn120").removeAttr("disabled") : n == 2 ? $(".chart-candles-btn60, .chart-candles-btn120, .chart-candles-btn240, .chart-candles-btn720").removeAttr("disabled") : n == 3 ? $(".chart-candles-btn120, .chart-candles-btn240, .chart-candles-btn720").removeAttr("disabled") : n == 4 ? $(".chart-candles-btn240, .chart-candles-btn720, .chart-candles-btn1440").removeAttr("disabled") : n == 5 ? $(".chart-candles-btn240, .chart-candles-btn720, .chart-candles-btn1440, .chart-candles-btn10080").removeAttr("disabled") : n == 6 ? $(".chart-candles-btn720, .chart-candles-btn1440, .chart-candles-btn10080").removeAttr("disabled") : n == 7 && $(".chart-candles-btn1440, .chart-candles-btn10080").removeAttr("disabled"), $(".chart-range-group > .btn-default").removeClass("active"), $(".chart-range-btn" + n).addClass("active"), updateChartData(n, selectedCandleGrouping), $(".chart-candles-btn" + selectedCandleGrouping).addClass("active"))
}

function updateChart(n) {
    $(".chart-loading").show();
    var t = n ? n.Candle : [
            [0, 0, 0, 0, 0, 0]
        ],
        i = n ? n.Volume : [
            [0, 0]
        ];
    tradechart && ($(".chart-nodata").hide(), tradechart.series[0].setData(t, !1, !1, !1), tradechart.series[1].setData(t, !1, !1, !1), tradechart.series[2].setData(i, !1, !1, !1), tradechart.redraw(!1), tradechart.rangeSelector.clickButton(selectedSeriesRange, !0, !1), setBorders(), $(".chart-loading").hide(), t.length == 1 && t[0][0] == 0 && $(".chart-nodata").show())
}