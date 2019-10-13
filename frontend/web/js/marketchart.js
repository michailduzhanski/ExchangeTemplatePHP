AmCharts.ready(function () {
    Number.prototype.noExponents = function () {
        var data = String(this).split(/[eE]/);
        if (data.length == 1) return data[0];

        var z = '',
            sign = this < 0 ? '-' : '',
            str = data[0].replace('.', ''),
            mag = Number(data[1]) + 1;

        if (mag < 0) {
            z = sign + '0.';
            while (mag++) z += '0';
            return z + str.replace(/^\-/, '');
        }
        mag -= str.length;
        while (mag--) z += '0';
        return str + x;
    }

    function myValue(value, valueText, valueAxis) {
        return value
    }


    var chartData = [];

    var chart = AmCharts.makeChart("chartdiv", {
        type: "stock",
        "theme": "light",
        dataDateFormat: "YYYY-MM-DD HH:NN:SS",
        balloonDateFormat: "YYYY-MM-DD HH:NN:SS",
        numberFormatter: {
            usePrefixes: false,
            precision: 9,
            decimalSeparator: ".",
            thousandsSeparator: " "
        },
        categoryAxesSettings: {
            maxSeries: 0,
            minPeriod: "ss",
            equalSpacing: true,
        },
        dataSets: [{
            fieldMappings: [{
                fromField: "oopen",
                toField: "oopen"
            }, {
                fromField: "oclose",
                toField: "oclose"
            }, {
                fromField: "ohigh",
                toField: "ohigh"
            }, {
                fromField: "olow",
                toField: "olow"
            }, {
                fromField: "ovolume",
                toField: "ovolume"
            }, {
                fromField: "close",
                toField: "value"
            }, {
                fromField: "average",
                toField: "average"
            }],

            color: "#7f8da9",
            dataProvider: chartData,
            title: " ",
            categoryField: "date"
        }],
        panels: [{
            title: "Price",
            showCategoryAxis: false,
            marginRight: 80,
            percentHeight: 75,
            valueAxes: [{
                labelFunction: function (value, valueText, valueAxis) {
                    return value.noExponents();
                },
                gridAlpha: 0.25,
                id: "v1",
                dashLength: 1,
                position: "left",
            }],

            categoryAxis: {
                dashLength: 1,
                gridAlpha: 0.25,
            },

            stockGraphs: [{
                type: "candlestick",
                id: "g1",
                balloonText: "Open:<b>[[oopen]]</b><br>Low:<b>[[olow]]</b><br>High:<b>[[ohigh]]</b><br>Close:<b>[[oclose]]</b><br>Average:<b>[[average]]</b>",
                openField: "oopen",
                closeField: "oclose",
                highField: "ohigh",
                lowField: "olow",
                valueField: "oclose",
                lineColor: "#038500",
                fillColors: "#038500",
                negativeLineColor: "#a50000",
                negativeFillColors: "#a50000",
                fillAlphas: 1,
                useDataSetColors: false,
                showBalloon: true,
                proCandlesticks: true
            }],

            stockLegend: {
                markerType: "none",
                markerSize: 0,
                forceWidth: true,
                labelWidth: 200,
                labelText: "",
                periodValueText: "",
                periodValueTextRegular: "[[close]]"
            }
        },

            {
                title: "Volume",
                percentHeight: 25,
                marginTop: 1,
                showCategoryAxis: true,
                valueAxes: [{
                    labelFunction: function (value, valueText, valueAxis) {
                        return value.noExponents();
                    },
                    inside: false,
                    precision: 9,
                    position: "right",
                    dashLength: 5
                }],

                categoryAxis: {
                    dashLength: 5
                },

                stockGraphs: [{
                    valueField: "ovolume",
                    type: "column",
                    showBalloon: true,
                    fillAlphas: 1
                }],

                stockLegend: {
                    markerType: "none",
                    markerSize: 0,
                    periodValueText: "",
                    periodValueTextRegular: "[[value]]"
                }
            }
        ],


        chartScrollbarSettings: {
            enabled: false,
        },

        chartCursorSettings: {
            valueLineEnabled: true,
            valueBalloonsEnabled: true,
            zoomable: false
        },

    });

    var myButton = $("#c1D");

    $(".candleget").on("click", function () {
        var myself = $(this);
        myButton.removeClass('btn-success btn-danger btn-warning').addClass('btn-default')
        myself.removeClass('btn-success btn-danger btn-default').addClass('btn-success')
        myButton = myself;
        updateChart($("#marketid").html(), $("#marketname").html(), $(this).attr('data-size'), $(this).attr('data-time'), myself);
    });

    $("#candlesize").on("change", function () {
        var myself = $("#c1D");
        myButton.removeClass('btn-success btn-danger btn-warning').addClass('btn-default')
        myself.removeClass('btn-success btn-danger btn-default').addClass('btn-success')
        myButton = myself;
        updateChart($("#marketid").html(), $("#marketname").html(), $("#candlesize").val(), $("#history").val(), myself);
    });

    $("#history").on("change", function () {
        var myself = $("#c1D");
        myButton.removeClass('btn-success btn-danger btn-warning').addClass('btn-default')
        myself.removeClass('btn-success btn-danger btn-default').addClass('btn-success')
        myButton = myself;
        updateChart($("#marketid").html(), $("#marketname").html(), $("#candlesize").val(), $("#history").val(), myself);
    });

    chart.addListener("dataUpdated", function (event) {
        chart.zoomOut();
    });

    var updatechart_c = 300;
    var updatehistory = setTimeout(updateChart, 100);

    function updateChart(marketid, marketname, candlesize, history, myself) {
        chartData.length = 0;

        (myself) ? myself = myself : myself = myButton;
        (marketid) ? marketid = marketid : marketid = $("#marketid").html();
        (marketname) ? marketname = marketname : marketname = $("#marketname").html();
        (candlesize) ? candlesize = candlesize : candlesize = myButton.attr('data-size');
        (history) ? history = history : history = myButton.attr('data-time');


        socket.emit('chart_data', '{"marketid":"' + marketid + '", "marketname":"' + marketname + '", "candlesize":"' + candlesize + '", "history":"' + history + '"}');
        socket.on('chart_data', function (data) {
            console.log(data)
            var result = JSON.parse(data).data
            //var result = dataArray.data
            //console.log(result)
            if (result.status == "ok") {
                var result = result.chartdata;
                var i = 0;
                if (result && result.length == 0) {
                    //myself.removeClass('btn-success btn-warning btn-default').addClass('btn-danger');
                    $("#chartdiv").removeClass('cls-show').addClass("cls-hidden").fadeOut("slow");
                    $("#nodatadiv").removeClass('cls-hidden').addClass("cls-show").fadeIn("slow");
                } else {
                    //myself.removeClass('btn-danger btn-warning btn-default').addClass('btn-success');
                    $("#chartdiv").removeClass('cls-hidden').addClass("cls-show").fadeIn("slow");
                    $("#nodatadiv").removeClass('cls-show').addClass("cls-hidden").fadeOut("slow");
                }
                if (result) {
                    for (var key in result) {
                        if (!result.hasOwnProperty(key)) continue;
                        var row = result[key];
                        chartData[i] = {
                            date: row.ticker,
                            oopen: row.open.toFixed(9),
                            oclose: row.close.toFixed(9),
                            ohigh: row.high.toFixed(9),
                            olow: row.low.toFixed(9),
                            ovolume: row.volume.toFixed(9),
                            average: row.average.toFixed(9),
                            value: row.average.toFixed(9)
                        };
                        i++;

                    }
                    chart.validateData(result.status);
                }
            }
        });

        if (updatechart_c >= 1) {
            var updatehistory = setTimeout(updateChart, 30000);
            updatechart_c = updatechart_c - 1;
        }

    }

//updateChart()
});