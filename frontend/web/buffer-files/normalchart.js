function getTradePairInfo(n) {
    tradePairLoadStart();
    getTradePairDataRequest = postJson(actionTradePairData, {
        tradePairId: n
    }, function(n) {
        SetTradePairSubscription(n.TradePair.TradePairId);
        selectedTradePair = n.TradePair;
        selectedMarket = selectedTradePair.Symbol + "/" + selectedTradePair.BaseSymbol;
        $(".tradepair-basefee").text(n.TradePair.BaseFee.toFixed(2));
        $(".tradepair-basemintrade").text(n.TradePair.BaseMinTrade.toFixed(8));
        updateSelectedChart();
        updateTitle(n.TradePair, !1);
        updateTicker(n.Ticker);
        updateStatusMessage(n.TradePair);
        updateBuyOrdersTable(n.Buys);
        updateSellOrdersTable(n.Sells);
        updateMarketHistoryTable(n.History);
        tradePairLoadComplete()
    });
    isAuthenticated && (createUserOpenOrdersTable(), createUserOrderHistoryTable(), updateBalance(n, !1), getUserTradePairDataRequest = postJson(actionTradePairUserData, {
        tradePairId: n
    }, function(n) {
        updateUserOpenOrdersTable(n.Open);
        updateUserOrderHistoryTable(n.History)
    }))
}

function clearTarget() {
    clearStatusMessage();
    clearTicker();
    clearCharts();
    clearBalance();
    clearBuySellInputs();
    clearBuyOrdersTable();
    clearSellOrdersTable();
    clearMarketHistoryTable();
    clearUserOpenOrdersTable();
    clearUserOrderHistoryTable()
}

function tradePairLoadStart() {
    $(".currencyData-tradepair").attr("disabled", "disabled");
    getTradePairDataRequest && getTradePairDataRequest.readyState != 4 && getTradePairDataRequest.abort();
    getUserTradePairDataRequest && getUserTradePairDataRequest.readyState != 4 && getUserTradePairDataRequest.abort();
    getTradePairBalanceRequest && getTradePairBalanceRequest.readyState != 4 && getTradePairBalanceRequest.abort();
    getTradePairChartRequest && getTradePairChartRequest.readyState != 4 && getTradePairChartRequest.abort();
    clearTarget();
    $(".dataTables_empty").html('<span><i class="fa fa-spinner fa-pulse"><\/i> ' + Resources.General.LoadingMessage + "<\/span>")
}

function tradePairLoadComplete() {
    $(".currencyData-tradepair").removeAttr("disabled")
}

function updateTicker(n) {
    $(".ticker-change").text(n.Change.toFixed(2)).addClass(changeHighlight(n.Change));
    $(".ticker-last").text(n.Last.toFixed(8));
    $(".ticker-high").text(n.High.toFixed(8));
    $(".ticker-low").text(n.Low.toFixed(8));
    $(".ticker-volume").text(n.Volume.toFixed(8));
    $(".ticker-basevolume").text(n.BaseVolume.toFixed(8));
    document.title = n.Last.toFixed(8) + " " + selectedTradePair.Symbol + "/" + selectedTradePair.BaseSymbol + " " + Resources.Exchange.MarketPageTitle + " - Cryptopia"
}

function clearTicker() {
    $(".ticker-change").removeClass("text-danger text-success").text("0.00");
    $(".ticker-last, .ticker-high, .ticker-low, .ticker-volume, .ticker-basevolume").text("0.00000000")
}

function updateTitle(n, t) {
    t ? $(".exchangeinfo-container ").fadeTo(200, .5, function() {
        $(".tradepair-symbol").text(n.Symbol);
        $(".tradepair-basesymbol").text(n.BaseSymbol);
        $(".exchangeinfo-title").text(n.Name);
        $(".exchangeinfo-title-logo").attr("src", "/Content/Images/Coins/" + n.Symbol + "-medium.png")
    }).fadeTo(200, 1) : ($(".tradepair-symbol").text(n.Symbol), $(".tradepair-basesymbol").text(n.BaseSymbol), $(".exchangeinfo-title").text(n.Name), $(".exchangeinfo-title-logo").attr("src", "/Content/Images/Coins/" + n.Symbol + "-medium.png"))
}

function updateStatusMessage(n) {
    var t = $("#tradepairStatus"),
        i = n.Symbol + "/" + n.BaseSymbol;
    n.Status == 0 ? n.StatusMessage && (t.show(), t.find(".alert").addClass("alert-info"), t.find("h4").text("Market Information"), t.find("p").text(n.StatusMessage)) : n.Status == 1 ? (t.show(), t.find(".alert").addClass("alert-danger"), t.find("h4").text("Market Closing"), t.find("p").text(n.StatusMessage || i + " market is closing, please cancel any open orders and withdraw your coins."), $(".submit-button").hide(), $(".submit-button-alert").html("Market Closing").show()) : n.Status == 2 && (t.show(), t.find(".alert").addClass("alert-warning"), t.find("h4").text("Market Paused"), t.find("p").text(n.StatusMessage || i + " trading is currently paused."), $(".submit-button").hide(), $(".submit-button-alert").html("Market Paused").show())
}

function clearStatusMessage() {
    $(".submit-button").show();
    $(".submit-button-alert").hide();
    $("#tradepairStatus").hide().find(".alert").removeClass("alert-info alert-warning alert-danger")
}

function marketFavoriteFilter(n, t) {
    if (n.sInstance == "market-list" && showFavoriteMarkets) {
        for (var i = 0; i < favoriteMarkets.length; i++)
            if (favoriteMarkets[i] == t[1]) return !0;
        return favoriteMarkets.length == 0
    }
    return !0
}

function setupMarketList(n) {
    var t, r, u;
    if (n) {
        var f = $(".stackmenu-content").height(),
            e = $("#market-list_wrapper  .dataTables_scrollHead").height(),
            o = $("#market-list_wrapper  .dataTables_filter").height(),
            i = f - (e + o);
        $("#market-list_wrapper .dataTables_scrollBody").height(i);
        updateMarketFavorites();
        t = $("#market-list_wrapper .currencyData-tradepair-" + selectedTradePair.TradePairId);
        t && t.position() && (r = i / 6, u = t.position().top + r, u > i && $("#market-list_wrapper .dataTables_scrollBody").scrollTop(t.position().top - r))
    }
}

function updateMarketFavorites() {
    clearTimeout(updateMarketFavoritesTimeout);
    updateMarketFavoritesTimeout = setTimeout(function() {
        if ($("#market-list_wrapper .market-favorite").removeClass("market-favorite-active"), favoriteMarkets)
            for (var n = 0; n < favoriteMarkets.length; n++) $("#market-list_wrapper .market-favorite-" + favoriteMarkets[n]).addClass("market-favorite-active")
    }, 100)
}

function changeBaseMarket(n) {
    $(".currencyData-content").hide();
    $("#currencyData-content-" + n).show();
    $(".currencyData-btn").removeClass("active");
    $(".currencyData-btn-" + n).addClass("active");
    marketSummaryView && (marketSummaryTables[n] || (marketSummaryTables[n] = createSummaryTable(n)), marketSummaryTables[n] && marketSummaryTables[n].clear().draw());
    marketTable.clear().draw();
    var t = '<span><i class="fa fa-spinner fa-pulse"><\/i> ' + Resources.General.LoadingMessage + "<\/span>";
    $(".exchange-menu .dataTables_empty").html(t);
    $(".currencyData-content .dataTables_empty").html(t);
    getCurrencySummaryRequest && getCurrencySummaryRequest.readyState != 4 && getCurrencySummaryRequest.abort();
    getCurrencySummaryRequest = postJson(actionCurrencySummary, {
        baseMarket: n
    }, function(t) {
        marketTable.rows.add(t.aaData).draw();
        marketSummaryView && marketSummaryTables[n] && marketSummaryTables[n].rows.add(t.aaData).draw();
        marketDataSet = t.aaData;
        setupMarketList(!0);
        marketTable.columns.adjust()
    })
}

function updateMarketItem(n) {
    for (var t, f, e, i, o, r, u = 0; u < marketDataSet.length; u++)
        if (t = marketDataSet[u], t[1] == n.TradePairId) {
            r = t[9] > n.Last.toFixed(8) ? "red" : t[9] < n.Last.toFixed(8) ? "green" : "blue";
            t[4] = n.Change.toFixed(2);
            t[5] = n.BaseVolume.toFixed(8);
            t[6] = n.Volume.toFixed(8);
            t[7] = n.High.toFixed(8);
            t[8] = n.Low.toFixed(8);
            t[9] = n.Last.toFixed(8);
            break
        }
    r && (f = $("#market-list .currencyData-tradepair-" + n.TradePairId), marketTable.row(f).invalidate(), highlightRemove("#market-list .currencyData-tradepair"), highlightItem(f, r), updateMarketFavorites(), marketSummaryView && (e = $("#currencyData-" + currentBaseMarket + " .currencyData-tradepair-" + n.TradePairId), marketSummaryTables[currentBaseMarket].row(e).invalidate(), highlightRemove("#currencyData-" + currentBaseMarket + " .currencyData-tradepair"), highlightItem(e, r), i = "#top-stats .currencyData-tradepair-" + n.TradePairId, o = n.Change > 0 ? "text-success" : n.Change < 0 ? "text-danger" : "", $(i + " > td:nth-child(2)").removeClass("text-*").addClass(o).text(n.Change.toFixed(2) + "%"), $(i + " > td:nth-child(3)").text(n.BaseVolume.toFixed(8)), $(i + " > td:nth-child(4)").text(n.Volume.toFixed(8)), $(i + " > td:nth-child(5)").text(n.High.toFixed(8)), $(i + " > td:nth-child(6)").text(n.Low.toFixed(8))))
}

function createSummaryTable(n) {
    var t = $("#currencyData-" + n);
    return t.find("tbody").empty(), t.DataTable({
        dom: "<'row'<'col-sm-12'tr>>",
        order: [
            [5, "desc"]
        ],
        lengthChange: !1,
        processing: !1,
        bServerSide: !1,
        searching: !1,
        paging: !1,
        scrollCollapse: !1,
        scrollY: "100%",
        autoWidth: !1,
        info: !1,
        language: {
            emptyTable: Resources.Exchange.MarketsLoadingMessage,
            sZeroRecords: Resources.Exchange.MarketsEmptyListMessage,
            search: "",
            searchPlaceholder: Resources.Exchange.MarketsSearchPlaceholder,
            paginate: {
                previous: Resources.General.Previous,
                next: Resources.General.Next
            }
        },
        columnDefs: [{
            targets: [0],
            visible: !1
        }, {
            targets: [1],
            visible: !1
        }, {
            targets: [10],
            visible: !1
        }, {
            targets: [2],
            render: function(n, t, i) {
                return '<div style="display:inline-block"><div class="sprite-small sprite-' + i[3] + '-small-png"><\/div> ' + n + " (" + i[3] + ")<\/div>"
            }
        }, {
            targets: [3],
            render: function(t) {
                return '<a href="/Exchange?market=' + t + "_" + n + '">' + t + "/" + n + "<\/a>"
            }
        }, {
            targets: [4],
            render: function(n) {
                return '<div class="text-right ' + (n > 0 ? "text-success" : n < 0 ? "text-danger" : "") + '">' + n + "%<\/div>"
            }
        }, {
            targets: [5, 6, 7, 8, 9],
            render: function(n) {
                return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
            }
        }],
        fnRowCallback: function(n, t) {
            $(n).addClass("currencyData-tradepair-" + t[1])
        }
    })
}

function toggleSideMenu() {
    isSideMenuOpen ? ($("#main-wrapper").css({
        "min-width": "325px"
    }), $("#sidebar-wrapper").animate({
        width: "0px",
        opacity: "0"
    }, 400), $("#main-wrapper").animate({
        marginLeft: "35px"
    }, 400, function() {
        triggerWindowResize()
    })) : ($("#main-wrapper").css({
        "min-width": "725px"
    }), $("#sidebar-wrapper").animate({
        width: "365px",
        opacity: "1"
    }, 400), $("#main-wrapper").animate({
        marginLeft: "400px"
    }, 400, function() {
        triggerWindowResize()
    }));
    isSideMenuOpen = !isSideMenuOpen
}

function SetTradePairSubscription(n) {
    n && n != currentTradePairGroupId && $.connection.hub.state == $.signalR.connectionState.connected && (notificationHub.server.setTradePairSubscription(n, currentTradePairGroupId), currentTradePairGroupId = n)
}

function setupBalances() {
    if (sideMenuBalanceTable) sideMenuBalanceTable.columns.adjust();
    else {
        sideMenuBalanceTable = $("#userBalances").DataTable({
            dom: "<'row'<'col-sm-12'tr>>",
            order: [
                [balanceTableSortColumn, balanceTableSortDirection]
            ],
            lengthChange: !1,
            processing: !1,
            bServerSide: !1,
            searching: !0,
            paging: !1,
            sort: !0,
            info: !1,
            scrollX: "100%",
            sAjaxSource: actionTradeBalances,
            sServerMethod: "POST",
            language: {
                emptyTable: Resources.Exchange.BalanceEmptyListMessage,
                sZeroRecords: Resources.Exchange.BalanceEmptyListMessage,
                paginate: {
                    previous: Resources.General.Previous,
                    next: Resources.General.Next
                }
            },
            columnDefs: [{
                targets: [5, 6, 7, 8],
                visible: !1
            }, {
                targets: [0],
                visible: !0,
                sortable: !1,
                render: function(n, t, i) {
                    var r = i[8] ? " balance-favorite-active" : "";
                    return '<div class="balance-favorite balance-favorite-' + n + r + '" data-balanceid="' + n + '"><i class="fa fa-ellipsis-v" aria-hidden="true" style="margin-left:5px"><\/i><\/div>'
                }
            }, {
                targets: 1,
                searchable: !0,
                orderable: !0,
                render: function(n) {
                    return '<div style="display:inline-block;white-space:nowrap"><div class="sprite-small sprite-' + n + '-small-png"><\/div> ' + n + "<\/div>"
                }
            }, {
                targets: 2,
                searchable: !0,
                orderable: !0,
                render: function(n) {
                    return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
                }
            }, {
                targets: 3,
                searchable: !0,
                orderable: !0,
                render: function(n) {
                    return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
                }
            }, {
                targets: 4,
                searchable: !0,
                orderable: !0,
                render: function(n) {
                    return '<div class="text-right">' + (+n || 0).toFixed(2) + "<\/div>"
                }
            }],
            fnRowCallback: function(n, t) {
                $(n).addClass("balance-" + t[1]);
                $(n).addClass("balanceid-" + t[0])
            },
            fnDrawCallback: function() {
                setupBalanceList()
            }
        });
        $("#userBalances_wrapper .dataTables_scrollHead th").on("click", function() {
            var n = $(this)[0].cellIndex,
                t = $(this).hasClass("sorting_asc") ? "asc" : "desc";
            store.set("balance-sort-col", n);
            store.set("balance-sort-dir", t)
        })
    }
}

function balanceFilter(n, t) {
    if (n.sInstance == "userBalances") {
        var i = t[5] == 0,
            r = t[8] == "true";
        return showFavoriteBalances ? i ? showZeroBalances && r : r : !showZeroBalances && i ? !1 : !0
    }
    return !0
}

function setupBalanceList() {
    var n = $(".stackmenu-content").height(),
        t = $("#userBalances_wrapper .dataTables_scrollHead").height(),
        i = $("#userBalances_wrapper .dataTables_filter").height();
    $("#userBalances_wrapper .dataTables_scrollBody").height(n - (t + i))
}

function updateBalance(n, t) {
    getTradePairBalanceRequest && getTradePairBalanceRequest.readyState != 4 && getTradePairBalanceRequest.abort();
    postJson(actionTradePairBalance, {
        tradePairId: n
    }, function(n) {
        var r, i;
        if (!n.IsError && (t ? (n.Symbol == selectedTradePair.Symbol && $("#userBalanceSell").html(n.Available.toFixed(8)), n.BaseSymbol == selectedTradePair.BaseSymbol && $("#userBalanceBuy").html(n.BaseAvailable.toFixed(8))) : ($("#userBalanceSell").html(n.Available.toFixed(8)), $("#userBalanceBuy").html(n.BaseAvailable.toFixed(8))), sideMenuBalanceTable)) {
            var u = 0,
                f = sideMenuBalanceTable.rows().data(),
                e = $("#userBalances .balance-" + n.Symbol),
                o = $("#userBalances .balance-" + n.BaseSymbol);
            for (r = 0; r < f.length; r++)
                if (i = f[r], i[1] == n.Symbol ? (i[2] = n.Available.toFixed(8), i[3] = n.HeldForOrders.toFixed(8), sideMenuBalanceTable.row(e).invalidate(), u++) : i[1] == n.BaseSymbol && (i[2] = n.BaseAvailable.toFixed(8), i[3] = n.BaseHeldForOrders.toFixed(8), sideMenuBalanceTable.row(o).invalidate(), u++), u == 2) break
        }
    })
}

function clearBalance() {
    $("#userBalanceSell, #userBalanceSell").html("0.00000000")
}

function setupOpenOrders() {
    sideMenuOpenOrdersTable ? sideMenuOpenOrdersTable.columns.adjust() : sideMenuOpenOrdersTable = $("#sideMenuOpenOrders").DataTable({
        dom: "<'row'<'col-sm-12'tr>>",
        order: [
            [0, "asc"]
        ],
        lengthChange: !1,
        processing: !1,
        bServerSide: !1,
        searching: !0,
        paging: !1,
        sort: !0,
        info: !1,
        scrollX: "100%",
        sAjaxSource: actionUserOpenTrades,
        sServerMethod: "POST",
        language: {
            emptyTable: Resources.Exchange.OrdersEmptyListMessage,
            sZeroRecords: Resources.Exchange.OrdersEmptyListMessage,
            paginate: {
                previous: Resources.General.Previous,
                next: Resources.General.Next
            }
        },
        columnDefs: [{
            targets: 5,
            visible: !1
        }, {
            targets: 0,
            searchable: !0,
            orderable: !0,
            render: function(n) {
                var t = n.replace("/", "_"),
                    i = t.split("_")[0];
                return '<div style="display:inline-block;white-space:nowrap"><div class="sprite-small sprite-' + i + '-small-png"><\/div><a href="/Exchange?market=' + t + '"> ' + n + "<\/a><\/div>"
            }
        }, {
            targets: 2,
            searchable: !0,
            orderable: !0,
            render: function(n) {
                return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
            }
        }, {
            targets: 3,
            searchable: !0,
            orderable: !0,
            render: function(n) {
                return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
            }
        }, {
            targets: 4,
            searchable: !1,
            orderable: !1,
            render: function(n, t, i) {
                return '<div class="text-center"><i style="font-size:12px" class="trade-item-remove fa fa-times" data-orderid="' + n + '" data-tradepairid="' + i[5] + '" ><\/i><\/div>'
            }
        }],
        fnRowCallback: function(n, t) {
            $(n).addClass("order-" + t[4])
        },
        fnDrawCallback: function() {
            setupOrderList()
        }
    })
}

function updateOpenOrders(n) {
    var r = sideMenuOpenOrdersTable.rows().data(),
        u = $("#sideMenuOpenOrders .order-" + n.OrderId),
        t, i, f, e;
    if (n.Action == 1 || n.Action == 3) {
        for (t = 0; t < r.length; t++)
            if (i = r[t], i[4] == n.OrderId) {
                sideMenuOpenOrdersTable.row(u).remove().draw();
                break
            }
    } else if (n.Action == 2) {
        for (t = 0; t < r.length; t++)
            if (i = r[t], i[4] == n.OrderId) {
                i[3] = n.Remaining.toFixed(8);
                sideMenuOpenOrdersTable.row(u).invalidate();
                break
            }
    } else n.Action == 0 && (f = n.Type == 0 ? "Buy" : "Sell", e = n.Market, sideMenuOpenOrdersTable.row.add([e, f, n.Rate.toFixed(8), n.Remaining.toFixed(8), n.OrderId, n.TradePairId]).draw())
}

function setupOrderList() {
    var n = $(".stackmenu-content").height(),
        t = $("#sideMenuOpenOrders_wrapper .dataTables_scrollHead").height(),
        i = $("#sideMenuOpenOrders_wrapper .dataTables_filter").height();
    $("#sideMenuOpenOrders_wrapper .dataTables_scrollBody").height(n - (t + i))
}

function setupChatList() {
    var n = $(".chat-menu .chat-footer").height();
    $(".chat-menu .chat-container").height($(".stackmenu-body").height() - n)
}

function enableChat() {
    chatModule.initializeChat()
}

function disableChat() {
    chatModule.destroy()
}

function adjustTableHeaders(n) {
    n && n.columns.adjust()
}

function createSellOrdersTable() {
    sellOrdersTable || ($("#sellorders > tbody").empty(), sellOrdersTable = $("#sellorders").DataTable({
        order: [
            [1, "asc"]
        ],
        lengthChange: !1,
        processing: !1,
        bServerSide: !1,
        searching: !1,
        sort: !1,
        paging: !1,
        info: !1,
        scrollY: "250px",
        scrollCollapse: !1,
        bAutoWidth: !1,
        language: {
            emptyTable: Resources.Exchange.HistorySellOrdersEmptyList,
            paginate: {
                previous: Resources.General.Previous,
                next: Resources.General.Next
            }
        },
        columnDefs: [{
            targets: [1, 2, 3, 4],
            orderable: !1,
            render: function(n) {
                return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
            }
        }, {
            targets: 0,
            orderable: !1,
            render: function(n, t, i) {
                var r = (+i[1]).toFixed(8);
                return '<div class="orderbook-indicator" data-price="' + r + '"><i class="fa fa-ellipsis-v" aria-hidden="true"><\/i><\/div>'
            }
        }],
        fnDrawCallback: function(n) {
            n.aoData.length > 0 && (setUserOrderIndicator(), setOrderbookSumTotal("#sellorders"))
        }
    }))
}

function createBuyOrdersTable() {
    buyOrdersTable || ($("#buyorders > tbody").empty(), buyOrdersTable = $("#buyorders").DataTable({
        order: [
            [1, "desc"]
        ],
        lengthChange: !1,
        processing: !1,
        bServerSide: !1,
        searching: !1,
        paging: !1,
        sort: !1,
        info: !1,
        scrollY: "250px",
        scrollCollapse: !1,
        language: {
            emptyTable: Resources.Exchange.HistoryBuyOrdersEmptyList,
            paginate: {
                previous: Resources.General.Previous,
                next: Resources.General.Next
            }
        },
        columnDefs: [{
            targets: [1, 2, 3, 4],
            orderable: !1,
            render: function(n) {
                return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
            }
        }, {
            targets: 0,
            searchable: !1,
            orderable: !1,
            render: function(n, t, i) {
                var r = (+i[1]).toFixed(8);
                return '<div class="orderbook-indicator" data-price="' + r + '"><i class="fa fa-ellipsis-v" aria-hidden="true"><\/i><\/div>'
            }
        }],
        fnDrawCallback: function(n) {
            n.aoData.length > 0 && (setUserOrderIndicator(), setOrderbookSumTotal("#buyorders"))
        }
    }))
}

function updateSellOrdersTable(n) {
    createSellOrdersTable();
    sellOrdersTable.clear().draw();
    n && n.length > 0 && sellOrdersTable.rows.add(n).draw()
}

function clearSellOrdersTable() {
    $("#orderbook-total-sell").text("0.00000000");
    sellOrdersTable && sellOrdersTable.clear().draw()
}

function updateBuyOrdersTable(n) {
    createBuyOrdersTable();
    buyOrdersTable.clear().draw();
    n && n.length > 0 && buyOrdersTable.rows.add(n).draw()
}

function clearBuyOrdersTable() {
    $("#orderbook-total-buy").text("0.00000000");
    buyOrdersTable && buyOrdersTable.clear().draw()
}

function setBuyVolumeIndicator() {
    clearTimeout(setBuyVolumeIndicatorTimeout);
    setBuyVolumeIndicatorTimeout = setTimeout(function() {
        updateOrderBookVolumeIndicator("Buy")
    }, 250)
}

function setSellVolumeIndicator() {
    clearTimeout(setSellVolumeIndicatorTimeout);
    setSellVolumeIndicatorTimeout = setTimeout(function() {
        updateOrderBookVolumeIndicator("Sell")
    }, 250)
}

function updateOrderBookVolumeIndicator(n) {
    var i, r, t;
    n == "Buy" ? (i = $("#orderbook-total-buy").text(), i > 0 && $(".panel-container-buy .table-striped > tbody > tr").each(function() {
        var l, a, v, y, p;
        $(this).children().css({
            "background-size": "0px",
            "background-position-x": "0px"
        });
        var b = $(this).children(":nth-child(5)").text() / i * 100,
            n = ~~Math.max($(this).outerWidth() / 100 * b, 5),
            e = $(this).children(":nth-child(1)"),
            o = $(this).children(":nth-child(2)"),
            s = $(this).children(":nth-child(3)"),
            h = $(this).children(":nth-child(4)"),
            c = $(this).children(":nth-child(5)"),
            w = e.outerWidth(),
            f = o.outerWidth(),
            u = s.outerWidth(),
            r = h.outerWidth(),
            t = c.outerWidth();
        n >= t ? (c.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }), n >= t + r ? (h.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }), n >= t + r + u ? (s.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }), n >= t + r + u + f ? (o.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }), n >= t + r + u + f + w ? e.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }) : (l = n - (t + r + u + f), e.css({
            "background-size": l + "px",
            "background-position-x": w - l + "px"
        }))) : (a = n - (t + r + u), o.css({
            "background-size": a + "px",
            "background-position-x": f - a + "px"
        }))) : (v = n - (t + r), s.css({
            "background-size": v + "px",
            "background-position-x": u - v + "px"
        }))) : (y = n - t, h.css({
            "background-size": y + "px",
            "background-position-x": r - y + "px"
        }))) : (p = n, c.css({
            "background-size": p + "px",
            "background-position-x": t - p + "px"
        }))
    })) : (r = $("#orderbook-total-sell").text(), r > 0 && (t = 0, $(".panel-container-sell .table-striped > tbody > tr").each(function() {
        var a, v, y, p, w;
        $(this).children().css({
            "background-size": "0px",
            "background-position-x": "0px"
        });
        t = t + +$(this).children(":nth-child(3)").text();
        var b = t / r * 100,
            n = ~~Math.max($(this).outerWidth() / 100 * b, 5),
            e = $(this).children(":nth-child(1)"),
            o = $(this).children(":nth-child(2)"),
            s = $(this).children(":nth-child(3)"),
            h = $(this).children(":nth-child(4)"),
            c = $(this).children(":nth-child(5)"),
            i = e.outerWidth(),
            u = o.outerWidth(),
            f = s.outerWidth(),
            l = h.outerWidth(),
            k = c.outerWidth();
        n >= i ? (e.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }), n >= i + u ? (o.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }), n >= i + u + f ? (s.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }), n >= i + u + f + l ? (h.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }), n >= i + u + f + l + k ? c.css({
            "background-size": "100%",
            "background-position-x": "0px"
        }) : (a = n - (i + u + f + l), c.css({
            "background-size": a + "px",
            "background-position-x": "0px"
        }))) : (v = n - (i + u + f), h.css({
            "background-size": v + "px",
            "background-position-x": "0px"
        }))) : (y = n - (i + u), s.css({
            "background-size": y + "px",
            "background-position-x": "0px"
        }))) : (p = n - i, o.css({
            "background-size": p + "px",
            "background-position-x": "0px"
        }))) : (w = n, e.css({
            "background-size": w + "px",
            "background-position-x": "0px"
        }))
    })))
}

function updateOrderbook(n) {
    var t, i, r, u, f, e;
    if (n.Action == 1) t = n.Type == 0 ? "#sellorders" : "#buyorders", i = $(t + " > tbody td > div").filter(function() {
        return +$(this).text() == n.Rate
    }).closest("tr"), updateOrderbookRow(t, i, n);
    else if (n.Action == 0) {
        if (r = n.Type == 0, t = r ? "#buyorders" : "#sellorders", i = $(t + " > tbody td > div").filter(function() {
                return +$(this).text() == n.Rate
            }).closest("tr"), u = $(t + " > tbody tr:first > td:nth-child(2) > div").text() || Resources.Exchange.HistorySellOrdersEmptyList, u === Resources.Exchange.HistorySellOrdersEmptyList || u === Resources.Exchange.HistoryBuyOrdersEmptyList) {
            $(t + " > tbody tr:first").remove();
            appendOrderbookRow(t, n);
            return
        }
        f = $(t + " > tbody tr:last > td:nth-child(2) > div").text();
        e = i.find("td:nth-child(2) > div").text();
        i && e == n.Rate ? updateOrderbookRow(t, i, n) : !r && u > n.Rate || r && n.Rate > u ? prependOrderbookRow(t, n) : !r && n.Rate > +f || r && n.Rate < +f ? appendOrderbookRow(t, n) : insertOrderbookRow(t, n)
    } else n.Action == 3 && (t = n.Type == 0 ? "#buyorders" : "#sellorders", i = $(t + " > tbody td > div").filter(function() {
        return +$(this).text() == n.Rate
    }).closest("tr"), updateOrderbookRow(t, i, n))
}

function appendOrderbookRow(n, t) {
    $(n + " > tbody").append(Mustache.render(orderTemplate, {
        highlight: "greenhighlight",
        price: t.Rate.toFixed(8),
        amount: t.Amount.toFixed(8),
        total: (t.Amount * t.Rate).toFixed(8)
    }));
    setOrderbookSumTotal(n)
}

function prependOrderbookRow(n, t) {
    var i = Mustache.render(orderTemplate, {
            highlight: "greenhighlight",
            price: t.Rate.toFixed(8),
            amount: t.Amount.toFixed(8),
            total: (t.Amount * t.Rate).toFixed(8)
        }),
        r = $(n + " > tbody").prepend(i);
    setOrderbookSumTotal(n)
}

function insertOrderbookRow(n, t) {
    var i = $(n + " > tbody td:nth-child(2) > div").filter(function() {
            return t.Type === 0 ? +$(this).text() < t.Rate : +$(this).text() > t.Rate
        }).first().closest("tr"),
        r = Mustache.render(orderTemplate, {
            highlight: "greenhighlight",
            price: t.Rate.toFixed(8),
            amount: t.Amount.toFixed(8),
            total: (t.Amount * t.Rate).toFixed(8)
        });
    i.before(r);
    setOrderbookSumTotal(n)
}

function updateOrderbookRow(n, t, i) {
    var e = t.find("td:nth-child(3) > div"),
        o = t.find("td:nth-child(4) > div"),
        u = +e.text(),
        r = i.Action == 1 || i.Action == 3 ? (u - i.Amount).toFixed(8) : (u + i.Amount).toFixed(8),
        f = (r * i.Rate).toFixed(8);
    isNaN(r) || isNaN(f) || r <= 0 || f <= 0 ? t.remove() : (e.text(r), o.text(f), i.Action == 3 ? highlightRow(t, "red") : r > u && highlightRow(t, "green"));
    setOrderbookSumTotal(n)
}

function setOrderbookSumTotal(n) {
    n === "#buyorders" && setBuySumTotal();
    n === "#sellorders" && setSellSumTotal();
    updateOrderBookChartThrottle()
}

function setSellSumTotal() {
    clearTimeout(setSellSumTotalTimeout);
    setSellSumTotalTimeout = setTimeout(function() {
        calculateOrderbookSum("#sellorders")
    }, 50)
}

function setBuySumTotal() {
    clearTimeout(setBuySumTotalTimeout);
    setBuySumTotalTimeout = setTimeout(function() {
        calculateOrderbookSum("#buyorders")
    }, 50)
}

function calculateOrderbookSum(n) {
    var t = 0,
        i = 0;
    $(n + " > tbody  > tr").each(function() {
        var n = $(this);
        t += +n.find("td:nth-child(4) > div").text();
        i += +n.find("td:nth-child(3) > div").text();
        n.find("td:nth-child(5) > div").text(t.toFixed(8))
    });
    n === "#buyorders" && $("#orderbook-total-buy").html(t.toFixed(8));
    n === "#sellorders" && $("#orderbook-total-sell").html(i.toFixed(8))
}

function createMarketHistoryTable() {
    marketHistoryTable || ($("#markethistory > tbody").empty(), marketHistoryTable = $("#markethistory").DataTable({
        order: [
            [0, "desc"]
        ],
        lengthChange: !1,
        processing: !1,
        bServerSide: !1,
        sort: !1,
        searching: !1,
        paging: !1,
        info: !1,
        scrollY: "300px",
        scrollCollapse: !1,
        bAutoWidth: !1,
        language: {
            emptyTable: Resources.Exchange.HistoryMarketEmtpyList,
            paginate: {
                previous: Resources.General.Previous,
                next: Resources.General.Next
            }
        },
        fnRowCallback: function(n, t) {
            $(n).addClass("history-" + t[1])
        },
        columnDefs: [{
            targets: [2, 3, 4],
            orderable: !1,
            render: function(n) {
                return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
            }
        }, {
            targets: 0,
            orderable: !1,
            render: function(n) {
                return '<div style="margin-left:8px;white-space: nowrap;">' + toLocalTime(n) + "<\/div>"
            }
        }]
    }))
}

function updateMarketHistoryTable(n) {
    createMarketHistoryTable();
    marketHistoryTable.clear().draw();
    n && n.length > 0 && marketHistoryTable.rows.add(n).draw()
}

function clearMarketHistoryTable() {
    marketHistoryTable && marketHistoryTable.clear().draw()
}

function addMarketHistory(n) {
    var i = $("#markethistory tbody"),
        t;
    i.find("tr > .dataTables_empty").closest("tr").remove();
    t = n.Type === 0 ? "Buy" : "Sell";
    i.prepend(Mustache.render(tradeHistoryTemplate, {
        highlight: n.Type === 0 ? "greenhighlight history-" + t : "redhighlight history-" + t,
        time: toLocalTime(n.Timestamp),
        type: t,
        rate: n.Rate.toFixed(8),
        amount: n.Amount.toFixed(8),
        total: (n.Rate * n.Amount).toFixed(8)
    }))
}

function createUserOpenOrdersTable() {
    userOpenOrdersTable || ($("#useropenorders > tbody").empty(), userOpenOrdersTable = $("#useropenorders").DataTable({
        order: [
            [0, "desc"]
        ],
        lengthChange: !1,
        processing: !1,
        bServerSide: !1,
        searching: !1,
        paging: !1,
        sort: !0,
        info: !1,
        scrollY: "300px",
        scrollCollapse: !0,
        bAutoWidth: !1,
        language: {
            emptyTable: Resources.Exchange.HistoryMyOpenOrdersEmtpyList,
            paginate: {
                previous: Resources.General.Previous,
                next: Resources.General.Next
            }
        },
        fnRowCallback: function(n, t) {
            $(n).addClass("openorder-" + t[6]).addClass("history-" + t[1])
        },
        columnDefs: [{
            targets: [2, 3, 4, 5],
            render: function(n) {
                return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
            }
        }, {
            targets: 0,
            searchable: !0,
            orderable: !0,
            render: function(n) {
                return '<div style="margin-left:8px;white-space: nowrap;">' + toLocalTime(n) + "<\/div>"
            }
        }, {
            targets: -1,
            searchable: !1,
            orderable: !1,
            render: function(n) {
                return '<div class="text-right"><i class="trade-item-remove fa fa-times" data-orderid="' + n + '"><\/i><\/div>'
            }
        }],
        fnDrawCallback: function(n) {
            n.aoData.length > 0 && setUserOrderIndicator()
        }
    }))
}

function updateUserOpenOrdersTable(n) {
    userOpenOrdersTable.clear().draw();
    n && n.length > 0 && userOpenOrdersTable.rows.add(n).draw()
}

function clearUserOpenOrdersTable() {
    userOpenOrdersTable && userOpenOrdersTable.clear().draw()
}

function updateUserOpenOrders(n) {
    var f, r, t, i, u;
    if (n.Action === 0) f = n.Type === 0 ? "Buy" : "Sell", userOpenOrdersTable.row.add([toLocalTime(n.Timestamp), f, n.Rate.toFixed(8), n.Amount.toFixed(8), n.Remaining.toFixed(8), n.Total.toFixed(8), n.OrderId, n.TradePairId]).draw();
    else if (n.Action === 1 || n.Action === 3) t = $("#useropenorders tbody > tr.openorder-" + n.OrderId), userOpenOrdersTable.row(t).remove().draw();
    else if (n.Action === 2)
        for (r = userOpenOrdersTable.rows().data(), t = $("#useropenorders tbody > tr.openorder-" + n.OrderId), i = 0; i < r.length; i++)
            if (u = r[i], u[6] == n.OrderId) {
                u[4] = n.Remaining.toFixed(8);
                userOpenOrdersTable.row(t).invalidate().draw();
                break
            }
    setUserOrderIndicator()
}

function createUserOrderHistoryTable() {
    userOrderHistoryTable || ($("#userorderhistory > tbody").empty(), userOrderHistoryTable = $("#userorderhistory").DataTable({
        order: [
            [0, "desc"]
        ],
        lengthChange: !1,
        processing: !1,
        bServerSide: !1,
        searching: !1,
        paging: !1,
        sort: !1,
        info: !1,
        scrollY: "300px",
        scrollCollapse: !0,
        bAutoWidth: !1,
        language: {
            emptyTable: Resources.Exchange.HistoryMyOrdersEmtpyList,
            paginate: {
                previous: Resources.General.Previous,
                next: Resources.General.Next
            }
        },
        fnRowCallback: function(n, t) {
            $(n).addClass("history-" + t[1])
        },
        columnDefs: [{
            targets: [2, 3, 4],
            orderable: !1,
            render: function(n) {
                return '<div class="text-right">' + (+n || 0).toFixed(8) + "<\/div>"
            }
        }, {
            targets: 0,
            searchable: !1,
            orderable: !0,
            render: function(n) {
                return '<div style="margin-left:8px;white-space: nowrap;">' + toLocalTime(n) + "<\/div>"
            }
        }]
    }))
}

function updateUserOrderHistoryTable(n) {
    userOrderHistoryTable.clear().draw();
    n && n.length > 0 && userOrderHistoryTable.rows.add(n).draw()
}

function clearUserOrderHistoryTable() {
    userOrderHistoryTable && userOrderHistoryTable.clear().draw()
}

function addUserTradeHistory(n) {
    var i = $("#userorderhistory tbody"),
        t;
    i.find("tr > .dataTables_empty").closest("tr").remove();
    t = n.Type === 0 ? "Buy" : "Sell";
    i.prepend(Mustache.render(tradeHistoryTemplate, {
        highlight: n.Type === 0 ? "greenhighlight history-" + t : "redhighlight history-" + t,
        time: toLocalTime(n.Timestamp),
        type: t,
        rate: n.Rate.toFixed(8),
        amount: n.Amount.toFixed(8),
        total: (n.Rate * n.Amount).toFixed(8)
    }))
}

function truncateInputDecimals(n, t) {
    var i = new Decimal(n.val());
    i.dp() >= t && n.val(i.toFixed(8))
}

function calculateFee(n) {
    if (selectedTradePair) {
        var r = new Decimal(selectedTradePair.BaseFee),
            u = new Decimal(selectedTradePair.BaseMinTrade),
            h = new Decimal($("#buyprice").val()),
            c = new Decimal($("#buyamount").val()),
            t = h.mul(c),
            f = t.div(100).mul(r),
            e = t.plus(f);
        $("#buyfee").val(f.toFixed(8));
        $("#buytotal").val(t.toFixed(8));
        n && $("#buynettotal").val(e.toFixed(8, Decimal.ROUND_UP));
        $("#buysubmit").prop("disabled", e.lessThan(u));
        var l = new Decimal($("#sellprice").val()),
            a = new Decimal($("#sellamount").val()),
            i = l.mul(a),
            o = i.div(100).mul(r),
            s = i.minus(o);
        $("#sellfee").val(o.toFixed(8));
        $("#selltotal").val(i.toFixed(8));
        n && $("#sellnettotal").val(s.toFixed(8, Decimal.ROUND_UP));
        $("#sellsubmit").prop("disabled", s.lessThan(u))
    }
}

function clearBuySellInputs() {
    $("#buyamount, #buyprice, #buytotal, #sellamount, #sellprice, #selltotal").val(0..toFixed(8));
    calculateFee(!0)
}

function setUserOrderIndicator() {
    clearTimeout(setUserOrderIndicatorTimeout);
    setUserOrderIndicatorTimeout = setTimeout(function() {
        updateUserOrderIndicator()
    }, 200)
}

function updateUserOrderIndicator() {
    var n = $(".orderbook-table > tbody > tr > td:nth-child(1)");
    n.find(".orderbook-indicator").removeClass("orderbook-indicator-active");
    $("#useropenorders > tbody > tr > td:nth-child(3) > div").each(function() {
        n.find('[data-price="' + $(this).text() + '"]').addClass("orderbook-indicator-active")
    })
}

function clearCharts() {
    clearTradeChart();
    clearOrderBookChart();
    clearDistributionChart()
}

function updateSelectedChart() {
    selectedChart == "trade" ? updateTradeChart() : selectedChart == "distribution" && updateDistributionChart()
}

function resizeCharts() {
    resizeTradeChart();
    resizeOrderBookChart();
    resizeDistributionChart()
}

function createOrderBookChart() {
    orderbookChart || (orderbookChart = new Highcharts.Chart({
        chart: {
            type: "area",
            zoomType: "xy",
            renderTo: "depthdata",
            height: fullChart ? 554 : 354,
            backgroundColor: "transparent",
            margin: [25, 0, 15, 0],
            animation: enableChartAnimations
        },
        title: {
            text: ""
        },
        legend: {
            enabled: !1
        },
        exporting: {
            enabled: !1
        },
        xAxis: {
            type: "linear",
            labels: {
                format: "{value:.8f}",
                y: 15,
                autoRotationLimit: 0,
                padding: 10,
                overflow: !1,
                rotation: 0
            },
            crosshair: !0,
            tickLength: 0,
            maxPadding: 0,
            minPadding: 0,
            allowDecimals: !0,
            endOnTick: !0
        },
        yAxis: [{
            type: "linear",
            labels: {
                format: "{value:.8f}",
                align: "right",
                x: -3,
                y: 0,
                enabled: !0
            },
            offset: 0,
            lineWidth: 1,
            tickPosition: "inside",
            opposite: !0,
            showFirstLabel: !1,
            showLastLabel: !1,
            maxPadding: 0,
            minPadding: 0,
            endOnTick: !1,
            showLastLabel: !0
        }, {
            type: "linear",
            labels: {
                format: "{value:.8f}",
                align: "left",
                x: 3,
                y: 0,
                enabled: !0
            },
            offset: 0,
            linkedTo: 0,
            lineWidth: 1,
            tickPosition: "inside",
            opposite: !1,
            showFirstLabel: !1,
            showLastLabel: !1,
            maxPadding: 0,
            minPadding: 0,
            endOnTick: !1,
            showLastLabel: !0
        }],
        credits: {
            enabled: !1
        },
        tooltip: {
            changeDecimals: 8,
            valueDecimals: 8,
            followPointer: !1,
            formatter: function() {
                return Mustache.render(orderbookTooltipTemplate, {
                    Price: this.x.toFixed(8),
                    Volume: (this.y / this.x).toFixed(8),
                    Depth: this.y.toFixed(8),
                    Symbol: selectedTradePair.Symbol,
                    BaseSymbol: selectedTradePair.BaseSymbol
                })
            }
        },
        series: [{
            name: "Buy",
            data: [],
            color: "#5cb85c",
            fillOpacity: .5,
            lineWidth: 1,
            marker: {
                enabled: !1
            },
            yAxis: 0
        }, {
            name: "Sell",
            color: "#d9534f",
            fillOpacity: .5,
            data: [],
            lineWidth: 1,
            marker: {
                enabled: !1
            },
            yAxis: 0
        }]
    }))
}

function updateOrderBookChart() {
    var n, t, i, r, u;
    if (createOrderBookChart(), n = [], $("#buyorders tbody > tr").each(function() {
            var t = $(this),
                i = +t.find("td:nth-child(2)").text(),
                r = +t.find("td:nth-child(5)").text();
            i && r && n.push([i, r])
        }), t = [], $("#sellorders tbody > tr").each(function() {
            var n = $(this),
                i = +n.find("td:nth-child(2)").text(),
                r = +n.find("td:nth-child(5)").text();
            i && r && t.push([i, r])
        }), i = 0, r = 0, n.length > 0 && t.length > 0 && (u = (+t[0][0] + +n[0][0]) / 2, orderBookChartPercent == 25 && n.length >= 4 && t.length >= 4 ? (n = n.splice(0, n.length / 4), t = t.splice(0, t.length / 4), i = n[n.length - 1][0], r = t[t.length - 1][0]) : orderBookChartPercent == 50 && n.length >= 2 && t.length >= 2 ? (n = n.splice(0, n.length / 2), t = t.splice(0, t.length / 2), i = n[n.length - 1][0], r = t[t.length - 1][0]) : orderBookChartPercent == 100 ? (i = n[n.length - 1][0], r = t[t.length - 1][0]) : (i = Math.max(n[n.length - 1][0], u * .1), r = Math.min(t[t.length - 1][0], u * 1.8)), n.reverse()), n.length == 0 && t.length == 0) {
        $(".chart-orderbook-nodata").show();
        return
    }
    orderbookChart && (orderbookChart.showLoading(), orderbookChart.series[0].setData(n, !1, !1, !1), orderbookChart.series[1].setData(t, !1, !1, !1), orderbookChart.xAxis[0].setExtremes(i, r, !1, !1, !1), orderbookChart.reflow(), orderbookChart.hideLoading(), orderbookChart.update({
        chart: {
            height: fullChart ? 554 : 354,
            width: $("#depthdata").width()
        }
    }, !0))
}

function clearOrderBookChart() {
    orderbookChart && (orderbookChart.series[0].setData([
        [0, 0]
    ], !1, !1, !1), orderbookChart.series[1].setData([
        [0, 0]
    ], !1, !1, !1), orderbookChart.update({
        chart: {
            height: fullChart ? 554 : 354,
            width: $("#depthdata").width()
        }
    }, !0))
}

function resizeOrderBookChart() {
    orderbookChart && (orderbookChart.reflow(), orderbookChart.update({
        chart: {
            height: fullChart ? 554 : 354,
            width: $("#depthdata").width()
        }
    }, !0))
}

function updateOrderBookChartThrottle() {
    selectedChart == "orderbook" && (clearTimeout(updateOrderBookChartThrottleTimeout), updateOrderBookChartThrottleTimeout = setTimeout(function() {
        updateOrderBookChart()
    }, orderBookChartThrottle))
}

function createDistributionChart() {
    distributionChart || (distributionChart = new Highcharts.Chart({
        chart: {
            type: "column",
            renderTo: "distributiondata",
            height: fullChart ? 540 : 340,
            backgroundColor: "transparent",
            margin: [0, 0, 0, 0]
        },
        title: {
            text: ""
        },
        credits: {
            enabled: !1
        },
        exporting: {
            enabled: !1
        },
        xAxis: {
            labels: {
                enabled: !1
            },
            crosshair: !0,
            tickLength: 0,
            maxPadding: 0,
            minPadding: 0
        },
        yAxis: [{
            type: "linear",
            labels: {
                format: "{value:.8f}",
                align: "right",
                x: -3,
                y: 0
            },
            title: {
                enabled: !1
            },
            offset: 0,
            lineWidth: 2,
            tickPosition: "inside",
            opposite: !0,
            showFirstLabel: !1,
            showLastLabel: !1,
            maxPadding: 0,
            minPadding: 0,
            lineColor: "transparent"
        }],
        tooltip: {
            headerFormat: "<span><\/span>",
            pointFormatter: function() {
                return '<span  style="white-space:nowrap">' + this.y.toFixed(8) + " " + selectedTradePair.Symbol + "<\/span>"
            },
            shared: !0,
            useHTML: !0
        },
        plotOptions: {
            column: {
                pointPadding: 0,
                borderWidth: 0
            }
        },
        series: [{
            name: " ",
            showInLegend: !1,
            minPointLength: 10,
            data: []
        }]
    }))
}

function updateDistributionChart() {
    createDistributionChart();
    distributionChart && ($(".chart-distribution-loading").show(), getData(actionDistributionChart, {
        currencyId: selectedTradePair.CurrencyId,
        count: distributionChartCount
    }, function(n) {
        var t = n ? n.Distribution : [];
        if (t.length == 0) {
            $(".chart-distribution-nodata").show();
            return
        }
        distributionChart && (distributionChart.showLoading(), distributionChart.series[0].setData(t), distributionChart.reflow(), distributionChart.hideLoading(), distributionChart.update({
            chart: {
                height: fullChart ? 540 : 340,
                width: $("#distributiondata").width()
            }
        }, !0));
        $(".chart-distribution-loading").hide()
    }))
}

function clearDistributionChart() {
    distributionChart && distributionChart.series[0].setData([])
}

function resizeDistributionChart() {
    distributionChart && (distributionChart.reflow(), distributionChart.update({
        chart: {
            height: fullChart ? 540 : 340,
            width: $("#distributiondata").width()
        }
    }, !0))
}

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

function clearTradeChart() {
    tradechart && (tradechart.series[0].setData([
        [0, 0, 0, 0, 0, 0]
    ], !1, !1, !1), tradechart.series[1].setData([
        [0, 0, 0, 0, 0, 0]
    ], !1, !1, !1), tradechart.series[2].setData([
        [0, 0]
    ], !1, !1, !1), tradechart.redraw())
}

function resizeTradeChart() {
    tradechart && (tradechart.reflow(), setBorders())
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

function updateChartData(n, t) {
    selectedSeriesRange = n;
    selectedCandleGrouping = getCandleGrouping(n, t);
    $(".chart-nodata").hide();
    $(".chart-loading").show();
    getTradePairChartRequest && getTradePairChartRequest.readyState != 4 && getTradePairChartRequest.abort();
    getTradePairChartRequest = getData(actionTradeChart, {
        tradePairId: selectedTradePair.TradePairId,
        dataRange: selectedSeriesRange,
        dataGroup: selectedCandleGrouping
    }, function(n) {
        updateChart(n)
    })
}

function toggleFullChart() {
    if (macdChart || signalChart || histogramChart) {
        fullChart = !0;
        tradechart.yAxis[0].height != 300 && (tradechart.yAxis[0].update({
            height: 300
        }, !1), tradechart.yAxis[1].update({
            height: 300
        }, !1), tradechart.yAxis[2].update({
            height: 100
        }, !1), tradechart.update({
            chart: {
                height: 554
            }
        }, !1), $(".chart-container").height(565), tradechart.redraw(!0), toggleFibonacci(fibonacciChart), setBorders());
        return
    }
    fullChart = !1;
    tradechart.yAxis[0].height != 225 && (tradechart.yAxis[0].update({
        height: 225
    }, !1), tradechart.yAxis[1].update({
        height: 225
    }, !1), tradechart.yAxis[2].update({
        height: 0
    }, !1), tradechart.update({
        chart: {
            height: 354
        }
    }, !1), $(".chart-container").height(365), tradechart.redraw(!0), toggleFibonacci(fibonacciChart), setBorders())
}

function updateSeriesRange(n) {
    tradechart && ($(".chart-candles-group > .btn-default").removeClass("active").attr("disabled", "disabled"), n == 0 ? $(".chart-candles-btn15, .chart-candles-btn30, .chart-candles-btn60, .chart-candles-btn120").removeAttr("disabled") : n == 1 ? $(".chart-candles-btn15, .chart-candles-btn30, .chart-candles-btn60, .chart-candles-btn120").removeAttr("disabled") : n == 2 ? $(".chart-candles-btn60, .chart-candles-btn120, .chart-candles-btn240, .chart-candles-btn720").removeAttr("disabled") : n == 3 ? $(".chart-candles-btn120, .chart-candles-btn240, .chart-candles-btn720").removeAttr("disabled") : n == 4 ? $(".chart-candles-btn240, .chart-candles-btn720, .chart-candles-btn1440").removeAttr("disabled") : n == 5 ? $(".chart-candles-btn240, .chart-candles-btn720, .chart-candles-btn1440, .chart-candles-btn10080").removeAttr("disabled") : n == 6 ? $(".chart-candles-btn720, .chart-candles-btn1440, .chart-candles-btn10080").removeAttr("disabled") : n == 7 && $(".chart-candles-btn1440, .chart-candles-btn10080").removeAttr("disabled"), $(".chart-range-group > .btn-default").removeClass("active"), $(".chart-range-btn" + n).addClass("active"), updateChartData(n, selectedCandleGrouping), $(".chart-candles-btn" + selectedCandleGrouping).addClass("active"))
}

function getCandleGrouping(n, t) {
    return n == 0 && t > 120 ? 30 : n == 1 && t > 120 ? 60 : n == 2 && (t > 720 || t < 60) ? 120 : n == 3 && (t > 720 || t < 120) ? 120 : n == 4 && (t > 1440 || t < 240) ? 240 : n == 5 && t < 240 ? 720 : n == 6 && t < 720 ? 720 : n == 7 && t < 1440 ? 1440 : t
}

function toggleSeries(n, t, i) {
    n == 0 && toggleStockPrice(i);
    n == 1 && toggleCandleStick(i);
    n == 2 && toggleVolume(i);
    n == 3 && (smaChartValue = t, toggleSMA(i, t));
    n == 4 && (ema1ChartValue = t, toggleEMA1(i, t));
    n == 5 && (ema2ChartValue = t, toggleEMA2(i, t));
    n == 6 && toggleMACD(i);
    n == 7 && toggleSignal(i);
    n == 8 && toggleHistogram(i);
    n == 9 && toggleFibonacci(i)
}

function toggleCandleStick(n) {
    if (candlestickChart = n, n) {
        tradechart.series[1].show();
        $(".chart-candlestick-item").show();
        toggleFibonacci(fibonacciChart);
        return
    }
    tradechart.series[1].hide();
    $(".chart-candlestick-item").hide();
    toggleFibonacci(fibonacciChart)
}

function toggleStockPrice(n) {
    if (stockPriceChart = n, n) {
        tradechart.series[0].update({
            lineWidth: 1
        });
        $(".chart-stockprice-item").show();
        return
    }
    tradechart.series[0].update({
        lineWidth: 0
    });
    $(".chart-stockprice-item").hide()
}

function toggleVolume(n) {
    if (volumeChart = n, n) {
        tradechart.series[2].show();
        $(".chart-volume-item").show();
        return
    }
    tradechart.series[2].hide();
    $(".chart-volume-item").hide()
}

function toggleSMA(n, t) {
    if (smaChart = n, smaChartValue = t, tradechart.series[3].update({
            periods: smaChartValue
        }), n) {
        tradechart.series[3].show();
        $(".chart-sma-item").show();
        return
    }
    tradechart.series[3].hide();
    $(".chart-sma-item").hide()
}

function toggleEMA1(n, t) {
    if (ema1Chart = n, ema1ChartValue = t, tradechart.series[4].update({
            periods: ema1ChartValue
        }), n) {
        tradechart.series[4].show();
        $(".chart-ema1-item").show();
        return
    }
    tradechart.series[4].hide();
    $(".chart-ema1-item").hide()
}

function toggleEMA2(n, t) {
    if (ema2Chart = n, ema2ChartValue = t, tradechart.series[5].update({
            periods: ema2ChartValue
        }), n) {
        tradechart.series[5].show();
        $(".chart-ema2-item").show();
        return
    }
    tradechart.series[5].hide();
    $(".chart-ema2-item").hide()
}

function toggleMACD(n) {
    if (n) {
        macdChart = !0;
        tradechart.series[6].show();
        toggleFullChart();
        $(".chart-macd-item").show();
        return
    }
    macdChart = !1;
    tradechart.series[6].hide();
    toggleFullChart();
    $(".chart-macd-item").hide()
}

function toggleSignal(n) {
    if (n) {
        signalChart = !0;
        tradechart.series[7].show();
        toggleFullChart();
        $(".chart-signal-item").show();
        return
    }
    signalChart = !1;
    tradechart.series[7].hide();
    toggleFullChart();
    $(".chart-signal-item").hide()
}

function toggleHistogram(n) {
    if (n) {
        histogramChart = !0;
        tradechart.series[8].show();
        toggleFullChart();
        $(".chart-histogram-item").show();
        return
    }
    histogramChart = !1;
    tradechart.series[8].hide();
    toggleFullChart();
    $(".chart-histogram-item").hide()
}

function toggleFibonacci(n) {
    var u, o, t, b, k, p, f, i, ut, ft, e, r;
    if (fibonacciChart = n, n && (candlestickChart || stockPriceChart)) {
        if (t = tradechart.yAxis[0].getExtremes(), b = t.dataMax != t.dataMin, b) {
            if (k = candlestickChart ? 1 : 0, p = tradechart.series[k].points.length, candlestickChart)
                for (f = p; f > 0; f--)(i = tradechart.series[1].points[f], i != null) && ((!u || i.x < u) && i.low == t.dataMin && (u = i.x), o || i.high != t.dataMax || (o = i.x));
            if (stockPriceChart && !candlestickChart)
                for (f = p; f > 0; f--)(i = tradechart.series[0].points[f], i != null) && ((!u || i.x < u) && i.y == t.dataMin && (u = i.x), o || i.y != t.dataMax || (o = i.x));
            var et = t.dataMax - t.dataMin,
                c = et / 100,
                s = tradechart.yAxis[0].toPixels(t.dataMin),
                l = tradechart.yAxis[0].toPixels(t.dataMin + c * 23.6),
                a = tradechart.yAxis[0].toPixels(t.dataMin + c * 38.2),
                v = tradechart.yAxis[0].toPixels(t.dataMin + c * 50),
                y = tradechart.yAxis[0].toPixels(t.dataMin + c * 61.8),
                h = tradechart.yAxis[0].toPixels(t.dataMax),
                d = ["M", tradechart.plotLeft, s, "L", tradechart.plotLeft + tradechart.plotWidth, s],
                g = ["M", tradechart.plotLeft, l, "L", tradechart.plotLeft + tradechart.plotWidth, l],
                nt = ["M", tradechart.plotLeft, a, "L", tradechart.plotLeft + tradechart.plotWidth, a],
                tt = ["M", tradechart.plotLeft, v, "L", tradechart.plotLeft + tradechart.plotWidth, v],
                it = ["M", tradechart.plotLeft, y, "L", tradechart.plotLeft + tradechart.plotWidth, y],
                rt = ["M", tradechart.plotLeft, h, "L", tradechart.plotLeft + tradechart.plotWidth, h],
                w;
            u && o && (ut = tradechart.xAxis[0].toPixels(u), ft = tradechart.xAxis[0].toPixels(o), w = ["M", ft, h, "L", ut, s]);
            e = {
                "stroke-width": .5,
                stroke: fibonacciChartColor,
                zIndex: 100
            };
            r = {
                zIndex: 100,
                css: {
                    fontSize: "11px",
                    color: fibonacciChartColor
                }
            };
            tradechart.fib1 ? (tradechart.fib1.attr({
                d: d
            }), tradechart.fib1Label.attr({
                y: s - 2,
                text: "0%"
            })) : (tradechart.fib1 = tradechart.renderer.path(d).attr(e).add(), tradechart.fib1Label = tradechart.renderer.text("0%", 0, s - 2).css(r.css).attr(r).add());
            tradechart.fib2 ? (tradechart.fib2.attr({
                d: g
            }), tradechart.fib2Label.attr({
                y: l - 2,
                text: "23.6%"
            })) : (tradechart.fib2 = tradechart.renderer.path(g).attr(e).add(), tradechart.fib2Label = tradechart.renderer.text("23.6%", 0, l - 2).css(r.css).attr(r).add());
            tradechart.fib3 ? (tradechart.fib3.attr({
                d: nt
            }), tradechart.fib3Label.attr({
                y: a - 2,
                text: "38.2%"
            })) : (tradechart.fib3 = tradechart.renderer.path(nt).attr(e).add(), tradechart.fib3Label = tradechart.renderer.text("38.2%", 0, a - 2).css(r.css).attr(r).add());
            tradechart.fib4 ? (tradechart.fib4.attr({
                d: tt
            }), tradechart.fib4Label.attr({
                y: v - 2,
                text: "50%"
            })) : (tradechart.fib4 = tradechart.renderer.path(tt).attr(e).add(), tradechart.fib4Label = tradechart.renderer.text("50%", 0, v - 2).css(r.css).attr(r).add());
            tradechart.fib5 ? (tradechart.fib5.attr({
                d: it
            }), tradechart.fib5Label.attr({
                y: y - 2,
                text: "61.8%"
            })) : (tradechart.fib5 = tradechart.renderer.path(it).attr(e).add(), tradechart.fib5Label = tradechart.renderer.text("61.8%", 0, y - 2).css(r.css).attr(r).add());
            tradechart.fib6 ? (tradechart.fib6.attr({
                d: rt
            }), tradechart.fib6Label.attr({
                y: h - 2,
                text: "100%"
            })) : (tradechart.fib6 = tradechart.renderer.path(rt).attr(e).add(), tradechart.fib6Label = tradechart.renderer.text("100%", 0, h - 2).css(r.css).attr(r).add());
            tradechart.fibd ? tradechart.fibd.attr({
                d: w
            }) : tradechart.fibd = tradechart.renderer.path(w).attr(e).add()
        }
        return
    }
    tradechart.fibd && (tradechart.fibd.attr({
        d: []
    }), tradechart.fib1.attr({
        d: []
    }), tradechart.fib2.attr({
        d: []
    }), tradechart.fib3.attr({
        d: []
    }), tradechart.fib4.attr({
        d: []
    }), tradechart.fib5.attr({
        d: []
    }), tradechart.fib6.attr({
        d: []
    }), tradechart.fib1Label.attr({
        y: 0,
        text: ""
    }), tradechart.fib2Label.attr({
        y: 0,
        text: ""
    }), tradechart.fib3Label.attr({
        y: 0,
        text: ""
    }), tradechart.fib4Label.attr({
        y: 0,
        text: ""
    }), tradechart.fib5Label.attr({
        y: 0,
        text: ""
    }), tradechart.fib6Label.attr({
        y: 0,
        text: ""
    }))
}

function setBorders() {
    var n = {
            "stroke-width": .2,
            stroke: chartBorderColor,
            zIndex: 200
        },
        r = ["M", tradechart.plotLeft, 32, "L", tradechart.plotLeft + tradechart.plotWidth, 32],
        t, i;
    tradechart.topBorder ? tradechart.topBorder.attr({
        d: r
    }) : tradechart.topBorder = tradechart.renderer.path(r).attr(n).add();
    fullChart ? (t = ["M", tradechart.plotLeft, 336, "L", tradechart.plotLeft + tradechart.plotWidth, 336], i = ["M", tradechart.plotLeft, 360, "L", tradechart.plotLeft + tradechart.plotWidth, 360], tradechart.bottomBorder ? (tradechart.bottomBorder.attr({
        d: t
    }), tradechart.bottomBorder2.attr({
        d: i
    })) : (tradechart.bottomBorder = tradechart.renderer.path(t).attr(n).add(), tradechart.bottomBorder2 = tradechart.renderer.path(i).attr(n).add())) : tradechart.bottomBorder && (tradechart.bottomBorder.attr({
        d: []
    }), tradechart.bottomBorder2.attr({
        d: []
    }))
}

function drawHorizontalCrosshair(n) {
    var e = n.pageX,
        t = n.offsetY;
    path = ["M", tradechart.plotLeft, t, "L", tradechart.plotLeft + tradechart.plotWidth, t];
    var i, r = t - tradechart.plotTop,
        u = tradechart.yAxis[0].len,
        f = tradechart.yAxis[2].len;
    if (r >= 0 && r <= u) i = tradechart.yAxis[0].toValue(t).toFixed(8);
    else if (r >= 325 && r <= 325 + f) i = tradechart.yAxis[2].toValue(t).toFixed(8);
    else {
        tradechart.crossLines && tradechart.crossLabel && (tradechart.crossLabel.attr({
            y: 0,
            text: ""
        }), tradechart.crossLines.attr({
            d: []
        }));
        return
    }
    i && (tradechart.crossLines ? tradechart.crossLines.attr({
        d: path
    }) : tradechart.crossLines = tradechart.renderer.path(path).attr({
        "stroke-width": .2,
        stroke: chartCrossHairColor,
        zIndex: 100
    }).add(), tradechart.crossLabel ? tradechart.crossLabel.attr({
        x: tradechart.plotWidth - 2,
        y: t - 2,
        text: i
    }) : tradechart.crossLabel = tradechart.renderer.text(i, tradechart.plotWidth - 2, t - 2).css({
        fontSize: "11px",
        color: chartTextColor
    }).attr({
        zIndex: 100,
        align: "right"
    }).add())
}

function saveChartSettings() {
    var n = "";
    n += volumeChart ? "1," : "0,";
    n += stockPriceChart ? "1," : "0,";
    n += candlestickChart ? "1," : "0,";
    n += macdChart ? "1," : "0,";
    n += signalChart ? "1," : "0,";
    n += histogramChart ? "1," : "0,";
    n += fibonacciChart ? "1," : "0,";
    n += smaChart ? "1:" + smaChartValue + "," : "0:" + smaChartValue + ",";
    n += ema1Chart ? "1:" + ema1ChartValue + "," : "0:" + ema1ChartValue + ",";
    n += ema2Chart ? "1:" + ema2ChartValue + "," : "0:" + ema2ChartValue + ",";
    n += distributionChartCount + ",";
    n += orderBookChartPercent;
    postJson(actionUpdateChartSettings, {
        settings: n
    }, function(n) {
        notify(n.Success ? Resources.Exchange.InfoSettingsSavedMessage : Resources.Exchange.InfoSettingsFailedMessage, n.Message)
    })
}
var sideMenuBalanceTable, sideMenuOpenOrdersTable, buyOrdersTable, sellOrdersTable, marketHistoryTable, userOpenOrdersTable, userOrderHistoryTable, marketTable, getTradePairDataRequest, getUserTradePairDataRequest, getTradePairBalanceRequest, getTradePairChartRequest, getCurrencySummaryRequest, updateMarketFavoritesTimeout, currentTradePairGroupId, setBuyVolumeIndicatorTimeout, setSellVolumeIndicatorTimeout, setSellSumTotalTimeout, setBuySumTotalTimeout, setUserOrderIndicatorTimeout, updateOrderBookChartThrottleTimeout;
(function(n, t) {
    "object" == typeof module && module.exports ? module.exports = n.document ? t(n) : t : n.Highcharts = t(n)
})("undefined" != typeof window ? window : this, function(n) {
    return n = function() {
            var n = window,
                t = n.document,
                i = n.navigator && n.navigator.userAgent || "",
                r = t && t.createElementNS && !!t.createElementNS("http://www.w3.org/2000/svg", "svg").createSVGRect,
                f = /(edge|msie|trident)/i.test(i) && !window.opera,
                e = !r,
                u = /Firefox/.test(i),
                o = u && 4 > parseInt(i.split("Firefox/")[1], 10);
            return n.Highcharts ? n.Highcharts.error(16, !0) : {
                product: "Highstock",
                version: "5.0.7",
                deg2rad: Math.PI / 180,
                doc: t,
                hasBidiBug: o,
                hasTouch: t && void 0 !== t.documentElement.ontouchstart,
                isMS: f,
                isWebKit: /AppleWebKit/.test(i),
                isFirefox: u,
                isTouchDevice: /(Mobile|Android|Windows Phone)/.test(i),
                SVG_NS: "http://www.w3.org/2000/svg",
                chartCount: 0,
                seriesTypes: {},
                symbolSizes: {},
                svg: r,
                vml: e,
                win: n,
                charts: [],
                marginNames: ["plotTop", "marginRight", "marginBottom", "plotLeft"],
                noop: function() {}
            }
        }(),
        function(n) {
            var t = [],
                u = n.charts,
                r = n.doc,
                i = n.win;
            n.error = function(t, r) {
                if (t = n.isNumber(t) ? "Highcharts error #" + t + ": www.highcharts.com/errors/" + t : t, r) throw Error(t);
                i.console && console.log(t)
            };
            n.Fx = function(n, t, i) {
                this.options = t;
                this.elem = n;
                this.prop = i
            };
            n.Fx.prototype = {
                dSetter: function() {
                    var r = this.paths[0],
                        u = this.paths[1],
                        t = [],
                        f = this.now,
                        n = r.length,
                        i;
                    if (1 === f) t = this.toD;
                    else if (n === u.length && 1 > f)
                        for (; n--;) i = parseFloat(r[n]), t[n] = isNaN(i) ? r[n] : f * parseFloat(u[n] - i) + i;
                    else t = u;
                    this.elem.attr("d", t, null, !0)
                },
                update: function() {
                    var n = this.elem,
                        t = this.prop,
                        i = this.now,
                        r = this.options.step;
                    this[t + "Setter"] ? this[t + "Setter"]() : n.attr ? n.element && n.attr(t, i, null, !0) : n.style[t] = i + this.unit;
                    r && r.call(n, i, this)
                },
                run: function(n, i, r) {
                    var e = this,
                        u = function(n) {
                            return u.stopped ? !1 : e.step(n)
                        },
                        f;
                    this.startTime = +new Date;
                    this.start = n;
                    this.end = i;
                    this.unit = r;
                    this.now = this.start;
                    this.pos = 0;
                    u.elem = this.elem;
                    u.prop = this.prop;
                    u() && 1 === t.push(u) && (u.timerId = setInterval(function() {
                        for (f = 0; f < t.length; f++) t[f]() || t.splice(f--, 1);
                        t.length || clearInterval(u.timerId)
                    }, 13))
                },
                step: function(n) {
                    var u = +new Date,
                        t, i = this.options;
                    t = this.elem;
                    var f = i.complete,
                        e = i.duration,
                        r = i.curAnim,
                        o;
                    if (t.attr && !t.element) t = !1;
                    else if (n || u >= e + this.startTime) {
                        this.now = this.end;
                        this.pos = 1;
                        this.update();
                        n = r[this.prop] = !0;
                        for (o in r) !0 !== r[o] && (n = !1);
                        n && f && f.call(t);
                        t = !1
                    } else this.pos = i.easing((u - this.startTime) / e), this.now = this.start + (this.end - this.start) * this.pos, this.update(), t = !0;
                    return t
                },
                initPath: function(t, i, r) {
                    function y(n) {
                        var t, i;
                        for (u = n.length; u--;) t = "M" === n[u] || "L" === n[u], i = /[a-zA-Z]/.test(n[u + 3]), t && i && n.splice(u + 1, 0, n[u + 1], n[u + 2], n[u + 1], n[u + 2])
                    }

                    function p(n, t) {
                        for (; n.length < c;) {
                            n[0] = t[c - n.length];
                            var i = n.slice(0, f);
                            [].splice.apply(n, [0, 0].concat(i));
                            l && (i = n.slice(n.length - f), [].splice.apply(n, [n.length, 0].concat(i)), u--)
                        }
                        n[0] = "M"
                    }

                    function w(n, t) {
                        for (var i = (c - n.length) / f; 0 < i && i--;) e = n.slice().splice(n.length / o - f, f * o), e[0] = t[c - f - i * f], v && (e[f - 6] = e[f - 2], e[f - 5] = e[f - 1]), [].splice.apply(n, [n.length / o, 0].concat(e)), l && i--
                    }
                    var l, o, b;
                    i = i || "";
                    var s, h = t.startX,
                        a = t.endX,
                        v = -1 < i.indexOf("C"),
                        f = v ? 7 : 3,
                        c, e, u;
                    if (i = i.split(" "), r = r.slice(), l = t.isArea, o = l ? 2 : 1, v && (y(i), y(r)), h && a) {
                        for (u = 0; u < h.length; u++)
                            if (h[u] === a[0]) {
                                s = u;
                                break
                            } else if (h[0] === a[a.length - h.length + u]) {
                            s = u;
                            b = !0;
                            break
                        }
                        void 0 === s && (i = [])
                    }
                    return i.length && n.isNumber(s) && (c = r.length + s * o * f, b ? (p(i, r), w(r, i)) : (p(r, i), w(i, r))), [i, r]
                }
            };
            n.extend = function(n, t) {
                var i;
                n || (n = {});
                for (i in t) n[i] = t[i];
                return n
            };
            n.merge = function() {
                var i, t = arguments,
                    u, r = {},
                    f = function(t, i) {
                        var u, r;
                        "object" != typeof t && (t = {});
                        for (r in i) i.hasOwnProperty(r) && (u = i[r], t[r] = n.isObject(u, !0) && "renderTo" !== r && "number" != typeof u.nodeType ? f(t[r] || {}, u) : i[r]);
                        return t
                    };
                for (!0 === t[0] && (r = t[1], t = Array.prototype.slice.call(t, 2)), u = t.length, i = 0; i < u; i++) r = f(r, t[i]);
                return r
            };
            n.pInt = function(n, t) {
                return parseInt(n, t || 10)
            };
            n.isString = function(n) {
                return "string" == typeof n
            };
            n.isArray = function(n) {
                return n = Object.prototype.toString.call(n), "[object Array]" === n || "[object Array Iterator]" === n
            };
            n.isObject = function(t, i) {
                return t && "object" == typeof t && (!i || !n.isArray(t))
            };
            n.isNumber = function(n) {
                return "number" == typeof n && !isNaN(n)
            };
            n.erase = function(n, t) {
                for (var i = n.length; i--;)
                    if (n[i] === t) {
                        n.splice(i, 1);
                        break
                    }
            };
            n.defined = function(n) {
                return void 0 !== n && null !== n
            };
            n.attr = function(t, i, r) {
                var u, f;
                if (n.isString(i)) n.defined(r) ? t.setAttribute(i, r) : t && t.getAttribute && (f = t.getAttribute(i));
                else if (n.defined(i) && n.isObject(i))
                    for (u in i) t.setAttribute(u, i[u]);
                return f
            };
            n.splat = function(t) {
                return n.isArray(t) ? t : [t]
            };
            n.syncTimeout = function(n, t, i) {
                if (t) return setTimeout(n, t, i);
                n.call(0, i)
            };
            n.pick = function() {
                for (var i = arguments, n, r = i.length, t = 0; t < r; t++)
                    if (n = i[t], void 0 !== n && null !== n) return n
            };
            n.css = function(t, i) {
                n.isMS && !n.svg && i && void 0 !== i.opacity && (i.filter = "alpha(opacity=" + 100 * i.opacity + ")");
                n.extend(t.style, i)
            };
            n.createElement = function(t, i, u, f, e) {
                t = r.createElement(t);
                var o = n.css;
                return i && n.extend(t, i), e && o(t, {
                    padding: 0,
                    border: "none",
                    margin: 0
                }), u && o(t, u), f && f.appendChild(t), t
            };
            n.extendClass = function(t, i) {
                var r = function() {};
                return r.prototype = new t, n.extend(r.prototype, i), r
            };
            n.pad = function(n, t, i) {
                return Array((t || 2) + 1 - String(n).length).join(i || 0) + n
            };
            n.relativeLength = function(n, t) {
                return /%$/.test(n) ? t * parseFloat(n) / 100 : parseFloat(n)
            };
            n.wrap = function(n, t, i) {
                var r = n[t];
                n[t] = function() {
                    var n = Array.prototype.slice.call(arguments),
                        u = arguments,
                        t = this;
                    return t.proceed = function() {
                        r.apply(t, arguments.length ? arguments : u)
                    }, n.unshift(r), n = i.apply(this, n), t.proceed = null, n
                }
            };
            n.getTZOffset = function(t) {
                var i = n.Date;
                return 6e4 * (i.hcGetTimezoneOffset && i.hcGetTimezoneOffset(t) || i.hcTimezoneOffset || 0)
            };
            n.dateFormat = function(t, i, r) {
                if (!n.defined(i) || isNaN(i)) return n.defaultOptions.lang.invalidDate || "";
                t = n.pick(t, "%Y-%m-%d %H:%M:%S");
                var u = n.Date,
                    e = new u(i - n.getTZOffset(i)),
                    o, s = e[u.hcGetHours](),
                    h = e[u.hcGetDay](),
                    a = e[u.hcGetDate](),
                    l = e[u.hcGetMonth](),
                    v = e[u.hcGetFullYear](),
                    c = n.defaultOptions.lang,
                    y = c.weekdays,
                    p = c.shortWeekdays,
                    f = n.pad,
                    u = n.extend({
                        a: p ? p[h] : y[h].substr(0, 3),
                        A: y[h],
                        d: f(a),
                        e: f(a, 2, " "),
                        w: h,
                        b: c.shortMonths[l],
                        B: c.months[l],
                        m: f(l + 1),
                        y: v.toString().substr(2, 2),
                        Y: v,
                        H: f(s),
                        k: s,
                        I: f(s % 12 || 12),
                        l: s % 12 || 12,
                        M: f(e[u.hcGetMinutes]()),
                        p: 12 > s ? "AM" : "PM",
                        P: 12 > s ? "am" : "pm",
                        S: f(e.getSeconds()),
                        L: f(Math.round(i % 1e3), 3)
                    }, n.dateFormats);
                for (o in u)
                    for (; - 1 !== t.indexOf("%" + o);) t = t.replace("%" + o, "function" == typeof u[o] ? u[o](i) : u[o]);
                return r ? t.substr(0, 1).toUpperCase() + t.substr(1) : t
            };
            n.formatSingle = function(t, i) {
                var r = /\.([0-9])/,
                    u = n.defaultOptions.lang;
                return /f$/.test(t) ? (r = (r = t.match(r)) ? r[1] : -1, null !== i && (i = n.numberFormat(i, r, u.decimalPoint, -1 < t.indexOf(",") ? u.thousandsSep : ""))) : i = n.dateFormat(t, i), i
            };
            n.format = function(t, i) {
                for (var u = "{", s = !1, r, h, e, c, o = [], f; t;) {
                    if (u = t.indexOf(u), -1 === u) break;
                    if (r = t.slice(0, u), s) {
                        for (r = r.split(":"), h = r.shift().split("."), c = h.length, f = i, e = 0; e < c; e++) f = f[h[e]];
                        r.length && (f = n.formatSingle(r.join(":"), f));
                        o.push(f)
                    } else o.push(r);
                    t = t.slice(u + 1);
                    u = (s = !s) ? "}" : "{"
                }
                return o.push(t), o.join("")
            };
            n.getMagnitude = function(n) {
                return Math.pow(10, Math.floor(Math.log(n) / Math.LN10))
            };
            n.normalizeTickInterval = function(t, i, r, u, f) {
                var o, e = t;
                for (r = n.pick(r, 1), o = t / r, i || (i = f ? [1, 1.2, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10] : [1, 2, 2.5, 5, 10], !1 === u && (1 === r ? i = n.grep(i, function(n) {
                        return 0 == n % 1
                    }) : .1 >= r && (i = [1 / r]))), u = 0; u < i.length && !(e = i[u], f && e * r >= t || !f && o <= (i[u] + (i[u + 1] || i[u])) / 2); u++);
                return n.correctFloat(e * r, -Math.round(Math.log(.001) / Math.LN10))
            };
            n.stableSort = function(n, t) {
                for (var u = n.length, r, i = 0; i < u; i++) n[i].safeI = i;
                for (n.sort(function(n, i) {
                        return r = t(n, i), 0 === r ? n.safeI - i.safeI : r
                    }), i = 0; i < u; i++) delete n[i].safeI
            };
            n.arrayMin = function(n) {
                for (var t = n.length, i = n[0]; t--;) n[t] < i && (i = n[t]);
                return i
            };
            n.arrayMax = function(n) {
                for (var t = n.length, i = n[0]; t--;) n[t] > i && (i = n[t]);
                return i
            };
            n.destroyObjectProperties = function(n, t) {
                for (var i in n) n[i] && n[i] !== t && n[i].destroy && n[i].destroy(), delete n[i]
            };
            n.discardElement = function(t) {
                var i = n.garbageBin;
                i || (i = n.createElement("div"));
                t && i.appendChild(t);
                i.innerHTML = ""
            };
            n.correctFloat = function(n, t) {
                return parseFloat(n.toPrecision(t || 14))
            };
            n.setAnimation = function(t, i) {
                i.renderer.globalAnimation = n.pick(t, i.options.chart.animation, !0)
            };
            n.animObject = function(t) {
                return n.isObject(t) ? n.merge(t) : {
                    duration: t ? 500 : 0
                }
            };
            n.timeUnits = {
                millisecond: 1,
                second: 1e3,
                minute: 6e4,
                hour: 36e5,
                day: 864e5,
                week: 6048e5,
                month: 24192e5,
                year: 314496e5
            };
            n.numberFormat = function(t, i, r, u) {
                t = +t || 0;
                i = +i;
                var s = n.defaultOptions.lang,
                    f = (t.toString().split(".")[1] || "").length,
                    e, o;
                return -1 === i ? i = Math.min(f, 20) : n.isNumber(i) || (i = 2), o = (Math.abs(t) + Math.pow(10, -Math.max(i, f) - 1)).toFixed(i), f = String(n.pInt(o)), e = 3 < f.length ? f.length % 3 : 0, r = n.pick(r, s.decimalPoint), u = n.pick(u, s.thousandsSep), t = (0 > t ? "-" : "") + (e ? f.substr(0, e) + u : ""), t += f.substr(e).replace(/(\d{3})(?=\d)/g, "$1" + u), i && (t += r + o.slice(-i)), t
            };
            Math.easeInOutSine = function(n) {
                return -.5 * (Math.cos(Math.PI * n) - 1)
            };
            n.getStyle = function(t, r) {
                return "width" === r ? Math.min(t.offsetWidth, t.scrollWidth) - n.getStyle(t, "padding-left") - n.getStyle(t, "padding-right") : "height" === r ? Math.min(t.offsetHeight, t.scrollHeight) - n.getStyle(t, "padding-top") - n.getStyle(t, "padding-bottom") : (t = i.getComputedStyle(t, void 0)) && n.pInt(t.getPropertyValue(r))
            };
            n.inArray = function(n, t) {
                return t.indexOf ? t.indexOf(n) : [].indexOf.call(t, n)
            };
            n.grep = function(n, t) {
                return [].filter.call(n, t)
            };
            n.find = function(n, t) {
                return [].find.call(n, t)
            };
            n.map = function(n, t) {
                for (var r = [], i = 0, u = n.length; i < u; i++) r[i] = t.call(n[i], n[i], i, n);
                return r
            };
            n.offset = function(n) {
                var t = r.documentElement;
                return n = n.getBoundingClientRect(), {
                    top: n.top + (i.pageYOffset || t.scrollTop) - (t.clientTop || 0),
                    left: n.left + (i.pageXOffset || t.scrollLeft) - (t.clientLeft || 0)
                }
            };
            n.stop = function(n, i) {
                for (var r = t.length; r--;) t[r].elem !== n || i && i !== t[r].prop || (t[r].stopped = !0)
            };
            n.each = function(n, t, i) {
                return Array.prototype.forEach.call(n, t, i)
            };
            n.addEvent = function(t, r, u) {
                function e(n) {
                    n.target = n.srcElement || i;
                    u.call(t, n)
                }
                var f = t.hcEvents = t.hcEvents || {};
                return t.addEventListener ? t.addEventListener(r, u, !1) : t.attachEvent && (t.hcEventsIE || (t.hcEventsIE = {}), t.hcEventsIE[u.toString()] = e, t.attachEvent("on" + r, e)), f[r] || (f[r] = []), f[r].push(u),
                    function() {
                        n.removeEvent(t, r, u)
                    }
            };
            n.removeEvent = function(t, i, r) {
                function o(n, i) {
                    t.removeEventListener ? t.removeEventListener(n, i, !1) : t.attachEvent && (i = t.hcEventsIE[i.toString()], t.detachEvent("on" + n, i))
                }

                function s() {
                    var n, r;
                    if (t.nodeName)
                        for (r in i ? (n = {}, n[i] = !0) : n = u, n)
                            if (u[r])
                                for (n = u[r].length; n--;) o(r, u[r][n])
                }
                var f, u = t.hcEvents,
                    e;
                u && (i ? (f = u[i] || [], r ? (e = n.inArray(r, f), -1 < e && (f.splice(e, 1), u[i] = f), o(i, r)) : (s(), u[i] = [])) : (s(), t.hcEvents = {}))
            };
            n.fireEvent = function(t, i, u, f) {
                var e, o, s;
                if (e = t.hcEvents, u = u || {}, r.createEvent && (t.dispatchEvent || t.fireEvent)) e = r.createEvent("Events"), e.initEvent(i, !0, !0), n.extend(e, u), t.dispatchEvent ? t.dispatchEvent(e) : t.fireEvent(i, e);
                else if (e)
                    for (e = e[i] || [], o = e.length, u.target || n.extend(u, {
                            preventDefault: function() {
                                u.defaultPrevented = !0
                            },
                            target: t,
                            type: i
                        }), i = 0; i < o; i++)(s = e[i]) && !1 === s.call(t, u) && u.preventDefault();
                f && !u.defaultPrevented && f(u)
            };
            n.animate = function(t, i, r) {
                var f, s = "",
                    u, o, e;
                n.isObject(r) || (f = arguments, r = {
                    duration: f[2],
                    easing: f[3],
                    complete: f[4]
                });
                n.isNumber(r.duration) || (r.duration = 400);
                r.easing = "function" == typeof r.easing ? r.easing : Math[r.easing] || Math.easeInOutSine;
                r.curAnim = n.merge(i);
                for (e in i) n.stop(t, e), o = new n.Fx(t, r, e), u = null, "d" === e ? (o.paths = o.initPath(t, t.d, i.d), o.toD = i.d, f = 0, u = 1) : t.attr ? f = t.attr(e) : (f = parseFloat(n.getStyle(t, e)) || 0, "opacity" !== e && (s = "px")), u || (u = i[e]), u.match && u.match("px") && (u = u.replace(/px/g, "")), o.run(f, u, s)
            };
            n.seriesType = function(t, i, r, u, f) {
                var o = n.getOptions(),
                    e = n.seriesTypes;
                return o.plotOptions[t] = n.merge(o.plotOptions[i], r), e[t] = n.extendClass(e[i] || function() {}, u), e[t].prototype.type = t, f && (e[t].prototype.pointClass = n.extendClass(n.Point, f)), e[t]
            };
            n.uniqueKey = function() {
                var n = Math.random().toString(36).substring(2, 9),
                    t = 0;
                return function() {
                    return "highcharts-" + n + "-" + t++
                }
            }();
            i.jQuery && (i.jQuery.fn.highcharts = function() {
                var t = [].slice.call(arguments);
                if (this[0]) return t[0] ? (new n[n.isString(t[0]) ? t.shift() : "Chart"](this[0], t[0], t[1]), this) : u[n.attr(this[0], "data-highcharts-chart")]
            });
            r && !r.defaultView && (n.getStyle = function(t, i) {
                var r = {
                    width: "clientWidth",
                    height: "clientHeight"
                } [i];
                return t.style[i] ? n.pInt(t.style[i]) : ("opacity" === i && (i = "filter"), r) ? (t.style.zoom = 1, Math.max(t[r] - 2 * n.getStyle(t, "padding"), 0)) : (t = t.currentStyle[i.replace(/\-(\w)/g, function(n, t) {
                    return t.toUpperCase()
                })], "filter" === i && (t = t.replace(/alpha\(opacity=([0-9]+)\)/, function(n, t) {
                    return t / 100
                })), "" === t ? 1 : n.pInt(t))
            });
            Array.prototype.forEach || (n.each = function(n, t, i) {
                for (var r = 0, u = n.length; r < u; r++)
                    if (!1 === t.call(i, n[r], r, n)) return r
            });
            Array.prototype.indexOf || (n.inArray = function(n, t) {
                var r, i = 0;
                if (t)
                    for (r = t.length; i < r; i++)
                        if (t[i] === n) return i;
                return -1
            });
            Array.prototype.filter || (n.grep = function(n, t) {
                for (var r = [], i = 0, u = n.length; i < u; i++) t(n[i], i) && r.push(n[i]);
                return r
            });
            Array.prototype.find || (n.find = function(n, t) {
                for (var r = n.length, i = 0; i < r; i++)
                    if (t(n[i], i)) return n[i]
            })
        }(n),
        function(n) {
            var i = n.each,
                r = n.isNumber,
                u = n.map,
                f = n.merge,
                t = n.pInt;
            n.Color = function(t) {
                if (!(this instanceof n.Color)) return new n.Color(t);
                this.init(t)
            };
            n.Color.prototype = {
                parsers: [{
                    regex: /rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]?(?:\.[0-9]+)?)\s*\)/,
                    parse: function(n) {
                        return [t(n[1]), t(n[2]), t(n[3]), parseFloat(n[4], 10)]
                    }
                }, {
                    regex: /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/,
                    parse: function(n) {
                        return [t(n[1], 16), t(n[2], 16), t(n[3], 16), 1]
                    }
                }, {
                    regex: /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/,
                    parse: function(n) {
                        return [t(n[1]), t(n[2]), t(n[3]), 1]
                    }
                }],
                names: {
                    white: "#ffffff",
                    black: "#000000"
                },
                init: function(t) {
                    var e, i, r, f;
                    if ((this.input = t = this.names[t] || t) && t.stops) this.stops = u(t.stops, function(t) {
                        return new n.Color(t[1])
                    });
                    else
                        for (r = this.parsers.length; r-- && !i;) f = this.parsers[r], (e = f.regex.exec(t)) && (i = f.parse(e));
                    this.rgba = i || []
                },
                get: function(n) {
                    var e = this.input,
                        t = this.rgba,
                        u;
                    return this.stops ? (u = f(e), u.stops = [].concat(u.stops), i(this.stops, function(t, i) {
                        u.stops[i] = [u.stops[i][0], t.get(n)]
                    })) : u = t && r(t[0]) ? "rgb" === n || !n && 1 === t[3] ? "rgb(" + t[0] + "," + t[1] + "," + t[2] + ")" : "a" === n ? t[3] : "rgba(" + t.join(",") + ")" : e, u
                },
                brighten: function(n) {
                    var u, f = this.rgba;
                    if (this.stops) i(this.stops, function(t) {
                        t.brighten(n)
                    });
                    else if (r(n) && 0 !== n)
                        for (u = 0; 3 > u; u++) f[u] += t(255 * n), 0 > f[u] && (f[u] = 0), 255 < f[u] && (f[u] = 255);
                    return this
                },
                setOpacity: function(n) {
                    return this.rgba[3] = n, this
                }
            };
            n.color = function(t) {
                return new n.Color(t)
            }
        }(n),
        function(n) {
            var r, y, p = n.addEvent,
                ft = n.animate,
                i = n.attr,
                et = n.charts,
                ot = n.color,
                h = n.css,
                nt = n.createElement,
                t = n.defined,
                w = n.deg2rad,
                st = n.destroyObjectProperties,
                f = n.doc,
                e = n.each,
                o = n.extend,
                b = n.erase,
                ht = n.grep,
                ct = n.hasTouch,
                lt = n.inArray,
                tt = n.isArray,
                it = n.isFirefox,
                k = n.isMS,
                c = n.isObject,
                at = n.isString,
                vt = n.isWebKit,
                s = n.merge,
                yt = n.noop,
                u = n.pick,
                l = n.pInt,
                rt = n.removeEvent,
                ut = n.stop,
                a = n.svg,
                d = n.SVG_NS,
                v = n.symbolSizes,
                g = n.win;
            r = n.SVGElement = function() {
                return this
            };
            r.prototype = {
                opacity: 1,
                SVG_NS: d,
                textProps: "direction fontSize fontWeight fontFamily fontStyle color lineHeight width textDecoration textOverflow textOutline".split(" "),
                init: function(n, t) {
                    this.element = "span" === t ? nt(t) : f.createElementNS(this.SVG_NS, t);
                    this.renderer = n
                },
                animate: function(t, i, r) {
                    return i = n.animObject(u(i, this.renderer.globalAnimation, !0)), 0 !== i.duration ? (r && (i.complete = r), ft(this, t, i)) : this.attr(t, null, r), this
                },
                colorGradient: function(i, r, u) {
                    var c = this.renderer,
                        w, h, f, b, y, v, p, k, d, l, a, o = [],
                        g;
                    if (i.linearGradient ? h = "linearGradient" : i.radialGradient && (h = "radialGradient"), h) {
                        f = i[h];
                        y = c.gradients;
                        p = i.stops;
                        l = u.radialReference;
                        tt(f) && (i[h] = f = {
                            x1: f[0],
                            y1: f[1],
                            x2: f[2],
                            y2: f[3],
                            gradientUnits: "userSpaceOnUse"
                        });
                        "radialGradient" === h && l && !t(f.gradientUnits) && (b = f, f = s(f, c.getRadialAttr(l, b), {
                            gradientUnits: "userSpaceOnUse"
                        }));
                        for (a in f) "id" !== a && o.push(a, f[a]);
                        for (a in p) o.push(p[a]);
                        o = o.join(",");
                        y[o] ? l = y[o].attr("id") : (f.id = l = n.uniqueKey(), y[o] = v = c.createElement(h).attr(f).add(c.defs), v.radAttr = b, v.stops = [], e(p, function(t) {
                            0 === t[1].indexOf("rgba") ? (w = n.color(t[1]), k = w.get("rgb"), d = w.get("a")) : (k = t[1], d = 1);
                            t = c.createElement("stop").attr({
                                offset: t[0],
                                "stop-color": k,
                                "stop-opacity": d
                            }).add(v);
                            v.stops.push(t)
                        }));
                        g = "url(" + c.url + "#" + l + ")";
                        u.setAttribute(r, g);
                        u.gradient = o;
                        i.toString = function() {
                            return g
                        }
                    }
                },
                applyTextOutline: function(n) {
                    var t = this.element,
                        u, f, r, o; - 1 !== n.indexOf("contrast") && (n = n.replace(/contrast/g, this.renderer.getContrast(t.style.fill)));
                    this.fakeTS = !0;
                    this.ySetter = this.xSetter;
                    u = [].slice.call(t.getElementsByTagName("tspan"));
                    n = n.split(" ");
                    f = n[n.length - 1];
                    (r = n[0]) && "none" !== r && (r = r.replace(/(^[\d\.]+)(.*?)$/g, function(n, t, i) {
                        return 2 * t + i
                    }), e(u, function(n) {
                        "highcharts-text-outline" === n.getAttribute("class") && b(u, t.removeChild(n))
                    }), o = t.firstChild, e(u, function(n, u) {
                        0 === u && (n.setAttribute("x", t.getAttribute("x")), u = t.getAttribute("y"), n.setAttribute("y", u || 0), null === u && t.setAttribute("y", 0));
                        n = n.cloneNode(1);
                        i(n, {
                            "class": "highcharts-text-outline",
                            fill: f,
                            stroke: f,
                            "stroke-width": r,
                            "stroke-linejoin": "round"
                        });
                        t.insertBefore(n, o)
                    }))
                },
                attr: function(n, t, i, r) {
                    var u, e = this.element,
                        o, s = this,
                        f;
                    if ("string" == typeof n && void 0 !== t && (u = n, n = {}, n[u] = t), "string" == typeof n) s = (this[n + "Getter"] || this._defaultGetter).call(this, n, e);
                    else {
                        for (u in n) t = n[u], f = !1, r || ut(this, u), this.symbolName && /^(x|y|width|height|r|start|end|innerR|anchorX|anchorY)/.test(u) && (o || (this.symbolAttr(n), o = !0), f = !0), !this.rotation || "x" !== u && "y" !== u || (this.doTransform = !0), f || (f = this[u + "Setter"] || this._defaultSetter, f.call(this, t, u, e), this.shadows && /^(width|height|visibility|x|y|d|transform|cx|cy|r)$/.test(u) && this.updateShadows(u, t, f));
                        this.doTransform && (this.updateTransform(), this.doTransform = !1)
                    }
                    return i && i(), s
                },
                updateShadows: function(n, t, i) {
                    for (var r = this.shadows, u = r.length; u--;) i.call(r[u], "height" === n ? Math.max(t - (r[u].cutHeight || 0), 0) : "d" === n ? this.d : t, n, r[u])
                },
                addClass: function(n, t) {
                    var i = this.attr("class") || "";
                    return -1 === i.indexOf(n) && (t || (n = (i + (i ? " " : "") + n).replace("  ", " ")), this.attr("class", n)), this
                },
                hasClass: function(n) {
                    return -1 !== i(this.element, "class").indexOf(n)
                },
                removeClass: function(n) {
                    return i(this.element, "class", (i(this.element, "class") || "").replace(n, "")), this
                },
                symbolAttr: function(n) {
                    var t = this;
                    e("x y r start end width height innerR anchorX anchorY".split(" "), function(i) {
                        t[i] = u(n[i], t[i])
                    });
                    t.attr({
                        d: t.renderer.symbols[t.symbolName](t.x, t.y, t.width, t.height, t)
                    })
                },
                clip: function(n) {
                    return this.attr("clip-path", n ? "url(" + this.renderer.url + "#" + n.id + ")" : "none")
                },
                crisp: function(n, i) {
                    var r, f = {},
                        u;
                    i = i || n.strokeWidth || 0;
                    u = Math.round(i) % 2 / 2;
                    n.x = Math.floor(n.x || this.x || 0) + u;
                    n.y = Math.floor(n.y || this.y || 0) + u;
                    n.width = Math.floor((n.width || this.width || 0) - 2 * u);
                    n.height = Math.floor((n.height || this.height || 0) - 2 * u);
                    t(n.strokeWidth) && (n.strokeWidth = i);
                    for (r in n) this[r] !== n[r] && (this[r] = f[r] = n[r]);
                    return f
                },
                css: function(n) {
                    var r = this.styles,
                        e = {},
                        s = this.element,
                        u, t, f = "",
                        c;
                    if (u = !r, c = ["textOverflow", "width"], n && n.color && (n.fill = n.color), r)
                        for (t in n) n[t] !== r[t] && (e[t] = n[t], u = !0);
                    if (u) {
                        if (u = this.textWidth = n && n.width && "text" === s.nodeName.toLowerCase() && l(n.width) || this.textWidth, r && (n = o(r, e)), this.styles = n, u && !a && this.renderer.forExport && delete n.width, k && !a) h(this.element, n);
                        else {
                            r = function(n, t) {
                                return "-" + t.toLowerCase()
                            };
                            for (t in n) - 1 === lt(t, c) && (f += t.replace(/([A-Z])/g, r) + ":" + n[t] + ";");
                            f && i(s, "style", f)
                        }
                        this.added && (u && this.renderer.buildText(this), n && n.textOutline && this.applyTextOutline(n.textOutline))
                    }
                    return this
                },
                strokeWidth: function() {
                    return this["stroke-width"] || 0
                },
                on: function(n, t) {
                    var r = this,
                        i = r.element;
                    return ct && "click" === n ? (i.ontouchstart = function(n) {
                        r.touchEventFired = Date.now();
                        n.preventDefault();
                        t.call(i, n)
                    }, i.onclick = function(n) {
                        (-1 === g.navigator.userAgent.indexOf("Android") || 1100 < Date.now() - (r.touchEventFired || 0)) && t.call(i, n)
                    }) : i["on" + n] = t, this
                },
                setRadialReference: function(n) {
                    var t = this.renderer.gradients[this.element.gradient];
                    return this.element.radialReference = n, t && t.radAttr && t.animate(this.renderer.getRadialAttr(n, t.radAttr)), this
                },
                translate: function(n, t) {
                    return this.attr({
                        translateX: n,
                        translateY: t
                    })
                },
                invert: function(n) {
                    return this.inverted = n, this.updateTransform(), this
                },
                updateTransform: function() {
                    var n = this.translateX || 0,
                        r = this.translateY || 0,
                        f = this.scaleX,
                        e = this.scaleY,
                        o = this.inverted,
                        s = this.rotation,
                        i = this.element;
                    o && (n += this.width, r += this.height);
                    n = ["translate(" + n + "," + r + ")"];
                    o ? n.push("rotate(90) scale(-1,1)") : s && n.push("rotate(" + s + " " + (i.getAttribute("x") || 0) + " " + (i.getAttribute("y") || 0) + ")");
                    (t(f) || t(e)) && n.push("scale(" + u(f, 1) + " " + u(e, 1) + ")");
                    n.length && i.setAttribute("transform", n.join(" "))
                },
                toFront: function() {
                    var n = this.element;
                    return n.parentNode.appendChild(n), this
                },
                align: function(n, t, i) {
                    var f, r, e, c, o = {},
                        s, h;
                    return r = this.renderer, e = r.alignedObjects, n ? (this.alignOptions = n, this.alignByTranslate = t, !i || at(i)) && (this.alignTo = f = i || "renderer", b(e, this), e.push(this), i = null) : (n = this.alignOptions, t = this.alignByTranslate, f = this.alignTo), i = u(i, r[f], r), f = n.align, r = n.verticalAlign, e = (i.x || 0) + (n.x || 0), c = (i.y || 0) + (n.y || 0), "right" === f ? s = 1 : "center" === f && (s = 2), s && (e += (i.width - (n.width || 0)) / s), o[t ? "translateX" : "x"] = Math.round(e), "bottom" === r ? h = 1 : "middle" === r && (h = 2), h && (c += (i.height - (n.height || 0)) / h), o[t ? "translateY" : "y"] = Math.round(c), this[this.placed ? "animate" : "attr"](o), this.placed = !0, this.alignAttr = o, this
                },
                getBBox: function(n, t) {
                    var i, f = this.renderer,
                        c, h = this.element,
                        s = this.styles,
                        y, p = this.textStr,
                        l, a = f.cache,
                        v = f.cacheKeys,
                        r;
                    if (t = u(t, this.rotation), c = t * w, y = s && s.fontSize, void 0 !== p && (r = p.toString(), -1 === r.indexOf("<") && (r = r.replace(/[0-9]/g, "0")), r += ["", t || 0, y, s && s.width, s && s.textOverflow].join()), r && !n && (i = a[r]), !i) {
                        if (h.namespaceURI === this.SVG_NS || f.forExport) {
                            try {
                                (l = this.fakeTS && function(n) {
                                    e(h.querySelectorAll(".highcharts-text-outline"), function(t) {
                                        t.style.display = n
                                    })
                                }) && l("none");
                                i = h.getBBox ? o({}, h.getBBox()) : {
                                    width: h.offsetWidth,
                                    height: h.offsetHeight
                                };
                                l && l("")
                            } catch (b) {}(!i || 0 > i.width) && (i = {
                                width: 0,
                                height: 0
                            })
                        } else i = this.htmlGetBBox();
                        if (f.isSVG && (n = i.width, f = i.height, s && "11px" === s.fontSize && 17 === Math.round(f) && (i.height = f = 14), t && (i.width = Math.abs(f * Math.sin(c)) + Math.abs(n * Math.cos(c)), i.height = Math.abs(f * Math.cos(c)) + Math.abs(n * Math.sin(c)))), r && 0 < i.height) {
                            for (; 250 < v.length;) delete a[v.shift()];
                            a[r] || v.push(r);
                            a[r] = i
                        }
                    }
                    return i
                },
                show: function(n) {
                    return this.attr({
                        visibility: n ? "inherit" : "visible"
                    })
                },
                hide: function() {
                    return this.attr({
                        visibility: "hidden"
                    })
                },
                fadeOut: function(n) {
                    var t = this;
                    t.animate({
                        opacity: 0
                    }, {
                        duration: n || 150,
                        complete: function() {
                            t.attr({
                                y: -9999
                            })
                        }
                    })
                },
                add: function(n) {
                    var t = this.renderer,
                        r = this.element,
                        i;
                    return n && (this.parentGroup = n), this.parentInverted = n && n.inverted, void 0 !== this.textStr && t.buildText(this), this.added = !0, (!n || n.handleZ || this.zIndex) && (i = this.zIndexSetter()), i || (n ? n.element : t.box).appendChild(r), this.onAdd && this.onAdd(), this
                },
                safeRemoveChild: function(n) {
                    var t = n.parentNode;
                    t && t.removeChild(n)
                },
                destroy: function() {
                    var n = this.element || {},
                        t = this.renderer.isSVG && "SPAN" === n.nodeName && this.parentGroup,
                        r, i;
                    if (n.onclick = n.onmouseout = n.onmouseover = n.onmousemove = n.point = null, ut(this), this.clipPath && (this.clipPath = this.clipPath.destroy()), this.stops) {
                        for (i = 0; i < this.stops.length; i++) this.stops[i] = this.stops[i].destroy();
                        this.stops = null
                    }
                    for (this.safeRemoveChild(n), this.destroyShadows(); t && t.div && 0 === t.div.childNodes.length;) n = t.parentGroup, this.safeRemoveChild(t.div), delete t.div, t = n;
                    this.alignTo && b(this.renderer.alignedObjects, this);
                    for (r in this) delete this[r];
                    return null
                },
                shadow: function(n, t, r) {
                    var c = [],
                        e, f, h = this.element,
                        o, s, l, a;
                    if (n) {
                        if (!this.shadows) {
                            for (s = u(n.width, 3), l = (n.opacity || .15) / s, a = this.parentInverted ? "(-1,-1)" : "(" + u(n.offsetX, 1) + ", " + u(n.offsetY, 1) + ")", e = 1; e <= s; e++) f = h.cloneNode(0), o = 2 * s + 1 - 2 * e, i(f, {
                                isShadow: "true",
                                stroke: n.color || "#000000",
                                "stroke-opacity": l * e,
                                "stroke-width": o,
                                transform: "translate" + a,
                                fill: "none"
                            }), r && (i(f, "height", Math.max(i(f, "height") - o, 0)), f.cutHeight = o), t ? t.element.appendChild(f) : h.parentNode.insertBefore(f, h), c.push(f);
                            this.shadows = c
                        }
                    } else this.destroyShadows();
                    return this
                },
                destroyShadows: function() {
                    e(this.shadows || [], function(n) {
                        this.safeRemoveChild(n)
                    }, this);
                    this.shadows = void 0
                },
                xGetter: function(n) {
                    return "circle" === this.element.nodeName && ("x" === n ? n = "cx" : "y" === n && (n = "cy")), this._defaultGetter(n)
                },
                _defaultGetter: function(n) {
                    return n = u(this[n], this.element ? this.element.getAttribute(n) : null, 0), /^[\-0-9\.]+$/.test(n) && (n = parseFloat(n)), n
                },
                dSetter: function(n, t, i) {
                    n && n.join && (n = n.join(" "));
                    /(NaN| {2}|^$)/.test(n) && (n = "M 0 0");
                    i.setAttribute(t, n);
                    this[t] = n
                },
                dashstyleSetter: function(n) {
                    var t, i = this["stroke-width"];
                    if ("inherit" === i && (i = 1), n = n && n.toLowerCase()) {
                        for (n = n.replace("shortdashdotdot", "3,1,1,1,1,1,").replace("shortdashdot", "3,1,1,1").replace("shortdot", "1,1,").replace("shortdash", "3,1,").replace("longdash", "8,3,").replace(/dot/g, "1,3,").replace("dash", "4,3,").replace(/,$/, "").split(","), t = n.length; t--;) n[t] = l(n[t]) * i;
                        n = n.join(",").replace(/NaN/g, "none");
                        this.element.setAttribute("stroke-dasharray", n)
                    }
                },
                alignSetter: function(n) {
                    this.element.setAttribute("text-anchor", {
                        left: "start",
                        center: "middle",
                        right: "end"
                    } [n])
                },
                opacitySetter: function(n, t, i) {
                    this[t] = n;
                    i.setAttribute(t, n)
                },
                titleSetter: function(n) {
                    var t = this.element.getElementsByTagName("title")[0];
                    t || (t = f.createElementNS(this.SVG_NS, "title"), this.element.appendChild(t));
                    t.firstChild && t.removeChild(t.firstChild);
                    t.appendChild(f.createTextNode(String(u(n), "").replace(/<[^>]*>/g, "")))
                },
                textSetter: function(n) {
                    n !== this.textStr && (delete this.bBox, this.textStr = n, this.added && this.renderer.buildText(this))
                },
                fillSetter: function(n, t, i) {
                    "string" == typeof n ? i.setAttribute(t, n) : n && this.colorGradient(n, t, i)
                },
                visibilitySetter: function(n, t, i) {
                    "inherit" === n ? i.removeAttribute(t) : i.setAttribute(t, n)
                },
                zIndexSetter: function(n, i) {
                    var h = this.renderer,
                        r = this.parentGroup,
                        f = (r || h).element || h.box,
                        u, e = this.element,
                        o, s;
                    if (u = this.added, t(n) && (e.zIndex = n, n = +n, this[i] === n && (u = !1), this[i] = n), u) {
                        for ((n = this.zIndex) && r && (r.handleZ = !0), i = f.childNodes, s = 0; s < i.length && !o; s++) r = i[s], u = r.zIndex, r !== e && (l(u) > n || !t(n) && t(u) || 0 > n && !t(u) && f !== h.box) && (f.insertBefore(e, r), o = !0);
                        o || f.appendChild(e)
                    }
                    return o
                },
                _defaultSetter: function(n, t, i) {
                    i.setAttribute(t, n)
                }
            };
            r.prototype.yGetter = r.prototype.xGetter;
            r.prototype.translateXSetter = r.prototype.translateYSetter = r.prototype.rotationSetter = r.prototype.verticalAlignSetter = r.prototype.scaleXSetter = r.prototype.scaleYSetter = function(n, t) {
                this[t] = n;
                this.doTransform = !0
            };
            r.prototype["stroke-widthSetter"] = r.prototype.strokeSetter = function(n, t, i) {
                this[t] = n;
                this.stroke && this["stroke-width"] ? (r.prototype.fillSetter.call(this, this.stroke, "stroke", i), i.setAttribute("stroke-width", this["stroke-width"]), this.hasStroke = !0) : "stroke-width" === t && 0 === n && this.hasStroke && (i.removeAttribute("stroke"), this.hasStroke = !1)
            };
            y = n.SVGRenderer = function() {
                this.init.apply(this, arguments)
            };
            y.prototype = {
                Element: r,
                SVG_NS: d,
                init: function(n, t, r, u, e, o) {
                    var c, s;
                    u = this.createElement("svg").attr({
                        version: "1.1",
                        "class": "highcharts-root"
                    }).css(this.getStyle(u));
                    c = u.element;
                    n.appendChild(c); - 1 === n.innerHTML.indexOf("xmlns") && i(c, "xmlns", this.SVG_NS);
                    this.isSVG = !0;
                    this.box = c;
                    this.boxWrapper = u;
                    this.alignedObjects = [];
                    this.url = (it || vt) && f.getElementsByTagName("base").length ? g.location.href.replace(/#.*?$/, "").replace(/<[^>]*>/g, "").replace(/([\('\)])/g, "\\$1").replace(/ /g, "%20") : "";
                    this.createElement("desc").add().element.appendChild(f.createTextNode("Created with Highstock 5.0.7"));
                    this.defs = this.createElement("defs").add();
                    this.allowHTML = o;
                    this.forExport = e;
                    this.gradients = {};
                    this.cache = {};
                    this.cacheKeys = [];
                    this.imgCount = 0;
                    this.setSize(t, r, !1);
                    it && n.getBoundingClientRect && (t = function() {
                        h(n, {
                            left: 0,
                            top: 0
                        });
                        s = n.getBoundingClientRect();
                        h(n, {
                            left: Math.ceil(s.left) - s.left + "px",
                            top: Math.ceil(s.top) - s.top + "px"
                        })
                    }, t(), this.unSubPixelFix = p(g, "resize", t))
                },
                getStyle: function(n) {
                    return this.style = o({
                        fontFamily: '"Lucida Grande", "Lucida Sans Unicode", Arial, Helvetica, sans-serif',
                        fontSize: "12px"
                    }, n)
                },
                setStyle: function(n) {
                    this.boxWrapper.css(this.getStyle(n))
                },
                isHidden: function() {
                    return !this.boxWrapper.getBBox().width
                },
                destroy: function() {
                    var n = this.defs;
                    return this.box = null, this.boxWrapper = this.boxWrapper.destroy(), st(this.gradients || {}), this.gradients = null, n && (this.defs = n.destroy()), this.unSubPixelFix && this.unSubPixelFix(), this.alignedObjects = null
                },
                createElement: function(n) {
                    var t = new this.Element;
                    return t.init(this, n), t
                },
                draw: yt,
                getRadialAttr: function(n, t) {
                    return {
                        cx: n[0] - n[2] / 2 + t.cx * n[2],
                        cy: n[1] - n[2] / 2 + t.cy * n[2],
                        r: t.r * n[2]
                    }
                },
                buildText: function(n) {
                    var o = n.element,
                        c = this,
                        rt = c.forExport,
                        r = u(n.textStr, "").toString(),
                        ut = -1 !== r.indexOf("<"),
                        ft = o.childNodes,
                        w, b, k, v, g = i(o, "x"),
                        t = n.styles,
                        s = n.textWidth,
                        nt = t && t.lineHeight,
                        y = t && t.textOutline,
                        p = t && "ellipsis" === t.textOverflow,
                        tt = t && "nowrap" === t.whiteSpace,
                        et = t && t.fontSize,
                        it, ot = ft.length,
                        t = s && !n.added && this.box,
                        st = function(n) {
                            var t;
                            return t = /(px|em)$/.test(n && n.style.fontSize) ? n.style.fontSize : et || c.style.fontSize || 12, nt ? l(nt) : c.fontMetrics(t, n.getAttribute("style") ? n : o).h
                        };
                    if (it = [r, p, tt, nt, y, et, s].join(), it !== n.textCache) {
                        for (n.textCache = it; ot--;) o.removeChild(ft[ot]);
                        ut || y || p || s || -1 !== r.indexOf(" ") ? (w = /<.*class="([^"]+)".*>/, b = /<.*style="([^"]+)".*>/, k = /<.*href="(http[^"]+)".*>/, t && t.appendChild(o), r = ut ? r.replace(/<(b|strong)>/g, '<span style="font-weight:bold">').replace(/<(i|em)>/g, '<span style="font-style:italic">').replace(/<a/g, "<span").replace(/<\/(b|strong|i|em|a)>/g, "<\/span>").split(/<br.*?>/g) : [r], r = ht(r, function(n) {
                            return "" !== n
                        }), e(r, function(t, r) {
                            var u, l = 0;
                            t = t.replace(/^\s+|\s+$/g, "").replace(/<span/g, "|||<span").replace(/<\/span>/g, "<\/span>|||");
                            u = t.split("|||");
                            e(u, function(t) {
                                var y, e, ft, et;
                                if (("" !== t || 1 === u.length) && (y = {}, e = f.createElementNS(c.SVG_NS, "tspan"), w.test(t) && (ft = t.match(w)[1], i(e, "class", ft)), b.test(t) && (et = t.match(b)[1].replace(/(;| |^)color([ :])/, "$1fill$2"), i(e, "style", et)), k.test(t) && !rt && (i(e, "onclick", 'location.href="' + t.match(k)[1] + '"'), h(e, {
                                        cursor: "pointer"
                                    })), t = (t.replace(/<(.|\n)*?>/g, "") || " ").replace(/&lt;/g, "<").replace(/&gt;/g, ">"), " " !== t)) {
                                    if (e.appendChild(f.createTextNode(t)), l ? y.dx = 0 : r && null !== g && (y.x = g), i(e, y), o.appendChild(e), !l && r && (!a && rt && h(e, {
                                            display: "block"
                                        }), i(e, "dy", st(e))), s) {
                                        y = t.replace(/([^\^])-/g, "$1- ").split(" ");
                                        ft = 1 < u.length || r || 1 < y.length && !tt;
                                        for (var nt, it, ot = [], ct = st(e), lt = n.rotation, ut = t, ht = ut.length;
                                            (ft || p) && (y.length || ot.length);) n.rotation = 0, nt = n.getBBox(!0), it = nt.width, !a && c.forExport && (it = c.measureSpanWidth(e.firstChild.data, n.styles)), nt = it > s, void 0 === v && (v = nt), p && v ? (ht /= 2, "" === ut || !nt && .5 > ht ? y = [] : (ut = t.substring(0, ut.length + (nt ? -1 : 1) * Math.ceil(ht)), y = [ut + (3 < s ? "…" : "")], e.removeChild(e.firstChild))) : nt && 1 !== y.length ? (e.removeChild(e.firstChild), ot.unshift(y.pop())) : (y = ot, ot = [], y.length && !tt && (e = f.createElementNS(d, "tspan"), i(e, {
                                            dy: ct,
                                            x: g
                                        }), et && i(e, "style", et), o.appendChild(e)), it > s && (s = it)), y.length && e.appendChild(f.createTextNode(y.join(" ").replace(/- /g, "-")));
                                        n.rotation = lt
                                    }
                                    l++
                                }
                            })
                        }), v && n.attr("title", n.textStr), t && t.removeChild(o), y && n.applyTextOutline && n.applyTextOutline(y)) : o.appendChild(f.createTextNode(r.replace(/&lt;/g, "<").replace(/&gt;/g, ">")))
                    }
                },
                getContrast: function(n) {
                    return n = ot(n).rgba, 510 < n[0] + n[1] + n[2] ? "#000000" : "#FFFFFF"
                },
                button: function(n, t, i, r, u, f, e, h, c) {
                    var l = this.label(n, t, i, c, null, null, null, null, "button"),
                        a = 0,
                        v, y, w, b;
                    l.attr(s({
                        padding: 8,
                        r: 2
                    }, u));
                    u = s({
                        fill: "#f7f7f7",
                        stroke: "#cccccc",
                        "stroke-width": 1,
                        style: {
                            color: "#333333",
                            cursor: "pointer",
                            fontWeight: "normal"
                        }
                    }, u);
                    v = u.style;
                    delete u.style;
                    f = s(u, {
                        fill: "#e6e6e6"
                    }, f);
                    y = f.style;
                    delete f.style;
                    e = s(u, {
                        fill: "#e6ebf5",
                        style: {
                            color: "#000000",
                            fontWeight: "bold"
                        }
                    }, e);
                    w = e.style;
                    delete e.style;
                    h = s(u, {
                        style: {
                            color: "#cccccc"
                        }
                    }, h);
                    b = h.style;
                    delete h.style;
                    p(l.element, k ? "mouseover" : "mouseenter", function() {
                        3 !== a && l.setState(1)
                    });
                    p(l.element, k ? "mouseout" : "mouseleave", function() {
                        3 !== a && l.setState(a)
                    });
                    l.setState = function(n) {
                        1 !== n && (l.state = a = n);
                        l.removeClass(/highcharts-button-(normal|hover|pressed|disabled)/).addClass("highcharts-button-" + ["normal", "hover", "pressed", "disabled"][n || 0]);
                        l.attr([u, f, e, h][n || 0]).css([v, y, w, b][n || 0])
                    };
                    l.attr(u).css(o({
                        cursor: "default"
                    }, v));
                    return l.on("click", function(n) {
                        3 !== a && r.call(l, n)
                    })
                },
                crispLine: function(n, t) {
                    return n[1] === n[4] && (n[1] = n[4] = Math.round(n[1]) - t % 2 / 2), n[2] === n[5] && (n[2] = n[5] = Math.round(n[2]) + t % 2 / 2), n
                },
                path: function(n) {
                    var t = {
                        fill: "none"
                    };
                    return tt(n) ? t.d = n : c(n) && o(t, n), this.createElement("path").attr(t)
                },
                circle: function(n, t, i) {
                    return n = c(n) ? n : {
                        x: n,
                        y: t,
                        r: i
                    }, t = this.createElement("circle"), t.xSetter = t.ySetter = function(n, t, i) {
                        i.setAttribute("c" + t, n)
                    }, t.attr(n)
                },
                arc: function(n, t, i, r, u, f) {
                    return c(n) && (t = n.y, i = n.r, r = n.innerR, u = n.start, f = n.end, n = n.x), n = this.symbol("arc", n || 0, t || 0, i || 0, i || 0, {
                        innerR: r || 0,
                        start: u || 0,
                        end: f || 0
                    }), n.r = i, n
                },
                rect: function(n, t, r, u, f, e) {
                    f = c(n) ? n.r : f;
                    var o = this.createElement("rect");
                    return n = c(n) ? n : void 0 === n ? {} : {
                        x: n,
                        y: t,
                        width: Math.max(r, 0),
                        height: Math.max(u, 0)
                    }, void 0 !== e && (n.strokeWidth = e, n = o.crisp(n)), n.fill = "none", f && (n.r = f), o.rSetter = function(n, t, r) {
                        i(r, {
                            rx: n,
                            ry: n
                        })
                    }, o.attr(n)
                },
                setSize: function(n, t, i) {
                    var r = this.alignedObjects,
                        f = r.length;
                    for (this.width = n, this.height = t, this.boxWrapper.animate({
                            width: n,
                            height: t
                        }, {
                            step: function() {
                                this.attr({
                                    viewBox: "0 0 " + this.attr("width") + " " + this.attr("height")
                                })
                            },
                            duration: u(i, !0) ? void 0 : 0
                        }); f--;) r[f].align()
                },
                g: function(n) {
                    var t = this.createElement("g");
                    return n ? t.attr({
                        "class": "highcharts-" + n
                    }) : t
                },
                image: function(n, t, i, r, u) {
                    var f = {
                        preserveAspectRatio: "none"
                    };
                    return 1 < arguments.length && o(f, {
                        x: t,
                        y: i,
                        width: r,
                        height: u
                    }), f = this.createElement("image").attr(f), f.element.setAttributeNS ? f.element.setAttributeNS("http://www.w3.org/1999/xlink", "href", n) : f.element.setAttribute("hc-svg-href", n), f
                },
                symbol: function(n, i, r, s, c, l) {
                    var p = this,
                        a, b = this.symbols[n],
                        d = t(i) && b && this.symbols[n](Math.round(i), Math.round(r), s, c, l),
                        k = /^url\((.*?)\)$/,
                        y, w;
                    return b ? (a = this.path(d), a.attr("fill", "none"), o(a, {
                        symbolName: n,
                        x: i,
                        y: r,
                        width: s,
                        height: c
                    }), l && o(a, l)) : k.test(n) && (y = n.match(k)[1], a = this.image(y), a.imgwidth = u(v[y] && v[y].width, l && l.width), a.imgheight = u(v[y] && v[y].height, l && l.height), w = function() {
                        a.attr({
                            width: a.width,
                            height: a.height
                        })
                    }, e(["width", "height"], function(n) {
                        a[n + "Setter"] = function(n, i) {
                            var u = {},
                                r = this["img" + i],
                                f = "width" === i ? "translateX" : "translateY";
                            this[i] = n;
                            t(r) && (this.element && this.element.setAttribute(i, r), this.alignByTranslate || (u[f] = ((this[i] || 0) - r) / 2, this.attr(u)))
                        }
                    }), t(i) && a.attr({
                        x: i,
                        y: r
                    }), a.isImg = !0, t(a.imgwidth) && t(a.imgheight) ? w() : (a.attr({
                        width: 0,
                        height: 0
                    }), nt("img", {
                        onload: function() {
                            var n = et[p.chartIndex];
                            0 === this.width && (h(this, {
                                position: "absolute",
                                top: "-999em"
                            }), f.body.appendChild(this));
                            v[y] = {
                                width: this.width,
                                height: this.height
                            };
                            a.imgwidth = this.width;
                            a.imgheight = this.height;
                            a.element && w();
                            this.parentNode && this.parentNode.removeChild(this);
                            p.imgCount--;
                            !p.imgCount && n && n.onload && n.onload()
                        },
                        src: y
                    }), this.imgCount++)), a
                },
                symbols: {
                    circle: function(n, t, i, r) {
                        return this.arc(n + i / 2, t + r / 2, i / 2, r / 2, {
                            start: 0,
                            end: 2 * Math.PI,
                            open: !1
                        })
                    },
                    square: function(n, t, i, r) {
                        return ["M", n, t, "L", n + i, t, n + i, t + r, n, t + r, "Z"]
                    },
                    triangle: function(n, t, i, r) {
                        return ["M", n + i / 2, t, "L", n + i, t + r, n, t + r, "Z"]
                    },
                    "triangle-down": function(n, t, i, r) {
                        return ["M", n, t, "L", n + i, t, n + i / 2, t + r, "Z"]
                    },
                    diamond: function(n, t, i, r) {
                        return ["M", n + i / 2, t, "L", n + i, t + r / 2, n + i / 2, t + r, n, t + r / 2, "Z"]
                    },
                    arc: function(n, i, r, u, f) {
                        var s = f.start,
                            e = f.r || r,
                            h = f.r || u || r,
                            o = f.end - .001;
                        r = f.innerR;
                        u = f.open;
                        var c = Math.cos(s),
                            l = Math.sin(s),
                            a = Math.cos(o),
                            o = Math.sin(o);
                        return f = f.end - s < Math.PI ? 0 : 1, e = ["M", n + e * c, i + h * l, "A", e, h, 0, f, 1, n + e * a, i + h * o], t(r) && e.push(u ? "M" : "L", n + r * a, i + r * o, "A", r, r, 0, f, 0, n + r * c, i + r * l), e.push(u ? "" : "Z"), e
                    },
                    callout: function(n, t, i, r, u) {
                        var f = Math.min(u && u.r || 0, i, r),
                            o = f + 6,
                            e = u && u.anchorX,
                            s;
                        return u = u && u.anchorY, s = ["M", n + f, t, "L", n + i - f, t, "C", n + i, t, n + i, t, n + i, t + f, "L", n + i, t + r - f, "C", n + i, t + r, n + i, t + r, n + i - f, t + r, "L", n + f, t + r, "C", n, t + r, n, t + r, n, t + r - f, "L", n, t + f, "C", n, t, n, t, n + f, t], e && e > i ? u > t + o && u < t + r - o ? s.splice(13, 3, "L", n + i, u - 6, n + i + 6, u, n + i, u + 6, n + i, t + r - f) : s.splice(13, 3, "L", n + i, r / 2, e, u, n + i, r / 2, n + i, t + r - f) : e && 0 > e ? u > t + o && u < t + r - o ? s.splice(33, 3, "L", n, u + 6, n - 6, u, n, u - 6, n, t + f) : s.splice(33, 3, "L", n, r / 2, e, u, n, r / 2, n, t + f) : u && u > r && e > n + o && e < n + i - o ? s.splice(23, 3, "L", e + 6, t + r, e, t + r + 6, e - 6, t + r, n + f, t + r) : u && 0 > u && e > n + o && e < n + i - o && s.splice(3, 3, "L", e - 6, t, e, t - 6, e + 6, t, i - f, t), s
                    }
                },
                clipRect: function(t, i, r, u) {
                    var f = n.uniqueKey(),
                        e = this.createElement("clipPath").attr({
                            id: f
                        }).add(this.defs);
                    return t = this.rect(t, i, r, u, 0).add(e), t.id = f, t.clipPath = e, t.count = 0, t
                },
                text: function(n, t, i, r) {
                    var f = !a && this.forExport,
                        u = {};
                    return r && (this.allowHTML || !this.forExport) ? this.html(n, t, i) : (u.x = Math.round(t || 0), i && (u.y = Math.round(i)), (n || 0 === n) && (u.text = n), n = this.createElement("text").attr(u), f && n.css({
                        position: "absolute"
                    }), r || (n.xSetter = function(n, t, i) {
                        for (var f = i.getElementsByTagName("tspan"), r, e = i.getAttribute(t), u = 0; u < f.length; u++) r = f[u], r.getAttribute(t) === e && r.setAttribute(t, n);
                        i.setAttribute(t, n)
                    }), n)
                },
                fontMetrics: function(n, t) {
                    return n = n || t && t.style && t.style.fontSize || this.style && this.style.fontSize, n = /px/.test(n) ? l(n) : /em/.test(n) ? parseFloat(n) * (t ? this.fontMetrics(null, t.parentNode).f : 16) : 12, t = 24 > n ? n + 3 : Math.round(1.2 * n), {
                        h: t,
                        b: Math.round(.8 * t),
                        f: n
                    }
                },
                rotCorr: function(n, t, i) {
                    var r = n;
                    return t && i && (r = Math.max(r * Math.cos(t * w), 4)), {
                        x: -n / 3 * Math.sin(t * w),
                        y: r
                    }
                },
                label: function(n, i, u, f, h, c, l, a, v) {
                    var d = this,
                        y = d.g("button" !== v && "label"),
                        p = y.text = d.text("", 0, 0, l).attr({
                            zIndex: 1
                        }),
                        w, b, ut = 0,
                        k = 3,
                        ft = 0,
                        g, ht, et, ct, tt, lt = {},
                        pt, at, vt = /^url\((.*?)\)$/.test(f),
                        ot = vt,
                        yt, st, it, nt, wt;
                    return v && y.addClass("highcharts-" + v), ot = vt, yt = function() {
                        return (pt || 0) % 2 / 2
                    }, st = function() {
                        var n = p.element.style,
                            i = {};
                        b = (void 0 === g || void 0 === ht || tt) && t(p.textStr) && p.getBBox();
                        y.width = (g || b.width || 0) + 2 * k + ft;
                        y.height = (ht || b.height || 0) + 2 * k;
                        at = k + d.fontMetrics(n && n.fontSize, p).b;
                        ot && (w || (y.box = w = d.symbols[f] || vt ? d.symbol(f) : d.rect(), w.addClass(("button" === v ? "" : "highcharts-label-box") + (v ? " highcharts-" + v + "-box" : "")), w.add(y), n = yt(), i.x = n, i.y = (a ? -at : 0) + n), i.width = Math.round(y.width), i.height = Math.round(y.height), w.attr(o(i, lt)), lt = {})
                    }, it = function() {
                        var i = ft + k,
                            n;
                        n = a ? 0 : at;
                        t(g) && b && ("center" === tt || "right" === tt) && (i += {
                            center: .5,
                            right: 1
                        } [tt] * (g - b.width));
                        (i !== p.x || n !== p.y) && (p.attr("x", i), void 0 !== n && p.attr("y", n));
                        p.x = i;
                        p.y = n
                    }, nt = function(n, t) {
                        w ? w.attr(n, t) : lt[n] = t
                    }, y.onAdd = function() {
                        p.add(y);
                        y.attr({
                            text: n || 0 === n ? n : "",
                            x: i,
                            y: u
                        });
                        w && t(h) && y.attr({
                            anchorX: h,
                            anchorY: c
                        })
                    }, y.widthSetter = function(n) {
                        g = n
                    }, y.heightSetter = function(n) {
                        ht = n
                    }, y["text-alignSetter"] = function(n) {
                        tt = n
                    }, y.paddingSetter = function(n) {
                        t(n) && n !== k && (k = y.padding = n, it())
                    }, y.paddingLeftSetter = function(n) {
                        t(n) && n !== ft && (ft = n, it())
                    }, y.alignSetter = function(n) {
                        n = {
                            left: 0,
                            center: .5,
                            right: 1
                        } [n];
                        n !== ut && (ut = n, b && y.attr({
                            x: et
                        }))
                    }, y.textSetter = function(n) {
                        void 0 !== n && p.textSetter(n);
                        st();
                        it()
                    }, y["stroke-widthSetter"] = function(n, t) {
                        n && (ot = !0);
                        pt = this["stroke-width"] = n;
                        nt(t, n)
                    }, y.strokeSetter = y.fillSetter = y.rSetter = function(n, t) {
                        "fill" === t && n && (ot = !0);
                        nt(t, n)
                    }, y.anchorXSetter = function(n, t) {
                        h = n;
                        nt(t, Math.round(n) - yt() - et)
                    }, y.anchorYSetter = function(n, t) {
                        c = n;
                        nt(t, n - ct)
                    }, y.xSetter = function(n) {
                        y.x = n;
                        ut && (n -= ut * ((g || b.width) + 2 * k));
                        et = Math.round(n);
                        y.attr("translateX", et)
                    }, y.ySetter = function(n) {
                        ct = y.y = Math.round(n);
                        y.attr("translateY", ct)
                    }, wt = y.css, o(y, {
                        css: function(n) {
                            if (n) {
                                var t = {};
                                n = s(n);
                                e(y.textProps, function(i) {
                                    void 0 !== n[i] && (t[i] = n[i], delete n[i])
                                });
                                p.css(t)
                            }
                            return wt.call(y, n)
                        },
                        getBBox: function() {
                            return {
                                width: b.width + 2 * k,
                                height: b.height + 2 * k,
                                x: b.x - k,
                                y: b.y - k
                            }
                        },
                        shadow: function(n) {
                            return n && (st(), w && w.shadow(n)), y
                        },
                        destroy: function() {
                            rt(y.element, "mouseenter");
                            rt(y.element, "mouseleave");
                            p && (p = p.destroy());
                            w && (w = w.destroy());
                            r.prototype.destroy.call(y);
                            y = d = st = it = nt = null
                        }
                    })
                }
            };
            n.Renderer = y
        }(n),
        function(n) {
            var e = n.attr,
                o = n.createElement,
                t = n.css,
                s = n.defined,
                i = n.each,
                r = n.extend,
                u = n.isFirefox,
                h = n.isMS,
                f = n.isWebKit,
                c = n.pInt,
                l = n.SVGRenderer,
                a = n.win,
                v = n.wrap;
            r(n.SVGElement.prototype, {
                htmlCss: function(n) {
                    var i = this.element;
                    return (i = n && "SPAN" === i.tagName && n.width) && (delete n.width, this.textWidth = i, this.updateTransform()), n && "ellipsis" === n.textOverflow && (n.whiteSpace = "nowrap", n.overflow = "hidden"), this.styles = r(this.styles, n), t(this.element, n), this
                },
                htmlGetBBox: function() {
                    var n = this.element;
                    return "text" === n.nodeName && (n.style.position = "absolute"), {
                        x: n.offsetLeft,
                        y: n.offsetTop,
                        width: n.offsetWidth,
                        height: n.offsetHeight
                    }
                },
                htmlUpdateTransform: function() {
                    if (this.added) {
                        var o = this.renderer,
                            n = this.element,
                            h = this.translateX || 0,
                            l = this.translateY || 0,
                            w = this.x || 0,
                            b = this.y || 0,
                            e = this.textAlign || "left",
                            a = {
                                left: 0,
                                center: .5,
                                right: 1
                            } [e],
                            r = this.styles;
                        if (t(n, {
                                marginLeft: h,
                                marginTop: l
                            }), this.shadows && i(this.shadows, function(n) {
                                t(n, {
                                    marginLeft: h + 1,
                                    marginTop: l + 1
                                })
                            }), this.inverted && i(n.childNodes, function(t) {
                                o.invertChild(t, n)
                            }), "SPAN" === n.tagName) {
                            var u = this.rotation,
                                v = c(this.textWidth),
                                y = r && r.whiteSpace,
                                p = [u, e, n.innerHTML, this.textWidth, this.textAlign].join();
                            p !== this.cTT && (r = o.fontMetrics(n.style.fontSize).b, s(u) && this.setSpanRotation(u, a, r), t(n, {
                                width: "",
                                whiteSpace: y || "nowrap"
                            }), n.offsetWidth > v && /[ \-]/.test(n.textContent || n.innerText) && t(n, {
                                width: v + "px",
                                display: "block",
                                whiteSpace: y || "normal"
                            }), this.getSpanCorrection(n.offsetWidth, r, a, u, e));
                            t(n, {
                                left: w + (this.xCorr || 0) + "px",
                                top: b + (this.yCorr || 0) + "px"
                            });
                            f && (r = n.offsetHeight);
                            this.cTT = p
                        }
                    } else this.alignOnAdd = !0
                },
                setSpanRotation: function(n, i, r) {
                    var e = {},
                        o = h ? "-ms-transform" : f ? "-webkit-transform" : u ? "MozTransform" : a.opera ? "-o-transform" : "";
                    e[o] = e.transform = "rotate(" + n + "deg)";
                    e[o + (u ? "Origin" : "-origin")] = e.transformOrigin = 100 * i + "% " + r + "px";
                    t(this.element, e)
                },
                getSpanCorrection: function(n, t, i) {
                    this.xCorr = -n * i;
                    this.yCorr = -t
                }
            });
            r(l.prototype, {
                html: function(n, t, u) {
                    var f = this.createElement("span"),
                        s = f.element,
                        h = f.renderer,
                        c = h.isSVG,
                        l = function(n, t) {
                            i(["opacity", "visibility"], function(i) {
                                v(n, i + "Setter", function(n, i, r, u) {
                                    n.call(this, i, r, u);
                                    t[r] = i
                                })
                            })
                        };
                    return f.textSetter = function(n) {
                        n !== s.innerHTML && delete this.bBox;
                        s.innerHTML = this.textStr = n;
                        f.htmlUpdateTransform()
                    }, c && l(f, f.element.style), f.xSetter = f.ySetter = f.alignSetter = f.rotationSetter = function(n, t) {
                        "align" === t && (t = "textAlign");
                        f[t] = n;
                        f.htmlUpdateTransform()
                    }, f.attr({
                        text: n,
                        x: Math.round(t),
                        y: Math.round(u)
                    }).css({
                        fontFamily: this.style.fontFamily,
                        fontSize: this.style.fontSize,
                        position: "absolute"
                    }), s.style.whiteSpace = "nowrap", f.css = f.htmlCss, c && (f.add = function(n) {
                        var t, c = h.box.parentNode,
                            u = [];
                        if (this.parentGroup = n) {
                            if (t = n.div, !t) {
                                for (; n;) u.push(n), n = n.parentGroup;
                                i(u.reverse(), function(n) {
                                    var i, s = e(n.element, "class");
                                    s && (s = {
                                        className: s
                                    });
                                    t = n.div = n.div || o("div", s, {
                                        position: "absolute",
                                        left: (n.translateX || 0) + "px",
                                        top: (n.translateY || 0) + "px",
                                        display: n.display,
                                        opacity: n.opacity,
                                        pointerEvents: n.styles && n.styles.pointerEvents
                                    }, t || c);
                                    i = t.style;
                                    r(n, {
                                        on: function() {
                                            return f.on.apply({
                                                element: u[0].div
                                            }, arguments), n
                                        },
                                        translateXSetter: function(t, r) {
                                            i.left = t + "px";
                                            n[r] = t;
                                            n.doTransform = !0
                                        },
                                        translateYSetter: function(t, r) {
                                            i.top = t + "px";
                                            n[r] = t;
                                            n.doTransform = !0
                                        }
                                    });
                                    l(n, i)
                                })
                            }
                        } else t = c;
                        return t.appendChild(s), f.added = !0, f.alignOnAdd && f.htmlUpdateTransform(), f
                    }), f
                }
            })
        }(n),
        function(n) {
            var e, t, u = n.createElement,
                s = n.css,
                y = n.defined,
                f = n.deg2rad,
                p = n.discardElement,
                i = n.doc,
                h = n.each,
                w = n.erase,
                a = n.extend;
            e = n.extendClass;
            var d = n.isArray,
                b = n.isNumber,
                v = n.isObject,
                g = n.merge;
            t = n.noop;
            var c = n.pick,
                r = n.pInt,
                l = n.SVGElement,
                o = n.SVGRenderer,
                k = n.win;
            n.svg || (t = {
                docMode8: i && 8 === i.documentMode,
                init: function(n, t) {
                    var i = ["<", t, ' filled="f" stroked="f"'],
                        r = ["position: ", "absolute", ";"],
                        f = "div" === t;
                    ("shape" === t || f) && r.push("left:0;top:0;width:1px;height:1px;");
                    r.push("visibility: ", f ? "hidden" : "visible");
                    i.push(' style="', r.join(""), '"/>');
                    t && (i = f || "span" === t || "img" === t ? i.join("") : n.prepVML(i), this.element = u(i));
                    this.renderer = n
                },
                add: function(n) {
                    var i = this.renderer,
                        r = this.element,
                        t = i.box,
                        u = n && n.inverted,
                        t = n ? n.element || n : t;
                    return n && (this.parentGroup = n), u && i.invertChild(r, t), t.appendChild(r), this.added = !0, this.alignOnAdd && !this.deferUpdateTransform && this.updateTransform(), this.onAdd && this.onAdd(), this.className && this.attr("class", this.className), this
                },
                updateTransform: l.prototype.htmlUpdateTransform,
                setSpanRotation: function() {
                    var n = this.rotation,
                        t = Math.cos(n * f),
                        i = Math.sin(n * f);
                    s(this.element, {
                        filter: n ? ["progid:DXImageTransform.Microsoft.Matrix(M11=", t, ", M12=", -i, ", M21=", i, ", M22=", t, ", sizingMethod='auto expand')"].join("") : "none"
                    })
                },
                getSpanCorrection: function(n, t, i, r, u) {
                    var e = r ? Math.cos(r * f) : 1,
                        o = r ? Math.sin(r * f) : 0,
                        l = c(this.elemHeight, this.element.offsetHeight),
                        h;
                    this.xCorr = 0 > e && -n;
                    this.yCorr = 0 > o && -l;
                    h = 0 > e * o;
                    this.xCorr += o * t * (h ? 1 - i : i);
                    this.yCorr -= e * t * (r ? h ? i : 1 - i : 1);
                    u && "left" !== u && (this.xCorr -= n * i * (0 > e ? -1 : 1), r && (this.yCorr -= l * i * (0 > o ? -1 : 1)), s(this.element, {
                        textAlign: u
                    }))
                },
                pathToVML: function(n) {
                    for (var t = n.length, i = []; t--;) b(n[t]) ? i[t] = Math.round(10 * n[t]) - 5 : "Z" === n[t] ? i[t] = "x" : (i[t] = n[t], !n.isArc || "wa" !== n[t] && "at" !== n[t] || (i[t + 5] === i[t + 7] && (i[t + 7] += n[t + 7] > n[t + 5] ? 1 : -1), i[t + 6] === i[t + 8] && (i[t + 8] += n[t + 8] > n[t + 6] ? 1 : -1)));
                    return i.join(" ") || "x"
                },
                clip: function(n) {
                    var t = this,
                        i;
                    return n ? (i = n.members, w(i, t), i.push(t), t.destroyClip = function() {
                        w(i, t)
                    }, n = n.getCSS(t)) : (t.destroyClip && t.destroyClip(), n = {
                        clip: t.docMode8 ? "inherit" : "rect(auto)"
                    }), t.css(n)
                },
                css: l.prototype.htmlCss,
                safeRemoveChild: function(n) {
                    n.parentNode && p(n)
                },
                destroy: function() {
                    return this.destroyClip && this.destroyClip(), l.prototype.destroy.apply(this)
                },
                on: function(n, t) {
                    return this.element["on" + n] = function() {
                        var n = k.event;
                        n.target = n.srcElement;
                        t(n)
                    }, this
                },
                cutOffPath: function(n, t) {
                    var i;
                    return n = n.split(/[ ,]/), i = n.length, (9 === i || 11 === i) && (n[i - 4] = n[i - 2] = r(n[i - 2]) - 10 * t), n.join(" ")
                },
                shadow: function(n, t, i) {
                    var y = [],
                        e, o = this.element,
                        p = this.renderer,
                        f, w = o.style,
                        h, s = o.path,
                        l, a, v, b;
                    if (s && "string" != typeof s.value && (s = "x"), a = s, n) {
                        for (v = c(n.width, 3), b = (n.opacity || .15) / v, e = 1; 3 >= e; e++) l = 2 * v + 1 - 2 * e, i && (a = this.cutOffPath(s.value, l + .5)), h = ['<shape isShadow="true" strokeweight="', l, '" filled="false" path="', a, '" coordsize="10 10" style="', o.style.cssText, '" />'], f = u(p.prepVML(h), null, {
                            left: r(w.left) + c(n.offsetX, 1),
                            top: r(w.top) + c(n.offsetY, 1)
                        }), i && (f.cutOff = l + 1), h = ['<stroke color="', n.color || "#000000", '" opacity="', b * e, '"/>'], u(p.prepVML(h), null, null, f), t ? t.element.appendChild(f) : o.parentNode.insertBefore(f, o), y.push(f);
                        this.shadows = y
                    }
                    return this
                },
                updateShadows: t,
                setAttr: function(n, t) {
                    this.docMode8 ? this.element[n] = t : this.element.setAttribute(n, t)
                },
                classSetter: function(n) {
                    (this.added ? this.element : this).className = n
                },
                dashstyleSetter: function(n, t, i) {
                    (i.getElementsByTagName("stroke")[0] || u(this.renderer.prepVML(["<stroke/>"]), null, null, i))[t] = n || "solid";
                    this[t] = n
                },
                dSetter: function(n, t, i) {
                    var r = this.shadows;
                    if (n = n || [], this.d = n.join && n.join(" "), i.path = n = this.pathToVML(n), r)
                        for (i = r.length; i--;) r[i].path = r[i].cutOff ? this.cutOffPath(n, r[i].cutOff) : n;
                    this.setAttr(t, n)
                },
                fillSetter: function(n, t, i) {
                    var r = i.nodeName;
                    "SPAN" === r ? i.style.color = n : "IMG" !== r && (i.filled = "none" !== n, this.setAttr("fillcolor", this.renderer.color(n, i, t, this)))
                },
                "fill-opacitySetter": function(n, t, i) {
                    u(this.renderer.prepVML(["<", t.split("-")[0], ' opacity="', n, '"/>']), null, null, i)
                },
                opacitySetter: t,
                rotationSetter: function(n, t, i) {
                    i = i.style;
                    this[t] = i[t] = n;
                    i.left = -Math.round(Math.sin(n * f) + 1) + "px";
                    i.top = Math.round(Math.cos(n * f)) + "px"
                },
                strokeSetter: function(n, t, i) {
                    this.setAttr("strokecolor", this.renderer.color(n, i, t, this))
                },
                "stroke-widthSetter": function(n, t, i) {
                    i.stroked = !!n;
                    this[t] = n;
                    b(n) && (n += "px");
                    this.setAttr("strokeweight", n)
                },
                titleSetter: function(n, t) {
                    this.setAttr(t, n)
                },
                visibilitySetter: function(n, t, i) {
                    "inherit" === n && (n = "visible");
                    this.shadows && h(this.shadows, function(i) {
                        i.style[t] = n
                    });
                    "DIV" === i.nodeName && (n = "hidden" === n ? "-999em" : 0, this.docMode8 || (i.style[t] = n ? "visible" : "hidden"), t = "top");
                    i.style[t] = n
                },
                xSetter: function(n, t, i) {
                    this[t] = n;
                    "x" === t ? t = "left" : "y" === t && (t = "top");
                    this.updateClipping ? (this[t] = n, this.updateClipping()) : i.style[t] = n
                },
                zIndexSetter: function(n, t, i) {
                    i.style[t] = n
                }
            }, t["stroke-opacitySetter"] = t["fill-opacitySetter"], n.VMLElement = t = e(l, t), t.prototype.ySetter = t.prototype.widthSetter = t.prototype.heightSetter = t.prototype.xSetter, t = {
                Element: t,
                isIE8: -1 < k.navigator.userAgent.indexOf("MSIE 8.0"),
                init: function(n, t, r) {
                    var u, f;
                    if (this.alignedObjects = [], u = this.createElement("div").css({
                            position: "relative"
                        }), f = u.element, n.appendChild(u.element), this.isVML = !0, this.box = f, this.boxWrapper = u, this.gradients = {}, this.cache = {}, this.cacheKeys = [], this.imgCount = 0, this.setSize(t, r, !1), !i.namespaces.hcv) {
                        i.namespaces.add("hcv", "urn:schemas-microsoft-com:vml");
                        try {
                            i.createStyleSheet().cssText = "hcv\\:fill, hcv\\:path, hcv\\:shape, hcv\\:stroke{ behavior:url(#default#VML); display: inline-block; } "
                        } catch (e) {
                            i.styleSheets[0].cssText += "hcv\\:fill, hcv\\:path, hcv\\:shape, hcv\\:stroke{ behavior:url(#default#VML); display: inline-block; } "
                        }
                    }
                },
                isHidden: function() {
                    return !this.box.offsetWidth
                },
                clipRect: function(n, t, i, r) {
                    var f = this.createElement(),
                        u = v(n);
                    return a(f, {
                        members: [],
                        count: 0,
                        left: (u ? n.x : n) + 1,
                        top: (u ? n.y : t) + 1,
                        width: (u ? n.width : i) - 1,
                        height: (u ? n.height : r) - 1,
                        getCSS: function(n) {
                            var t = n.element,
                                e = t.nodeName,
                                i = n.inverted,
                                r = this.top - ("shape" === e ? t.offsetTop : 0),
                                u = this.left,
                                t = u + this.width,
                                f = r + this.height,
                                r = {
                                    clip: "rect(" + Math.round(i ? u : r) + "px," + Math.round(i ? f : t) + "px," + Math.round(i ? t : f) + "px," + Math.round(i ? r : u) + "px)"
                                };
                            return !i && n.docMode8 && "DIV" === e && a(r, {
                                width: t + "px",
                                height: f + "px"
                            }), r
                        },
                        updateClipping: function() {
                            h(f.members, function(n) {
                                n.element && n.css(f.getCSS(n))
                            })
                        }
                    })
                },
                color: function(t, i, r, f) {
                    var ht = this,
                        c, g = /^rgba/,
                        nt, a, o = "none",
                        l, p, e, v, tt, it, rt, ut, w, b, k, d;
                    if (t && t.linearGradient ? a = "gradient" : t && t.radialGradient && (a = "pattern"), a)
                        if (e = t.linearGradient || t.radialGradient, w = "", t = t.stops, k = [], d = function() {
                                nt = ['<fill colors="' + k.join(",") + '" opacity="', it, '" o:opacity2="', tt, '" type="', a, '" ', w, 'focus="100%" method="any" />'];
                                u(ht.prepVML(nt), null, null, i)
                            }, v = t[0], b = t[t.length - 1], 0 < v[0] && t.unshift([0, v[1]]), 1 > b[0] && t.push([1, b[1]]), h(t, function(t, i) {
                                g.test(t[1]) ? (c = n.color(t[1]), l = c.get("rgb"), p = c.get("a")) : (l = t[1], p = 1);
                                k.push(100 * t[0] + "% " + l);
                                i ? (it = p, rt = l) : (tt = p, ut = l)
                            }), "fill" === r)
                            if ("gradient" === a) r = e.x1 || e[0] || 0, t = e.y1 || e[1] || 0, v = e.x2 || e[2] || 0, e = e.y2 || e[3] || 0, w = 'angle="' + (90 - 180 * Math.atan((e - t) / (v - r)) / Math.PI) + '"', d();
                            else {
                                var o = e.r,
                                    ft = 2 * o,
                                    et = 2 * o,
                                    ot = e.cx,
                                    st = e.cy,
                                    y = i.radialReference,
                                    s, o = function() {
                                        y && (s = f.getBBox(), ot += (y[0] - s.x) / s.width - .5, st += (y[1] - s.y) / s.height - .5, ft *= y[2] / s.width, et *= y[2] / s.height);
                                        w = 'src="' + n.getOptions().global.VMLRadialGradientURL + '" size="' + ft + "," + et + '" origin="0.5,0.5" position="' + ot + "," + st + '" color2="' + ut + '" ';
                                        d()
                                    };
                                f.added ? o() : f.onAdd = o;
                                o = rt
                            }
                    else o = l;
                    else g.test(t) && "IMG" !== i.tagName ? (c = n.color(t), f[r + "-opacitySetter"](c.get("a"), r, i), o = c.get("rgb")) : (o = i.getElementsByTagName(r), o.length && (o[0].opacity = 1, o[0].type = "solid"), o = t);
                    return o
                },
                prepVML: function(n) {
                    var t = this.isIE8;
                    return n = n.join(""), t ? (n = n.replace("/>", ' xmlns="urn:schemas-microsoft-com:vml" />'), n = -1 === n.indexOf('style="') ? n.replace("/>", ' style="display:inline-block;behavior:url(#default#VML);" />') : n.replace('style="', 'style="display:inline-block;behavior:url(#default#VML);')) : n = n.replace("<", "<hcv:"), n
                },
                text: o.prototype.html,
                path: function(n) {
                    var t = {
                        coordsize: "10 10"
                    };
                    return d(n) ? t.d = n : v(n) && a(t, n), this.createElement("shape").attr(t)
                },
                circle: function(n, t, i) {
                    var r = this.symbol("circle");
                    return v(n) && (i = n.r, t = n.y, n = n.x), r.isCircle = !0, r.r = i, r.attr({
                        x: n,
                        y: t
                    })
                },
                g: function(n) {
                    var t;
                    return n && (t = {
                        className: "highcharts-" + n,
                        "class": "highcharts-" + n
                    }), this.createElement("div").attr(t)
                },
                image: function(n, t, i, r, u) {
                    var f = this.createElement("img").attr({
                        src: n
                    });
                    return 1 < arguments.length && f.attr({
                        x: t,
                        y: i,
                        width: r,
                        height: u
                    }), f
                },
                createElement: function(n) {
                    return "rect" === n ? this.symbol(n) : o.prototype.createElement.call(this, n)
                },
                invertChild: function(n, t) {
                    var u = this,
                        i;
                    t = t.style;
                    i = "IMG" === n.tagName && n.style;
                    s(n, {
                        flip: "x",
                        left: r(t.width) - (i ? r(i.top) : 1),
                        top: r(t.height) - (i ? r(i.left) : 1),
                        rotation: -90
                    });
                    h(n.childNodes, function(t) {
                        u.invertChild(t, n)
                    })
                },
                symbols: {
                    arc: function(n, t, i, r, u) {
                        var f = u.start,
                            o = u.end,
                            e = u.r || i || r;
                        i = u.innerR;
                        r = Math.cos(f);
                        var s = Math.sin(f),
                            h = Math.cos(o),
                            c = Math.sin(o);
                        return 0 == o - f ? ["x"] : (f = ["wa", n - e, t - e, n + e, t + e, n + e * r, t + e * s, n + e * h, t + e * c], u.open && !i && f.push("e", "M", n, t), f.push("at", n - i, t - i, n + i, t + i, n + i * h, t + i * c, n + i * r, t + i * s, "x", "e"), f.isArc = !0, f)
                    },
                    circle: function(n, t, i, r, u) {
                        return u && y(u.r) && (i = r = 2 * u.r), u && u.isCircle && (n -= i / 2, t -= r / 2), ["wa", n, t, n + i, t + r, n + i, t + r / 2, n + i, t + r / 2, "e"]
                    },
                    rect: function(n, t, i, r, u) {
                        return o.prototype.symbols[y(u) && u.r ? "callout" : "square"].call(0, n, t, i, r, u)
                    }
                }
            }, n.VMLRenderer = e = function() {
                this.init.apply(this, arguments)
            }, e.prototype = g(o.prototype, t), n.Renderer = e);
            o.prototype.measureSpanWidth = function(n, t) {
                var r = i.createElement("span");
                return n = i.createTextNode(n), r.appendChild(n), s(r, t), this.box.appendChild(r), t = r.offsetWidth, p(r), t
            }
        }(n),
        function(n) {
            function f() {
                var t = n.defaultOptions.global,
                    i = u.moment;
                if (t.timezone) {
                    if (i) return function(n) {
                        return -i.tz(n, t.timezone).utcOffset()
                    };
                    n.error(25)
                }
                return t.useUTC && t.getTimezoneOffset
            }

            function i() {
                var s = n.defaultOptions.global,
                    i, e = s.useUTC,
                    h = e ? "getUTC" : "get",
                    c = e ? "setUTC" : "set";
                n.Date = i = s.Date || u.Date;
                i.hcTimezoneOffset = e && s.timezoneOffset;
                i.hcGetTimezoneOffset = f();
                i.hcMakeTime = function(n, r, u, f, s, h) {
                    var c;
                    return e ? (c = i.UTC.apply(0, arguments), c += o(c)) : c = new i(n, r, t(u, 1), t(f, 0), t(s, 0), t(h, 0)).getTime(), c
                };
                r("Minutes Hours Day Date Month FullYear".split(" "), function(n) {
                    i["hcGet" + n] = h + n
                });
                r("Milliseconds Seconds Minutes Hours Date Month FullYear".split(" "), function(n) {
                    i["hcSet" + n] = c + n
                })
            }
            var e = n.color,
                r = n.each,
                o = n.getTZOffset,
                s = n.merge,
                t = n.pick,
                u = n.win;
            n.defaultOptions = {
                colors: "#7cb5ec #434348 #90ed7d #f7a35c #8085e9 #f15c80 #e4d354 #2b908f #f45b5b #91e8e1".split(" "),
                symbols: ["circle", "diamond", "square", "triangle", "triangle-down"],
                lang: {
                    loading: "Loading...",
                    months: "January February March April May June July August September October November December".split(" "),
                    shortMonths: "Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec".split(" "),
                    weekdays: "Sunday Monday Tuesday Wednesday Thursday Friday Saturday".split(" "),
                    decimalPoint: ".",
                    numericSymbols: "kMGTPE".split(""),
                    resetZoom: "Reset zoom",
                    resetZoomTitle: "Reset zoom level 1:1",
                    thousandsSep: " "
                },
                global: {
                    useUTC: !0,
                    VMLRadialGradientURL: "http://code.highcharts.com/5.0.7/gfx/vml-radial-gradient.png"
                },
                chart: {
                    borderRadius: 0,
                    defaultSeriesType: "line",
                    ignoreHiddenSeries: !0,
                    spacing: [10, 10, 15, 10],
                    resetZoomButton: {
                        theme: {
                            zIndex: 20
                        },
                        position: {
                            align: "right",
                            x: -10,
                            y: 10
                        }
                    },
                    width: null,
                    height: null,
                    borderColor: "#335cad",
                    backgroundColor: "#ffffff",
                    plotBorderColor: "#cccccc"
                },
                title: {
                    text: "Chart title",
                    align: "center",
                    margin: 15,
                    widthAdjust: -44
                },
                subtitle: {
                    text: "",
                    align: "center",
                    widthAdjust: -44
                },
                plotOptions: {},
                labels: {
                    style: {
                        position: "absolute",
                        color: "#333333"
                    }
                },
                legend: {
                    enabled: !0,
                    align: "center",
                    layout: "horizontal",
                    labelFormatter: function() {
                        return this.name
                    },
                    borderColor: "#999999",
                    borderRadius: 0,
                    navigation: {
                        activeColor: "#003399",
                        inactiveColor: "#cccccc"
                    },
                    itemStyle: {
                        color: "#333333",
                        fontSize: "12px",
                        fontWeight: "bold"
                    },
                    itemHoverStyle: {
                        color: "#000000"
                    },
                    itemHiddenStyle: {
                        color: "#cccccc"
                    },
                    shadow: !1,
                    itemCheckboxStyle: {
                        position: "absolute",
                        width: "13px",
                        height: "13px"
                    },
                    squareSymbol: !0,
                    symbolPadding: 5,
                    verticalAlign: "bottom",
                    x: 0,
                    y: 0,
                    title: {
                        style: {
                            fontWeight: "bold"
                        }
                    }
                },
                loading: {
                    labelStyle: {
                        fontWeight: "bold",
                        position: "relative",
                        top: "45%"
                    },
                    style: {
                        position: "absolute",
                        backgroundColor: "#ffffff",
                        opacity: .5,
                        textAlign: "center"
                    }
                },
                tooltip: {
                    enabled: !0,
                    animation: n.svg,
                    borderRadius: 3,
                    dateTimeLabelFormats: {
                        millisecond: "%A, %b %e, %H:%M:%S.%L",
                        second: "%A, %b %e, %H:%M:%S",
                        minute: "%A, %b %e, %H:%M",
                        hour: "%A, %b %e, %H:%M",
                        day: "%A, %b %e, %Y",
                        week: "Week from %A, %b %e, %Y",
                        month: "%B %Y",
                        year: "%Y"
                    },
                    footerFormat: "",
                    padding: 8,
                    snap: n.isTouchDevice ? 25 : 10,
                    backgroundColor: e("#f7f7f7").setOpacity(.85).get(),
                    borderWidth: 1,
                    headerFormat: '<span style="font-size: 10px">{point.key}<\/span><br/>',
                    pointFormat: '<span style="color:{point.color}">●<\/span> {series.name}: <b>{point.y}<\/b><br/>',
                    shadow: !0,
                    style: {
                        color: "#333333",
                        cursor: "default",
                        fontSize: "12px",
                        pointerEvents: "none",
                        whiteSpace: "nowrap"
                    }
                },
                credits: {
                    enabled: !0,
                    href: "http://www.highcharts.com",
                    position: {
                        align: "right",
                        x: -10,
                        verticalAlign: "bottom",
                        y: -5
                    },
                    style: {
                        cursor: "pointer",
                        color: "#999999",
                        fontSize: "9px"
                    },
                    text: "Highcharts.com"
                }
            };
            n.setOptions = function(t) {
                return n.defaultOptions = s(!0, n.defaultOptions, t), i(), n.defaultOptions
            };
            n.getOptions = function() {
                return n.defaultOptions
            };
            n.defaultPlotOptions = n.defaultOptions.plotOptions;
            i()
        }(n),
        function(n) {
            var i = n.arrayMax,
                r = n.arrayMin,
                t = n.defined,
                f = n.destroyObjectProperties,
                e = n.each,
                u = n.erase,
                o = n.merge,
                s = n.pick;
            n.PlotLineOrBand = function(n, t) {
                this.axis = n;
                t && (this.options = t, this.id = t.id)
            };
            n.PlotLineOrBand.prototype = {
                render: function() {
                    var e = this,
                        u = e.axis,
                        c = u.horiz,
                        n = e.options,
                        l = n.label,
                        a = e.label,
                        v = n.to,
                        y = n.from,
                        p = n.value,
                        r = t(y) && t(v),
                        g = t(p),
                        f = e.svgElem,
                        nt = !f,
                        i = [],
                        tt, h = n.color,
                        b = s(n.zIndex, 0),
                        k = n.events,
                        i = {
                            "class": "highcharts-plot-" + (r ? "band " : "line ") + (n.className || "")
                        },
                        it = {},
                        rt = u.chart.renderer,
                        w = r ? "bands" : "lines",
                        d = u.log2lin;
                    if (u.isLog && (y = d(y), v = d(v), p = d(p)), g ? (i = {
                            stroke: h,
                            "stroke-width": n.width
                        }, n.dashStyle && (i.dashstyle = n.dashStyle)) : r && (h && (i.fill = h), n.borderWidth && (i.stroke = n.borderColor, i["stroke-width"] = n.borderWidth)), it.zIndex = b, w += "-" + b, (h = u[w]) || (u[w] = h = rt.g("plot-" + w).attr(it).add()), nt && (e.svgElem = f = rt.path().attr(i).add(h)), g) i = u.getPlotLinePath(p, f.strokeWidth());
                    else if (r) i = u.getPlotBandPath(y, v, n);
                    else return;
                    if (nt && i && i.length) {
                        if (f.attr({
                                d: i
                            }), k)
                            for (tt in n = function(n) {
                                    f.on(n, function(t) {
                                        k[n].apply(e, [t])
                                    })
                                }, k) n(tt)
                    } else f && (i ? (f.show(), f.animate({
                        d: i
                    })) : (f.hide(), a && (e.label = a = a.destroy())));
                    return l && t(l.text) && i && i.length && 0 < u.width && 0 < u.height && !i.flat ? (l = o({
                        align: c && r && "center",
                        x: c ? !r && 4 : 10,
                        verticalAlign: !c && r && "middle",
                        y: c ? r ? 16 : 10 : r ? 6 : -4,
                        rotation: c && !r && 90
                    }, l), this.renderLabel(l, i, r, b)) : a && a.hide(), e
                },
                renderLabel: function(n, t, u, f) {
                    var e = this.label,
                        o = this.axis.chart.renderer;
                    e || (e = {
                        align: n.textAlign || n.align,
                        rotation: n.rotation,
                        "class": "highcharts-plot-" + (u ? "band" : "line") + "-label " + (n.className || "")
                    }, e.zIndex = f, this.label = e = o.text(n.text, 0, 0, n.useHTML).attr(e).add(), e.css(n.style));
                    f = [t[1], t[4], u ? t[6] : t[1]];
                    t = [t[2], t[5], u ? t[7] : t[2]];
                    u = r(f);
                    o = r(t);
                    e.align(n, !1, {
                        x: u,
                        y: o,
                        width: i(f) - u,
                        height: i(t) - o
                    });
                    e.show()
                },
                destroy: function() {
                    u(this.axis.plotLinesAndBands, this);
                    delete this.axis;
                    f(this)
                }
            };
            n.AxisPlotLineOrBandExtension = {
                getPlotBandPath: function(n, t) {
                    return t = this.getPlotLinePath(t, null, null, !0), (n = this.getPlotLinePath(n, null, null, !0)) && t ? (n.flat = n.toString() === t.toString(), n.push(t[4], t[5], t[1], t[2], "z")) : n = null, n
                },
                addPlotBand: function(n) {
                    return this.addPlotBandOrLine(n, "plotBands")
                },
                addPlotLine: function(n) {
                    return this.addPlotBandOrLine(n, "plotLines")
                },
                addPlotBandOrLine: function(t, i) {
                    var r = new n.PlotLineOrBand(this, t).render(),
                        u = this.userOptions;
                    return r && (i && (u[i] = u[i] || [], u[i].push(t)), this.plotLinesAndBands.push(r)), r
                },
                removePlotBandOrLine: function(n) {
                    for (var i = this.plotLinesAndBands, r = this.options, f = this.userOptions, t = i.length; t--;) i[t].id === n && i[t].destroy();
                    e([r.plotLines || [], f.plotLines || [], r.plotBands || [], f.plotBands || []], function(i) {
                        for (t = i.length; t--;) i[t].id === n && u(i, i[t])
                    })
                }
            }
        }(n),
        function(n) {
            var f = n.correctFloat,
                i = n.defined,
                e = n.destroyObjectProperties,
                u = n.isNumber,
                o = n.merge,
                t = n.pick,
                r = n.deg2rad;
            n.Tick = function(n, t, i, r) {
                this.axis = n;
                this.pos = t;
                this.type = i || "";
                this.isNew = !0;
                i || r || this.addLabel()
            };
            n.Tick.prototype = {
                addLabel: function() {
                    var n = this.axis,
                        s = n.options,
                        l = n.chart,
                        r = n.categories,
                        h = n.names,
                        u = this.pos,
                        c = s.labels,
                        e = n.tickPositions,
                        a = u === e[0],
                        v = u === e[e.length - 1],
                        h = r ? t(r[u], h[u], u) : u,
                        r = this.label,
                        e = e.info,
                        y;
                    n.isDatetimeAxis && e && (y = s.dateTimeLabelFormats[e.higherRanks[u] || e.unitName]);
                    this.isFirst = a;
                    this.isLast = v;
                    s = n.labelFormatter.call({
                        axis: n,
                        chart: l,
                        isFirst: a,
                        isLast: v,
                        dateTimeLabelFormat: y,
                        value: n.isLog ? f(n.lin2log(h)) : h
                    });
                    i(r) ? r && r.attr({
                        text: s
                    }) : (this.labelLength = (this.label = r = i(s) && c.enabled ? l.renderer.text(s, 0, 0, c.useHTML).css(o(c.style)).add(n.labelGroup) : null) && r.getBBox().width, this.rotation = 0)
                },
                getLabelSize: function() {
                    return this.label ? this.label.getBBox()[this.axis.horiz ? "height" : "width"] : 0
                },
                handleOverflow: function(n) {
                    var i = this.axis,
                        e = n.x,
                        a = i.chart.chartWidth,
                        s = i.chart.spacing,
                        v = t(i.labelLeft, Math.min(i.pos, s[3])),
                        s = t(i.labelRight, Math.max(i.pos + i.len, a - s[1])),
                        y = this.label,
                        h = this.rotation,
                        f = {
                            left: 0,
                            center: .5,
                            right: 1
                        } [i.labelAlign],
                        o = y.getBBox().width,
                        c = i.getSlotWidth(),
                        u = c,
                        w = 1,
                        l, p = {};
                    h ? 0 > h && e - f * o < v ? l = Math.round(e / Math.cos(h * r) - v) : 0 < h && e + f * o > s && (l = Math.round((a - e) / Math.cos(h * r))) : (a = e + (1 - f) * o, e - f * o < v ? u = n.x + u * (1 - f) - v : a > s && (u = s - n.x + u * f, w = -1), u = Math.min(c, u), u < c && "center" === i.labelAlign && (n.x += w * (c - u - f * (c - Math.min(o, u)))), o > u || i.autoRotation && (y.styles || {}).width) && (l = u);
                    l && (p.width = l, (i.options.labels.style || {}).textOverflow || (p.textOverflow = "ellipsis"), y.css(p))
                },
                getPosition: function(n, t, i, r) {
                    var u = this.axis,
                        f = u.chart,
                        e = r && f.oldChartHeight || f.chartHeight;
                    return {
                        x: n ? u.translate(t + i, null, null, r) + u.transB : u.left + u.offset + (u.opposite ? (r && f.oldChartWidth || f.chartWidth) - u.right - u.left : 0),
                        y: n ? e - u.bottom + u.offset - (u.opposite ? u.height : 0) : e - u.translate(t + i, null, null, r) - u.transB
                    }
                },
                getLabelPosition: function(n, t, u, f, e, o, s, h) {
                    var c = this.axis,
                        y = c.transA,
                        p = c.reversed,
                        l = c.staggerLines,
                        a = c.tickRotCorr || {
                            x: 0,
                            y: 0
                        },
                        v = e.y;
                    return i(v) || (v = 0 === c.side ? u.rotation ? -8 : -u.getBBox().height : 2 === c.side ? a.y + 8 : Math.cos(u.rotation * r) * (a.y - u.getBBox(!1, 0).height / 2)), n = n + e.x + a.x - (o && f ? o * y * (p ? -1 : 1) : 0), t = t + v - (o && !f ? o * y * (p ? 1 : -1) : 0), l && (u = s / (h || 1) % l, c.opposite && (u = l - u - 1), t += c.labelOffset / l * u), {
                        x: n,
                        y: Math.round(t)
                    }
                },
                getMarkPath: function(n, t, i, r, u, f) {
                    return f.crispLine(["M", n, t, "L", n + (u ? 0 : -i), t + (u ? i : 0)], r)
                },
                render: function(n, i, r) {
                    var f = this.axis,
                        s = f.options,
                        g = f.chart.renderer,
                        h = f.horiz,
                        e = this.type,
                        l = this.label,
                        y = this.pos,
                        p = s.labels,
                        a = this.gridLine,
                        w = e ? e + "Tick" : "tick",
                        b = f.tickSize(w),
                        v = this.mark,
                        rt = !v,
                        nt = p.step,
                        c = {},
                        tt = !0,
                        it = f.tickmarkOffset,
                        o = this.getPosition(h, y, it, i),
                        k = o.x,
                        o = o.y,
                        ut = h && k === f.pos + f.len || !h && o === f.pos ? -1 : 1,
                        d = e ? e + "Grid" : "grid",
                        et = s[d + "LineWidth"],
                        ot = s[d + "LineColor"],
                        ft = s[d + "LineDashStyle"],
                        d = t(s[w + "Width"], !e && f.isXAxis ? 1 : 0),
                        w = s[w + "Color"];
                    r = t(r, 1);
                    this.isActive = !0;
                    a || (c.stroke = ot, c["stroke-width"] = et, ft && (c.dashstyle = ft), e || (c.zIndex = 1), i && (c.opacity = 0), this.gridLine = a = g.path().attr(c).addClass("highcharts-" + (e ? e + "-" : "") + "grid-line").add(f.gridGroup));
                    !i && a && (y = f.getPlotLinePath(y + it, a.strokeWidth() * ut, i, !0)) && a[this.isNew ? "attr" : "animate"]({
                        d: y,
                        opacity: r
                    });
                    b && (f.opposite && (b[0] = -b[0]), rt && (this.mark = v = g.path().addClass("highcharts-" + (e ? e + "-" : "") + "tick").add(f.axisGroup), v.attr({
                        stroke: w,
                        "stroke-width": d
                    })), v[rt ? "attr" : "animate"]({
                        d: this.getMarkPath(k, o, b[0], v.strokeWidth() * ut, h, g),
                        opacity: r
                    }));
                    l && u(k) && (l.xy = o = this.getLabelPosition(k, o, l, h, p, it, n, nt), this.isFirst && !this.isLast && !t(s.showFirstLabel, 1) || this.isLast && !this.isFirst && !t(s.showLastLabel, 1) ? tt = !1 : !h || f.isRadial || p.step || p.rotation || i || 0 === r || this.handleOverflow(o), nt && n % nt && (tt = !1), tt && u(o.y) ? (o.opacity = r, l[this.isNew ? "attr" : "animate"](o)) : l.attr("y", -9999), this.isNew = !1)
                },
                destroy: function() {
                    e(this, this.axis)
                }
            }
        }(n),
        function(n) {
            var w = n.addEvent,
                b = n.animObject,
                o = n.arrayMax,
                s = n.arrayMin,
                k = n.AxisPlotLineOrBandExtension,
                d = n.color,
                f = n.correctFloat,
                l = n.defaultOptions,
                i = n.defined,
                g = n.deg2rad,
                a = n.destroyObjectProperties,
                r = n.each,
                v = n.extend,
                y = n.fireEvent,
                nt = n.format,
                tt = n.getMagnitude,
                it = n.grep,
                h = n.inArray,
                rt = n.isArray,
                u = n.isNumber,
                p = n.isString,
                c = n.merge,
                ut = n.normalizeTickInterval,
                t = n.pick,
                ft = n.PlotLineOrBand,
                et = n.removeEvent,
                ot = n.splat,
                st = n.syncTimeout,
                e = n.Tick;
            n.Axis = function() {
                this.init.apply(this, arguments)
            };
            n.Axis.prototype = {
                defaultOptions: {
                    dateTimeLabelFormats: {
                        millisecond: "%H:%M:%S.%L",
                        second: "%H:%M:%S",
                        minute: "%H:%M",
                        hour: "%H:%M",
                        day: "%e. %b",
                        week: "%e. %b",
                        month: "%b '%y",
                        year: "%Y"
                    },
                    endOnTick: !1,
                    labels: {
                        enabled: !0,
                        style: {
                            color: "#666666",
                            cursor: "default",
                            fontSize: "11px"
                        },
                        x: 0
                    },
                    minPadding: .01,
                    maxPadding: .01,
                    minorTickLength: 2,
                    minorTickPosition: "outside",
                    startOfWeek: 1,
                    startOnTick: !1,
                    tickLength: 10,
                    tickmarkPlacement: "between",
                    tickPixelInterval: 100,
                    tickPosition: "outside",
                    title: {
                        align: "middle",
                        style: {
                            color: "#666666"
                        }
                    },
                    type: "linear",
                    minorGridLineColor: "#f2f2f2",
                    minorGridLineWidth: 1,
                    minorTickColor: "#999999",
                    lineColor: "#ccd6eb",
                    lineWidth: 1,
                    gridLineColor: "#e6e6e6",
                    tickColor: "#ccd6eb"
                },
                defaultYAxisOptions: {
                    endOnTick: !0,
                    tickPixelInterval: 72,
                    showLastLabel: !0,
                    labels: {
                        x: -8
                    },
                    maxPadding: .05,
                    minPadding: .05,
                    startOnTick: !0,
                    title: {
                        rotation: 270,
                        text: "Values"
                    },
                    stackLabels: {
                        enabled: !1,
                        formatter: function() {
                            return n.numberFormat(this.total, -1)
                        },
                        style: {
                            fontSize: "11px",
                            fontWeight: "bold",
                            color: "#000000",
                            textOutline: "1px contrast"
                        }
                    },
                    gridLineWidth: 1,
                    lineWidth: 0
                },
                defaultLeftAxisOptions: {
                    labels: {
                        x: -15
                    },
                    title: {
                        rotation: 270
                    }
                },
                defaultRightAxisOptions: {
                    labels: {
                        x: 15
                    },
                    title: {
                        rotation: 90
                    }
                },
                defaultBottomAxisOptions: {
                    labels: {
                        autoRotation: [-45],
                        x: 0
                    },
                    title: {
                        rotation: 0
                    }
                },
                defaultTopAxisOptions: {
                    labels: {
                        autoRotation: [-45],
                        x: 0
                    },
                    title: {
                        rotation: 0
                    }
                },
                init: function(n, r) {
                    var f = r.isX,
                        u, e, o;
                    this.chart = n;
                    this.horiz = n.inverted ? !f : f;
                    this.isXAxis = f;
                    this.coll = this.coll || (f ? "xAxis" : "yAxis");
                    this.opposite = r.opposite;
                    this.side = r.side || (this.horiz ? this.opposite ? 0 : 2 : this.opposite ? 1 : 3);
                    this.setOptions(r);
                    u = this.options;
                    e = u.type;
                    this.labelFormatter = u.labels.formatter || this.defaultLabelFormatter;
                    this.userOptions = r;
                    this.minPixelPadding = 0;
                    this.reversed = u.reversed;
                    this.visible = !1 !== u.visible;
                    this.zoomEnabled = !1 !== u.zoomEnabled;
                    this.hasNames = "category" === e || !0 === u.categories;
                    this.categories = u.categories || this.hasNames;
                    this.names = this.names || [];
                    this.isLog = "logarithmic" === e;
                    this.isDatetimeAxis = "datetime" === e;
                    this.isLinked = i(u.linkedTo);
                    this.ticks = {};
                    this.labelEdge = [];
                    this.minorTicks = {};
                    this.plotLinesAndBands = [];
                    this.alternateBands = {};
                    this.len = 0;
                    this.minRange = this.userMinRange = u.minRange || u.maxZoom;
                    this.range = u.range;
                    this.offset = u.offset || 0;
                    this.stacks = {};
                    this.oldStacks = {};
                    this.stacksTouched = 0;
                    this.min = this.max = null;
                    this.crosshair = t(u.crosshair, ot(n.options.tooltip.crosshairs)[f ? 0 : 1], !1);
                    r = this.options.events; - 1 === h(this, n.axes) && (f ? n.axes.splice(n.xAxis.length, 0, this) : n.axes.push(this), n[this.coll].push(this));
                    this.series = this.series || [];
                    n.inverted && f && void 0 === this.reversed && (this.reversed = !0);
                    this.removePlotLine = this.removePlotBand = this.removePlotBandOrLine;
                    for (o in r) w(this, o, r[o]);
                    this.isLog && (this.val2lin = this.log2lin, this.lin2val = this.lin2log)
                },
                setOptions: function(n) {
                    this.options = c(this.defaultOptions, "yAxis" === this.coll && this.defaultYAxisOptions, [this.defaultTopAxisOptions, this.defaultRightAxisOptions, this.defaultBottomAxisOptions, this.defaultLeftAxisOptions][this.side], c(l[this.coll], n))
                },
                defaultLabelFormatter: function() {
                    var r = this.axis,
                        t = this.value,
                        u = r.categories,
                        s = this.dateTimeLabelFormat,
                        o = l.lang,
                        e = o.numericSymbols,
                        o = o.numericSymbolMagnitude || 1e3,
                        f = e && e.length,
                        i, h = r.options.labels.format,
                        r = r.isLog ? t : r.tickInterval;
                    if (h) i = nt(h, this);
                    else if (u) i = t;
                    else if (s) i = n.dateFormat(s, t);
                    else if (f && 1e3 <= r)
                        for (; f-- && void 0 === i;) u = Math.pow(o, f + 1), r >= u && 0 == 10 * t % u && null !== e[f] && 0 !== t && (i = n.numberFormat(t / u, -1) + e[f]);
                    return void 0 === i && (i = 1e4 <= Math.abs(t) ? n.numberFormat(t, -1) : n.numberFormat(t, -1, void 0, "")), i
                },
                getSeriesExtremes: function() {
                    var n = this,
                        f = n.chart;
                    n.hasVisibleSeries = !1;
                    n.dataMin = n.dataMax = n.threshold = null;
                    n.softThreshold = !n.isXAxis;
                    n.buildStacks && n.buildStacks();
                    r(n.series, function(r) {
                        if (r.visible || !f.options.chart.ignoreHiddenSeries) {
                            var e = r.options,
                                h = e.threshold,
                                c;
                            n.hasVisibleSeries = !0;
                            n.isLog && 0 >= h && (h = null);
                            n.isXAxis ? (e = r.xData, e.length && (r = s(e), u(r) || r instanceof Date || (e = it(e, function(n) {
                                return u(n)
                            }), r = s(e)), n.dataMin = Math.min(t(n.dataMin, e[0]), r), n.dataMax = Math.max(t(n.dataMax, e[0]), o(e)))) : (r.getExtremes(), c = r.dataMax, r = r.dataMin, i(r) && i(c) && (n.dataMin = Math.min(t(n.dataMin, r), r), n.dataMax = Math.max(t(n.dataMax, c), c)), i(h) && (n.threshold = h), !e.softThreshold || n.isLog) && (n.softThreshold = !1)
                        }
                    })
                },
                translate: function(n, t, i, r, f, e) {
                    var o = this.linkedParent || this,
                        s = 1,
                        c = 0,
                        h = r ? o.oldTransA : o.transA,
                        l;
                    return r = r ? o.oldMin : o.min, l = o.minPixelPadding, f = (o.isOrdinal || o.isBroken || o.isLog && f) && o.lin2val, h || (h = o.transA), i && (s *= -1, c = o.len), o.reversed && (s *= -1, c -= s * (o.sector || o.len)), t ? (n = (n * s + c - l) / h + r, f && (n = o.lin2val(n))) : (f && (n = o.val2lin(n)), n = s * (n - r) * h + c + s * l + (u(e) ? h * e : 0)), n
                },
                toPixels: function(n, t) {
                    return this.translate(n, !1, !this.horiz, null, !0) + (t ? 0 : this.pos)
                },
                toValue: function(n, t) {
                    return this.translate(n - (t ? 0 : this.pos), !0, !this.horiz, null, !0)
                },
                getPlotLinePath: function(n, i, r, f, e) {
                    var s = this.chart,
                        c = this.left,
                        l = this.top,
                        o, h, y = r && s.oldChartHeight || s.chartHeight,
                        p = r && s.oldChartWidth || s.chartWidth,
                        a, v;
                    return o = this.transB, v = function(n, t, i) {
                        return (n < t || n > i) && (f ? n = Math.min(Math.max(t, n), i) : a = !0), n
                    }, e = t(e, this.translate(n, null, null, r)), n = r = Math.round(e + o), o = h = Math.round(y - e - o), u(e) ? this.horiz ? (o = l, h = y - this.bottom, n = r = v(n, c, c + this.width)) : (n = c, r = p - this.right, o = h = v(o, l, l + this.height)) : a = !0, a && !f ? null : s.renderer.crispLine(["M", n, o, "L", r, h], i || 1)
                },
                getLinearTickPositions: function(n, t, i) {
                    var r, o = f(Math.floor(t / n) * n),
                        s = f(Math.ceil(i / n) * n),
                        e = [];
                    if (t === i && u(t)) return [t];
                    for (t = o; t <= s;) {
                        if (e.push(t), t = f(t + n), t === r) break;
                        r = t
                    }
                    return e
                },
                getMinorTickPositions: function() {
                    var f = this.options,
                        t = this.tickPositions,
                        u = this.minorTickInterval,
                        i = [],
                        n, r = this.pointRangePadding || 0,
                        e;
                    if (n = this.min - r, r = this.max + r, e = r - n, e && e / u < this.len / 3)
                        if (this.isLog)
                            for (r = t.length, n = 1; n < r; n++) i = i.concat(this.getLogTickPositions(u, t[n - 1], t[n], !0));
                        else if (this.isDatetimeAxis && "auto" === f.minorTickInterval) i = i.concat(this.getTimeTicks(this.normalizeTimeTickInterval(u), n, r, f.startOfWeek));
                    else
                        for (t = n + (t[0] - n) % u; t <= r && t !== i[0]; t += u) i.push(t);
                    return 0 !== i.length && this.trimTicks(i, f.startOnTick, f.endOnTick), i
                },
                adjustForMinRange: function() {
                    var h = this.options,
                        n = this.min,
                        u = this.max,
                        f, y = this.dataMax - this.dataMin >= this.minRange,
                        l, c, v, a, p, e;
                    this.isXAxis && void 0 === this.minRange && !this.isLog && (i(h.min) || i(h.max) ? this.minRange = null : (r(this.series, function(n) {
                        for (a = n.xData, c = p = n.xIncrement ? 1 : a.length - 1; 0 < c; c--)(v = a[c] - a[c - 1], void 0 === l || v < l) && (l = v)
                    }), this.minRange = Math.min(5 * l, this.dataMax - this.dataMin)));
                    u - n < this.minRange && (e = this.minRange, f = (e - u + n) / 2, f = [n - f, t(h.min, n - f)], y && (f[2] = this.isLog ? this.log2lin(this.dataMin) : this.dataMin), n = o(f), u = [n + e, t(h.max, n + e)], y && (u[2] = this.isLog ? this.log2lin(this.dataMax) : this.dataMax), u = s(u), u - n < e && (f[0] = u - e, f[1] = t(h.min, u - e), n = o(f)));
                    this.min = n;
                    this.max = u
                },
                getClosest: function() {
                    var n;
                    return this.categories ? n = 1 : r(this.series, function(t) {
                        var r = t.closestPointRange,
                            u = t.visible || !t.chart.options.chart.ignoreHiddenSeries;
                        !t.noSharedTooltip && i(r) && u && (n = i(n) ? Math.min(n, r) : r)
                    }), n
                },
                nameToX: function(n) {
                    var u = rt(this.categories),
                        f = u ? this.categories : this.names,
                        t = n.options.x,
                        r;
                    return n.series.requireSorting = !1, i(t) || (t = !1 === this.options.uniqueNames ? n.series.autoIncrement() : h(n.name, f)), -1 === t ? u || (r = f.length) : r = t, this.names[r] = n.name, r
                },
                updateNames: function() {
                    var n = this;
                    0 < this.names.length && (this.names.length = 0, this.minRange = void 0, r(this.series || [], function(t) {
                        t.xIncrement = null;
                        (!t.points || t.isDirtyData) && (t.processData(), t.generatePoints());
                        r(t.points, function(i, r) {
                            var u;
                            i.options && (u = n.nameToX(i), u !== i.x && (i.x = u, t.xData[r] = u))
                        })
                    }))
                },
                setAxisTranslation: function(n) {
                    var i = this,
                        l = i.max - i.min,
                        s = i.axisPointRange || 0,
                        f, e = 0,
                        o = 0,
                        u = i.linkedParent,
                        a = !!i.categories,
                        h = i.transA,
                        c = i.isXAxis;
                    (c || a || s) && (f = i.getClosest(), u ? (e = u.minPointOffset, o = u.pointRangePadding) : r(i.series, function(n) {
                        var r = a ? 1 : c ? t(n.options.pointRange, f, 0) : i.axisPointRange || 0;
                        n = n.options.pointPlacement;
                        s = Math.max(s, r);
                        i.single || (e = Math.max(e, p(n) ? 0 : r / 2), o = Math.max(o, "on" === n ? 0 : r))
                    }), u = i.ordinalSlope && f ? i.ordinalSlope / f : 1, i.minPointOffset = e *= u, i.pointRangePadding = o *= u, i.pointRange = Math.min(s, l), c && (i.closestPointRange = f));
                    n && (i.oldTransA = h);
                    i.translationSlope = i.transA = h = i.len / (l + o || 1);
                    i.transB = i.horiz ? i.left : i.bottom;
                    i.minPixelPadding = h * e
                },
                minFromRange: function() {
                    return this.max - this.range
                },
                setTickInterval: function(e) {
                    var o = this,
                        c = o.chart,
                        s = o.options,
                        nt = o.isLog,
                        a = o.log2lin,
                        it = o.isDatetimeAxis,
                        ot = o.isXAxis,
                        w = o.isLinked,
                        b = s.maxPadding,
                        k = s.minPadding,
                        l = s.tickInterval,
                        d = s.tickPixelInterval,
                        g = o.categories,
                        h = o.threshold,
                        rt = o.softThreshold,
                        ft, et, v, p;
                    it || g || w || this.getTickAmount();
                    v = t(o.userMin, s.min);
                    p = t(o.userMax, s.max);
                    w ? (o.linkedParent = c[o.coll][s.linkedTo], c = o.linkedParent.getExtremes(), o.min = t(c.min, c.dataMin), o.max = t(c.max, c.dataMax), s.type !== o.linkedParent.options.type && n.error(11, 1)) : (!rt && i(h) && (o.dataMin >= h ? (ft = h, k = 0) : o.dataMax <= h && (et = h, b = 0)), o.min = t(v, ft, o.dataMin), o.max = t(p, et, o.dataMax));
                    nt && (!e && 0 >= Math.min(o.min, t(o.dataMin, o.min)) && n.error(10, 1), o.min = f(a(o.min), 15), o.max = f(a(o.max), 15));
                    o.range && i(o.max) && (o.userMin = o.min = v = Math.max(o.min, o.minFromRange()), o.userMax = p = o.max, o.range = null);
                    y(o, "foundExtremes");
                    o.beforePadding && o.beforePadding();
                    o.adjustForMinRange();
                    !(g || o.axisPointRange || o.usePercentage || w) && i(o.min) && i(o.max) && (a = o.max - o.min) && (!i(v) && k && (o.min -= a * k), !i(p) && b && (o.max += a * b));
                    u(s.floor) ? o.min = Math.max(o.min, s.floor) : u(s.softMin) && (o.min = Math.min(o.min, s.softMin));
                    u(s.ceiling) ? o.max = Math.min(o.max, s.ceiling) : u(s.softMax) && (o.max = Math.max(o.max, s.softMax));
                    rt && i(o.dataMin) && (h = h || 0, !i(v) && o.min < h && o.dataMin >= h ? o.min = h : !i(p) && o.max > h && o.dataMax <= h && (o.max = h));
                    o.tickInterval = o.min === o.max || void 0 === o.min || void 0 === o.max ? 1 : w && !l && d === o.linkedParent.options.tickPixelInterval ? l = o.linkedParent.tickInterval : t(l, this.tickAmount ? (o.max - o.min) / Math.max(this.tickAmount - 1, 1) : void 0, g ? 1 : (o.max - o.min) * d / Math.max(o.len, d));
                    ot && !e && r(o.series, function(n) {
                        n.processData(o.min !== o.oldMin || o.max !== o.oldMax)
                    });
                    o.setAxisTranslation(!0);
                    o.beforeSetTickPositions && o.beforeSetTickPositions();
                    o.postProcessTickInterval && (o.tickInterval = o.postProcessTickInterval(o.tickInterval));
                    o.pointRange && !l && (o.tickInterval = Math.max(o.pointRange, o.tickInterval));
                    e = t(s.minTickInterval, o.isDatetimeAxis && o.closestPointRange);
                    !l && o.tickInterval < e && (o.tickInterval = e);
                    it || nt || l || (o.tickInterval = ut(o.tickInterval, null, tt(o.tickInterval), t(s.allowDecimals, !(.5 < o.tickInterval && 5 > o.tickInterval && 1e3 < o.max && 9999 > o.max)), !!this.tickAmount));
                    this.tickAmount || (o.tickInterval = o.unsquish());
                    this.setTickPositions()
                },
                setTickPositions: function() {
                    var t = this.options,
                        n, u = t.tickPositions,
                        r = t.tickPositioner,
                        e = t.startOnTick,
                        o = t.endOnTick,
                        f;
                    this.tickmarkOffset = this.categories && "between" === t.tickmarkPlacement && 1 === this.tickInterval ? .5 : 0;
                    this.minorTickInterval = "auto" === t.minorTickInterval && this.tickInterval ? this.tickInterval / 5 : t.minorTickInterval;
                    this.tickPositions = n = u && u.slice();
                    !n && (n = this.isDatetimeAxis ? this.getTimeTicks(this.normalizeTimeTickInterval(this.tickInterval, t.units), this.min, this.max, t.startOfWeek, this.ordinalPositions, this.closestPointRange, !0) : this.isLog ? this.getLogTickPositions(this.tickInterval, this.min, this.max) : this.getLinearTickPositions(this.tickInterval, this.min, this.max), n.length > this.len && (n = [n[0], n.pop()]), this.tickPositions = n, r && (r = r.apply(this, [this.min, this.max]))) && (this.tickPositions = n = r);
                    this.trimTicks(n, e, o);
                    this.isLinked || (this.min === this.max && i(this.min) && !this.tickAmount && (f = !0, this.min -= .5, this.max += .5), this.single = f, u || r || this.adjustTickAmount())
                },
                trimTicks: function(n, t, r) {
                    var u = n[0],
                        f = n[n.length - 1],
                        e = this.minPointOffset || 0;
                    if (!this.isLinked) {
                        if (t) this.min = u;
                        else
                            for (; this.min - e > n[0];) n.shift();
                        if (r) this.max = f;
                        else
                            for (; this.max + e < n[n.length - 1];) n.pop();
                        0 === n.length && i(u) && n.push((f + u) / 2)
                    }
                },
                alignToOthers: function() {
                    var n = {},
                        t, i = this.options;
                    return !1 === this.chart.options.chart.alignTicks || !1 === i.alignTicks || this.isLog || r(this.chart[this.coll], function(i) {
                        var r = i.options,
                            r = [i.horiz ? r.left : r.top, r.width, r.height, r.pane].join();
                        i.series.length && (n[r] ? t = !0 : n[r] = 1)
                    }), t
                },
                getTickAmount: function() {
                    var t = this.options,
                        n = t.tickAmount,
                        r = t.tickPixelInterval;
                    !i(t.tickInterval) && this.len < r && !this.isRadial && !this.isLog && t.startOnTick && t.endOnTick && (n = 2);
                    !n && this.alignToOthers() && (n = Math.ceil(this.len / r) + 1);
                    4 > n && (this.finalTickAmt = n, n = 5);
                    this.tickAmount = n
                },
                adjustTickAmount: function() {
                    var t = this.tickInterval,
                        n = this.tickPositions,
                        r = this.tickAmount,
                        u = this.finalTickAmt,
                        e = n && n.length;
                    if (e < r) {
                        for (; n.length < r;) n.push(f(n[n.length - 1] + t));
                        this.transA *= (e - 1) / (r - 1);
                        this.max = n[n.length - 1]
                    } else e > r && (this.tickInterval *= 2, this.setTickPositions());
                    if (i(u)) {
                        for (t = r = n.length; t--;)(3 === u && 1 == t % 2 || 2 >= u && 0 < t && t < r - 1) && n.splice(t, 1);
                        this.finalTickAmt = void 0
                    }
                },
                setScale: function() {
                    var t, n;
                    this.oldMin = this.min;
                    this.oldMax = this.max;
                    this.oldAxisLength = this.len;
                    this.setAxisSize();
                    n = this.len !== this.oldAxisLength;
                    r(this.series, function(n) {
                        (n.isDirtyData || n.isDirty || n.xAxis.isDirty) && (t = !0)
                    });
                    n || t || this.isLinked || this.forceRedraw || this.userMin !== this.oldUserMin || this.userMax !== this.oldUserMax || this.alignToOthers() ? (this.resetStacks && this.resetStacks(), this.forceRedraw = !1, this.getSeriesExtremes(), this.setTickInterval(), this.oldUserMin = this.userMin, this.oldUserMax = this.userMax, this.isDirty || (this.isDirty = n || this.min !== this.oldMin || this.max !== this.oldMax)) : this.cleanStacks && this.cleanStacks()
                },
                setExtremes: function(n, i, u, f, e) {
                    var o = this,
                        s = o.chart;
                    u = t(u, !0);
                    r(o.series, function(n) {
                        delete n.kdTree
                    });
                    e = v(e, {
                        min: n,
                        max: i
                    });
                    y(o, "setExtremes", e, function() {
                        o.userMin = n;
                        o.userMax = i;
                        o.eventArgs = e;
                        u && s.redraw(f)
                    })
                },
                zoom: function(n, r) {
                    var e = this.dataMin,
                        o = this.dataMax,
                        u = this.options,
                        f = Math.min(e, t(u.min, e)),
                        u = Math.max(o, t(u.max, o));
                    return (n !== this.min || r !== this.max) && (this.allowZoomOutside || (i(e) && (n < f && (n = f), n > u && (n = u)), i(o) && (r < f && (r = f), r > u && (r = u))), this.displayBtn = void 0 !== n || void 0 !== r, this.setExtremes(n, r, !1, void 0, {
                        trigger: "zoom"
                    })), !0
                },
                setAxisSize: function() {
                    var n = this.chart,
                        i = this.options,
                        r = i.offsets || [0, 0, 0, 0],
                        o = this.horiz,
                        e = t(i.width, n.plotWidth - r[3] + r[1]),
                        u = t(i.height, n.plotHeight - r[0] + r[2]),
                        f = t(i.top, n.plotTop + r[0]),
                        i = t(i.left, n.plotLeft + r[3]),
                        r = /%$/;
                    r.test(u) && (u = Math.round(parseFloat(u) / 100 * n.plotHeight));
                    r.test(f) && (f = Math.round(parseFloat(f) / 100 * n.plotHeight + n.plotTop));
                    this.left = i;
                    this.top = f;
                    this.width = e;
                    this.height = u;
                    this.bottom = n.chartHeight - u - f;
                    this.right = n.chartWidth - e - i;
                    this.len = Math.max(o ? e : u, 0);
                    this.pos = o ? i : f
                },
                getExtremes: function() {
                    var n = this.isLog,
                        t = this.lin2log;
                    return {
                        min: n ? f(t(this.min)) : this.min,
                        max: n ? f(t(this.max)) : this.max,
                        dataMin: this.dataMin,
                        dataMax: this.dataMax,
                        userMin: this.userMin,
                        userMax: this.userMax
                    }
                },
                getThreshold: function(n) {
                    var t = this.isLog,
                        r = this.lin2log,
                        i = t ? r(this.min) : this.min,
                        t = t ? r(this.max) : this.max;
                    return null === n ? n = i : i > n ? n = i : t < n && (n = t), this.translate(n, 0, 1, 0, 1)
                },
                autoLabelAlign: function(n) {
                    return n = (t(n, 0) - 90 * this.side + 720) % 360, 15 < n && 165 > n ? "right" : 195 < n && 345 > n ? "left" : "center"
                },
                tickSize: function(n) {
                    var r = this.options,
                        i = r[n + "Length"],
                        u = t(r[n + "Width"], "tick" === n && this.isXAxis ? 1 : 0);
                    if (u && i) return "inside" === r[n + "Position"] && (i = -i), [i, u]
                },
                labelMetrics: function() {
                    return this.chart.renderer.fontMetrics(this.options.labels.style && this.options.labels.style.fontSize, this.ticks[0] && this.ticks[0].label)
                },
                unsquish: function() {
                    var n = this.options.labels,
                        y = this.horiz,
                        f = this.tickInterval,
                        e = f,
                        h = this.len / (((this.categories ? 1 : 0) + this.max - this.min) / f),
                        c, u = n.rotation,
                        l = this.labelMetrics(),
                        o, a = Number.MAX_VALUE,
                        s, v = function(n) {
                            return n /= h || 1, n = 1 < n ? Math.ceil(n) : 1, n * f
                        };
                    return y ? (s = !n.staggerLines && !n.step && (i(u) ? [u] : h < t(n.autoRotationLimit, 80) && n.autoRotation)) && r(s, function(n) {
                        var t;
                        (n === u || n && -90 <= n && 90 >= n) && (o = v(Math.abs(l.h / Math.sin(g * n))), t = o + Math.abs(n / 360), t < a && (a = t, c = n, e = o))
                    }) : n.step || (e = v(l.h)), this.autoRotation = s, this.labelRotation = t(c, u), e
                },
                getSlotWidth: function() {
                    var n = this.chart,
                        t = this.horiz,
                        i = this.options.labels,
                        u = Math.max(this.tickPositions.length - (this.categories ? 0 : 1), 1),
                        r = n.margin[3];
                    return t && 2 > (i.step || 0) && !i.rotation && (this.staggerLines || 1) * this.len / u || !t && (r && r - n.spacing[3] || .33 * n.chartWidth)
                },
                renderUnsquish: function() {
                    var o = this.chart,
                        w = o.renderer,
                        e = this.tickPositions,
                        s = this.ticks,
                        t = this.options.labels,
                        b = this.horiz,
                        h = this.getSlotWidth(),
                        n = Math.max(1, Math.round(h - 2 * (t.padding || 5))),
                        i = {},
                        l = this.labelMetrics(),
                        y = t.style && t.style.textOverflow,
                        u, f = 0,
                        a, v;
                    if (p(t.rotation) || (i.rotation = t.rotation || 0), r(e, function(n) {
                            (n = s[n]) && n.labelLength > f && (f = n.labelLength)
                        }), this.maxLabelLength = f, this.autoRotation) f > n && f > l.h ? i.rotation = this.labelRotation : this.labelRotation = 0;
                    else if (h && (u = {
                            width: n + "px"
                        }, !y))
                        for (u.textOverflow = "clip", a = e.length; !b && a--;)(v = e[a], n = s[v].label) && (n.styles && "ellipsis" === n.styles.textOverflow ? n.css({
                            textOverflow: "clip"
                        }) : s[v].labelLength > h && n.css({
                            width: h + "px"
                        }), n.getBBox().height > this.len / e.length - (l.h - l.f) && (n.specCss = {
                            textOverflow: "ellipsis"
                        }));
                    i.rotation && (u = {
                        width: (f > .5 * o.chartHeight ? .33 * o.chartHeight : o.chartHeight) + "px"
                    }, y || (u.textOverflow = "ellipsis"));
                    (this.labelAlign = t.align || this.autoLabelAlign(this.labelRotation)) && (i.align = this.labelAlign);
                    r(e, function(n) {
                        var t = (n = s[n]) && n.label;
                        t && (t.attr(i), u && t.css(c(u, t.specCss)), delete t.specCss, n.rotation = i.rotation)
                    });
                    this.tickRotCorr = w.rotCorr(l.b, this.labelRotation || 0, 0 !== this.side)
                },
                hasData: function() {
                    return this.hasVisibleSeries || i(this.min) && i(this.max) && !!this.tickPositions
                },
                addTitle: function(n) {
                    var u = this.chart.renderer,
                        f = this.horiz,
                        r = this.opposite,
                        t = this.options.title,
                        i;
                    this.axisTitle || ((i = t.textAlign) || (i = (f ? {
                        low: "left",
                        middle: "center",
                        high: "right"
                    } : {
                        low: r ? "right" : "left",
                        middle: "center",
                        high: r ? "left" : "right"
                    })[t.align]), this.axisTitle = u.text(t.text, 0, 0, t.useHTML).attr({
                        zIndex: 7,
                        rotation: t.rotation || 0,
                        align: i
                    }).addClass("highcharts-axis-title").css(t.style).add(this.axisGroup), this.axisTitle.isNew = !0);
                    this.axisTitle[n ? "show" : "hide"](!0)
                },
                generateTick: function(n) {
                    var t = this.ticks;
                    t[n] ? t[n].addLabel() : t[n] = new e(this, n)
                },
                getOffset: function() {
                    var n = this,
                        o = n.chart,
                        h = o.renderer,
                        f = n.options,
                        p = n.tickPositions,
                        a = n.ticks,
                        w = n.horiz,
                        u = n.side,
                        it = o.inverted ? [1, 0, 3, 2][u] : u,
                        v, b, rt = 0,
                        k, e = 0,
                        c = f.title,
                        l = f.labels,
                        s = 0,
                        d = o.axisOffset,
                        o = o.clipOffset,
                        y = [-1, 1, 1, -1][u],
                        g, nt = f.className,
                        tt = n.axisParent,
                        ut = this.tickSize("tick");
                    if (v = n.hasData(), n.showAxis = b = v || t(f.showEmpty, !0), n.staggerLines = n.horiz && l.staggerLines, n.axisGroup || (n.gridGroup = h.g("grid").attr({
                            zIndex: f.gridZIndex || 1
                        }).addClass("highcharts-" + this.coll.toLowerCase() + "-grid " + (nt || "")).add(tt), n.axisGroup = h.g("axis").attr({
                            zIndex: f.zIndex || 2
                        }).addClass("highcharts-" + this.coll.toLowerCase() + " " + (nt || "")).add(tt), n.labelGroup = h.g("axis-labels").attr({
                            zIndex: l.zIndex || 7
                        }).addClass("highcharts-" + n.coll.toLowerCase() + "-labels " + (nt || "")).add(tt)), v || n.isLinked) r(p, function(t, i) {
                        n.generateTick(t, i)
                    }), n.renderUnsquish(), !1 === l.reserveSpace || 0 !== u && 2 !== u && {
                        1: "left",
                        3: "right"
                    } [u] !== n.labelAlign && "center" !== n.labelAlign || r(p, function(n) {
                        s = Math.max(a[n].getLabelSize(), s)
                    }), n.staggerLines && (s *= n.staggerLines, n.labelOffset = s * (n.opposite ? -1 : 1));
                    else
                        for (g in a) a[g].destroy(), delete a[g];
                    c && c.text && !1 !== c.enabled && (n.addTitle(b), b && (rt = n.axisTitle.getBBox()[w ? "height" : "width"], k = c.offset, e = i(k) ? 0 : t(c.margin, w ? 5 : 10)));
                    n.renderLine();
                    n.offset = y * t(f.offset, d[u]);
                    n.tickRotCorr = n.tickRotCorr || {
                        x: 0,
                        y: 0
                    };
                    h = 0 === u ? -n.labelMetrics().h : 2 === u ? n.tickRotCorr.y : 0;
                    e = Math.abs(s) + e;
                    s && (e = e - h + y * (w ? t(l.y, n.tickRotCorr.y + 8 * y) : l.x));
                    n.axisTitleMargin = t(k, e);
                    d[u] = Math.max(d[u], n.axisTitleMargin + rt + y * n.offset, e, v && p.length && ut ? ut[0] : 0);
                    f = f.offset ? 0 : 2 * Math.floor(n.axisLine.strokeWidth() / 2);
                    o[it] = Math.max(o[it], f)
                },
                getLinePath: function(n) {
                    var t = this.chart,
                        u = this.opposite,
                        i = this.offset,
                        r = this.horiz,
                        f = this.left + (u ? this.width : 0) + i,
                        i = t.chartHeight - this.bottom - (u ? this.height : 0) + i;
                    return u && (n *= -1), t.renderer.crispLine(["M", r ? this.left : f, r ? i : this.top, "L", r ? t.chartWidth - this.right : f, r ? i : t.chartHeight - this.bottom], n)
                },
                renderLine: function() {
                    this.axisLine || (this.axisLine = this.chart.renderer.path().addClass("highcharts-axis-line").add(this.axisGroup), this.axisLine.attr({
                        stroke: this.options.lineColor,
                        "stroke-width": this.options.lineWidth,
                        zIndex: 7
                    }))
                },
                getTitlePosition: function() {
                    var n = this.horiz,
                        r = this.left,
                        e = this.top,
                        t = this.len,
                        i = this.options.title,
                        u = n ? r : e,
                        f = this.opposite,
                        o = this.offset,
                        s = i.x || 0,
                        h = i.y || 0,
                        c = this.chart.renderer.fontMetrics(i.style && i.style.fontSize, this.axisTitle).f,
                        t = {
                            low: u + (n ? 0 : t),
                            middle: u + t / 2,
                            high: u + (n ? t : 0)
                        } [i.align],
                        r = (n ? e + this.height : r) + (n ? 1 : -1) * (f ? -1 : 1) * this.axisTitleMargin + (2 === this.side ? c : 0);
                    return {
                        x: n ? t + s : r + (f ? this.width : 0) + o + s,
                        y: n ? r + h - (f ? this.height : 0) + o : t + h
                    }
                },
                renderMinorTick: function(n) {
                    var i = this.chart.hasRendered && u(this.oldMin),
                        t = this.minorTicks;
                    t[n] || (t[n] = new e(this, n, "minor"));
                    i && t[n].isNew && t[n].render(null, !0);
                    t[n].render(null, !1, 1)
                },
                renderTick: function(n, t) {
                    var r = this.isLinked,
                        i = this.ticks,
                        f = this.chart.hasRendered && u(this.oldMin);
                    (!r || n >= this.min && n <= this.max) && (i[n] || (i[n] = new e(this, n)), f && i[n].isNew && i[n].render(t, !0, .1), i[n].render(t))
                },
                render: function() {
                    var n = this,
                        l = n.chart,
                        s = n.options,
                        v = n.isLog,
                        y = n.lin2log,
                        g = n.isLinked,
                        f = n.tickPositions,
                        h = n.axisTitle,
                        o = n.ticks,
                        p = n.minorTicks,
                        t = n.alternateBands,
                        w = s.stackLabels,
                        k = s.alternateGridColor,
                        i = n.tickmarkOffset,
                        u = n.axisLine,
                        d = n.showAxis,
                        nt = b(l.renderer.globalAnimation),
                        a, c;
                    n.labelEdge.length = 0;
                    n.overlap = !1;
                    r([o, p, t], function(n) {
                        for (var t in n) n[t].isActive = !1
                    });
                    (n.hasData() || g) && (n.minorTickInterval && !n.categories && r(n.getMinorTickPositions(), function(t) {
                        n.renderMinorTick(t)
                    }), f.length && (r(f, function(t, i) {
                        n.renderTick(t, i)
                    }), i && (0 === n.min || n.single) && (o[-1] || (o[-1] = new e(n, -1, null, !0)), o[-1].render(-1))), k && r(f, function(r, u) {
                        c = void 0 !== f[u + 1] ? f[u + 1] + i : n.max - i;
                        0 == u % 2 && r < n.max && c <= n.max + (l.polar ? -i : i) && (t[r] || (t[r] = new ft(n)), a = r + i, t[r].options = {
                            from: v ? y(a) : a,
                            to: v ? y(c) : c,
                            color: k
                        }, t[r].render(), t[r].isActive = !0)
                    }), n._addedPlotLB || (r((s.plotLines || []).concat(s.plotBands || []), function(t) {
                        n.addPlotBandOrLine(t)
                    }), n._addedPlotLB = !0));
                    r([o, p, t], function(n) {
                        var i, r, u = [],
                            f = nt.duration;
                        for (i in n) n[i].isActive || (n[i].render(i, !1, 0), n[i].isActive = !1, u.push(i));
                        st(function() {
                            for (r = u.length; r--;) n[u[r]] && !n[u[r]].isActive && (n[u[r]].destroy(), delete n[u[r]])
                        }, n !== t && l.hasRendered && f ? f : 0)
                    });
                    u && (u[u.isPlaced ? "animate" : "attr"]({
                        d: this.getLinePath(u.strokeWidth())
                    }), u.isPlaced = !0, u[d ? "show" : "hide"](!0));
                    h && d && (h[h.isNew ? "attr" : "animate"](n.getTitlePosition()), h.isNew = !1);
                    w && w.enabled && n.renderStackTotals();
                    n.isDirty = !1
                },
                redraw: function() {
                    this.visible && (this.render(), r(this.plotLinesAndBands, function(n) {
                        n.render()
                    }));
                    r(this.series, function(n) {
                        n.isDirty = !0
                    })
                },
                keepProps: "extKey hcEvents names series userMax userMin".split(" "),
                destroy: function(n) {
                    var t = this,
                        u = t.stacks,
                        f, e = t.plotLinesAndBands,
                        i;
                    n || et(t);
                    for (f in u) a(u[f]), u[f] = null;
                    if (r([t.ticks, t.minorTicks, t.alternateBands], function(n) {
                            a(n)
                        }), e)
                        for (n = e.length; n--;) e[n].destroy();
                    r("stackTotalGroup axisLine axisTitle axisGroup gridGroup labelGroup cross".split(" "), function(n) {
                        t[n] && (t[n] = t[n].destroy())
                    });
                    for (i in t) t.hasOwnProperty(i) && -1 === h(i, t.keepProps) && delete t[i]
                },
                drawCrosshair: function(n, r) {
                    var o, u = this.crosshair,
                        s = t(u.snap, !0),
                        e, f = this.cross;
                    n || (n = this.cross && this.cross.e);
                    this.crosshair && !1 !== (i(r) || !s) ? (s ? i(r) && (e = this.isXAxis ? r.plotX : this.len - r.plotY) : e = n && (this.horiz ? n.chartX - this.pos : this.len - n.chartY + this.pos), i(e) && (o = this.getPlotLinePath(r && (this.isXAxis ? r.x : t(r.stackY, r.y)), null, null, null, e) || null), i(o) ? (r = this.categories && !this.isRadial, f || (this.cross = f = this.chart.renderer.path().addClass("highcharts-crosshair highcharts-crosshair-" + (r ? "category " : "thin ") + u.className).attr({
                        zIndex: t(u.zIndex, 2)
                    }).add(), f.attr({
                        stroke: u.color || (r ? d("#ccd6eb").setOpacity(.25).get() : "#cccccc"),
                        "stroke-width": t(u.width, 1)
                    }), u.dashStyle && f.attr({
                        dashstyle: u.dashStyle
                    })), f.show().attr({
                        d: o
                    }), r && !u.width && f.attr({
                        "stroke-width": this.transA
                    }), this.cross.e = n) : this.hideCrosshair()) : this.hideCrosshair()
                },
                hideCrosshair: function() {
                    this.cross && this.cross.hide()
                }
            };
            v(n.Axis.prototype, k)
        }(n),
        function(n) {
            var u = n.Axis,
                t = n.Date,
                f = n.dateFormat,
                e = n.defaultOptions,
                o = n.defined,
                s = n.each,
                h = n.extend,
                c = n.getMagnitude,
                r = n.getTZOffset,
                l = n.normalizeTickInterval,
                a = n.pick,
                i = n.timeUnits;
            u.prototype.getTimeTicks = function(n, u, c, l) {
                var k = [],
                    tt = {},
                    b = e.global.useUTC,
                    w, v = new t(u - r(u)),
                    d = t.hcMakeTime,
                    y = n.unitRange,
                    p = n.count,
                    g, nt, it;
                if (o(u)) {
                    for (v[t.hcSetMilliseconds](y >= i.second ? 0 : p * Math.floor(v.getMilliseconds() / p)), y >= i.second && v[t.hcSetSeconds](y >= i.minute ? 0 : p * Math.floor(v.getSeconds() / p)), y >= i.minute && v[t.hcSetMinutes](y >= i.hour ? 0 : p * Math.floor(v[t.hcGetMinutes]() / p)), y >= i.hour && v[t.hcSetHours](y >= i.day ? 0 : p * Math.floor(v[t.hcGetHours]() / p)), y >= i.day && v[t.hcSetDate](y >= i.month ? 1 : p * Math.floor(v[t.hcGetDate]() / p)), y >= i.month && (v[t.hcSetMonth](y >= i.year ? 0 : p * Math.floor(v[t.hcGetMonth]() / p)), w = v[t.hcGetFullYear]()), y >= i.year && v[t.hcSetFullYear](w - w % p), y === i.week && v[t.hcSetDate](v[t.hcGetDate]() - v[t.hcGetDay]() + a(l, 1)), w = v[t.hcGetFullYear](), l = v[t.hcGetMonth](), nt = v[t.hcGetDate](), it = v[t.hcGetHours](), (t.hcTimezoneOffset || t.hcGetTimezoneOffset) && (g = (!b || !!t.hcGetTimezoneOffset) && (c - u > 4 * i.month || r(u) !== r(c)), v = v.getTime(), v = new t(v + r(v))), b = v.getTime(), u = 1; b < c;) k.push(b), b = y === i.year ? d(w + u * p, 0) : y === i.month ? d(w, l + u * p) : !g || y !== i.day && y !== i.week ? g && y === i.hour ? d(w, l, nt, it + u * p) : b + y * p : d(w, l, nt + u * p * (y === i.day ? 1 : 7)), u++;
                    k.push(b);
                    y <= i.hour && 1e4 > k.length && s(k, function(n) {
                        0 == n % 18e5 && "000000000" === f("%H%M%S%L", n) && (tt[n] = "day")
                    })
                }
                return k.info = h(n, {
                    higherRanks: tt,
                    totalRange: y * p
                }), k
            };
            u.prototype.normalizeTimeTickInterval = function(n, t) {
                var u = t || [
                        ["millisecond", [1, 2, 5, 10, 20, 25, 50, 100, 200, 500]],
                        ["second", [1, 2, 5, 10, 15, 30]],
                        ["minute", [1, 2, 5, 10, 15, 30]],
                        ["hour", [1, 2, 3, 4, 6, 8, 12]],
                        ["day", [1, 2]],
                        ["week", [1, 2]],
                        ["month", [1, 2, 3, 4, 6]],
                        ["year", null]
                    ],
                    r, f, e;
                for (t = u[u.length - 1], r = i[t[0]], f = t[1], e = 0; e < u.length && !(t = u[e], r = i[t[0]], f = t[1], u[e + 1] && n <= (r * f[f.length - 1] + i[u[e + 1][0]]) / 2); e++);
                return r === i.year && n < 5 * r && (f = [1, 2, 5]), n = l(n / r, f, "year" === t[0] ? Math.max(c(n / r), 1) : 1), {
                    unitRange: r,
                    count: n,
                    unitName: t[0]
                }
            }
        }(n),
        function(n) {
            var t = n.Axis,
                i = n.getMagnitude,
                r = n.map,
                u = n.normalizeTickInterval,
                f = n.pick;
            t.prototype.getLogTickPositions = function(n, t, e, o) {
                var h = this.options,
                    s = this.len,
                    v = this.lin2log,
                    w = this.log2lin,
                    l = [],
                    a, b, y, c, p;
                if (o || (this._minorAutoInterval = null), .5 <= n) n = Math.round(n), l = this.getLinearTickPositions(n, t, e);
                else if (.08 <= n)
                    for (s = Math.floor(t), h = .3 < n ? [1, 2, 4] : .15 < n ? [1, 2, 4, 6, 8] : [1, 2, 3, 4, 5, 6, 7, 8, 9]; s < e + 1 && !p; s++)
                        for (b = h.length, a = 0; a < b && !p; a++) y = w(v(s) * h[a]), y > t && (!o || c <= e) && void 0 !== c && l.push(c), c > e && (p = !0), c = y;
                else t = v(t), e = v(e), n = h[o ? "minorTickInterval" : "tickInterval"], n = f("auto" === n ? null : n, this._minorAutoInterval, h.tickPixelInterval / (o ? 5 : 1) * (e - t) / ((o ? s / this.tickPositions.length : s) || 1)), n = u(n, null, i(n)), l = r(this.getLinearTickPositions(n, t, e), w), o || (this._minorAutoInterval = n / 5);
                return o || (this.tickInterval = n), l
            };
            t.prototype.log2lin = function(n) {
                return Math.log(n) / Math.LN10
            };
            t.prototype.lin2log = function(n) {
                return Math.pow(10, n)
            }
        }(n),
        function(n) {
            var f = n.dateFormat,
                i = n.each,
                o = n.extend,
                s = n.format,
                h = n.isNumber,
                e = n.map,
                c = n.merge,
                t = n.pick,
                r = n.splat,
                l = n.syncTimeout,
                u = n.timeUnits;
            n.Tooltip = function() {
                this.init.apply(this, arguments)
            };
            n.Tooltip.prototype = {
                init: function(n, t) {
                    this.chart = n;
                    this.options = t;
                    this.crosshairs = [];
                    this.now = {
                        x: 0,
                        y: 0
                    };
                    this.isHidden = !0;
                    this.split = t.split && !n.inverted;
                    this.shared = t.shared || this.split
                },
                cleanSplit: function(n) {
                    i(this.chart.series, function(t) {
                        var i = t && t.tt;
                        i && (!i.isActive || n ? t.tt = i.destroy() : i.isActive = !1)
                    })
                },
                getLabel: function() {
                    var t = this.chart.renderer,
                        n = this.options;
                    return this.label || (this.split ? this.label = t.g("tooltip") : (this.label = t.label("", 0, 0, n.shape || "callout", null, null, n.useHTML, null, "tooltip").attr({
                        padding: n.padding,
                        r: n.borderRadius
                    }), this.label.attr({
                        fill: n.backgroundColor,
                        "stroke-width": n.borderWidth
                    }).css(n.style).shadow(n.shadow)), this.label.attr({
                        zIndex: 8
                    }).add()), this.label
                },
                update: function(n) {
                    this.destroy();
                    this.init(this.chart, c(!0, this.options, n))
                },
                destroy: function() {
                    this.label && (this.label = this.label.destroy());
                    this.split && this.tt && (this.cleanSplit(this.chart, !0), this.tt = this.tt.destroy());
                    clearTimeout(this.hideTimer);
                    clearTimeout(this.tooltipTimeout)
                },
                move: function(n, t, i, r) {
                    var u = this,
                        f = u.now,
                        e = !1 !== u.options.animation && !u.isHidden && (1 < Math.abs(n - f.x) || 1 < Math.abs(t - f.y)),
                        s = u.followPointer || 1 < u.len;
                    o(f, {
                        x: e ? (2 * f.x + n) / 3 : n,
                        y: e ? (f.y + t) / 2 : t,
                        anchorX: s ? void 0 : e ? (2 * f.anchorX + i) / 3 : i,
                        anchorY: s ? void 0 : e ? (f.anchorY + r) / 2 : r
                    });
                    u.getLabel().attr(f);
                    e && (clearTimeout(this.tooltipTimeout), this.tooltipTimeout = setTimeout(function() {
                        u && u.move(n, t, i, r)
                    }, 32))
                },
                hide: function(n) {
                    var i = this;
                    clearTimeout(this.hideTimer);
                    n = t(n, this.options.hideDelay, 500);
                    this.isHidden || (this.hideTimer = l(function() {
                        i.getLabel()[n ? "fadeOut" : "hide"]();
                        i.isHidden = !0
                    }, n))
                },
                getAnchor: function(n, t) {
                    var f, u = this.chart,
                        o = u.inverted,
                        c = u.plotTop,
                        v = u.plotLeft,
                        s = 0,
                        h = 0,
                        l, a;
                    return n = r(n), f = n[0].tooltipPos, this.followPointer && t && (void 0 === t.chartX && (t = u.pointer.normalize(t)), f = [t.chartX - u.plotLeft, t.chartY - c]), f || (i(n, function(n) {
                        l = n.series.yAxis;
                        a = n.series.xAxis;
                        s += n.plotX + (!o && a ? a.left - v : 0);
                        h += (n.plotLow ? (n.plotLow + n.plotHigh) / 2 : n.plotY) + (!o && l ? l.top - c : 0)
                    }), s /= n.length, h /= n.length, f = [o ? u.plotWidth - h : s, this.shared && !o && 1 < n.length && t ? t.chartY - c : o ? u.plotHeight - s : h]), e(f, Math.round)
                },
                getPosition: function(n, i, r) {
                    var u = this.chart,
                        e = this.distance,
                        f = {},
                        o = r.h || 0,
                        s, h = ["y", u.chartHeight, i, r.plotY + u.plotTop, u.plotTop, u.plotTop + u.plotHeight],
                        c = ["x", u.chartWidth, n, r.plotX + u.plotLeft, u.plotLeft, u.plotLeft + u.plotWidth],
                        v = !this.followPointer && t(r.ttBelow, !u.inverted == !!r.negative),
                        y = function(n, t, i, r, u, s) {
                            var c = i < r - e,
                                l = r + e + i < t,
                                h = r - e - i;
                            if (r += e, v && l) f[n] = r;
                            else if (!v && c) f[n] = h;
                            else if (c) f[n] = Math.min(s - i, 0 > h - o ? h : h - o);
                            else if (l) f[n] = Math.max(u, r + o + i > t ? r : r + o);
                            else return !1
                        },
                        p = function(n, t, i, r) {
                            var u;
                            return r < e || r > t - e ? u = !1 : f[n] = r < i / 2 ? 1 : r > t - i / 2 ? t - i - 2 : r - i / 2, u
                        },
                        l = function(n) {
                            var t = h;
                            h = c;
                            c = t;
                            s = n
                        },
                        a = function() {
                            !1 !== y.apply(0, h) ? !1 !== p.apply(0, c) || s || (l(!0), a()) : s ? f.x = f.y = 0 : (l(!0), a())
                        };
                    return (u.inverted || 1 < this.len) && l(), a(), f
                },
                defaultFormatter: function(n) {
                    var i = this.points || r(this),
                        t;
                    return t = [n.tooltipFooterHeaderFormatter(i[0])], t = t.concat(n.bodyFormatter(i)), t.push(n.tooltipFooterHeaderFormatter(i[0], !0)), t
                },
                refresh: function(n, u) {
                    var h = this.chart,
                        e, l = this.options,
                        a, s, f = {},
                        c = [],
                        o;
                    e = l.formatter || this.defaultFormatter;
                    f = h.hoverPoints;
                    o = this.shared;
                    clearTimeout(this.hideTimer);
                    this.followPointer = r(n)[0].series.tooltipOptions.followPointer;
                    s = this.getAnchor(n, u);
                    u = s[0];
                    a = s[1];
                    !o || n.series && n.series.noSharedTooltip ? f = n.getLabelConfig() : (h.hoverPoints = n, f && i(f, function(n) {
                        n.setState()
                    }), i(n, function(n) {
                        n.setState("hover");
                        c.push(n.getLabelConfig())
                    }), f = {
                        x: n[0].category,
                        y: n[0].y
                    }, f.points = c, n = n[0]);
                    this.len = c.length;
                    f = e.call(f, this);
                    o = n.series;
                    this.distance = t(o.tooltipOptions.distance, 16);
                    !1 === f ? this.hide() : (e = this.getLabel(), this.isHidden && e.attr({
                        opacity: 1
                    }).show(), this.split ? this.renderSplit(f, h.hoverPoints) : (e.attr({
                        text: f && f.join ? f.join("") : f
                    }), e.removeClass(/highcharts-color-[\d]+/g).addClass("highcharts-color-" + t(n.colorIndex, o.colorIndex)), e.attr({
                        stroke: l.borderColor || n.color || o.color || "#666666"
                    }), this.updatePosition({
                        plotX: u,
                        plotY: a,
                        negative: n.negative,
                        ttBelow: n.ttBelow,
                        h: s[2] || 0
                    })), this.isHidden = !1)
                },
                renderSplit: function(r, u) {
                    var c = this,
                        o = [],
                        f = this.chart,
                        l = f.renderer,
                        h = !0,
                        e = this.options,
                        s, a = this.getLabel();
                    i(r.slice(0, u.length + 1), function(n, i) {
                        i = u[i - 1] || {
                            isHeader: !0,
                            plotX: u[0].plotX
                        };
                        var y = i.series || c,
                            v = y.tt,
                            r = i.series || {},
                            p = "highcharts-color-" + t(i.colorIndex, r.colorIndex, "none");
                        v || (y.tt = v = l.label(null, null, null, "callout").addClass("highcharts-tooltip-box " + p).attr({
                            padding: e.padding,
                            r: e.borderRadius,
                            fill: e.backgroundColor,
                            stroke: i.color || r.color || "#333333",
                            "stroke-width": e.borderWidth
                        }).add(a));
                        v.isActive = !0;
                        v.attr({
                            text: n
                        });
                        v.css(e.style);
                        n = v.getBBox();
                        r = n.width + v.strokeWidth();
                        i.isHeader ? (s = n.height, r = Math.max(0, Math.min(i.plotX + f.plotLeft - r / 2, f.chartWidth - r))) : r = i.plotX + f.plotLeft - t(e.distance, 16) - r;
                        0 > r && (h = !1);
                        n = (i.series && i.series.yAxis && i.series.yAxis.pos) + (i.plotY || 0);
                        n -= f.plotTop;
                        o.push({
                            target: i.isHeader ? f.plotHeight + s : n,
                            rank: i.isHeader ? 1 : 0,
                            size: y.tt.getBBox().height + 1,
                            point: i,
                            x: r,
                            tt: v
                        })
                    });
                    this.cleanSplit();
                    n.distribute(o, f.plotHeight + s);
                    i(o, function(n) {
                        var i = n.point,
                            r = i.series;
                        n.tt.attr({
                            visibility: void 0 === n.pos ? "hidden" : "inherit",
                            x: h || i.isHeader ? n.x : i.plotX + f.plotLeft + t(e.distance, 16),
                            y: n.pos + f.plotTop,
                            anchorX: i.isHeader ? i.plotX + f.plotLeft : i.plotX + r.xAxis.pos,
                            anchorY: i.isHeader ? n.pos + f.plotTop - 15 : i.plotY + r.yAxis.pos
                        })
                    })
                },
                updatePosition: function(n) {
                    var i = this.chart,
                        t = this.getLabel(),
                        t = (this.options.positioner || this.getPosition).call(this, t.width, t.height, n);
                    this.move(Math.round(t.x), Math.round(t.y || 0), n.plotX + i.plotLeft, n.plotY + i.plotTop)
                },
                getDateFormat: function(n, t, i, r) {
                    var s = f("%m-%d %H:%M:%S.%L", t),
                        h, e, o = {
                            millisecond: 15,
                            second: 12,
                            minute: 9,
                            hour: 6,
                            day: 3
                        },
                        c = "millisecond";
                    for (e in u) {
                        if (n === u.week && +f("%w", t) === i && "00:00:00.000" === s.substr(6)) {
                            e = "week";
                            break
                        }
                        if (u[e] > n) {
                            e = c;
                            break
                        }
                        if (o[e] && s.substr(o[e]) !== "01-01 00:00:00.000".substr(o[e])) break;
                        "week" !== e && (c = e)
                    }
                    return e && (h = r[e]), h
                },
                getXDateFormat: function(n, t, i) {
                    t = t.dateTimeLabelFormats;
                    var r = i && i.closestPointRange;
                    return (r ? this.getDateFormat(r, n.x, i.options.startOfWeek, t) : t.day) || t.year
                },
                tooltipFooterHeaderFormatter: function(n, t) {
                    var i = t ? "footer" : "header";
                    t = n.series;
                    var u = t.tooltipOptions,
                        r = u.xDateFormat,
                        f = t.xAxis,
                        e = f && "datetime" === f.options.type && h(n.key),
                        i = u[i + "Format"];
                    return e && !r && (r = this.getXDateFormat(n, u, f)), e && r && (i = i.replace("{point.key}", "{point.key:" + r + "}")), s(i, {
                        point: n,
                        series: t
                    })
                },
                bodyFormatter: function(n) {
                    return e(n, function(n) {
                        var t = n.series.tooltipOptions;
                        return (t.pointFormatter || n.point.tooltipFormatter).call(n.point, t.pointFormat)
                    })
                }
            }
        }(n),
        function(n) {
            var u = n.addEvent,
                l = n.attr,
                i = n.charts,
                a = n.color,
                v = n.css,
                h = n.defined,
                r = n.doc,
                t = n.each,
                f = n.extend,
                o = n.fireEvent,
                y = n.offset,
                e = n.pick,
                s = n.removeEvent,
                p = n.splat,
                c = n.Tooltip,
                w = n.win;
            n.Pointer = function(n, t) {
                this.init(n, t)
            };
            n.Pointer.prototype = {
                init: function(n, t) {
                    this.options = t;
                    this.chart = n;
                    this.runChartClick = t.chart.events && !!t.chart.events.click;
                    this.pinchDown = [];
                    this.lastValidTouch = {};
                    c && t.tooltip.enabled && (n.tooltip = new c(n, t.tooltip), this.followTouchMove = e(t.tooltip.followTouchMove, !0));
                    this.setDOMEvents()
                },
                zoomOption: function(n) {
                    var i = this.chart,
                        r = i.options.chart,
                        t = r.zoomType || "",
                        i = i.inverted;
                    /touch/.test(n.type) && (t = e(r.pinchType, t));
                    this.zoomX = n = /x/.test(t);
                    this.zoomY = t = /y/.test(t);
                    this.zoomHor = n && !i || t && i;
                    this.zoomVert = t && !i || n && i;
                    this.hasZoom = n || t
                },
                normalize: function(n, t) {
                    var r, i;
                    return n = n || w.event, n.target || (n.target = n.srcElement), i = n.touches ? n.touches.length ? n.touches.item(0) : n.changedTouches[0] : n, t || (this.chartPosition = t = y(this.chart.container)), void 0 === i.pageX ? (r = Math.max(n.x, n.clientX - t.left), t = n.y) : (r = i.pageX - t.left, t = i.pageY - t.top), f(n, {
                        chartX: Math.round(r),
                        chartY: Math.round(t)
                    })
                },
                getCoordinates: function(n) {
                    var i = {
                        xAxis: [],
                        yAxis: []
                    };
                    return t(this.chart.axes, function(t) {
                        i[t.isXAxis ? "xAxis" : "yAxis"].push({
                            axis: t,
                            value: t.toValue(n[t.horiz ? "chartX" : "chartY"])
                        })
                    }), i
                },
                runPointActions: function(f) {
                    var v = this.chart,
                        l = v.series,
                        c = v.tooltip,
                        a = c ? c.shared : !1,
                        b = !0,
                        y = v.hoverPoint,
                        h = v.hoverSeries,
                        s, p, k, o = [],
                        w;
                    if (!a && !h)
                        for (s = 0; s < l.length; s++)(l[s].directTouch || !l[s].options.stickyTracking) && (l = []);
                    if (h && (a ? h.noSharedTooltip : h.directTouch) && y ? o = [y] : (a || !h || h.options.stickyTracking || (l = [h]), t(l, function(n) {
                            p = n.noSharedTooltip && a;
                            k = !a && n.directTouch;
                            n.visible && !p && !k && e(n.options.enableMouseTracking, !0) && (w = n.searchPoint(f, !p && 1 === n.kdDimensions)) && w.series && o.push(w)
                        }), o.sort(function(n, t) {
                            var i = n.distX - t.distX,
                                r = n.dist - t.dist,
                                u = (t.series.group && t.series.group.zIndex) - (n.series.group && n.series.group.zIndex);
                            return 0 !== i && a ? i : 0 !== r ? r : 0 !== u ? u : n.series.index > t.series.index ? -1 : 1
                        })), a)
                        for (s = o.length; s--;)(o[s].x !== o[0].x || o[s].series.noSharedTooltip) && o.splice(s, 1);
                    if (o[0] && (o[0] !== this.prevKDPoint || c && c.isHidden)) {
                        if (a && !o[0].series.noSharedTooltip) {
                            for (s = 0; s < o.length; s++) o[s].onMouseOver(f, o[s] !== (h && h.directTouch && y || o[0]));
                            o.length && c && c.refresh(o.sort(function(n, t) {
                                return n.series.index - t.series.index
                            }), f)
                        } else if (c && c.refresh(o[0], f), !h || !h.directTouch) o[0].onMouseOver(f);
                        this.prevKDPoint = o[0];
                        b = !1
                    }
                    b && (l = h && h.tooltipOptions.followPointer, c && l && !c.isHidden && (l = c.getAnchor([{}], f), c.updatePosition({
                        plotX: l[0],
                        plotY: l[1]
                    })));
                    this.unDocMouseMove || (this.unDocMouseMove = u(r, "mousemove", function(t) {
                        if (i[n.hoverChartIndex]) i[n.hoverChartIndex].pointer.onDocumentMouseMove(t)
                    }));
                    t(a ? o : [e(y, o[0])], function(n) {
                        t(v.axes, function(t) {
                            (!n || n.series && n.series[t.coll] === t) && t.drawCrosshair(f, n)
                        })
                    })
                },
                reset: function(n, i) {
                    var r = this.chart,
                        s = r.hoverSeries,
                        u = r.hoverPoint,
                        o = r.hoverPoints,
                        f = r.tooltip,
                        e = f && f.shared ? o : u;
                    n && e && t(p(e), function(t) {
                        t.series.isCartesian && void 0 === t.plotX && (n = !1)
                    });
                    n ? f && e && (f.refresh(e), u && (u.setState(u.state, !0), t(r.axes, function(n) {
                        n.crosshair && n.drawCrosshair(null, u)
                    }))) : (u && u.onMouseOut(), o && t(o, function(n) {
                        n.setState()
                    }), s && s.onMouseOut(), f && f.hide(i), this.unDocMouseMove && (this.unDocMouseMove = this.unDocMouseMove()), t(r.axes, function(n) {
                        n.hideCrosshair()
                    }), this.hoverX = this.prevKDPoint = r.hoverPoints = r.hoverPoint = null)
                },
                scaleGroups: function(n, i) {
                    var r = this.chart,
                        u;
                    t(r.series, function(t) {
                        u = n || t.getPlotBox();
                        t.xAxis && t.xAxis.zoomEnabled && t.group && (t.group.attr(u), t.markerGroup && (t.markerGroup.attr(u), t.markerGroup.clip(i ? r.clipRect : null)), t.dataLabelsGroup && t.dataLabelsGroup.attr(u))
                    });
                    r.clipRect.attr(i || r.clipBox)
                },
                dragStart: function(n) {
                    var t = this.chart;
                    t.mouseIsDown = n.type;
                    t.cancelClick = !1;
                    t.mouseDownX = this.mouseDownX = n.chartX;
                    t.mouseDownY = this.mouseDownY = n.chartY
                },
                drag: function(n) {
                    var i = this.chart,
                        o = i.options.chart,
                        t = n.chartX,
                        u = n.chartY,
                        y = this.zoomHor,
                        p = this.zoomVert,
                        f = i.plotLeft,
                        e = i.plotTop,
                        c = i.plotWidth,
                        l = i.plotHeight,
                        v, r = this.selectionMarker,
                        s = this.mouseDownX,
                        h = this.mouseDownY,
                        w = o.panKey && n[o.panKey + "Key"];
                    r && r.touch || (t < f ? t = f : t > f + c && (t = f + c), u < e ? u = e : u > e + l && (u = e + l), this.hasDragged = Math.sqrt(Math.pow(s - t, 2) + Math.pow(h - u, 2)), 10 < this.hasDragged && (v = i.isInsidePlot(s - f, h - e), i.hasCartesianSeries && (this.zoomX || this.zoomY) && v && !w && !r && (this.selectionMarker = r = i.renderer.rect(f, e, y ? 1 : c, p ? 1 : l, 0).attr({
                        fill: o.selectionMarkerFill || a("#335cad").setOpacity(.25).get(),
                        "class": "highcharts-selection-marker",
                        zIndex: 7
                    }).add()), r && y && (t -= s, r.attr({
                        width: Math.abs(t),
                        x: (0 < t ? 0 : t) + s
                    })), r && p && (t = u - h, r.attr({
                        height: Math.abs(t),
                        y: (0 < t ? 0 : t) + h
                    })), v && !r && o.panning && i.pan(n, o.panning)))
                },
                drop: function(n) {
                    var a = this,
                        r = this.chart,
                        u = this.hasPinched;
                    if (this.selectionMarker) {
                        var e = {
                                originalEvent: n,
                                xAxis: [],
                                yAxis: []
                            },
                            i = this.selectionMarker,
                            s = i.attr ? i.attr("x") : i.x,
                            c = i.attr ? i.attr("y") : i.y,
                            y = i.attr ? i.attr("width") : i.width,
                            p = i.attr ? i.attr("height") : i.height,
                            l;
                        (this.hasDragged || u) && (t(r.axes, function(t) {
                            if (t.zoomEnabled && h(t.min) && (u || a[{
                                    xAxis: "zoomX",
                                    yAxis: "zoomY"
                                } [t.coll]])) {
                                var i = t.horiz,
                                    r = "touchend" === n.type ? t.minPixelPadding : 0,
                                    f = t.toValue((i ? s : c) + r),
                                    i = t.toValue((i ? s + y : c + p) - r);
                                e[t.coll].push({
                                    axis: t,
                                    min: Math.min(f, i),
                                    max: Math.max(f, i)
                                });
                                l = !0
                            }
                        }), l && o(r, "selection", e, function(n) {
                            r.zoom(f(n, u ? {
                                animation: !1
                            } : null))
                        }));
                        this.selectionMarker = this.selectionMarker.destroy();
                        u && this.scaleGroups()
                    }
                    r && (v(r.container, {
                        cursor: r._cursor
                    }), r.cancelClick = 10 < this.hasDragged, r.mouseIsDown = this.hasDragged = this.hasPinched = !1, this.pinchDown = [])
                },
                onContainerMouseDown: function(n) {
                    n = this.normalize(n);
                    this.zoomOption(n);
                    n.preventDefault && n.preventDefault();
                    this.dragStart(n)
                },
                onDocumentMouseUp: function(t) {
                    i[n.hoverChartIndex] && i[n.hoverChartIndex].pointer.drop(t)
                },
                onDocumentMouseMove: function(n) {
                    var t = this.chart,
                        i = this.chartPosition;
                    n = this.normalize(n, i);
                    !i || this.inClass(n.target, "highcharts-tracker") || t.isInsidePlot(n.chartX - t.plotLeft, n.chartY - t.plotTop) || this.reset()
                },
                onContainerMouseLeave: function(t) {
                    var r = i[n.hoverChartIndex];
                    r && (t.relatedTarget || t.toElement) && (r.pointer.reset(), r.pointer.chartPosition = null)
                },
                onContainerMouseMove: function(t) {
                    var r = this.chart;
                    h(n.hoverChartIndex) && i[n.hoverChartIndex] && i[n.hoverChartIndex].mouseIsDown || (n.hoverChartIndex = r.index);
                    t = this.normalize(t);
                    t.returnValue = !1;
                    "mousedown" === r.mouseIsDown && this.drag(t);
                    (this.inClass(t.target, "highcharts-tracker") || r.isInsidePlot(t.chartX - r.plotLeft, t.chartY - r.plotTop)) && !r.openMenu && this.runPointActions(t)
                },
                inClass: function(n, t) {
                    for (var i; n;) {
                        if (i = l(n, "class")) {
                            if (-1 !== i.indexOf(t)) return !0;
                            if (-1 !== i.indexOf("highcharts-container")) return !1
                        }
                        n = n.parentNode
                    }
                },
                onTrackerMouseOut: function(n) {
                    var t = this.chart.hoverSeries;
                    n = n.relatedTarget || n.toElement;
                    !t || !n || t.options.stickyTracking || this.inClass(n, "highcharts-tooltip") || this.inClass(n, "highcharts-series-" + t.index) && this.inClass(n, "highcharts-tracker") || t.onMouseOut()
                },
                onContainerClick: function(n) {
                    var t = this.chart,
                        i = t.hoverPoint,
                        r = t.plotLeft,
                        u = t.plotTop;
                    n = this.normalize(n);
                    t.cancelClick || (i && this.inClass(n.target, "highcharts-tracker") ? (o(i.series, "click", f(n, {
                        point: i
                    })), t.hoverPoint && i.firePointEvent("click", n)) : (f(n, this.getCoordinates(n)), t.isInsidePlot(n.chartX - r, n.chartY - u) && o(t, "click", n)))
                },
                setDOMEvents: function() {
                    var t = this,
                        i = t.chart.container;
                    i.onmousedown = function(n) {
                        t.onContainerMouseDown(n)
                    };
                    i.onmousemove = function(n) {
                        t.onContainerMouseMove(n)
                    };
                    i.onclick = function(n) {
                        t.onContainerClick(n)
                    };
                    u(i, "mouseleave", t.onContainerMouseLeave);
                    1 === n.chartCount && u(r, "mouseup", t.onDocumentMouseUp);
                    n.hasTouch && (i.ontouchstart = function(n) {
                        t.onContainerTouchStart(n)
                    }, i.ontouchmove = function(n) {
                        t.onContainerTouchMove(n)
                    }, 1 === n.chartCount && u(r, "touchend", t.onDocumentTouchEnd))
                },
                destroy: function() {
                    var t;
                    s(this.chart.container, "mouseleave", this.onContainerMouseLeave);
                    n.chartCount || (s(r, "mouseup", this.onDocumentMouseUp), s(r, "touchend", this.onDocumentTouchEnd));
                    clearInterval(this.tooltipTimeout);
                    for (t in this) this[t] = null
                }
            }
        }(n),
        function(n) {
            var i = n.charts,
                r = n.each,
                u = n.extend,
                f = n.map,
                e = n.noop,
                t = n.pick;
            u(n.Pointer.prototype, {
                pinchTranslate: function(n, t, i, r, u, f) {
                    this.zoomHor && this.pinchTranslateDirection(!0, n, t, i, r, u, f);
                    this.zoomVert && this.pinchTranslateDirection(!1, n, t, i, r, u, f)
                },
                pinchTranslateDirection: function(n, t, i, r, u, f, e, o) {
                    var a = this.chart,
                        c = n ? "x" : "y",
                        k = n ? "X" : "Y",
                        v = "chart" + k,
                        it = n ? "width" : "height",
                        d = a["plot" + (n ? "Left" : "Top")],
                        l, g, s = o || 1,
                        nt = a.inverted,
                        y = a.bounds[n ? "h" : "v"],
                        p = 1 === t.length,
                        w = t[0][v],
                        h = i[0][v],
                        rt = !p && t[1][v],
                        b = !p && i[1][v],
                        tt;
                    i = function() {
                        !p && 20 < Math.abs(w - rt) && (s = o || Math.abs(h - b) / Math.abs(w - rt));
                        g = (d - h) / s + w;
                        l = a["plot" + (n ? "Width" : "Height")] / s
                    };
                    i();
                    t = g;
                    t < y.min ? (t = y.min, tt = !0) : t + l > y.max && (t = y.max - l, tt = !0);
                    tt ? (h -= .8 * (h - e[c][0]), p || (b -= .8 * (b - e[c][1])), i()) : e[c] = [h, b];
                    nt || (f[c] = g - d, f[it] = l);
                    f = nt ? 1 / s : s;
                    u[it] = l;
                    u[c] = t;
                    r[nt ? n ? "scaleY" : "scaleX" : "scale" + k] = s;
                    r["translate" + k] = f * d + (h - f * w)
                },
                pinch: function(n) {
                    var i = this,
                        s = i.chart,
                        o = i.pinchDown,
                        h = n.touches,
                        c = h.length,
                        l = i.lastValidTouch,
                        v = i.hasZoom,
                        a = i.selectionMarker,
                        y = {},
                        w = 1 === c && (i.inClass(n.target, "highcharts-tracker") && s.runTrackerClick || i.runChartClick),
                        p = {};
                    1 < c && (i.initiated = !0);
                    v && i.initiated && !w && n.preventDefault();
                    f(h, function(n) {
                        return i.normalize(n)
                    });
                    "touchstart" === n.type ? (r(h, function(n, t) {
                        o[t] = {
                            chartX: n.chartX,
                            chartY: n.chartY
                        }
                    }), l.x = [o[0].chartX, o[1] && o[1].chartX], l.y = [o[0].chartY, o[1] && o[1].chartY], r(s.axes, function(n) {
                        if (n.zoomEnabled) {
                            var i = s.bounds[n.horiz ? "h" : "v"],
                                r = n.minPixelPadding,
                                u = n.toPixels(t(n.options.min, n.dataMin)),
                                f = n.toPixels(t(n.options.max, n.dataMax)),
                                e = Math.max(u, f);
                            i.min = Math.min(n.pos, Math.min(u, f) - r);
                            i.max = Math.max(n.pos + n.len, e + r)
                        }
                    }), i.res = !0) : i.followTouchMove && 1 === c ? this.runPointActions(i.normalize(n)) : o.length && (a || (i.selectionMarker = a = u({
                        destroy: e,
                        touch: !0
                    }, s.plotBox)), i.pinchTranslate(o, h, y, a, p, l), i.hasPinched = v, i.scaleGroups(y, p), i.res && (i.res = !1, this.reset(!1, 0)))
                },
                touch: function(i, r) {
                    var u = this.chart,
                        f, e;
                    if (u.index !== n.hoverChartIndex) this.onContainerMouseLeave({
                        relatedTarget: !0
                    });
                    n.hoverChartIndex = u.index;
                    1 === i.touches.length ? (i = this.normalize(i), (e = u.isInsidePlot(i.chartX - u.plotLeft, i.chartY - u.plotTop)) && !u.openMenu ? (r && this.runPointActions(i), "touchmove" === i.type && (r = this.pinchDown, f = r[0] ? 4 <= Math.sqrt(Math.pow(r[0].chartX - i.chartX, 2) + Math.pow(r[0].chartY - i.chartY, 2)) : !1), t(f, !0) && this.pinch(i)) : r && this.reset()) : 2 === i.touches.length && this.pinch(i)
                },
                onContainerTouchStart: function(n) {
                    this.zoomOption(n);
                    this.touch(n, !0)
                },
                onContainerTouchMove: function(n) {
                    this.touch(n)
                },
                onDocumentTouchEnd: function(t) {
                    i[n.hoverChartIndex] && i[n.hoverChartIndex].pointer.drop(t)
                }
            })
        }(n),
        function(n) {
            var s = n.addEvent,
                o = n.charts,
                h = n.css,
                c = n.doc,
                l = n.extend,
                a = n.noop,
                i = n.Pointer,
                v = n.removeEvent,
                r = n.win,
                u = n.wrap;
            if (r.PointerEvent || r.MSPointerEvent) {
                var t = {},
                    f = !!r.PointerEvent,
                    y = function() {
                        var n, i = [];
                        i.item = function(n) {
                            return this[n]
                        };
                        for (n in t) t.hasOwnProperty(n) && i.push({
                            pageX: t[n].pageX,
                            pageY: t[n].pageY,
                            target: t[n].target
                        });
                        return i
                    },
                    e = function(t, i, r, u) {
                        ("touch" === t.pointerType || t.pointerType === t.MSPOINTER_TYPE_TOUCH) && o[n.hoverChartIndex] && (u(t), u = o[n.hoverChartIndex].pointer, u[i]({
                            type: r,
                            target: t.currentTarget,
                            preventDefault: a,
                            touches: y()
                        }))
                    };
                l(i.prototype, {
                    onContainerPointerDown: function(n) {
                        e(n, "onContainerTouchStart", "touchstart", function(n) {
                            t[n.pointerId] = {
                                pageX: n.pageX,
                                pageY: n.pageY,
                                target: n.currentTarget
                            }
                        })
                    },
                    onContainerPointerMove: function(n) {
                        e(n, "onContainerTouchMove", "touchmove", function(n) {
                            t[n.pointerId] = {
                                pageX: n.pageX,
                                pageY: n.pageY
                            };
                            t[n.pointerId].target || (t[n.pointerId].target = n.currentTarget)
                        })
                    },
                    onDocumentPointerUp: function(n) {
                        e(n, "onDocumentTouchEnd", "touchend", function(n) {
                            delete t[n.pointerId]
                        })
                    },
                    batchMSEvents: function(n) {
                        n(this.chart.container, f ? "pointerdown" : "MSPointerDown", this.onContainerPointerDown);
                        n(this.chart.container, f ? "pointermove" : "MSPointerMove", this.onContainerPointerMove);
                        n(c, f ? "pointerup" : "MSPointerUp", this.onDocumentPointerUp)
                    }
                });
                u(i.prototype, "init", function(n, t, i) {
                    n.call(this, t, i);
                    this.hasZoom && h(t.container, {
                        "-ms-touch-action": "none",
                        "touch-action": "none"
                    })
                });
                u(i.prototype, "setDOMEvents", function(n) {
                    n.apply(this);
                    (this.hasZoom || this.followTouchMove) && this.batchMSEvents(s)
                });
                u(i.prototype, "destroy", function(n) {
                    this.batchMSEvents(v);
                    n.call(this)
                })
            }
        }(n),
        function(n) {
            var u, o = n.addEvent,
                s = n.css,
                h = n.discardElement,
                f = n.defined,
                t = n.each,
                c = n.extend,
                l = n.isFirefox,
                e = n.marginNames,
                r = n.merge,
                i = n.pick,
                a = n.setAnimation,
                v = n.stableSort,
                y = n.win,
                p = n.wrap;
            u = n.Legend = function(n, t) {
                this.init(n, t)
            };
            u.prototype = {
                init: function(n, t) {
                    this.chart = n;
                    this.setOptions(t);
                    t.enabled && (this.render(), o(this.chart, "endResize", function() {
                        this.legend.positionCheckboxes()
                    }))
                },
                setOptions: function(n) {
                    var t = i(n.padding, 8);
                    this.options = n;
                    this.itemStyle = n.itemStyle;
                    this.itemHiddenStyle = r(this.itemStyle, n.itemHiddenStyle);
                    this.itemMarginTop = n.itemMarginTop || 0;
                    this.initialItemX = this.padding = t;
                    this.initialItemY = t - 5;
                    this.itemHeight = this.maxItemWidth = 0;
                    this.symbolWidth = i(n.symbolWidth, 16);
                    this.pages = []
                },
                update: function(n, t) {
                    var u = this.chart;
                    this.setOptions(r(!0, this.options, n));
                    this.destroy();
                    u.isDirtyLegend = u.isDirtyBox = !0;
                    i(t, !0) && u.redraw()
                },
                colorizeItem: function(n, t) {
                    n.legendGroup[t ? "removeClass" : "addClass"]("highcharts-legend-item-hidden");
                    var u = this.options,
                        e = n.legendItem,
                        o = n.legendLine,
                        f = n.legendSymbol,
                        i = this.itemHiddenStyle.color,
                        u = t ? u.itemStyle.color : i,
                        s = t ? n.color || i : i,
                        c = n.options && n.options.marker,
                        r = {
                            fill: s
                        },
                        h;
                    if (e && e.css({
                            fill: u,
                            color: u
                        }), o && o.attr({
                            stroke: s
                        }), f) {
                        if (c && f.isMarker && (r = n.pointAttribs(), !t))
                            for (h in r) r[h] = i;
                        f.attr(r)
                    }
                },
                positionItem: function(n) {
                    var i = this.options,
                        f = i.symbolPadding,
                        i = !i.rtl,
                        t = n._legendItemPos,
                        r = t[0],
                        t = t[1],
                        u = n.checkbox;
                    (n = n.legendGroup) && n.element && n.translate(i ? r : this.legendWidth - r - 2 * f - 4, t);
                    u && (u.x = r, u.y = t)
                },
                destroyItem: function(n) {
                    var i = n.checkbox;
                    t(["legendItem", "legendLine", "legendSymbol", "legendGroup"], function(t) {
                        n[t] && (n[t] = n[t].destroy())
                    });
                    i && h(n.checkbox)
                },
                destroy: function() {
                    function n(n) {
                        this[n] && (this[n] = this[n].destroy())
                    }
                    t(this.getAllItems(), function(i) {
                        t(["legendItem", "legendGroup"], n, i)
                    });
                    t(["box", "title", "group"], n, this);
                    this.display = null
                },
                positionCheckboxes: function(n) {
                    var r = this.group && this.group.alignAttr,
                        i, u = this.clipHeight || this.legendHeight,
                        f = this.titleHeight;
                    r && (i = r.translateY, t(this.allItems, function(t) {
                        var e = t.checkbox,
                            o;
                        e && (o = i + f + e.y + (n || 0) + 3, s(e, {
                            left: r.translateX + t.checkboxOffset + e.x - 20 + "px",
                            top: o + "px",
                            display: o > i - 6 && o < i + u - 6 ? "" : "none"
                        }))
                    }))
                },
                renderTitle: function() {
                    var n = this.padding,
                        t = this.options.title,
                        i = 0;
                    t.text && (this.title || (this.title = this.chart.renderer.label(t.text, n - 3, n - 4, null, null, null, null, null, "legend-title").attr({
                        zIndex: 1
                    }).css(t.style).add(this.group)), n = this.title.getBBox(), i = n.height, this.offsetWidth = n.width, this.contentGroup.attr({
                        translateY: i
                    }));
                    this.titleHeight = i
                },
                setText: function(t) {
                    var i = this.options;
                    t.legendItem.attr({
                        text: i.labelFormat ? n.format(i.labelFormat, t) : i.labelFormatter.call(t)
                    })
                },
                renderItem: function(n) {
                    var p = this.chart,
                        e = p.renderer,
                        t = this.options,
                        s = "horizontal" === t.layout,
                        u = this.symbolWidth,
                        f = t.symbolPadding,
                        h = this.itemStyle,
                        tt = this.itemHiddenStyle,
                        w = this.padding,
                        b = s ? i(t.itemDistance, 20) : 0,
                        k = !t.rtl,
                        d = t.width,
                        v = t.itemMarginBottom || 0,
                        c = this.itemMarginTop,
                        l = this.initialItemX,
                        o = n.legendItem,
                        g = !n.series,
                        y = !g && n.series.drawLegendSymbol ? n.series : n,
                        a = y.options,
                        a = this.createCheckboxForItem && a && a.showCheckbox,
                        nt = t.useHTML;
                    o || (n.legendGroup = e.g("legend-item").addClass("highcharts-" + y.type + "-series highcharts-color-" + n.colorIndex + (n.options.className ? " " + n.options.className : "") + (g ? " highcharts-series-" + n.index : "")).attr({
                        zIndex: 1
                    }).add(this.scrollGroup), n.legendItem = o = e.text("", k ? u + f : -f, this.baseline || 0, nt).css(r(n.visible ? h : tt)).attr({
                        align: k ? "left" : "right",
                        zIndex: 2
                    }).add(n.legendGroup), this.baseline || (h = h.fontSize, this.fontMetrics = e.fontMetrics(h, o), this.baseline = this.fontMetrics.f + 3 + c, o.attr("y", this.baseline)), this.symbolHeight = t.symbolHeight || this.fontMetrics.f, y.drawLegendSymbol(this, n), this.setItemEvents && this.setItemEvents(n, o, nt), a && this.createCheckboxForItem(n));
                    this.colorizeItem(n, n.visible);
                    this.setText(n);
                    e = o.getBBox();
                    u = n.checkboxOffset = t.itemWidth || n.legendItemWidth || u + f + e.width + b + (a ? 20 : 0);
                    this.itemHeight = f = Math.round(n.legendItemHeight || e.height);
                    s && this.itemX - l + u > (d || p.chartWidth - 2 * w - l - t.x) && (this.itemX = l, this.itemY += c + this.lastLineHeight + v, this.lastLineHeight = 0);
                    this.maxItemWidth = Math.max(this.maxItemWidth, u);
                    this.lastItemY = c + this.itemY + v;
                    this.lastLineHeight = Math.max(f, this.lastLineHeight);
                    n._legendItemPos = [this.itemX, this.itemY];
                    s ? this.itemX += u : (this.itemY += c + f + v, this.lastLineHeight = f);
                    this.offsetWidth = d || Math.max((s ? this.itemX - l - b : u) + w, this.offsetWidth)
                },
                getAllItems: function() {
                    var n = [];
                    return t(this.chart.series, function(t) {
                        var r = t && t.options;
                        t && i(r.showInLegend, f(r.linkedTo) ? !1 : void 0, !0) && (n = n.concat(t.legendItems || ("point" === r.legendType ? t.data : t)))
                    }), n
                },
                adjustMargins: function(n, r) {
                    var o = this.chart,
                        u = this.options,
                        s = u.align.charAt(0) + u.verticalAlign.charAt(0) + u.layout.charAt(0);
                    u.floating || t([/(lth|ct|rth)/, /(rtv|rm|rbv)/, /(rbh|cb|lbh)/, /(lbv|lm|ltv)/], function(t, h) {
                        t.test(s) && !f(n[h]) && (o[e[h]] = Math.max(o[e[h]], o.legend[(h + 1) % 2 ? "legendHeight" : "legendWidth"] + [1, -1, -1, 1][h] * u[h % 2 ? "x" : "y"] + i(u.margin, 12) + r[h]))
                    })
                },
                render: function() {
                    var n = this,
                        l = n.chart,
                        s = l.renderer,
                        e = n.group,
                        f, h, o, r, i = n.box,
                        u = n.options,
                        a = n.padding;
                    n.itemX = n.initialItemX;
                    n.itemY = n.initialItemY;
                    n.offsetWidth = 0;
                    n.lastItemY = 0;
                    e || (n.group = e = s.g("legend").attr({
                        zIndex: 7
                    }).add(), n.contentGroup = s.g().attr({
                        zIndex: 1
                    }).add(e), n.scrollGroup = s.g().add(n.contentGroup));
                    n.renderTitle();
                    f = n.getAllItems();
                    v(f, function(n, t) {
                        return (n.options && n.options.legendIndex || 0) - (t.options && t.options.legendIndex || 0)
                    });
                    u.reversed && f.reverse();
                    n.allItems = f;
                    n.display = h = !!f.length;
                    n.lastLineHeight = 0;
                    t(f, function(t) {
                        n.renderItem(t)
                    });
                    o = (u.width || n.offsetWidth) + a;
                    r = n.lastItemY + n.lastLineHeight + n.titleHeight;
                    r = n.handleOverflow(r);
                    r += a;
                    i || (n.box = i = s.rect().addClass("highcharts-legend-box").attr({
                        r: u.borderRadius
                    }).add(e), i.isNew = !0);
                    i.attr({
                        stroke: u.borderColor,
                        "stroke-width": u.borderWidth || 0,
                        fill: u.backgroundColor || "none"
                    }).shadow(u.shadow);
                    0 < o && 0 < r && (i[i.isNew ? "attr" : "animate"](i.crisp({
                        x: 0,
                        y: 0,
                        width: o,
                        height: r
                    }, i.strokeWidth())), i.isNew = !1);
                    i[h ? "show" : "hide"]();
                    n.legendWidth = o;
                    n.legendHeight = r;
                    t(f, function(t) {
                        n.positionItem(t)
                    });
                    h && e.align(c({
                        width: o,
                        height: r
                    }, u), !0, "spacingBox");
                    l.isResizing || this.positionCheckboxes()
                },
                handleOverflow: function(n) {
                    var r = this,
                        u = this.chart,
                        h = u.renderer,
                        f = this.options,
                        c = f.y,
                        u = u.spacingBox.height + ("top" === f.verticalAlign ? -c : c) - this.padding,
                        c = f.maxHeight,
                        l, s = this.clipRect,
                        a = f.navigation,
                        w = i(a.animation, !0),
                        v = a.arrowSize || 12,
                        e = this.nav,
                        o = this.pages,
                        y = this.padding,
                        p, b = this.allItems,
                        k = function(n) {
                            n ? s.attr({
                                height: n
                            }) : s && (r.clipRect = s.destroy(), r.contentGroup.clip());
                            r.contentGroup.div && (r.contentGroup.div.style.clip = n ? "rect(" + y + "px,9999px," + (y + n) + "px,0)" : "auto")
                        };
                    return "horizontal" !== f.layout || "middle" === f.verticalAlign || f.floating || (u /= 2), c && (u = Math.min(u, c)), o.length = 0, n > u && !1 !== a.enabled ? (this.clipHeight = l = Math.max(u - 20 - this.titleHeight - y, 0), this.currentPage = i(this.currentPage, 1), this.fullHeight = n, t(b, function(n, t) {
                        var i = n._legendItemPos[1],
                            r;
                        n = Math.round(n.legendItem.getBBox().height);
                        r = o.length;
                        (!r || i - o[r - 1] > l && (p || i) !== o[r - 1]) && (o.push(p || i), r++);
                        t === b.length - 1 && i + n - o[r - 1] > l && o.push(i);
                        i !== p && (p = i)
                    }), s || (s = r.clipRect = h.clipRect(0, y, 9999, 0), r.contentGroup.clip(s)), k(l), e || (this.nav = e = h.g().attr({
                        zIndex: 1
                    }).add(this.group), this.up = h.symbol("triangle", 0, 0, v, v).on("click", function() {
                        r.scroll(-1, w)
                    }).add(e), this.pager = h.text("", 15, 10).addClass("highcharts-legend-navigation").css(a.style).add(e), this.down = h.symbol("triangle-down", 0, 0, v, v).on("click", function() {
                        r.scroll(1, w)
                    }).add(e)), r.scroll(0), n = u) : e && (k(), e.hide(), this.scrollGroup.attr({
                        translateY: 1
                    }), this.clipHeight = 0), n
                },
                scroll: function(n, t) {
                    var u = this.pages,
                        i = u.length;
                    n = this.currentPage + n;
                    var f = this.clipHeight,
                        r = this.options.navigation,
                        e = this.pager,
                        o = this.padding;
                    n > i && (n = i);
                    0 < n && (void 0 !== t && a(t, this.chart), this.nav.attr({
                        translateX: o,
                        translateY: f + this.padding + 7 + this.titleHeight,
                        visibility: "visible"
                    }), this.up.attr({
                        "class": 1 === n ? "highcharts-legend-nav-inactive" : "highcharts-legend-nav-active"
                    }), e.attr({
                        text: n + "/" + i
                    }), this.down.attr({
                        x: 18 + this.pager.getBBox().width,
                        "class": n === i ? "highcharts-legend-nav-inactive" : "highcharts-legend-nav-active"
                    }), this.up.attr({
                        fill: 1 === n ? r.inactiveColor : r.activeColor
                    }).css({
                        cursor: 1 === n ? "default" : "pointer"
                    }), this.down.attr({
                        fill: n === i ? r.inactiveColor : r.activeColor
                    }).css({
                        cursor: n === i ? "default" : "pointer"
                    }), t = -u[n - 1] + this.initialItemY, this.scrollGroup.animate({
                        translateY: t
                    }), this.currentPage = n, this.positionCheckboxes(t))
                }
            };
            n.LegendSymbolMixin = {
                drawRectangle: function(n, t) {
                    var r = n.symbolHeight,
                        u = n.options.squareSymbol;
                    t.legendSymbol = this.chart.renderer.rect(u ? (n.symbolWidth - r) / 2 : 0, n.baseline - r + 1, u ? r : n.symbolWidth, r, i(n.options.symbolRadius, r / 2)).addClass("highcharts-point").attr({
                        zIndex: 3
                    }).add(t.legendGroup)
                },
                drawLineMarker: function(n) {
                    var t = this.options,
                        u = t.marker,
                        o = n.symbolWidth,
                        f = n.symbolHeight,
                        s = f / 2,
                        h = this.chart.renderer,
                        c = this.legendGroup,
                        e;
                    n = n.baseline - Math.round(.3 * n.fontMetrics.b);
                    e = {
                        "stroke-width": t.lineWidth || 0
                    };
                    t.dashStyle && (e.dashstyle = t.dashStyle);
                    this.legendLine = h.path(["M", 0, n, "L", o, n]).addClass("highcharts-graph").attr(e).add(c);
                    u && !1 !== u.enabled && (t = Math.min(i(u.radius, s), s), 0 === this.symbol.indexOf("url") && (u = r(u, {
                        width: f,
                        height: f
                    }), t = 0), this.legendSymbol = u = h.symbol(this.symbol, o / 2 - t, n - t, 2 * t, 2 * t, u).addClass("highcharts-point").add(c), u.isMarker = !0)
                }
            };
            (/Trident\/7\.0/.test(y.navigator.userAgent) || l) && p(u.prototype, "positionItem", function(n, t) {
                var r = this,
                    i = function() {
                        t._legendItemPos && n.call(r, t)
                    };
                i();
                setTimeout(i)
            })
        }(n),
        function(n) {
            var l = n.addEvent,
                ft = n.animate,
                et = n.animObject,
                w = n.attr,
                r = n.doc,
                ot = n.Axis,
                st = n.createElement,
                ht = n.defaultOptions,
                b = n.discardElement,
                u = n.charts,
                k = n.css,
                f = n.defined,
                t = n.each,
                o = n.extend,
                a = n.find,
                i = n.fireEvent,
                s = n.getStyle,
                d = n.grep,
                ct = n.isNumber,
                lt = n.isObject,
                v = n.isString,
                at = n.Legend,
                g = n.marginNames,
                h = n.merge,
                nt = n.Pointer,
                c = n.pick,
                y = n.pInt,
                tt = n.removeEvent,
                p = n.seriesTypes,
                it = n.splat,
                vt = n.svg,
                rt = n.syncTimeout,
                e = n.win,
                yt = n.Renderer,
                ut = n.Chart = function() {
                    this.getArgs.apply(this, arguments)
                };
            n.chart = function(n, t, i) {
                return new ut(n, t, i)
            };
            ut.prototype = {
                callbacks: [],
                getArgs: function() {
                    var n = [].slice.call(arguments);
                    (v(n[0]) || n[0].nodeName) && (this.renderTo = n.shift());
                    this.init(n[0], n[1])
                },
                init: function(t, i) {
                    var f, r = t.series,
                        e;
                    if (t.series = null, f = h(ht, t), f.series = t.series = r, this.userOptions = t, this.respRules = [], t = f.chart, r = t.events, this.margin = [], this.spacing = [], this.bounds = {
                            h: {},
                            v: {}
                        }, this.callback = i, this.isResizing = 0, this.options = f, this.axes = [], this.series = [], this.hasCartesianSeries = t.showAxes, this.index = u.length, u.push(this), n.chartCount++, r)
                        for (e in r) l(this, e, r[e]);
                    this.xAxis = [];
                    this.yAxis = [];
                    this.pointCount = this.colorCounter = this.symbolCounter = 0;
                    this.firstRender()
                },
                initSeries: function(t) {
                    var i = this.options.chart;
                    return (i = p[t.type || i.type || i.defaultSeriesType]) || n.error(17, !0), i = new i, i.init(this, t), i
                },
                orderSeries: function(n) {
                    var t = this.series;
                    for (n = n || 0; n < t.length; n++) t[n] && (t[n].index = n, t[n].name = t[n].name || "Series " + (t[n].index + 1))
                },
                isInsidePlot: function(n, t, i) {
                    var r = i ? t : n;
                    return n = i ? n : t, 0 <= r && r <= this.plotWidth && 0 <= n && n <= this.plotHeight
                },
                redraw: function(r) {
                    var s = this.axes,
                        u = this.series,
                        c = this.pointer,
                        l = this.legend,
                        a = this.isDirtyLegend,
                        h, v, y = this.hasCartesianSeries,
                        e = this.isDirtyBox,
                        p = u.length,
                        f = p,
                        w = this.renderer,
                        b = w.isHidden(),
                        k = [];
                    for (this.setResponsive && this.setResponsive(!1), n.setAnimation(r, this), b && this.cloneRenderTo(), this.layOutTitles(); f--;)
                        if (r = u[f], r.options.stacking && (h = !0, r.isDirty)) {
                            v = !0;
                            break
                        }
                    if (v)
                        for (f = p; f--;) r = u[f], r.options.stacking && (r.isDirty = !0);
                    t(u, function(n) {
                        n.isDirty && "point" === n.options.legendType && (n.updateTotals && n.updateTotals(), a = !0);
                        n.isDirtyData && i(n, "updatedData")
                    });
                    a && l.options.enabled && (l.render(), this.isDirtyLegend = !1);
                    h && this.getStacks();
                    y && t(s, function(n) {
                        n.updateNames();
                        n.setScale()
                    });
                    this.getMargins();
                    y && (t(s, function(n) {
                        n.isDirty && (e = !0)
                    }), t(s, function(n) {
                        var t = n.min + "," + n.max;
                        n.extKey !== t && (n.extKey = t, k.push(function() {
                            i(n, "afterSetExtremes", o(n.eventArgs, n.getExtremes()));
                            delete n.eventArgs
                        }));
                        (e || h) && n.redraw()
                    }));
                    e && this.drawChartBox();
                    i(this, "predraw");
                    t(u, function(n) {
                        (e || n.isDirty) && n.visible && n.redraw();
                        n.isDirtyData = !1
                    });
                    c && c.reset(!0);
                    w.draw();
                    i(this, "redraw");
                    i(this, "render");
                    b && this.cloneRenderTo(!0);
                    t(k, function(n) {
                        n.call()
                    })
                },
                get: function(n) {
                    function t(t) {
                        return t.id === n || t.options && t.options.id === n
                    }
                    for (var u = this.series, i = a(this.axes, t) || a(this.series, t), r = 0; !i && r < u.length; r++) i = a(u[r].points || [], t);
                    return i
                },
                getAxes: function() {
                    var r = this,
                        n = this.options,
                        i = n.xAxis = it(n.xAxis || {}),
                        n = n.yAxis = it(n.yAxis || {});
                    t(i, function(n, t) {
                        n.index = t;
                        n.isX = !0
                    });
                    t(n, function(n, t) {
                        n.index = t
                    });
                    i = i.concat(n);
                    t(i, function(n) {
                        new ot(r, n)
                    })
                },
                getSelectedPoints: function() {
                    var n = [];
                    return t(this.series, function(t) {
                        n = n.concat(d(t.points || [], function(n) {
                            return n.selected
                        }))
                    }), n
                },
                getSelectedSeries: function() {
                    return d(this.series, function(n) {
                        return n.selected
                    })
                },
                setTitle: function(n, i, r) {
                    var u = this,
                        f = u.options,
                        e;
                    e = f.title = h({
                        style: {
                            color: "#333333",
                            fontSize: f.isStock ? "16px" : "18px"
                        }
                    }, f.title, n);
                    f = f.subtitle = h({
                        style: {
                            color: "#666666"
                        }
                    }, f.subtitle, i);
                    t([
                        ["title", n, e],
                        ["subtitle", i, f]
                    ], function(n, t) {
                        var i = n[0],
                            r = u[i],
                            f = n[1];
                        n = n[2];
                        r && f && (u[i] = r = r.destroy());
                        n && n.text && !r && (u[i] = u.renderer.text(n.text, 0, 0, n.useHTML).attr({
                            align: n.align,
                            "class": "highcharts-" + i,
                            zIndex: n.zIndex || 4
                        }).add(), u[i].update = function(n) {
                            u.setTitle(!t && n, t && n)
                        }, u[i].css(n.style))
                    });
                    u.layOutTitles(r)
                },
                layOutTitles: function(n) {
                    var i = 0,
                        r, u = this.renderer,
                        f = this.spacingBox;
                    t(["title", "subtitle"], function(n) {
                        var r = this[n],
                            t = this.options[n],
                            e;
                        r && (e = t.style.fontSize, e = u.fontMetrics(e, r).b, r.css({
                            width: (t.width || f.width + t.widthAdjust) + "px"
                        }).align(o({
                            y: i + e + ("title" === n ? -3 : 2)
                        }, t), !1, "spacingBox"), t.floating || t.verticalAlign || (i = Math.ceil(i + r.getBBox().height)))
                    }, this);
                    r = this.titleOffset !== i;
                    this.titleOffset = i;
                    !this.isDirtyBox && r && (this.isDirtyBox = r, this.hasRendered && c(n, !0) && this.isDirtyBox && this.redraw())
                },
                getChartSize: function() {
                    var n = this.options.chart,
                        t = n.width,
                        n = n.height,
                        i = this.renderToClone || this.renderTo;
                    f(t) || (this.containerWidth = s(i, "width"));
                    f(n) || (this.containerHeight = s(i, "height"));
                    this.chartWidth = Math.max(0, t || this.containerWidth || 600);
                    this.chartHeight = Math.max(0, n || this.containerHeight || 400)
                },
                cloneRenderTo: function(n) {
                    var t = this.renderToClone,
                        i = this.container;
                    if (n) {
                        if (t) {
                            for (; t.childNodes.length;) this.renderTo.appendChild(t.firstChild);
                            b(t);
                            delete this.renderToClone
                        }
                    } else i && i.parentNode === this.renderTo && this.renderTo.removeChild(i), this.renderToClone = t = this.renderTo.cloneNode(0), k(t, {
                        position: "absolute",
                        top: "-9999px",
                        display: "block"
                    }), t.style.setProperty && t.style.setProperty("display", "block", "important"), r.body.appendChild(t), i && t.appendChild(i)
                },
                setClassName: function(n) {
                    this.container.className = "highcharts-container " + (n || "")
                },
                getContainer: function() {
                    var t, e = this.options,
                        f = e.chart,
                        i, s, h, c;
                    t = this.renderTo;
                    h = n.uniqueKey();
                    t || (this.renderTo = t = f.renderTo);
                    v(t) && (this.renderTo = t = r.getElementById(t));
                    t || n.error(13, !0);
                    i = y(w(t, "data-highcharts-chart"));
                    ct(i) && u[i] && u[i].hasRendered && u[i].destroy();
                    w(t, "data-highcharts-chart", this.index);
                    t.innerHTML = "";
                    f.skipClone || t.offsetWidth || this.cloneRenderTo();
                    this.getChartSize();
                    i = this.chartWidth;
                    s = this.chartHeight;
                    c = o({
                        position: "relative",
                        overflow: "hidden",
                        width: i + "px",
                        height: s + "px",
                        textAlign: "left",
                        lineHeight: "normal",
                        zIndex: 0,
                        "-webkit-tap-highlight-color": "rgba(0,0,0,0)"
                    }, f.style);
                    this.container = t = st("div", {
                        id: h
                    }, c, this.renderToClone || t);
                    this._cursor = t.style.cursor;
                    this.renderer = new(n[f.renderer] || yt)(t, i, s, null, f.forExport, e.exporting && e.exporting.allowHTML);
                    this.setClassName(f.className);
                    this.renderer.setStyle(f.style);
                    this.renderer.chartIndex = this.index
                },
                getMargins: function(n) {
                    var t = this.spacing,
                        i = this.margin,
                        r = this.titleOffset;
                    this.resetMargins();
                    r && !f(i[0]) && (this.plotTop = Math.max(this.plotTop, r + this.options.title.margin + t[0]));
                    this.legend.display && this.legend.adjustMargins(i, t);
                    this.extraMargin && (this[this.extraMargin.type] = (this[this.extraMargin.type] || 0) + this.extraMargin.value);
                    this.extraTopMargin && (this.plotTop += this.extraTopMargin);
                    n || this.getAxisMargins()
                },
                getAxisMargins: function() {
                    var n = this,
                        i = n.axisOffset = [0, 0, 0, 0],
                        r = n.margin;
                    n.hasCartesianSeries && t(n.axes, function(n) {
                        n.visible && n.getOffset()
                    });
                    t(g, function(t, u) {
                        f(r[u]) || (n[t] += i[u])
                    });
                    n.setChartSize()
                },
                reflow: function(n) {
                    var t = this,
                        i = t.options.chart,
                        u = t.renderTo,
                        h = f(i.width),
                        o = i.width || s(u, "width"),
                        i = i.height || s(u, "height"),
                        u = n ? n.target : e;
                    !h && !t.isPrinting && o && i && (u === e || u === r) && ((o !== t.containerWidth || i !== t.containerHeight) && (clearTimeout(t.reflowTimeout), t.reflowTimeout = rt(function() {
                        t.container && t.setSize(void 0, void 0, !1)
                    }, n ? 100 : 0)), t.containerWidth = o, t.containerHeight = i)
                },
                initReflow: function() {
                    var n = this,
                        t;
                    t = l(e, "resize", function(t) {
                        n.reflow(t)
                    });
                    l(n, "destroy", t)
                },
                setSize: function(r, u, f) {
                    var e = this,
                        o = e.renderer;
                    e.isResizing += 1;
                    n.setAnimation(f, e);
                    e.oldChartHeight = e.chartHeight;
                    e.oldChartWidth = e.chartWidth;
                    void 0 !== r && (e.options.chart.width = r);
                    void 0 !== u && (e.options.chart.height = u);
                    e.getChartSize();
                    r = o.globalAnimation;
                    (r ? ft : k)(e.container, {
                        width: e.chartWidth + "px",
                        height: e.chartHeight + "px"
                    }, r);
                    e.setChartSize(!0);
                    o.setSize(e.chartWidth, e.chartHeight, f);
                    t(e.axes, function(n) {
                        n.isDirty = !0;
                        n.setScale()
                    });
                    e.isDirtyLegend = !0;
                    e.isDirtyBox = !0;
                    e.layOutTitles();
                    e.getMargins();
                    e.redraw(f);
                    e.oldChartHeight = null;
                    i(e, "resize");
                    rt(function() {
                        e && i(e, "endResize", null, function() {
                            --e.isResizing
                        })
                    }, et(r).duration)
                },
                setChartSize: function(n) {
                    var u = this.inverted,
                        f = this.renderer,
                        i = this.chartWidth,
                        l = this.chartHeight,
                        a = this.options.chart,
                        r = this.spacing,
                        e = this.clipOffset,
                        h, c, o, s;
                    this.plotLeft = h = Math.round(this.plotLeft);
                    this.plotTop = c = Math.round(this.plotTop);
                    this.plotWidth = o = Math.max(0, Math.round(i - h - this.marginRight));
                    this.plotHeight = s = Math.max(0, Math.round(l - c - this.marginBottom));
                    this.plotSizeX = u ? s : o;
                    this.plotSizeY = u ? o : s;
                    this.plotBorderWidth = a.plotBorderWidth || 0;
                    this.spacingBox = f.spacingBox = {
                        x: r[3],
                        y: r[0],
                        width: i - r[3] - r[1],
                        height: l - r[0] - r[2]
                    };
                    this.plotBox = f.plotBox = {
                        x: h,
                        y: c,
                        width: o,
                        height: s
                    };
                    i = 2 * Math.floor(this.plotBorderWidth / 2);
                    u = Math.ceil(Math.max(i, e[3]) / 2);
                    f = Math.ceil(Math.max(i, e[0]) / 2);
                    this.clipBox = {
                        x: u,
                        y: f,
                        width: Math.floor(this.plotSizeX - Math.max(i, e[1]) / 2 - u),
                        height: Math.max(0, Math.floor(this.plotSizeY - Math.max(i, e[2]) / 2 - f))
                    };
                    n || t(this.axes, function(n) {
                        n.setAxisSize();
                        n.setAxisTranslation()
                    })
                },
                resetMargins: function() {
                    var n = this,
                        i = n.options.chart;
                    t(["margin", "spacing"], function(r) {
                        var u = i[r],
                            f = lt(u) ? u : [u, u, u, u];
                        t(["Top", "Right", "Bottom", "Left"], function(t, u) {
                            n[r][u] = c(i[r + t], f[u])
                        })
                    });
                    t(g, function(t, i) {
                        n[t] = c(n.margin[i], n.spacing[i])
                    });
                    n.axisOffset = [0, 0, 0, 0];
                    n.clipOffset = [0, 0, 0, 0]
                },
                drawChartBox: function() {
                    var n = this.options.chart,
                        u = this.renderer,
                        k = this.chartWidth,
                        d = this.chartHeight,
                        f = this.chartBackground,
                        s = this.plotBackground,
                        i = this.plotBorder,
                        r, c = this.plotBGImage,
                        e = n.backgroundColor,
                        g = n.plotBackgroundColor,
                        l = n.plotBackgroundImage,
                        o, a = this.plotLeft,
                        v = this.plotTop,
                        y = this.plotWidth,
                        p = this.plotHeight,
                        w = this.plotBox,
                        b = this.clipRect,
                        h = this.clipBox,
                        t = "animate";
                    f || (this.chartBackground = f = u.rect().addClass("highcharts-background").add(), t = "attr");
                    r = n.borderWidth || 0;
                    o = r + (n.shadow ? 8 : 0);
                    e = {
                        fill: e || "none"
                    };
                    (r || f["stroke-width"]) && (e.stroke = n.borderColor, e["stroke-width"] = r);
                    f.attr(e).shadow(n.shadow);
                    f[t]({
                        x: o / 2,
                        y: o / 2,
                        width: k - o - r % 2,
                        height: d - o - r % 2,
                        r: n.borderRadius
                    });
                    t = "animate";
                    s || (t = "attr", this.plotBackground = s = u.rect().addClass("highcharts-plot-background").add());
                    s[t](w);
                    s.attr({
                        fill: g || "none"
                    }).shadow(n.plotShadow);
                    l && (c ? c.animate(w) : this.plotBGImage = u.image(l, a, v, y, p).add());
                    b ? b.animate({
                        width: h.width,
                        height: h.height
                    }) : this.clipRect = u.clipRect(h);
                    t = "animate";
                    i || (t = "attr", this.plotBorder = i = u.rect().addClass("highcharts-plot-border").attr({
                        zIndex: 1
                    }).add());
                    i.attr({
                        stroke: n.plotBorderColor,
                        "stroke-width": n.plotBorderWidth || 0,
                        fill: "none"
                    });
                    i[t](i.crisp({
                        x: a,
                        y: v,
                        width: y,
                        height: p
                    }, -i.strokeWidth()));
                    this.isDirtyBox = !1
                },
                propFromSeries: function() {
                    var r = this,
                        u = r.options.chart,
                        n, f = r.options.series,
                        e, i;
                    t(["inverted", "angular", "polar"], function(t) {
                        for (n = p[u.type || u.defaultSeriesType], i = u[t] || n && n.prototype[t], e = f && f.length; !i && e--;)(n = p[f[e].type]) && n.prototype[t] && (i = !0);
                        r[t] = i
                    })
                },
                linkSeries: function() {
                    var n = this,
                        i = n.series;
                    t(i, function(n) {
                        n.linkedSeries.length = 0
                    });
                    t(i, function(t) {
                        var i = t.options.linkedTo;
                        v(i) && (i = ":previous" === i ? n.series[t.index - 1] : n.get(i)) && i.linkedParent !== t && (i.linkedSeries.push(t), t.linkedParent = i, t.visible = c(t.options.visible, i.options.visible, t.visible))
                    })
                },
                renderSeries: function() {
                    t(this.series, function(n) {
                        n.translate();
                        n.render()
                    })
                },
                renderLabels: function() {
                    var n = this,
                        i = n.options.labels;
                    i.items && t(i.items, function(t) {
                        var r = o(i.style, t.style),
                            u = y(r.left) + n.plotLeft,
                            f = y(r.top) + n.plotTop + 12;
                        delete r.left;
                        delete r.top;
                        n.renderer.text(t.html, u, f).attr({
                            zIndex: 2
                        }).css(r).add()
                    })
                },
                render: function() {
                    var n = this.axes,
                        e = this.renderer,
                        i = this.options,
                        f, r, u;
                    this.setTitle();
                    this.legend = new at(this, i.legend);
                    this.getStacks && this.getStacks();
                    this.getMargins(!0);
                    this.setChartSize();
                    i = this.plotWidth;
                    f = this.plotHeight -= 21;
                    t(n, function(n) {
                        n.setScale()
                    });
                    this.getAxisMargins();
                    r = 1.1 < i / this.plotWidth;
                    u = 1.05 < f / this.plotHeight;
                    (r || u) && (t(n, function(n) {
                        (n.horiz && r || !n.horiz && u) && n.setTickInterval(!0)
                    }), this.getMargins());
                    this.drawChartBox();
                    this.hasCartesianSeries && t(n, function(n) {
                        n.visible && n.render()
                    });
                    this.seriesGroup || (this.seriesGroup = e.g("series-group").attr({
                        zIndex: 3
                    }).add());
                    this.renderSeries();
                    this.renderLabels();
                    this.addCredits();
                    this.setResponsive && this.setResponsive();
                    this.hasRendered = !0
                },
                addCredits: function(n) {
                    var t = this;
                    n = h(!0, this.options.credits, n);
                    n.enabled && !this.credits && (this.credits = this.renderer.text(n.text + (this.mapCredits || ""), 0, 0).addClass("highcharts-credits").on("click", function() {
                        n.href && (e.location.href = n.href)
                    }).attr({
                        align: n.position.align,
                        zIndex: 8
                    }).css(n.style).add().align(n.position), this.credits.update = function(n) {
                        t.credits = t.credits.destroy();
                        t.addCredits(n)
                    })
                },
                destroy: function() {
                    var r = this,
                        o = r.axes,
                        s = r.series,
                        e = r.container,
                        f, h = e && e.parentNode;
                    for (i(r, "destroy"), u[r.index] = void 0, n.chartCount--, r.renderTo.removeAttribute("data-highcharts-chart"), tt(r), f = o.length; f--;) o[f] = o[f].destroy();
                    for (this.scroller && this.scroller.destroy && this.scroller.destroy(), f = s.length; f--;) s[f] = s[f].destroy();
                    t("title subtitle chartBackground plotBackground plotBGImage plotBorder seriesGroup clipRect credits pointer rangeSelector legend resetZoomButton tooltip renderer".split(" "), function(n) {
                        var t = r[n];
                        t && t.destroy && (r[n] = t.destroy())
                    });
                    e && (e.innerHTML = "", tt(e), h && b(e));
                    for (f in r) delete r[f]
                },
                isReadyToRender: function() {
                    var n = this;
                    return vt || e != e.top || "complete" === r.readyState ? !0 : (r.attachEvent("onreadystatechange", function() {
                        r.detachEvent("onreadystatechange", n.firstRender);
                        "complete" === r.readyState && n.firstRender()
                    }), !1)
                },
                firstRender: function() {
                    var n = this,
                        r = n.options;
                    n.isReadyToRender() && (n.getContainer(), i(n, "init"), n.resetMargins(), n.setChartSize(), n.propFromSeries(), n.getAxes(), t(r.series || [], function(t) {
                        n.initSeries(t)
                    }), n.linkSeries(), i(n, "beforeRender"), nt && (n.pointer = new nt(n, r)), n.render(), !n.renderer.imgCount && n.onload && n.onload(), n.cloneRenderTo(!0))
                },
                onload: function() {
                    t([this.callback].concat(this.callbacks), function(n) {
                        n && void 0 !== this.index && n.apply(this, [this])
                    }, this);
                    i(this, "load");
                    i(this, "render");
                    f(this.index) && !1 !== this.options.chart.reflow && this.initReflow();
                    this.onload = null
                }
            }
        }(n),
        function(n) {
            var t, f = n.each,
                r = n.extend,
                e = n.erase,
                o = n.fireEvent,
                s = n.format,
                h = n.isArray,
                u = n.isNumber,
                i = n.pick,
                c = n.removeEvent;
            t = n.Point = function() {};
            t.prototype = {
                init: function(n, t, r) {
                    return this.series = n, this.color = n.color, this.applyOptions(t, r), n.options.colorByPoint ? (t = n.options.colors || n.chart.options.colors, this.color = this.color || t[n.colorCounter], t = t.length, r = n.colorCounter, n.colorCounter++, n.colorCounter === t && (n.colorCounter = 0)) : r = n.colorIndex, this.colorIndex = i(this.colorIndex, r), n.chart.pointCount++, this
                },
                applyOptions: function(n, f) {
                    var e = this.series,
                        o = e.options.pointValKey || e.pointValKey;
                    return n = t.prototype.optionsToObject.call(this, n), r(this, n), this.options = this.options ? r(this.options, n) : n, n.group && delete this.group, o && (this.y = this[o]), this.isNull = i(this.isValid && !this.isValid(), null === this.x || !u(this.y, !0)), this.selected && (this.state = "select"), "name" in this && void 0 === f && e.xAxis && e.xAxis.hasNames && (this.x = e.xAxis.nameToX(this)), void 0 === this.x && e && (this.x = void 0 === f ? e.autoIncrement(this) : f), this
                },
                optionsToObject: function(n) {
                    var i = {},
                        t = this.series,
                        f = t.options.keys,
                        e = f || t.pointArrayMap || ["y"],
                        s = e.length,
                        r = 0,
                        o = 0;
                    if (u(n) || null === n) i[e[0]] = n;
                    else if (h(n))
                        for (!f && n.length > s && (t = typeof n[0], "string" === t ? i.name = n[0] : "number" === t && (i.x = n[0]), r++); o < s;) f && void 0 === n[r] || (i[e[o]] = n[r]), r++, o++;
                    else "object" == typeof n && (i = n, n.dataLabels && (t._hasPointLabels = !0), n.marker && (t._hasPointMarkers = !0));
                    return i
                },
                getClassName: function() {
                    return "highcharts-point" + (this.selected ? " highcharts-point-select" : "") + (this.negative ? " highcharts-negative" : "") + (this.isNull ? " highcharts-null-point" : "") + (void 0 !== this.colorIndex ? " highcharts-color-" + this.colorIndex : "") + (this.options.className ? " " + this.options.className : "") + (this.zone && this.zone.className ? " " + this.zone.className.replace("highcharts-negative", "") : "")
                },
                getZone: function() {
                    for (var t = this.series, i = t.zones, t = t.zoneAxis || "y", r = 0, n = i[r]; this[t] >= n.value;) n = i[++r];
                    return n && n.color && !this.options.color && (this.color = n.color), n
                },
                destroy: function() {
                    var n = this.series.chart,
                        t = n.hoverPoints,
                        i;
                    n.pointCount--;
                    t && (this.setState(), e(t, this), t.length || (n.hoverPoints = null));
                    this === n.hoverPoint && this.onMouseOut();
                    (this.graphic || this.dataLabel) && (c(this), this.destroyElements());
                    this.legendItem && n.legend.destroyItem(this);
                    for (i in this) this[i] = null
                },
                destroyElements: function() {
                    for (var i = ["graphic", "dataLabel", "dataLabelUpper", "connector", "shadowGroup"], n, t = 6; t--;) n = i[t], this[n] && (this[n] = this[n].destroy())
                },
                getLabelConfig: function() {
                    return {
                        x: this.category,
                        y: this.y,
                        color: this.color,
                        colorIndex: this.colorIndex,
                        key: this.name || this.category,
                        series: this.series,
                        point: this,
                        percentage: this.percentage,
                        total: this.total || this.stackTotal
                    }
                },
                tooltipFormatter: function(n) {
                    var r = this.series,
                        t = r.tooltipOptions,
                        o = i(t.valueDecimals, ""),
                        u = t.valuePrefix || "",
                        e = t.valueSuffix || "";
                    return f(r.pointArrayMap || ["y"], function(t) {
                        t = "{point." + t;
                        (u || e) && (n = n.replace(t + "}", u + t + "}" + e));
                        n = n.replace(t + "}", t + ":,." + o + "f}")
                    }), s(n, {
                        point: this,
                        series: this.series
                    })
                },
                firePointEvent: function(n, t, i) {
                    var r = this,
                        u = this.series.options;
                    (u.point.events[n] || r.options && r.options.events && r.options.events[n]) && this.importEvents();
                    "click" === n && u.allowPointSelect && (i = function(n) {
                        r.select && r.select(null, n.ctrlKey || n.metaKey || n.shiftKey)
                    });
                    o(this, n, t, i)
                },
                visible: !0
            }
        }(n),
        function(n) {
            var e = n.addEvent,
                o = n.animObject,
                p = n.arrayMax,
                w = n.arrayMin,
                s = n.correctFloat,
                u = n.Date,
                h = n.defaultOptions,
                b = n.defaultPlotOptions,
                r = n.defined,
                i = n.each,
                c = n.erase,
                k = n.extend,
                l = n.fireEvent,
                d = n.grep,
                a = n.isArray,
                f = n.isNumber,
                g = n.isString,
                v = n.merge,
                t = n.pick,
                nt = n.removeEvent,
                tt = n.splat,
                it = n.SVGElement,
                y = n.syncTimeout,
                rt = n.win;
            n.Series = n.seriesType("line", null, {
                lineWidth: 2,
                allowPointSelect: !1,
                showCheckbox: !1,
                animation: {
                    duration: 1e3
                },
                events: {},
                marker: {
                    lineWidth: 0,
                    lineColor: "#ffffff",
                    radius: 4,
                    states: {
                        hover: {
                            animation: {
                                duration: 50
                            },
                            enabled: !0,
                            radiusPlus: 2,
                            lineWidthPlus: 1
                        },
                        select: {
                            fillColor: "#cccccc",
                            lineColor: "#000000",
                            lineWidth: 2
                        }
                    }
                },
                point: {
                    events: {}
                },
                dataLabels: {
                    align: "center",
                    formatter: function() {
                        return null === this.y ? "" : n.numberFormat(this.y, -1)
                    },
                    style: {
                        fontSize: "11px",
                        fontWeight: "bold",
                        color: "contrast",
                        textOutline: "1px contrast"
                    },
                    verticalAlign: "bottom",
                    x: 0,
                    y: 0,
                    padding: 5
                },
                cropThreshold: 300,
                pointRange: 0,
                softThreshold: !0,
                states: {
                    hover: {
                        lineWidthPlus: 1,
                        marker: {},
                        halo: {
                            size: 10,
                            opacity: .25
                        }
                    },
                    select: {
                        marker: {}
                    }
                },
                stickyTracking: !0,
                turboThreshold: 1e3
            }, {
                isCartesian: !0,
                pointClass: n.Point,
                sorted: !0,
                requireSorting: !0,
                directTouch: !1,
                axisTypes: ["xAxis", "yAxis"],
                colorCounter: 0,
                parallelArrays: ["x", "y"],
                coll: "series",
                init: function(n, r) {
                    var u = this,
                        s, f, o = n.series,
                        h;
                    u.chart = n;
                    u.options = r = u.setOptions(r);
                    u.linkedSeries = [];
                    u.bindAxes();
                    k(u, {
                        name: r.name,
                        state: "",
                        visible: !1 !== r.visible,
                        selected: !0 === r.selected
                    });
                    f = r.events;
                    for (s in f) e(u, s, f[s]);
                    (f && f.click || r.point && r.point.events && r.point.events.click || r.allowPointSelect) && (n.runTrackerClick = !0);
                    u.getColor();
                    u.getSymbol();
                    i(u.parallelArrays, function(n) {
                        u[n + "Data"] = []
                    });
                    u.setData(r.data, !1);
                    u.isCartesian && (n.hasCartesianSeries = !0);
                    o.length && (h = o[o.length - 1]);
                    u._i = t(h && h._i, -1) + 1;
                    n.orderSeries(this.insert(o))
                },
                insert: function(n) {
                    var r = this.options.index,
                        i;
                    if (f(r)) {
                        for (i = n.length; i--;)
                            if (r >= t(n[i].options.index, n[i]._i)) {
                                n.splice(i + 1, 0, this);
                                break
                            } - 1 === i && n.unshift(this);
                        i += 1
                    } else n.push(this);
                    return t(i, n.length - 1)
                },
                bindAxes: function() {
                    var t = this,
                        r = t.options,
                        f = t.chart,
                        u;
                    i(t.axisTypes || [], function(e) {
                        i(f[e], function(n) {
                            u = n.options;
                            (r[e] === u.index || void 0 !== r[e] && r[e] === u.id || void 0 === r[e] && 0 === u.index) && (t.insert(n.series), t[e] = n, n.isDirty = !0)
                        });
                        t[e] || t.optionalAxis === e || n.error(18, !0)
                    })
                },
                updateParallelArrays: function(n, t) {
                    var r = n.series,
                        u = arguments,
                        e = f(t) ? function(i) {
                            var u = "y" === i && r.toYData ? r.toYData(n) : n[i];
                            r[i + "Data"][t] = u
                        } : function(n) {
                            Array.prototype[t].apply(r[n + "Data"], Array.prototype.slice.call(u, 2))
                        };
                    i(r.parallelArrays, e)
                },
                autoIncrement: function() {
                    var n = this.options,
                        r = this.xIncrement,
                        i, f = n.pointIntervalUnit,
                        r = t(r, n.pointStart, 0);
                    return this.pointInterval = i = t(this.pointInterval, n.pointInterval, 1), f && (n = new u(r), "day" === f ? n = +n[u.hcSetDate](n[u.hcGetDate]() + i) : "month" === f ? n = +n[u.hcSetMonth](n[u.hcGetMonth]() + i) : "year" === f && (n = +n[u.hcSetFullYear](n[u.hcGetFullYear]() + i)), i = n - r), this.xIncrement = r + i, r
                },
                setOptions: function(n) {
                    var i = this.chart,
                        t = i.options.plotOptions,
                        i = i.userOptions || {},
                        u = i.plotOptions || {},
                        f = t[this.type];
                    return this.userOptions = n, t = v(f, t.series, n), this.tooltipOptions = v(h.tooltip, h.plotOptions[this.type].tooltip, i.tooltip, u.series && u.series.tooltip, u[this.type] && u[this.type].tooltip, n.tooltip), null === f.marker && delete t.marker, this.zoneAxis = t.zoneAxis, n = this.zones = (t.zones || []).slice(), (t.negativeColor || t.negativeFillColor) && !t.zones && n.push({
                        value: t[this.zoneAxis + "Threshold"] || t.threshold || 0,
                        className: "highcharts-negative",
                        color: t.negativeColor,
                        fillColor: t.negativeFillColor
                    }), n.length && r(n[n.length - 1].value) && n.push({
                        color: this.color,
                        fillColor: this.fillColor
                    }), t
                },
                getCyclic: function(n, i, u) {
                    var f, e = this.chart,
                        s = this.userOptions,
                        o = n + "Index",
                        h = n + "Counter",
                        c = u ? u.length : t(e.options.chart[n + "Count"], e[n + "Count"]);
                    i || (f = t(s[o], s["_" + o]), r(f) || (e.series.length || (e[h] = 0), s["_" + o] = f = e[h] % c, e[h] += 1), u && (i = u[f]));
                    void 0 !== f && (this[o] = f);
                    this[n] = i
                },
                getColor: function() {
                    this.options.colorByPoint ? this.options.color = null : this.getCyclic("color", this.options.color || b[this.type].color, this.chart.options.colors)
                },
                getSymbol: function() {
                    this.getCyclic("symbol", this.options.marker.symbol, this.chart.options.symbols)
                },
                drawLegendSymbol: n.LegendSymbolMixin.drawLineMarker,
                setData: function(r, u, e, o) {
                    var s = this,
                        l = s.points,
                        b = l && l.length || 0,
                        c, y = s.options,
                        k = s.chart,
                        h = null,
                        p = s.xAxis,
                        d = y.turboThreshold,
                        w = this.xData,
                        v = this.yData,
                        nt = (c = s.pointArrayMap) && c.length;
                    if (r = r || [], c = r.length, u = t(u, !0), !1 !== o && c && b === c && !s.cropped && !s.hasGroupedData && s.visible) i(r, function(n, t) {
                        l[t].update && n !== y.data[t] && l[t].update(n, !1, null, !1)
                    });
                    else {
                        if (s.xIncrement = null, s.colorCounter = 0, i(this.parallelArrays, function(n) {
                                s[n + "Data"].length = 0
                            }), d && c > d) {
                            for (e = 0; null === h && e < c;) h = r[e], e++;
                            if (f(h))
                                for (e = 0; e < c; e++) w[e] = this.autoIncrement(), v[e] = r[e];
                            else if (a(h))
                                if (nt)
                                    for (e = 0; e < c; e++) h = r[e], w[e] = h[0], v[e] = h.slice(1, nt + 1);
                                else
                                    for (e = 0; e < c; e++) h = r[e], w[e] = h[0], v[e] = h[1];
                            else n.error(12)
                        } else
                            for (e = 0; e < c; e++) void 0 !== r[e] && (h = {
                                series: s
                            }, s.pointClass.prototype.applyOptions.apply(h, [r[e]]), s.updateParallelArrays(h, e));
                        for (g(v[0]) && n.error(14, !0), s.data = [], s.options.data = s.userOptions.data = r, e = b; e--;) l[e] && l[e].destroy && l[e].destroy();
                        p && (p.minRange = p.userMinRange);
                        s.isDirty = k.isDirtyBox = !0;
                        s.isDirtyData = !!l;
                        e = !1
                    }
                    "point" === y.legendType && (this.processData(), this.generatePoints());
                    u && k.redraw(e)
                },
                processData: function(t) {
                    var i = this.xData,
                        l = this.yData,
                        u = i.length,
                        e, a, s, f, r, o;
                    e = 0;
                    f = this.xAxis;
                    o = this.options;
                    r = o.cropThreshold;
                    var y = this.getExtremesFromAll || o.getExtremesFromAll,
                        v = this.isCartesian,
                        o = f && f.val2lin,
                        p = f && f.isLog,
                        h, c;
                    if (v && !this.isDirty && !f.isDirty && !this.yAxis.isDirty && !t) return !1;
                    for (f && (t = f.getExtremes(), h = t.min, c = t.max), v && this.sorted && !y && (!r || u > r || this.forceCrop) && (i[u - 1] < h || i[0] > c ? (i = [], l = []) : (i[0] < h || i[u - 1] > c) && (e = this.cropData(this.xData, this.yData, h, c), i = e.xData, l = e.yData, e = e.start, a = !0)), r = i.length || 1; --r;) u = p ? o(i[r]) - o(i[r - 1]) : i[r] - i[r - 1], 0 < u && (void 0 === s || u < s) ? s = u : 0 > u && this.requireSorting && n.error(15);
                    this.cropped = a;
                    this.cropStart = e;
                    this.processedXData = i;
                    this.processedYData = l;
                    this.closestPointRange = s
                },
                cropData: function(n, i, r, u) {
                    for (var s = n.length, e = 0, o = s, h = t(this.cropShoulder, 1), f = 0; f < s; f++)
                        if (n[f] >= r) {
                            e = Math.max(0, f - h);
                            break
                        }
                    for (r = f; r < s; r++)
                        if (n[r] > u) {
                            o = r + h;
                            break
                        }
                    return {
                        xData: n.slice(e, o),
                        yData: i.slice(e, o),
                        start: e,
                        end: o
                    }
                },
                generatePoints: function() {
                    var f = this.options.data,
                        t = this.data,
                        s, e = this.processedXData,
                        a = this.processedYData,
                        h = this.pointClass,
                        o = e.length,
                        c = this.cropStart || 0,
                        i, u = this.hasGroupedData,
                        r, l = [],
                        n;
                    for (t || u || (t = [], t.length = f.length, t = this.data = t), n = 0; n < o; n++) i = c + n, u ? (r = (new h).init(this, [e[n]].concat(tt(a[n]))), r.dataGroup = this.groupMap[n]) : (r = t[i]) || void 0 === f[i] || (t[i] = r = (new h).init(this, f[i], e[n])), r.index = i, l[n] = r;
                    if (t && (o !== (s = t.length) || u))
                        for (n = 0; n < s; n++) n !== c || u || (n += o), t[n] && (t[n].destroyElements(), t[n].plotX = void 0);
                    this.data = t;
                    this.points = l
                },
                getExtremes: function(n) {
                    var v = this.yAxis,
                        s = this.processedXData,
                        u, o = [],
                        h = 0,
                        c, l, r, e, t, i;
                    for (u = this.xAxis.getExtremes(), c = u.min, l = u.max, n = n || this.stackedYData || this.processedYData || [], u = n.length, i = 0; i < u; i++)
                        if (e = s[i], t = n[i], r = (f(t, !0) || a(t)) && (!v.isLog || t.length || 0 < t), e = this.getExtremesFromAll || this.options.getExtremesFromAll || this.cropped || (s[i + 1] || e) >= c && (s[i - 1] || e) <= l, r && e)
                            if (r = t.length)
                                for (; r--;) null !== t[r] && (o[h++] = t[r]);
                            else o[h++] = t;
                    this.dataMin = w(o);
                    this.dataMax = p(o)
                },
                translate: function() {
                    var v, c;
                    this.processedXData || this.processData();
                    this.generatePoints();
                    var e = this.options,
                        g = e.stacking,
                        y = this.xAxis,
                        b = y.categories,
                        h = this.yAxis,
                        nt = this.points,
                        it = nt.length,
                        rt = !!this.modifyValue,
                        o = e.pointPlacement,
                        ut = "between" === o || f(o),
                        p = e.threshold,
                        tt = e.startFromThreshold ? p : 0,
                        l, u, k, w, d = Number.MAX_VALUE;
                    for ("between" === o && (o = .5), f(o) && (o *= t(e.pointRange || y.pointRange)), e = 0; e < it; e++) {
                        var n = nt[e],
                            a = n.x,
                            i = n.y;
                        u = n.low;
                        v = g && h.stacks[(this.negStacks && i < (tt ? 0 : p) ? "-" : "") + this.stackKey];
                        h.isLog && null !== i && 0 >= i && (n.isNull = !0);
                        n.plotX = l = s(Math.min(Math.max(-1e5, y.translate(a, 0, 0, 0, 1, o, "flags" === this.type)), 1e5));
                        g && this.visible && !n.isNull && v && v[a] && (w = this.getStackIndicator(w, a, this.index), c = v[a], i = c.points[w.key], u = i[0], i = i[1], u === tt && w.key === v[a].base && (u = t(p, h.min)), h.isLog && 0 >= u && (u = null), n.total = n.stackTotal = c.total, n.percentage = c.total && n.y / c.total * 100, n.stackY = i, c.setOffset(this.pointXOffset || 0, this.barW || 0));
                        n.yBottom = r(u) ? h.translate(u, 0, 1, 0, 1) : null;
                        rt && (i = this.modifyValue(i, n));
                        n.plotY = u = "number" == typeof i && Infinity !== i ? Math.min(Math.max(-1e5, h.translate(i, 0, 1, 0, 1)), 1e5) : void 0;
                        n.isInside = void 0 !== u && 0 <= u && u <= h.len && 0 <= l && l <= y.len;
                        n.clientX = ut ? s(y.translate(a, 0, 0, 0, 1, o)) : l;
                        n.negative = n.y < (p || 0);
                        n.category = b && void 0 !== b[n.x] ? b[n.x] : n.x;
                        n.isNull || (void 0 !== k && (d = Math.min(d, Math.abs(l - k))), k = l);
                        n.zone = this.zones.length && n.getZone()
                    }
                    this.closestPointRangePx = d
                },
                getValidPoints: function(n, t) {
                    var i = this.chart;
                    return d(n || this.points || [], function(n) {
                        return t && !i.isInsidePlot(n.plotX, n.plotY, i.inverted) ? !1 : !n.isNull
                    })
                },
                setClip: function(n) {
                    var t = this.chart,
                        u = this.options,
                        o = t.renderer,
                        s = t.inverted,
                        f = this.clipBox,
                        e = f || t.clipBox,
                        i = this.sharedClipKey || ["_sharedClip", n && n.duration, n && n.easing, e.height, u.xAxis, u.yAxis].join(),
                        r = t[i],
                        h = t[i + "m"];
                    r || (n && (e.width = 0, t[i + "m"] = h = o.clipRect(-99, s ? -t.plotLeft : -t.plotTop, 99, s ? t.chartWidth : t.chartHeight)), t[i] = r = o.clipRect(e), r.count = {
                        length: 0
                    });
                    n && !r.count[this.index] && (r.count[this.index] = !0, r.count.length += 1);
                    !1 !== u.clip && (this.group.clip(n || f ? r : t.clipRect), this.markerGroup.clip(h), this.sharedClipKey = i);
                    n || (r.count[this.index] && (delete r.count[this.index], --r.count.length), 0 === r.count.length && i && t[i] && (f || (t[i] = t[i].destroy()), t[i + "m"] && (this.markerGroup.clip(), t[i + "m"] = t[i + "m"].destroy())))
                },
                animate: function(n) {
                    var t = this.chart,
                        r = o(this.options.animation),
                        i;
                    n ? this.setClip(r) : (i = this.sharedClipKey, (n = t[i]) && n.animate({
                        width: t.plotSizeX
                    }, r), t[i + "m"] && t[i + "m"].animate({
                        width: t.plotSizeX + 99
                    }, r), this.animate = null)
                },
                afterAnimate: function() {
                    this.setClip();
                    l(this, "afterAnimate")
                },
                drawPoints: function() {
                    var c = this.points,
                        a = this.chart,
                        u, o, n, i, s = this.options.marker,
                        e, l, r, h, v = this.markerGroup,
                        y = t(s.enabled, this.xAxis.isRadial ? !0 : null, this.closestPointRangePx > 2 * s.radius);
                    if (!1 !== s.enabled || this._hasPointMarkers)
                        for (o = 0; o < c.length; o++) n = c[o], u = n.plotY, i = n.graphic, e = n.marker || {}, l = !!n.marker, r = y && void 0 === e.enabled || e.enabled, h = n.isInside, r && f(u) && null !== n.y ? (u = t(e.symbol, this.symbol), n.hasImage = 0 === u.indexOf("url"), r = this.markerAttribs(n, n.selected && "select"), i ? i[h ? "show" : "hide"](!0).animate(r) : h && (0 < r.width || n.hasImage) && (n.graphic = i = a.renderer.symbol(u, r.x, r.y, r.width, r.height, l ? e : s).add(v)), i && i.attr(this.pointAttribs(n, n.selected && "select")), i && i.addClass(n.getClassName(), !0)) : i && (n.graphic = i.destroy())
                },
                markerAttribs: function(n, i) {
                    var r = this.options.marker,
                        f = n.marker || {},
                        u = t(f.radius, r.radius);
                    return i && (r = r.states[i], i = f.states && f.states[i], u = t(i && i.radius, r && r.radius, u + (r && r.radiusPlus || 0))), n.hasImage && (u = 0), n = {
                        x: Math.floor(n.plotX) - u,
                        y: n.plotY - u
                    }, u && (n.width = n.height = 2 * u), n
                },
                pointAttribs: function(n, i) {
                    var r = this.options.marker,
                        f = n && n.options,
                        e = f && f.marker || {},
                        u = this.color,
                        o = f && f.color,
                        s = n && n.color,
                        f = t(e.lineWidth, r.lineWidth);
                    return n = n && n.zone && n.zone.color, u = o || n || s || u, n = e.fillColor || r.fillColor || u, u = e.lineColor || r.lineColor || u, i && (r = r.states[i], i = e.states && e.states[i] || {}, f = t(i.lineWidth, r.lineWidth, f + t(i.lineWidthPlus, r.lineWidthPlus, 0)), n = i.fillColor || r.fillColor || n, u = i.lineColor || r.lineColor || u), {
                        stroke: u,
                        "stroke-width": f,
                        fill: n
                    }
                },
                destroy: function() {
                    var n = this,
                        f = n.chart,
                        s = /AppleWebKit\/533/.test(rt.navigator.userAgent),
                        r, o = n.data || [],
                        e, t, u;
                    for (l(n, "destroy"), nt(n), i(n.axisTypes || [], function(t) {
                            (u = n[t]) && u.series && (c(u.series, n), u.isDirty = u.forceRedraw = !0)
                        }), n.legendItem && n.chart.legend.destroyItem(n), r = o.length; r--;)(e = o[r]) && e.destroy && e.destroy();
                    n.points = null;
                    clearTimeout(n.animationTimeout);
                    for (t in n) n[t] instanceof it && !n[t].survive && (r = s && "group" === t ? "hide" : "destroy", n[t][r]());
                    f.hoverSeries === n && (f.hoverSeries = null);
                    c(f.series, n);
                    f.orderSeries();
                    for (t in n) delete n[t]
                },
                getGraphPath: function(n, t, u) {
                    var e = this,
                        h = e.options,
                        f = h.step,
                        l, s = [],
                        c = [],
                        o;
                    return n = n || e.points, (l = n.reversed) && n.reverse(), (f = {
                        right: 1,
                        center: 2
                    } [f] || f && 3) && l && (f = 4 - f), !h.connectNulls || t || u || (n = this.getValidPoints(n)), i(n, function(i, l) {
                        var v = i.plotX,
                            y = i.plotY,
                            a = n[l - 1];
                        (i.leftCliff || a && a.rightCliff) && !u && (o = !0);
                        i.isNull && !r(t) && 0 < l ? o = !h.connectNulls : i.isNull && !t ? o = !0 : (0 === l || o ? l = ["M", i.plotX, i.plotY] : e.getPointSpline ? l = e.getPointSpline(n, i, l) : f ? (l = 1 === f ? ["L", a.plotX, y] : 2 === f ? ["L", (a.plotX + v) / 2, a.plotY, "L", (a.plotX + v) / 2, y] : ["L", v, a.plotY], l.push("L", v, y)) : l = ["L", v, y], c.push(i.x), f && c.push(i.x), s.push.apply(s, l), o = !1)
                    }), s.xMap = c, e.graphPath = s
                },
                drawGraph: function() {
                    var n = this,
                        t = this.options,
                        r = (this.gappedPath || this.getGraphPath).call(this),
                        u = [
                            ["graph", "highcharts-graph", t.lineColor || this.color, t.dashStyle]
                        ];
                    i(this.zones, function(i, r) {
                        u.push(["zone-graph-" + r, "highcharts-graph highcharts-zone-graph-" + r + " " + (i.className || ""), i.color || n.color, i.dashStyle || t.dashStyle])
                    });
                    i(u, function(i, u) {
                        var e = i[0],
                            f = n[e];
                        f ? (f.endX = r.xMap, f.animate({
                            d: r
                        })) : r.length && (n[e] = n.chart.renderer.path(r).addClass(i[1]).attr({
                            zIndex: 1
                        }).add(n.group), f = {
                            stroke: i[2],
                            "stroke-width": t.lineWidth,
                            fill: n.fillGraph && n.color || "none"
                        }, i[3] ? f.dashstyle = i[3] : "square" !== t.linecap && (f["stroke-linecap"] = f["stroke-linejoin"] = "round"), f = n[e].attr(f).shadow(2 > u && t.shadow));
                        f && (f.startX = r.xMap, f.isArea = r.isArea)
                    })
                },
                applyZones: function() {
                    var k = this,
                        r = this.chart,
                        d = r.renderer,
                        g = this.zones,
                        f, e, o = this.clips || [],
                        n, c = this.graph,
                        l = this.area,
                        a = Math.max(r.chartWidth, r.chartHeight),
                        u = this[(this.zoneAxis || "y") + "Axis"],
                        s, p, w = r.inverted,
                        h, b, v, y, nt = !1;
                    g.length && (c || l) && u && void 0 !== u.min && (p = u.reversed, h = u.horiz, c && c.hide(), l && l.hide(), s = u.getExtremes(), i(g, function(i, g) {
                        f = p ? h ? r.plotWidth : 0 : h ? 0 : u.toPixels(s.min);
                        f = Math.min(Math.max(t(e, f), 0), a);
                        e = Math.min(Math.max(Math.round(u.toPixels(t(i.value, s.max), !0)), 0), a);
                        nt && (f = e = u.toPixels(s.max));
                        b = Math.abs(f - e);
                        v = Math.min(f, e);
                        y = Math.max(f, e);
                        u.isXAxis ? (n = {
                            x: w ? y : v,
                            y: 0,
                            width: b,
                            height: a
                        }, h || (n.x = r.plotHeight - n.x)) : (n = {
                            x: 0,
                            y: w ? y : v,
                            width: a,
                            height: b
                        }, h && (n.y = r.plotWidth - n.y));
                        w && d.isVML && (n = u.isXAxis ? {
                            x: 0,
                            y: p ? v : y,
                            height: n.width,
                            width: r.chartWidth
                        } : {
                            x: n.y - r.plotLeft - r.spacingBox.x,
                            y: 0,
                            width: n.height,
                            height: r.chartHeight
                        });
                        o[g] ? o[g].animate(n) : (o[g] = d.clipRect(n), c && k["zone-graph-" + g].clip(o[g]), l && k["zone-area-" + g].clip(o[g]));
                        nt = i.value > s.max
                    }), this.clips = o)
                },
                invertGroups: function(n) {
                    function r() {
                        i(["group", "markerGroup"], function(i) {
                            t[i] && (t[i].width = t.yAxis.len, t[i].height = t.xAxis.len, t[i].invert(n))
                        })
                    }
                    var t = this,
                        u;
                    t.xAxis && (u = e(t.chart, "resize", r), e(t, "destroy", u), r(n), t.invertGroups = r)
                },
                plotGroup: function(n, t, i, r, u) {
                    var f = this[n],
                        e = !f;
                    return e && (this[n] = f = this.chart.renderer.g(t).attr({
                        zIndex: r || .1
                    }).add(u), f.addClass("highcharts-series-" + this.index + " highcharts-" + this.type + "-series highcharts-color-" + this.colorIndex + " " + (this.options.className || ""))), f.attr({
                        visibility: i
                    })[e ? "attr" : "animate"](this.getPlotBox()), f
                },
                getPlotBox: function() {
                    var t = this.chart,
                        i = this.xAxis,
                        n = this.yAxis;
                    return t.inverted && (i = n, n = this.xAxis), {
                        translateX: i ? i.left : t.plotLeft,
                        translateY: n ? n.top : t.plotTop,
                        scaleX: 1,
                        scaleY: 1
                    }
                },
                render: function() {
                    var n = this,
                        t = n.chart,
                        i, r = n.options,
                        u = !!n.animate && t.renderer.isSVG && o(r.animation).duration,
                        f = n.visible ? "inherit" : "hidden",
                        e = r.zIndex,
                        s = n.hasRendered,
                        h = t.seriesGroup,
                        c = t.inverted;
                    i = n.plotGroup("group", "series", f, e, h);
                    n.markerGroup = n.plotGroup("markerGroup", "markers", f, e, h);
                    u && n.animate(!0);
                    i.inverted = n.isCartesian ? c : !1;
                    n.drawGraph && (n.drawGraph(), n.applyZones());
                    n.drawDataLabels && n.drawDataLabels();
                    n.visible && n.drawPoints();
                    n.drawTracker && !1 !== n.options.enableMouseTracking && n.drawTracker();
                    n.invertGroups(c);
                    !1 === r.clip || n.sharedClipKey || s || i.clip(t.clipRect);
                    u && n.animate();
                    s || (n.animationTimeout = y(function() {
                        n.afterAnimate()
                    }, u));
                    n.isDirty = !1;
                    n.hasRendered = !0
                },
                redraw: function() {
                    var n = this.chart,
                        f = this.isDirty || this.isDirtyData,
                        i = this.group,
                        r = this.xAxis,
                        u = this.yAxis;
                    i && (n.inverted && i.attr({
                        width: n.plotWidth,
                        height: n.plotHeight
                    }), i.animate({
                        translateX: t(r && r.left, n.plotLeft),
                        translateY: t(u && u.top, n.plotTop)
                    }));
                    this.translate();
                    this.render();
                    f && delete this.kdTree
                },
                kdDimensions: 1,
                kdAxisArray: ["clientX", "plotY"],
                searchPoint: function(n, t) {
                    var i = this.xAxis,
                        r = this.yAxis,
                        u = this.chart.inverted;
                    return this.searchKDTree({
                        clientX: u ? i.len - n.chartY + i.pos : n.chartX - i.pos,
                        plotY: u ? r.len - n.chartX + r.pos : n.chartY - r.pos
                    }, t)
                },
                buildKDTree: function() {
                    function t(i, r, u) {
                        var e, f;
                        if (f = i && i.length) return e = n.kdAxisArray[r % u], i.sort(function(n, t) {
                            return n[e] - t[e]
                        }), f = Math.floor(f / 2), {
                            point: i[f],
                            left: t(i.slice(0, f), r + 1, u),
                            right: t(i.slice(f + 1), r + 1, u)
                        }
                    }
                    this.buildingKdTree = !0;
                    var n = this,
                        i = n.kdDimensions;
                    delete n.kdTree;
                    y(function() {
                        n.kdTree = t(n.getValidPoints(null, !n.directTouch), i, i);
                        n.buildingKdTree = !1
                    }, n.options.kdNow ? 0 : 1)
                },
                searchKDTree: function(n, t) {
                    function e(n, t, s, h) {
                        var l = t.point,
                            a = o.kdAxisArray[s % h],
                            c, v, y = l;
                        return v = r(n[u]) && r(l[u]) ? Math.pow(n[u] - l[u], 2) : null, c = r(n[f]) && r(l[f]) ? Math.pow(n[f] - l[f], 2) : null, c = (v || 0) + (c || 0), l.dist = r(c) ? Math.sqrt(c) : Number.MAX_VALUE, l.distX = r(v) ? Math.sqrt(v) : Number.MAX_VALUE, a = n[a] - l[a], c = 0 > a ? "left" : "right", v = 0 > a ? "right" : "left", t[c] && (c = e(n, t[c], s + 1, h), y = c[i] < y[i] ? c : l), t[v] && Math.sqrt(a * a) < y[i] && (n = e(n, t[v], s + 1, h), y = n[i] < y[i] ? n : y), y
                    }
                    var o = this,
                        u = this.kdAxisArray[0],
                        f = this.kdAxisArray[1],
                        i = t ? "distX" : "dist";
                    return this.kdTree || this.buildingKdTree || this.buildKDTree(), this.kdTree ? e(n, this.kdTree, this.kdDimensions, this.kdDimensions) : void 0
                }
            })
        }(n),
        function(n) {
            function f(n, i, r, u, f) {
                var e = n.chart.inverted;
                this.axis = n;
                this.isNegative = r;
                this.options = i;
                this.x = u;
                this.total = null;
                this.points = {};
                this.stack = f;
                this.rightCliff = this.leftCliff = 0;
                this.alignOptions = {
                    align: i.align || (e ? r ? "left" : "right" : "center"),
                    verticalAlign: i.verticalAlign || (e ? "middle" : r ? "bottom" : "top"),
                    y: t(i.y, e ? 4 : r ? 14 : -6),
                    x: t(i.x, e ? r ? -6 : 6 : 0)
                };
                this.textAlign = i.textAlign || (e ? r ? "right" : "left" : "center")
            }
            var i = n.Axis,
                o = n.Chart,
                r = n.correctFloat,
                e = n.defined,
                s = n.destroyObjectProperties,
                u = n.each,
                h = n.format,
                t = n.pick;
            n = n.Series;
            f.prototype = {
                destroy: function() {
                    s(this, this.axis)
                },
                render: function(n) {
                    var t = this.options,
                        i = t.format,
                        i = i ? h(i, this) : t.formatter.call(this);
                    this.label ? this.label.attr({
                        text: i,
                        visibility: "hidden"
                    }) : this.label = this.axis.chart.renderer.text(i, null, null, t.useHTML).css(t.style).attr({
                        align: this.textAlign,
                        rotation: t.rotation,
                        visibility: "hidden"
                    }).add(n)
                },
                setOffset: function(n, t) {
                    var i = this.axis,
                        f = i.chart,
                        r = f.inverted,
                        e = i.reversed,
                        e = this.isNegative && !e || !this.isNegative && e,
                        u = i.translate(i.usePercentage ? 100 : this.total, 0, 0, 0, 1),
                        i = i.translate(0),
                        i = Math.abs(u - i),
                        o;
                    n = f.xAxis[0].translate(this.x) + n;
                    o = f.plotHeight;
                    r = {
                        x: r ? e ? u : u - i : n,
                        y: r ? o - n - t : e ? o - u - i : o - u,
                        width: r ? i : t,
                        height: r ? t : i
                    };
                    (t = this.label) && (t.align(this.alignOptions, null, r), r = t.alignAttr, t[!1 === this.options.crop || f.isInsidePlot(r.x, r.y) ? "show" : "hide"](!0))
                }
            };
            o.prototype.getStacks = function() {
                var n = this;
                u(n.yAxis, function(n) {
                    n.stacks && n.hasVisibleSeries && (n.oldStacks = n.stacks)
                });
                u(n.series, function(i) {
                    i.options.stacking && (!0 === i.visible || !1 === n.options.chart.ignoreHiddenSeries) && (i.stackKey = i.type + t(i.options.stack, ""))
                })
            };
            i.prototype.buildStacks = function() {
                var r = this.series,
                    u, f = t(this.options.reversedStacks, !0),
                    i = r.length,
                    n;
                if (!this.isXAxis) {
                    for (this.usePercentage = !1, n = i; n--;) r[f ? n : i - n - 1].setStackedPoints();
                    for (n = i; n--;) u = r[f ? n : i - n - 1], u.setStackCliffs && u.setStackCliffs();
                    if (this.usePercentage)
                        for (n = 0; n < i; n++) r[n].setPercentStacks()
                }
            };
            i.prototype.renderStackTotals = function() {
                var n = this.chart,
                    f = n.renderer,
                    i = this.stacks,
                    r, u, t = this.stackTotalGroup;
                t || (this.stackTotalGroup = t = f.g("stack-labels").attr({
                    visibility: "visible",
                    zIndex: 6
                }).add());
                t.translate(n.plotLeft, n.plotTop);
                for (r in i)
                    for (u in n = i[r], n) n[u].render(t)
            };
            i.prototype.resetStacks = function() {
                var n = this.stacks,
                    t, i;
                if (!this.isXAxis)
                    for (t in n)
                        for (i in n[t]) n[t][i].touched < this.stacksTouched ? (n[t][i].destroy(), delete n[t][i]) : (n[t][i].total = null, n[t][i].cum = null)
            };
            i.prototype.cleanStacks = function() {
                var n, t, i;
                if (!this.isXAxis)
                    for (t in this.oldStacks && (n = this.stacks = this.oldStacks), n)
                        for (i in n[t]) n[t][i].cum = n[t][i].total
            };
            n.prototype.setStackedPoints = function() {
                if (this.options.stacking && (!0 === this.visible || !1 === this.chart.options.chart.ignoreHiddenSeries)) {
                    var tt = this.processedXData,
                        b = this.processedYData,
                        k = [],
                        it = b.length,
                        c = this.options,
                        d = c.threshold,
                        y = c.startFromThreshold ? d : 0,
                        rt = c.stack,
                        c = c.stacking,
                        p = this.stackKey,
                        g = "-" + p,
                        nt = this.negStacks,
                        s = this.yAxis,
                        u = s.stacks,
                        w = s.oldStacks,
                        v, o, n, a, l, i, h;
                    for (s.stacksTouched += 1, l = 0; l < it; l++) i = tt[l], h = b[l], v = this.getStackIndicator(v, i, this.index), a = v.key, n = (o = nt && h < (y ? 0 : d)) ? g : p, u[n] || (u[n] = {}), u[n][i] || (w[n] && w[n][i] ? (u[n][i] = w[n][i], u[n][i].total = null) : u[n][i] = new f(s, s.options.stackLabels, o, i, rt)), n = u[n][i], null !== h && (n.points[a] = n.points[this.index] = [t(n.cum, y)], e(n.cum) || (n.base = a), n.touched = s.stacksTouched, 0 < v.index && !1 === this.singleStacks && (n.points[a][0] = n.points[this.index + "," + i + ",0"][0])), "percent" === c ? (o = o ? p : g, nt && u[o] && u[o][i] ? (o = u[o][i], n.total = o.total = Math.max(o.total, n.total) + Math.abs(h) || 0) : n.total = r(n.total + (Math.abs(h) || 0))) : n.total = r(n.total + (h || 0)), n.cum = t(n.cum, y) + (h || 0), null !== h && (n.points[a].push(n.cum), k[l] = n.cum);
                    "percent" === c && (s.usePercentage = !0);
                    this.stackedYData = k;
                    s.oldStacks = {}
                }
            };
            n.prototype.setPercentStacks = function() {
                var n = this,
                    i = n.stackKey,
                    f = n.yAxis.stacks,
                    e = n.processedXData,
                    t;
                u([i, "-" + i], function(i) {
                    for (var s = e.length, u, o; s--;)(u = e[s], t = n.getStackIndicator(t, u, n.index, i), u = (o = f[i] && f[i][u]) && o.points[t.key]) && (o = o.total ? 100 / o.total : 0, u[0] = r(u[0] * o), u[1] = r(u[1] * o), n.stackedYData[s] = u[1])
                })
            };
            n.prototype.getStackIndicator = function(n, t, i, r) {
                return !e(n) || n.x !== t || r && n.key !== r ? n = {
                    x: t,
                    index: 0,
                    key: r
                } : n.index++, n.key = [i, t, n.index].join(), n
            }
        }(n),
        function(n) {
            var w = n.addEvent,
                o = n.animate,
                s = n.Axis,
                h = n.createElement,
                f = n.css,
                b = n.defined,
                i = n.each,
                c = n.erase,
                r = n.extend,
                l = n.fireEvent,
                e = n.inArray,
                a = n.isNumber,
                v = n.isObject,
                u = n.merge,
                t = n.pick,
                k = n.Point,
                d = n.Series,
                y = n.seriesTypes,
                g = n.setAnimation,
                p = n.splat;
            r(n.Chart.prototype, {
                addSeries: function(n, i, r) {
                    var f, u = this;
                    return n && (i = t(i, !0), l(u, "addSeries", {
                        options: n
                    }, function() {
                        f = u.initSeries(n);
                        u.isDirtyLegend = !0;
                        u.linkSeries();
                        i && u.redraw(r)
                    })), f
                },
                addAxis: function(n, i, r, f) {
                    var e = i ? "xAxis" : "yAxis",
                        o = this.options;
                    n = u(n, {
                        index: this[e].length,
                        isX: i
                    });
                    new s(this, n);
                    o[e] = p(o[e] || {});
                    o[e].push(n);
                    t(r, !0) && this.redraw(f)
                },
                showLoading: function(n) {
                    var t = this,
                        e = t.options,
                        i = t.loadingDiv,
                        u = e.loading,
                        s = function() {
                            i && f(i, {
                                left: t.plotLeft + "px",
                                top: t.plotTop + "px",
                                width: t.plotWidth + "px",
                                height: t.plotHeight + "px"
                            })
                        };
                    i || (t.loadingDiv = i = h("div", {
                        className: "highcharts-loading highcharts-loading-hidden"
                    }, null, t.container), t.loadingSpan = h("span", {
                        className: "highcharts-loading-inner"
                    }, null, i), w(t, "redraw", s));
                    i.className = "highcharts-loading";
                    t.loadingSpan.innerHTML = n || e.lang.loading;
                    f(i, r(u.style, {
                        zIndex: 10
                    }));
                    f(t.loadingSpan, u.labelStyle);
                    t.loadingShown || (f(i, {
                        opacity: 0,
                        display: ""
                    }), o(i, {
                        opacity: u.style.opacity || .5
                    }, {
                        duration: u.showDuration || 0
                    }));
                    t.loadingShown = !0;
                    s()
                },
                hideLoading: function() {
                    var t = this.options,
                        n = this.loadingDiv;
                    n && (n.className = "highcharts-loading highcharts-loading-hidden", o(n, {
                        opacity: 0
                    }, {
                        duration: t.loading.hideDuration || 100,
                        complete: function() {
                            f(n, {
                                display: "none"
                            })
                        }
                    }));
                    this.loadingShown = !1
                },
                propsRequireDirtyBox: "backgroundColor borderColor borderWidth margin marginTop marginRight marginBottom marginLeft spacing spacingTop spacingRight spacingBottom spacingLeft borderRadius plotBackgroundColor plotBackgroundImage plotBorderColor plotBorderWidth plotShadow shadow".split(" "),
                propsRequireUpdateSeries: "chart.inverted chart.polar chart.ignoreHiddenSeries chart.type colors plotOptions".split(" "),
                update: function(n, r) {
                    var f, h = {
                            credits: "addCredits",
                            title: "setTitle",
                            subtitle: "setSubtitle"
                        },
                        o = n.chart,
                        c, s;
                    if (o) {
                        u(!0, this.options.chart, o);
                        "className" in o && this.setClassName(o.className);
                        ("inverted" in o || "polar" in o) && (this.propFromSeries(), c = !0);
                        for (f in o) o.hasOwnProperty(f) && (-1 !== e("chart." + f, this.propsRequireUpdateSeries) && (s = !0), -1 !== e(f, this.propsRequireDirtyBox) && (this.isDirtyBox = !0));
                        "style" in o && this.renderer.setStyle(o.style)
                    }
                    for (f in n) this[f] && "function" == typeof this[f].update ? this[f].update(n[f], !1) : "function" == typeof this[h[f]] && this[h[f]](n[f]), "chart" !== f && -1 !== e(f, this.propsRequireUpdateSeries) && (s = !0);
                    n.colors && (this.options.colors = n.colors);
                    n.plotOptions && u(!0, this.options.plotOptions, n.plotOptions);
                    i(["xAxis", "yAxis", "series"], function(t) {
                        n[t] && i(p(n[t]), function(n, i) {
                            (i = b(n.id) && this.get(n.id) || this[t][i]) && i.coll === t && i.update(n, !1)
                        }, this)
                    }, this);
                    c && i(this.axes, function(n) {
                        n.update({}, !1)
                    });
                    s && i(this.series, function(n) {
                        n.update({}, !1)
                    });
                    n.loading && u(!0, this.options.loading, n.loading);
                    f = o && o.width;
                    o = o && o.height;
                    a(f) && f !== this.chartWidth || a(o) && o !== this.chartHeight ? this.setSize(f, o) : t(r, !0) && this.redraw()
                },
                setSubtitle: function(n) {
                    this.setTitle(void 0, n)
                }
            });
            r(k.prototype, {
                update: function(n, i, r, u) {
                    function l() {
                        f.applyOptions(n);
                        null === f.y && o && (f.graphic = o.destroy());
                        v(n, !0) && (o && o.element && n && n.marker && n.marker.symbol && (f.graphic = o.destroy()), n && n.dataLabels && f.dataLabel && (f.dataLabel = f.dataLabel.destroy()));
                        s = f.index;
                        e.updateParallelArrays(f, s);
                        c.data[s] = v(c.data[s], !0) ? f.options : n;
                        e.isDirty = e.isDirtyData = !0;
                        !e.fixedBox && e.hasCartesianSeries && (h.isDirtyBox = !0);
                        "point" === c.legendType && (h.isDirtyLegend = !0);
                        i && h.redraw(r)
                    }
                    var f = this,
                        e = f.series,
                        o = f.graphic,
                        s, h = e.chart,
                        c = e.options;
                    i = t(i, !0);
                    !1 === u ? l() : f.firePointEvent("update", {
                        options: n
                    }, l)
                },
                remove: function(n, t) {
                    this.series.removePoint(e(this, this.series.data), n, t)
                }
            });
            r(d.prototype, {
                addPoint: function(n, i, r, u) {
                    var l = this.options,
                        s = this.data,
                        y = this.chart,
                        o = this.xAxis,
                        o = o && o.hasNames && o.names,
                        a = l.data,
                        e, v, c = this.xData,
                        f, h;
                    if (i = t(i, !0), e = {
                            series: this
                        }, this.pointClass.prototype.applyOptions.apply(e, [n]), h = e.x, f = c.length, this.requireSorting && h < c[f - 1])
                        for (v = !0; f && c[f - 1] > h;) f--;
                    this.updateParallelArrays(e, "splice", f, 0, 0);
                    this.updateParallelArrays(e, f);
                    o && e.name && (o[h] = e.name);
                    a.splice(f, 0, n);
                    v && (this.data.splice(f, 0, null), this.processData());
                    "point" === l.legendType && this.generatePoints();
                    r && (s[0] && s[0].remove ? s[0].remove(!1) : (s.shift(), this.updateParallelArrays(e, "shift"), a.shift()));
                    this.isDirtyData = this.isDirty = !0;
                    i && y.redraw(u)
                },
                removePoint: function(n, i, r) {
                    var u = this,
                        e = u.data,
                        f = e[n],
                        o = u.points,
                        s = u.chart,
                        h = function() {
                            o && o.length === e.length && o.splice(n, 1);
                            e.splice(n, 1);
                            u.options.data.splice(n, 1);
                            u.updateParallelArrays(f || {
                                series: u
                            }, "splice", n, 1);
                            f && f.destroy();
                            u.isDirty = !0;
                            u.isDirtyData = !0;
                            i && s.redraw()
                        };
                    g(r, s);
                    i = t(i, !0);
                    f ? f.firePointEvent("remove", null, h) : h()
                },
                remove: function(n, i, r) {
                    function e() {
                        f.destroy();
                        u.isDirtyLegend = u.isDirtyBox = !0;
                        u.linkSeries();
                        t(n, !0) && u.redraw(i)
                    }
                    var f = this,
                        u = f.chart;
                    !1 !== r ? l(f, "remove", null, e) : e()
                },
                update: function(n, f) {
                    var s = this,
                        o = this.chart,
                        l = this.userOptions,
                        h = this.type,
                        c = n.type || l.type || o.options.chart.type,
                        v = y[h].prototype,
                        e = ["group", "markerGroup", "dataLabelsGroup"],
                        a;
                    (c && c !== h || void 0 !== n.zIndex) && (e.length = 0);
                    i(e, function(n) {
                        e[n] = s[n];
                        delete s[n]
                    });
                    n = u(l, {
                        animation: !1,
                        index: this.index,
                        pointStart: this.xData[0]
                    }, {
                        data: this.options.data
                    }, n);
                    this.remove(!1, null, !1);
                    for (a in v) this[a] = void 0;
                    r(this, y[c || h].prototype);
                    i(e, function(n) {
                        s[n] = e[n]
                    });
                    this.init(o, n);
                    o.linkSeries();
                    t(f, !0) && o.redraw(!1)
                }
            });
            r(s.prototype, {
                update: function(n, i) {
                    var f = this.chart;
                    n = f.options[this.coll][this.options.index] = u(this.userOptions, n);
                    this.destroy(!0);
                    this.init(f, r(n, {
                        events: void 0
                    }));
                    f.isDirtyBox = !0;
                    t(i, !0) && f.redraw()
                },
                remove: function(n) {
                    for (var r = this.chart, u = this.coll, f = this.series, e = f.length; e--;) f[e] && f[e].remove(!1);
                    c(r.axes, this);
                    c(r[u], this);
                    r.options[u].splice(this.options.index, 1);
                    i(r[u], function(n, t) {
                        n.options.index = t
                    });
                    this.destroy();
                    r.isDirtyBox = !0;
                    t(n, !0) && r.redraw()
                },
                setTitle: function(n, t) {
                    this.update({
                        title: n
                    }, t)
                },
                setCategories: function(n, t) {
                    this.update({
                        categories: n
                    }, t)
                }
            })
        }(n),
        function(n) {
            var u = n.color,
                i = n.each,
                f = n.map,
                t = n.pick,
                r = n.Series,
                e = n.seriesType;
            e("area", "line", {
                softThreshold: !1,
                threshold: 0
            }, {
                singleStacks: !1,
                getStackPoints: function() {
                    var s = [],
                        e = [],
                        w = this.xAxis,
                        o = this.yAxis,
                        u = o.stacks[this.stackKey],
                        r = {},
                        h = this.points,
                        c = this.index,
                        a = o.series,
                        v = a.length,
                        y, p = t(o.options.reversedStacks, !0) ? 1 : -1,
                        n, l;
                    if (this.options.stacking) {
                        for (n = 0; n < h.length; n++) r[h[n].x] = h[n];
                        for (l in u) null !== u[l].total && e.push(l);
                        e.sort(function(n, t) {
                            return n - t
                        });
                        y = f(a, function() {
                            return this.visible
                        });
                        i(e, function(t, f) {
                            var h = 0,
                                l, a;
                            if (r[t] && !r[t].isNull) s.push(r[t]), i([-1, 1], function(i) {
                                var h = 1 === i ? "rightNull" : "leftNull",
                                    o = 0,
                                    s = u[e[f + i]];
                                if (s)
                                    for (n = c; 0 <= n && n < v;) l = s.points[n], l || (n === c ? r[t][h] = !0 : y[n] && (a = u[t].points[n]) && (o -= a[1] - a[0])), n += p;
                                r[t][1 === i ? "rightCliff" : "leftCliff"] = o
                            });
                            else {
                                for (n = c; 0 <= n && n < v;) {
                                    if (l = u[t].points[n]) {
                                        h = l[1];
                                        break
                                    }
                                    n += p
                                }
                                h = o.toPixels(h, !0);
                                s.push({
                                    isNull: !0,
                                    plotX: w.toPixels(t, !0),
                                    plotY: h,
                                    yBottom: h
                                })
                            }
                        })
                    }
                    return s
                },
                getGraphPath: function(n) {
                    var e = r.prototype.getGraphPath,
                        f = this.options,
                        o = f.stacking,
                        s = this.yAxis,
                        i, u, h = [],
                        c = [],
                        p = this.index,
                        l, w = s.stacks[this.stackKey],
                        b = f.threshold,
                        a = s.getThreshold(f.threshold),
                        v, f = f.connectNulls || "percent" === o,
                        y = function(t, i, r) {
                            var u = n[t],
                                e, f, v;
                            t = o && w[u.x].points[p];
                            e = u[r + "Null"] || 0;
                            r = u[r + "Cliff"] || 0;
                            u = !0;
                            r || e ? (f = (e ? t[0] : t[1]) + r, v = t[0] + r, u = !!e) : !o && n[i] && n[i].isNull && (f = v = b);
                            void 0 !== f && (c.push({
                                plotX: l,
                                plotY: null === f ? a : s.getThreshold(f),
                                isNull: u
                            }), h.push({
                                plotX: l,
                                plotY: null === v ? a : s.getThreshold(v),
                                doCurve: !1
                            }))
                        };
                    for (n = n || this.points, o && (n = this.getStackPoints()), i = 0; i < n.length; i++)(u = n[i].isNull, l = t(n[i].rectPlotX, n[i].plotX), v = t(n[i].yBottom, a), !u || f) && (f || y(i, i - 1, "left"), u && !o && f || (c.push(n[i]), h.push({
                        x: i,
                        plotX: l,
                        plotY: v
                    })), f || y(i, i + 1, "right"));
                    return i = e.call(this, c, !0, !0), h.reversed = !0, u = e.call(this, h, !0, !0), u.length && (u[0] = "L"), u = i.concat(u), e = e.call(this, c, !1, f), u.xMap = i.xMap, this.areaPath = u, e
                },
                drawGraph: function() {
                    this.areaPath = [];
                    r.prototype.drawGraph.apply(this);
                    var n = this,
                        f = this.areaPath,
                        e = this.options,
                        o = [
                            ["area", "highcharts-area", this.color, e.fillColor]
                        ];
                    i(this.zones, function(t, i) {
                        o.push(["zone-area-" + i, "highcharts-area highcharts-zone-area-" + i + " " + t.className, t.color || n.color, t.fillColor || e.fillColor])
                    });
                    i(o, function(i) {
                        var o = i[0],
                            r = n[o];
                        r ? (r.endX = f.xMap, r.animate({
                            d: f
                        })) : (r = n[o] = n.chart.renderer.path(f).addClass(i[1]).attr({
                            fill: t(i[3], u(i[2]).setOpacity(t(e.fillOpacity, .75)).get()),
                            zIndex: 0
                        }).add(n.group), r.isArea = !0);
                        r.startX = f.xMap;
                        r.shiftUnit = e.step ? 2 : 1
                    })
                },
                drawLegendSymbol: n.LegendSymbolMixin.drawRectangle
            })
        }(n),
        function(n) {
            var t = n.pick;
            n = n.seriesType;
            n("spline", "line", {}, {
                getPointSpline: function(n, i, r) {
                    var h = i.plotX,
                        u = i.plotY,
                        o = n[r - 1],
                        c, e, s, f, l;
                    return r = n[r + 1], o && !o.isNull && !1 !== o.doCurve && r && !r.isNull && !1 !== r.doCurve && (n = o.plotY, s = r.plotX, r = r.plotY, l = 0, c = (1.5 * h + o.plotX) / 2.5, e = (1.5 * u + n) / 2.5, s = (1.5 * h + s) / 2.5, f = (1.5 * u + r) / 2.5, s !== c && (l = (f - e) * (s - h) / (s - c) + u - f), e += l, f += l, e > n && e > u ? (e = Math.max(n, u), f = 2 * u - e) : e < n && e < u && (e = Math.min(n, u), f = 2 * u - e), f > r && f > u ? (f = Math.max(r, u), e = 2 * u - f) : f < r && f < u && (f = Math.min(r, u), e = 2 * u - f), i.rightContX = s, i.rightContY = f), i = ["C", t(o.rightContX, o.plotX), t(o.rightContY, o.plotY), t(c, h), t(e, u), h, u], o.rightContX = o.rightContY = null, i
                }
            })
        }(n),
        function(n) {
            var t = n.seriesTypes.area.prototype,
                i = n.seriesType;
            i("areaspline", "spline", n.defaultPlotOptions.area, {
                getStackPoints: t.getStackPoints,
                getGraphPath: t.getGraphPath,
                setStackCliffs: t.setStackCliffs,
                drawGraph: t.drawGraph,
                drawLegendSymbol: n.LegendSymbolMixin.drawRectangle
            })
        }(n),
        function(n) {
            var u = n.animObject,
                f = n.color,
                t = n.each,
                e = n.extend,
                o = n.isNumber,
                s = n.merge,
                i = n.pick,
                r = n.Series,
                h = n.seriesType,
                c = n.svg;
            h("column", "line", {
                borderRadius: 0,
                groupPadding: .2,
                marker: null,
                pointPadding: .1,
                minPointLength: 0,
                cropThreshold: 50,
                pointRange: null,
                states: {
                    hover: {
                        halo: !1,
                        brightness: .1,
                        shadow: !1
                    },
                    select: {
                        color: "#cccccc",
                        borderColor: "#000000",
                        shadow: !1
                    }
                },
                dataLabels: {
                    align: null,
                    verticalAlign: null,
                    y: null
                },
                softThreshold: !1,
                startFromThreshold: !0,
                stickyTracking: !1,
                tooltip: {
                    distance: 6
                },
                threshold: 0,
                borderColor: "#ffffff"
            }, {
                cropShoulder: 0,
                directTouch: !0,
                trackerGroups: ["group", "dataLabelsGroup"],
                negStacks: !0,
                init: function() {
                    r.prototype.init.apply(this, arguments);
                    var n = this,
                        i = n.chart;
                    i.hasRendered && t(i.series, function(t) {
                        t.type === n.type && (t.isDirty = !0)
                    })
                },
                getColumnMetrics: function() {
                    var n = this,
                        r = n.options,
                        u = n.xAxis,
                        c = n.yAxis,
                        l = u.reversed,
                        f, o = {},
                        e = 0;
                    !1 === r.grouping ? e = 1 : t(n.chart.series, function(t) {
                        var r = t.options,
                            u = t.yAxis,
                            i;
                        t.type === n.type && t.visible && c.len === u.len && c.pos === u.pos && (r.stacking ? (f = t.stackKey, void 0 === o[f] && (o[f] = e++), i = o[f]) : !1 !== r.grouping && (i = e++), t.columnIndex = i)
                    });
                    var s = Math.min(Math.abs(u.transA) * (u.ordinalSlope || r.pointRange || u.closestPointRange || u.tickInterval || 1), u.len),
                        a = s * r.groupPadding,
                        h = (s - 2 * a) / (e || 1),
                        r = Math.min(r.maxPointWidth || u.len, i(r.pointWidth, h * (1 - 2 * r.pointPadding)));
                    return n.columnMetrics = {
                        width: r,
                        offset: (h - r) / 2 + (a + ((n.columnIndex || 0) + (l ? 1 : 0)) * h - s / 2) * (l ? -1 : 1)
                    }, n.columnMetrics
                },
                crispCol: function(n, t, i, r) {
                    var e = this.chart,
                        u = this.borderWidth,
                        f = -(u % 2 ? .5 : 0),
                        u = u % 2 ? .5 : 1;
                    return e.inverted && e.renderer.isVML && (u += 1), i = Math.round(n + i) + f, n = Math.round(n) + f, r = Math.round(t + r) + u, f = .5 >= Math.abs(t) && .5 < r, t = Math.round(t) + u, r -= t, f && r && (--t, r += 1), {
                        x: n,
                        y: t,
                        width: i - n,
                        height: r
                    }
                },
                translate: function() {
                    var n = this,
                        e = n.chart,
                        o = n.options,
                        c = n.dense = 2 > n.closestPointRange * n.xAxis.transA,
                        c = n.borderWidth = i(o.borderWidth, c ? 0 : 1),
                        u = n.yAxis,
                        s = n.translatedThreshold = u.getThreshold(o.threshold),
                        f = i(o.minPointLength, 5),
                        l = n.getColumnMetrics(),
                        a = l.width,
                        h = n.barW = Math.max(a, 1 + 2 * c),
                        v = n.pointXOffset = l.offset;
                    e.inverted && (s -= .5);
                    o.pointPadding && (h = Math.ceil(h));
                    r.prototype.translate.apply(n);
                    t(n.points, function(t) {
                        var c = i(t.yBottom, s),
                            r = 999 + Math.abs(c),
                            r = Math.min(Math.max(-r, t.plotY), u.len + r),
                            l = t.plotX + v,
                            p = h,
                            y = Math.min(r, c),
                            w, o = Math.max(r, c) - y;
                        Math.abs(o) < f && f && (o = f, w = !u.reversed && !t.negative || u.reversed && t.negative, y = Math.abs(y - s) > f ? c - f : s - (w ? f : 0));
                        t.barX = l;
                        t.pointWidth = a;
                        t.tooltipPos = e.inverted ? [u.len + u.pos - e.plotLeft - r, n.xAxis.len - l - p / 2, o] : [l + p / 2, r + u.pos - e.plotTop, o];
                        t.shapeType = "rect";
                        t.shapeArgs = n.crispCol.apply(n, t.isNull ? [t.plotX, u.len / 2, 0, 0] : [l, y, p, o])
                    })
                },
                getSymbol: n.noop,
                drawLegendSymbol: n.LegendSymbolMixin.drawRectangle,
                drawGraph: function() {
                    this.group[this.dense ? "addClass" : "removeClass"]("highcharts-dense-data")
                },
                pointAttribs: function(n, t) {
                    var u = this.options,
                        i, e = this.pointAttrToOptions || {};
                    i = e.stroke || "borderColor";
                    var o = e["stroke-width"] || "borderWidth",
                        r = n && n.color || this.color,
                        s = n[i] || u[i] || this.color || r,
                        h = n[o] || u[o] || this[o] || 0,
                        e = u.dashStyle;
                    return n && this.zones.length && (r = (r = n.getZone()) && r.color || n.options.color || this.color), t && (n = u.states[t], t = n.brightness, r = n.color || void 0 !== t && f(r).brighten(n.brightness).get() || r, s = n[i] || s, h = n[o] || h, e = n.dashStyle || e), i = {
                        fill: r,
                        stroke: s,
                        "stroke-width": h
                    }, u.borderRadius && (i.r = u.borderRadius), e && (i.dashstyle = e), i
                },
                drawPoints: function() {
                    var n = this,
                        u = this.chart,
                        i = n.options,
                        f = u.renderer,
                        e = i.animationLimit || 250,
                        r;
                    t(n.points, function(t) {
                        var h = t.graphic;
                        o(t.plotY) && null !== t.y ? (r = t.shapeArgs, h ? h[u.pointCount < e ? "animate" : "attr"](s(r)) : t.graphic = h = f[t.shapeType](r).attr({
                            "class": t.getClassName()
                        }).add(t.group || n.group), h.attr(n.pointAttribs(t, t.selected && "select")).shadow(i.shadow, null, i.stacking && !i.borderRadius)) : h && (t.graphic = h.destroy())
                    })
                },
                animate: function(n) {
                    var t = this,
                        i = this.yAxis,
                        o = t.options,
                        f = this.chart.inverted,
                        r = {};
                    c && (n ? (r.scaleY = .001, n = Math.min(i.pos + i.len, Math.max(i.pos, i.toPixels(o.threshold))), f ? r.translateX = n - i.len : r.translateY = n, t.group.attr(r)) : (r[f ? "translateX" : "translateY"] = i.pos, t.group.animate(r, e(u(t.options.animation), {
                        step: function(n, i) {
                            t.group.attr({
                                scaleY: Math.max(.001, i.pos)
                            })
                        }
                    })), t.animate = null))
                },
                remove: function() {
                    var n = this,
                        i = n.chart;
                    i.hasRendered && t(i.series, function(t) {
                        t.type === n.type && (t.isDirty = !0)
                    });
                    r.prototype.remove.apply(n, arguments)
                }
            })
        }(n),
        function(n) {
            n = n.seriesType;
            n("bar", "column", null, {
                inverted: !0
            })
        }(n),
        function(n) {
            var t = n.Series;
            n = n.seriesType;
            n("scatter", "line", {
                lineWidth: 0,
                marker: {
                    enabled: !0
                },
                tooltip: {
                    headerFormat: '<span style="color:{point.color}">●<\/span> <span style="font-size: 0.85em"> {series.name}<\/span><br/>',
                    pointFormat: "x: <b>{point.x}<\/b><br/>y: <b>{point.y}<\/b><br/>"
                }
            }, {
                sorted: !1,
                requireSorting: !1,
                noSharedTooltip: !0,
                trackerGroups: ["group", "markerGroup", "dataLabelsGroup"],
                takeOrdinalPosition: !1,
                kdDimensions: 2,
                drawGraph: function() {
                    this.options.lineWidth && t.prototype.drawGraph.call(this)
                }
            })
        }(n),
        function(n) {
            var t = n.pick,
                i = n.relativeLength;
            n.CenteredSeriesMixin = {
                getCenter: function() {
                    for (var u = this.options, f = this.chart, e = 2 * (u.slicedOffset || 0), s = f.plotWidth - 2 * e, f = f.plotHeight - 2 * e, n = u.center, n = [t(n[0], "50%"), t(n[1], "50%"), u.size || "100%", u.innerSize || 0], h = Math.min(s, f), o, r = 0; 4 > r; ++r) o = n[r], u = 2 > r || 2 === r && /%$/.test(o), n[r] = i(o, [s, f, h, n[2]][r]) + (u ? e : 0);
                    return n[3] > n[2] && (n[3] = n[2]), n
                }
            }
        }(n),
        function(n) {
            var r = n.addEvent,
                e = n.defined,
                i = n.each,
                o = n.extend,
                u = n.inArray,
                f = n.noop,
                t = n.pick,
                s = n.Point,
                h = n.Series,
                c = n.seriesType,
                l = n.setAnimation;
            c("pie", "line", {
                center: [null, null],
                clip: !1,
                colorByPoint: !0,
                dataLabels: {
                    distance: 30,
                    enabled: !0,
                    formatter: function() {
                        if (null !== this.y) return this.point.name
                    },
                    x: 0
                },
                ignoreHiddenPoint: !0,
                legendType: "point",
                marker: null,
                size: null,
                showInLegend: !1,
                slicedOffset: 10,
                stickyTracking: !1,
                tooltip: {
                    followPointer: !0
                },
                borderColor: "#ffffff",
                borderWidth: 1,
                states: {
                    hover: {
                        brightness: .1,
                        shadow: !1
                    }
                }
            }, {
                isCartesian: !1,
                requireSorting: !1,
                directTouch: !0,
                noSharedTooltip: !0,
                trackerGroups: ["group", "dataLabelsGroup"],
                axisTypes: [],
                pointAttribs: n.seriesTypes.column.prototype.pointAttribs,
                animate: function(n) {
                    var t = this,
                        u = t.points,
                        r = t.startAngleRad;
                    n || (i(u, function(n) {
                        var i = n.graphic,
                            u = n.shapeArgs;
                        i && (i.attr({
                            r: n.startR || t.center[3] / 2,
                            start: r,
                            end: r
                        }), i.animate({
                            r: u.r,
                            start: u.start,
                            end: u.end
                        }, t.options.animation))
                    }), t.animate = null)
                },
                updateTotals: function() {
                    for (var i = 0, r = this.points, u = r.length, n, f = this.options.ignoreHiddenPoint, t = 0; t < u; t++) n = r[t], 0 > n.y && (n.y = null), i += f && !n.visible ? 0 : n.y;
                    for (this.total = i, t = 0; t < u; t++) n = r[t], n.percentage = 0 < i && (n.visible || !f) ? n.y / i * 100 : 0, n.total = i
                },
                generatePoints: function() {
                    h.prototype.generatePoints.call(this);
                    this.updateTotals()
                },
                translate: function(n) {
                    this.generatePoints();
                    var l = 0,
                        e = this.options,
                        a = e.slicedOffset,
                        s = a + (e.borderWidth || 0),
                        u, f, i, h = e.startAngle || 0,
                        v = this.startAngleRad = Math.PI / 180 * (h - 90),
                        h = (this.endAngleRad = Math.PI / 180 * (t(e.endAngle, h + 360) - 90)) - v,
                        y = this.points,
                        o = e.dataLabels.distance,
                        e = e.ignoreHiddenPoint,
                        c, p = y.length,
                        r;
                    for (n || (this.center = n = this.getCenter()), this.getX = function(t, r) {
                            return i = Math.asin(Math.min((t - n[1]) / (n[2] / 2 + o), 1)), n[0] + (r ? -1 : 1) * Math.cos(i) * (n[2] / 2 + o)
                        }, c = 0; c < p; c++) r = y[c], u = v + l * h, (!e || r.visible) && (l += r.percentage / 100), f = v + l * h, r.shapeType = "arc", r.shapeArgs = {
                        x: n[0],
                        y: n[1],
                        r: n[2] / 2,
                        innerR: n[3] / 2,
                        start: Math.round(1e3 * u) / 1e3,
                        end: Math.round(1e3 * f) / 1e3
                    }, i = (f + u) / 2, i > 1.5 * Math.PI ? i -= 2 * Math.PI : i < -Math.PI / 2 && (i += 2 * Math.PI), r.slicedTranslation = {
                        translateX: Math.round(Math.cos(i) * a),
                        translateY: Math.round(Math.sin(i) * a)
                    }, u = Math.cos(i) * n[2] / 2, f = Math.sin(i) * n[2] / 2, r.tooltipPos = [n[0] + .7 * u, n[1] + .7 * f], r.half = i < -Math.PI / 2 || i > Math.PI / 2 ? 1 : 0, r.angle = i, s = Math.min(s, o / 5), r.labelPos = [n[0] + u + Math.cos(i) * o, n[1] + f + Math.sin(i) * o, n[0] + u + Math.cos(i) * s, n[1] + f + Math.sin(i) * s, n[0] + u, n[1] + f, 0 > o ? "center" : r.half ? "right" : "left", i]
                },
                drawGraph: null,
                drawPoints: function() {
                    var n = this,
                        u = n.chart.renderer,
                        r, t, f, e, s = n.options.shadow;
                    s && !n.shadowGroup && (n.shadowGroup = u.g("shadow").add(n.group));
                    i(n.points, function(i) {
                        if (null !== i.y) {
                            t = i.graphic;
                            e = i.shapeArgs;
                            r = i.sliced ? i.slicedTranslation : {};
                            var h = i.shadowGroup;
                            s && !h && (h = i.shadowGroup = u.g("shadow").add(n.shadowGroup));
                            h && h.attr(r);
                            f = n.pointAttribs(i, i.selected && "select");
                            t ? t.setRadialReference(n.center).attr(f).animate(o(e, r)) : (i.graphic = t = u[i.shapeType](e).addClass(i.getClassName()).setRadialReference(n.center).attr(r).add(n.group), i.visible || t.attr({
                                visibility: "hidden"
                            }), t.attr(f).attr({
                                "stroke-linejoin": "round"
                            }).shadow(s, h))
                        }
                    })
                },
                searchPoint: f,
                sortByAngle: function(n, t) {
                    n.sort(function(n, i) {
                        return void 0 !== n.angle && (i.angle - n.angle) * t
                    })
                },
                drawLegendSymbol: n.LegendSymbolMixin.drawRectangle,
                getCenter: n.CenteredSeriesMixin.getCenter,
                getSymbol: f
            }, {
                init: function() {
                    s.prototype.init.apply(this, arguments);
                    var n = this,
                        i;
                    return n.name = t(n.name, "Slice"), i = function(t) {
                        n.slice("select" === t.type)
                    }, r(n, "select", i), r(n, "unselect", i), n
                },
                setVisible: function(n, r) {
                    var f = this,
                        e = f.series,
                        o = e.chart,
                        s = e.options.ignoreHiddenPoint;
                    r = t(r, s);
                    n !== f.visible && (f.visible = f.options.visible = n = void 0 === n ? !f.visible : n, e.options.data[u(f, e.data)] = f.options, i(["graphic", "dataLabel", "connector", "shadowGroup"], function(t) {
                        f[t] && f[t][n ? "show" : "hide"](!0)
                    }), f.legendItem && o.legend.colorizeItem(f, n), n || "hover" !== f.state || f.setState(""), s && (e.isDirty = !0), r && o.redraw())
                },
                slice: function(n, i, r) {
                    var f = this.series;
                    l(r, f.chart);
                    t(i, !0);
                    this.sliced = this.options.sliced = n = e(n) ? n : !this.sliced;
                    f.options.data[u(this, f.data)] = this.options;
                    n = n ? this.slicedTranslation : {
                        translateX: 0,
                        translateY: 0
                    };
                    this.graphic.animate(n);
                    this.shadowGroup && this.shadowGroup.animate(n)
                },
                haloPath: function(n) {
                    var t = this.shapeArgs;
                    return this.sliced || !this.visible ? [] : this.series.chart.renderer.symbols.arc(t.x, t.y, t.r + n, t.r + n, {
                        innerR: this.shapeArgs.r,
                        start: t.start,
                        end: t.end
                    })
                }
            })
        }(n),
        function(n) {
            var c = n.addEvent,
                l = n.arrayMax,
                e = n.defined,
                r = n.each,
                o = n.extend,
                a = n.format,
                s = n.map,
                h = n.merge,
                v = n.noop,
                t = n.pick,
                y = n.relativeLength,
                u = n.Series,
                i = n.seriesTypes,
                f = n.stableSort;
            n.distribute = function(n, t) {
                function h(n, t) {
                    return n.target - t.target
                }
                for (var u = !0, o = n, c = [], e = 0, i = n.length; i--;) e += n[i].size;
                if (e > t) {
                    for (f(n, function(n, t) {
                            return (t.rank || 0) - (n.rank || 0)
                        }), e = i = 0; e <= t;) e += n[i].size, i++;
                    c = n.splice(i - 1, n.length)
                }
                for (f(n, h), n = s(n, function(n) {
                        return {
                            size: n.size,
                            targets: [n.target]
                        }
                    }); u;) {
                    for (i = n.length; i--;) u = n[i], e = (Math.min.apply(0, u.targets) + Math.max.apply(0, u.targets)) / 2, u.pos = Math.min(Math.max(0, e - u.size / 2), t - u.size);
                    for (i = n.length, u = !1; i--;) 0 < i && n[i - 1].pos + n[i - 1].size > n[i].pos && (n[i - 1].size += n[i].size, n[i - 1].targets = n[i - 1].targets.concat(n[i].targets), n[i - 1].pos + n[i - 1].size > t && (n[i - 1].pos = t - n[i - 1].size), n.splice(i, 1), u = !0)
                }
                i = 0;
                r(n, function(n) {
                    var t = 0;
                    r(n.targets, function() {
                        o[i].pos = n.pos + t;
                        t += o[i].size;
                        i++
                    })
                });
                o.push.apply(o, c);
                f(o, h)
            };
            u.prototype.drawDataLabels = function() {
                var i = this,
                    u = i.options,
                    n = u.dataLabels,
                    w = i.points,
                    s, l, v = i.hasRendered || 0,
                    f, o, y = t(n.defer, !0),
                    p = i.chart.renderer;
                (n.enabled || i._hasPointLabels) && (i.dlProcessOptions && i.dlProcessOptions(n), o = i.plotGroup("dataLabelsGroup", "data-labels", y && !v ? "hidden" : "visible", n.zIndex || 6), y && (o.attr({
                    opacity: +v
                }), v || c(i, "afterAnimate", function() {
                    i.visible && o.show(!0);
                    o[u.animation ? "animate" : "attr"]({
                        opacity: 1
                    }, {
                        duration: 200
                    })
                })), l = n, r(w, function(r) {
                    var w, c = r.dataLabel,
                        v, b, k, d = r.connector,
                        g = !c,
                        y;
                    if (s = r.dlOptions || r.options && r.options.dataLabels, w = t(s && s.enabled, l.enabled) && null !== r.y)
                        for (b in n = h(l, s), v = r.getLabelConfig(), f = n.format ? a(n.format, v) : n.formatter.call(v, n), y = n.style, k = n.rotation, y.color = t(n.color, y.color, i.color, "#000000"), "contrast" === y.color && (y.color = n.inside || 0 > n.distance || u.stacking ? p.getContrast(r.color || i.color) : "#000000"), u.cursor && (y.cursor = u.cursor), v = {
                                fill: n.backgroundColor,
                                stroke: n.borderColor,
                                "stroke-width": n.borderWidth,
                                r: n.borderRadius || 0,
                                rotation: k,
                                padding: n.padding,
                                zIndex: 1
                            }, v) void 0 === v[b] && delete v[b];
                    !c || w && e(f) ? w && e(f) && (c ? v.text = f : (c = r.dataLabel = p[k ? "text" : "label"](f, 0, -9999, n.shape, null, null, n.useHTML, null, "data-label"), c.addClass("highcharts-data-label-color-" + r.colorIndex + " " + (n.className || "") + (n.useHTML ? "highcharts-tracker" : ""))), c.attr(v), c.css(y).shadow(n.shadow), c.added || c.add(o), i.alignDataLabel(r, c, n, null, g)) : (r.dataLabel = c.destroy(), d && (r.connector = d.destroy()))
                }))
            };
            u.prototype.alignDataLabel = function(n, i, r, u, f) {
                var c = this.chart,
                    e = c.inverted,
                    s = t(n.plotX, -9999),
                    y = t(n.plotY, -9999),
                    h = i.getBBox(),
                    l, a = r.rotation,
                    v = r.align,
                    p = this.visible && (n.series.forceDL || c.isInsidePlot(s, Math.round(y), e) || u && c.isInsidePlot(s, e ? u.x + 1 : u.y + u.height - 1, e)),
                    w = "justify" === t(r.overflow, "justify");
                p && (l = r.style.fontSize, l = c.renderer.fontMetrics(l, i).b, u = o({
                    x: e ? c.plotWidth - y : s,
                    y: Math.round(e ? c.plotHeight - s : y),
                    width: 0,
                    height: 0
                }, u), o(r, {
                    width: h.width,
                    height: h.height
                }), a ? (w = !1, e = c.renderer.rotCorr(l, a), e = {
                    x: u.x + r.x + u.width / 2 + e.x,
                    y: u.y + r.y + {
                        top: 0,
                        middle: .5,
                        bottom: 1
                    } [r.verticalAlign] * u.height
                }, i[f ? "attr" : "animate"](e).attr({
                    align: v
                }), s = (a + 720) % 360, s = 180 < s && 360 > s, "left" === v ? e.y -= s ? h.height : 0 : "center" === v ? (e.x -= h.width / 2, e.y -= h.height / 2) : "right" === v && (e.x -= h.width, e.y -= s ? 0 : h.height)) : (i.align(r, null, u), e = i.alignAttr), w ? this.justifyDataLabel(i, r, e, h, u, f) : t(r.crop, !0) && (p = c.isInsidePlot(e.x, e.y) && c.isInsidePlot(e.x + h.width, e.y + h.height)), r.shape && !a && i.attr({
                    anchorX: n.plotX,
                    anchorY: n.plotY
                }));
                p || (i.attr({
                    y: -9999
                }), i.placed = !1)
            };
            u.prototype.justifyDataLabel = function(n, t, i, r, u, f) {
                var s = this.chart,
                    c = t.align,
                    l = t.verticalAlign,
                    e, o, h = n.box ? 0 : n.padding || 0;
                e = i.x + h;
                0 > e && ("right" === c ? t.align = "left" : t.x = -e, o = !0);
                e = i.x + r.width - h;
                e > s.plotWidth && ("left" === c ? t.align = "right" : t.x = s.plotWidth - e, o = !0);
                e = i.y + h;
                0 > e && ("bottom" === l ? t.verticalAlign = "top" : t.y = -e, o = !0);
                e = i.y + r.height - h;
                e > s.plotHeight && ("top" === l ? t.verticalAlign = "bottom" : t.y = s.plotHeight - e, o = !0);
                o && (n.placed = !f, n.align(t, null, u))
            };
            i.pie && (i.pie.prototype.drawDataLabels = function() {
                var i = this,
                    st = i.data,
                    g, nt = i.chart,
                    o = i.options.dataLabels,
                    y = t(o.connectorPadding, 10),
                    rt = t(o.connectorWidth, 1),
                    ut = nt.plotWidth,
                    ft = nt.plotHeight,
                    h, w = o.distance,
                    tt = i.center,
                    it = tt[2] / 2,
                    et = tt[1],
                    ht = 0 < w,
                    c, b, p, k, ot = [
                        [],
                        []
                    ],
                    a, f, d, v, e = [0, 0, 0, 0];
                i.visible && (o.enabled || i._hasPointLabels) && (u.prototype.drawDataLabels.apply(i), r(st, function(n) {
                    n.dataLabel && n.visible && (ot[n.half].push(n), n.dataLabel._pos = null)
                }), r(ot, function(t, r) {
                    var h, rt, st = t.length,
                        u, ot, l;
                    if (st)
                        for (i.sortByAngle(t, r - .5), 0 < w && (h = Math.max(0, et - it - w), rt = Math.min(et + it + w, nt.plotHeight), u = s(t, function(n) {
                                if (n.dataLabel) return l = n.dataLabel.getBBox().height || 21, {
                                    target: n.labelPos[1] - h + l / 2,
                                    size: l,
                                    rank: n.y
                                }
                            }), n.distribute(u, rt + l - h)), v = 0; v < st; v++) g = t[v], p = g.labelPos, c = g.dataLabel, d = !1 === g.visible ? "hidden" : "inherit", ot = p[1], u ? void 0 === u[v].pos ? d = "hidden" : (k = u[v].size, f = h + u[v].pos) : f = ot, a = o.justify ? tt[0] + (r ? -1 : 1) * (it + w) : i.getX(f < h + 2 || f > rt - 2 ? ot : f, r), c._attr = {
                            visibility: d,
                            align: p[6]
                        }, c._pos = {
                            x: a + o.x + ({
                                left: y,
                                right: -y
                            } [p[6]] || 0),
                            y: f + o.y - 10
                        }, p.x = a, p.y = f, null === i.options.size && (b = c.width, a - b < y ? e[3] = Math.max(Math.round(b - a + y), e[3]) : a + b > ut - y && (e[1] = Math.max(Math.round(a + b - ut + y), e[1])), 0 > f - k / 2 ? e[0] = Math.max(Math.round(-f + k / 2), e[0]) : f + k / 2 > ft && (e[2] = Math.max(Math.round(f + k / 2 - ft), e[2])))
                }), 0 === l(e) || this.verifyDataLabelOverflow(e)) && (this.placeDataLabels(), ht && rt && r(this.points, function(n) {
                    var t;
                    h = n.connector;
                    (c = n.dataLabel) && c._pos && n.visible ? (d = c._attr.visibility, (t = !h) && (n.connector = h = nt.renderer.path().addClass("highcharts-data-label-connector highcharts-color-" + n.colorIndex).add(i.dataLabelsGroup), h.attr({
                        "stroke-width": rt,
                        stroke: o.connectorColor || n.color || "#666666"
                    })), h[t ? "attr" : "animate"]({
                        d: i.connectorPath(n.labelPos)
                    }), h.attr("visibility", d)) : h && (n.connector = h.destroy())
                }))
            }, i.pie.prototype.connectorPath = function(n) {
                var i = n.x,
                    r = n.y;
                return t(this.options.dataLabels.softConnector, !0) ? ["M", i + ("left" === n[6] ? 5 : -5), r, "C", i, r, 2 * n[2] - n[4], 2 * n[3] - n[5], n[2], n[3], "L", n[4], n[5]] : ["M", i + ("left" === n[6] ? 5 : -5), r, "L", n[2], n[3], "L", n[4], n[5]]
            }, i.pie.prototype.placeDataLabels = function() {
                r(this.points, function(n) {
                    var t = n.dataLabel;
                    t && n.visible && ((n = t._pos) ? (t.attr(t._attr), t[t.moved ? "animate" : "attr"](n), t.moved = !0) : t && t.attr({
                        y: -9999
                    }))
                })
            }, i.pie.prototype.alignDataLabel = v, i.pie.prototype.verifyDataLabelOverflow = function(n) {
                var t = this.center,
                    u = this.options,
                    f = u.center,
                    r = u.minSize || 80,
                    i, e;
                return null !== f[0] ? i = Math.max(t[2] - Math.max(n[1], n[3]), r) : (i = Math.max(t[2] - n[1] - n[3], r), t[0] += (n[3] - n[1]) / 2), null !== f[1] ? i = Math.max(Math.min(i, t[2] - Math.max(n[0], n[2])), r) : (i = Math.max(Math.min(i, t[2] - n[0] - n[2]), r), t[1] += (n[0] - n[2]) / 2), i < t[2] ? (t[2] = i, t[3] = Math.min(y(u.innerSize || 0, i), i), this.translate(t), this.drawDataLabels && this.drawDataLabels()) : e = !0, e
            });
            i.column && (i.column.prototype.alignDataLabel = function(n, i, r, f, e) {
                var s = this.chart.inverted,
                    c = n.series,
                    o = n.dlBox || n.shapeArgs,
                    l = t(n.below, n.plotY > t(this.translatedThreshold, c.yAxis.len)),
                    a = t(r.inside, !!this.options.stacking);
                o && (f = h(o), 0 > f.y && (f.height += f.y, f.y = 0), o = f.y + f.height - c.yAxis.len, 0 < o && (f.height -= o), s && (f = {
                    x: c.yAxis.len - f.y - f.height,
                    y: c.xAxis.len - f.x - f.width,
                    width: f.height,
                    height: f.width
                }), a || (s ? (f.x += l ? 0 : f.width, f.width = 0) : (f.y += l ? f.height : 0, f.height = 0)));
                r.align = t(r.align, !s || a ? "center" : l ? "right" : "left");
                r.verticalAlign = t(r.verticalAlign, s || a ? "middle" : l ? "top" : "bottom");
                u.prototype.alignDataLabel.call(this, n, i, r, f, e)
            })
        }(n),
        function(n) {
            var i = n.Chart,
                t = n.each,
                r = n.pick,
                u = n.addEvent;
            i.prototype.callbacks.push(function(n) {
                function i() {
                    var i = [];
                    t(n.series, function(n) {
                        var u = n.options.dataLabels,
                            f = n.dataLabelCollections || ["dataLabel"];
                        (u.enabled || n._hasPointLabels) && !u.allowOverlap && n.visible && t(f, function(u) {
                            t(n.points, function(n) {
                                n[u] && (n[u].labelrank = r(n.labelrank, n.shapeArgs && n.shapeArgs.height), i.push(n[u]))
                            })
                        })
                    });
                    n.hideOverlappingLabels(i)
                }
                i();
                u(n, "redraw", i)
            });
            i.prototype.hideOverlappingLabels = function(n) {
                for (var s = n.length, u, i, r, o, h, c, l, e, a = function(n, t, i, r, u, f, e, o) {
                        return !(u > n + i || u + e < n || f > t + r || f + o < t)
                    }, f = 0; f < s; f++)(u = n[f]) && (u.oldOpacity = u.opacity, u.newOpacity = 1);
                for (n.sort(function(n, t) {
                        return (t.labelrank || 0) - (n.labelrank || 0)
                    }), f = 0; f < s; f++)
                    for (i = n[f], u = f + 1; u < s; ++u)(r = n[u], i && r && i.placed && r.placed && 0 !== i.newOpacity && 0 !== r.newOpacity && (o = i.alignAttr, h = r.alignAttr, c = i.parentGroup, l = r.parentGroup, e = 2 * (i.box ? 0 : i.padding), o = a(o.x + c.translateX, o.y + c.translateY, i.width - e, i.height - e, h.x + l.translateX, h.y + l.translateY, r.width - e, r.height - e))) && ((i.labelrank < r.labelrank ? i : r).newOpacity = 0);
                t(n, function(n) {
                    var i, t;
                    n && (t = n.newOpacity, n.oldOpacity !== t && n.placed && (t ? n.show(!0) : i = function() {
                        n.hide()
                    }, n.alignAttr.opacity = t, n[n.isOld ? "animate" : "attr"](n.alignAttr, null, i)), n.isOld = !0)
                })
            }
        }(n),
        function(n) {
            var o = n.addEvent,
                l = n.Chart,
                a = n.createElement,
                s = n.css,
                h = n.defaultOptions,
                v = n.defaultPlotOptions,
                t = n.each,
                u = n.extend,
                i = n.fireEvent,
                c = n.hasTouch,
                e = n.inArray,
                y = n.isObject,
                p = n.Legend,
                w = n.merge,
                f = n.pick,
                b = n.Point,
                k = n.Series,
                r = n.seriesTypes,
                d = n.svg;
            n = n.TrackerMixin = {
                drawTrackerPoint: function() {
                    var n = this,
                        i = n.chart,
                        u = i.pointer,
                        r = function(n) {
                            for (var r = n.target, t; r && !t;) t = r.point, r = r.parentNode;
                            if (void 0 !== t && t !== i.hoverPoint) t.onMouseOver(n)
                        };
                    t(n.points, function(n) {
                        n.graphic && (n.graphic.element.point = n);
                        n.dataLabel && (n.dataLabel.div ? n.dataLabel.div.point = n : n.dataLabel.element.point = n)
                    });
                    n._hasTracking || (t(n.trackerGroups, function(t) {
                        if (n[t]) {
                            n[t].addClass("highcharts-tracker").on("mouseover", r).on("mouseout", function(n) {
                                u.onTrackerMouseOut(n)
                            });
                            if (c) n[t].on("touchstart", r);
                            n.options.cursor && n[t].css(s).css({
                                cursor: n.options.cursor
                            })
                        }
                    }), n._hasTracking = !0)
                },
                drawTrackerGraph: function() {
                    var n = this,
                        e = n.options,
                        u = e.trackByArea,
                        r = [].concat(u ? n.areaPath : n.graphPath),
                        o = r.length,
                        f = n.chart,
                        v = f.pointer,
                        y = f.renderer,
                        s = f.options.tooltip.snap,
                        h = n.tracker,
                        i, l = function() {
                            f.hoverSeries !== n && n.onMouseOver()
                        },
                        a = "rgba(192,192,192," + (d ? .0001 : .002) + ")";
                    if (o && !u)
                        for (i = o + 1; i--;) "M" === r[i] && r.splice(i + 1, 0, r[i + 1] - s, r[i + 2], "L"), (i && "M" === r[i] || i === o) && r.splice(i, 0, "L", r[i - 2] + s, r[i - 1]);
                    h ? h.attr({
                        d: r
                    }) : n.graph && (n.tracker = y.path(r).attr({
                        "stroke-linejoin": "round",
                        visibility: n.visible ? "visible" : "hidden",
                        stroke: a,
                        fill: u ? a : "none",
                        "stroke-width": n.graph.strokeWidth() + (u ? 0 : 2 * s),
                        zIndex: 2
                    }).add(n.group), t([n.tracker, n.markerGroup], function(n) {
                        n.addClass("highcharts-tracker").on("mouseover", l).on("mouseout", function(n) {
                            v.onTrackerMouseOut(n)
                        });
                        if (e.cursor && n.css({
                                cursor: e.cursor
                            }), c) n.on("touchstart", l)
                    }))
                }
            };
            r.column && (r.column.prototype.drawTracker = n.drawTrackerPoint);
            r.pie && (r.pie.prototype.drawTracker = n.drawTrackerPoint);
            r.scatter && (r.scatter.prototype.drawTracker = n.drawTrackerPoint);
            u(p.prototype, {
                setItemEvents: function(n, t, r) {
                    var u = this,
                        f = u.chart,
                        e = "highcharts-legend-" + (n.series ? "point" : "series") + "-active";
                    (r ? t : n.legendGroup).on("mouseover", function() {
                        n.setState("hover");
                        f.seriesGroup.addClass(e);
                        t.css(u.options.itemHoverStyle)
                    }).on("mouseout", function() {
                        t.css(n.visible ? u.itemStyle : u.itemHiddenStyle);
                        f.seriesGroup.removeClass(e);
                        n.setState()
                    }).on("click", function(t) {
                        var r = function() {
                            n.setVisible && n.setVisible()
                        };
                        t = {
                            browserEvent: t
                        };
                        n.firePointEvent ? n.firePointEvent("legendItemClick", t, r) : i(n, "legendItemClick", t, r)
                    })
                },
                createCheckboxForItem: function(n) {
                    n.checkbox = a("input", {
                        type: "checkbox",
                        checked: n.selected,
                        defaultChecked: n.selected
                    }, this.options.itemCheckboxStyle, this.chart.container);
                    o(n.checkbox, "click", function(t) {
                        i(n.series || n, "checkboxClick", {
                            checked: t.target.checked,
                            item: n
                        }, function() {
                            n.select()
                        })
                    })
                }
            });
            h.legend.itemStyle.cursor = "pointer";
            u(l.prototype, {
                showResetZoom: function() {
                    var t = this,
                        i = h.lang,
                        n = t.options.chart.resetZoomButton,
                        r = n.theme,
                        u = r.states,
                        f = "chart" === n.relativeTo ? null : "plotBox";
                    this.resetZoomButton = t.renderer.button(i.resetZoom, null, null, function() {
                        t.zoomOut()
                    }, r, u && u.hover).attr({
                        align: n.position.align,
                        title: i.resetZoomTitle
                    }).addClass("highcharts-reset-zoom").add().align(n.position, !1, f)
                },
                zoomOut: function() {
                    var n = this;
                    i(n, "selection", {
                        resetSelection: !0
                    }, function() {
                        n.zoom()
                    })
                },
                zoom: function(n) {
                    var r, e = this.pointer,
                        u = !1,
                        i;
                    !n || n.resetSelection ? t(this.axes, function(n) {
                        r = n.zoom()
                    }) : t(n.xAxis.concat(n.yAxis), function(n) {
                        var t = n.axis;
                        e[t.isXAxis ? "zoomX" : "zoomY"] && (r = t.zoom(n.min, n.max), t.displayBtn && (u = !0))
                    });
                    i = this.resetZoomButton;
                    u && !i ? this.showResetZoom() : !u && y(i) && (this.resetZoomButton = i.destroy());
                    r && this.redraw(f(this.options.chart.animation, n && n.animation, 100 > this.pointCount))
                },
                pan: function(n, i) {
                    var r = this,
                        u = r.hoverPoints,
                        f;
                    u && t(u, function(n) {
                        n.setState()
                    });
                    t("xy" === i ? [1, 0] : [1], function(t) {
                        t = r[t ? "xAxis" : "yAxis"][0];
                        var o = t.horiz,
                            h = n[o ? "chartX" : "chartY"],
                            o = o ? "mouseDownX" : "mouseDownY",
                            s = r[o],
                            i = (t.pointRange || 0) / 2,
                            u = t.getExtremes(),
                            e = t.toValue(s - h, !0) + i,
                            i = t.toValue(s + t.len - h, !0) - i,
                            c = i < e,
                            s = c ? i : e,
                            e = c ? e : i,
                            i = Math.min(u.dataMin, u.min) - s,
                            u = e - Math.max(u.dataMax, u.max);
                        t.series.length && 0 > i && 0 > u && (t.setExtremes(s, e, !1, !1, {
                            trigger: "pan"
                        }), f = !0);
                        r[o] = h
                    });
                    f && r.redraw(!1);
                    s(r.container, {
                        cursor: "move"
                    })
                }
            });
            u(b.prototype, {
                select: function(n, i) {
                    var r = this,
                        u = r.series,
                        o = u.chart;
                    n = f(n, !r.selected);
                    r.firePointEvent(n ? "select" : "unselect", {
                        accumulate: i
                    }, function() {
                        r.selected = r.options.selected = n;
                        u.options.data[e(r, u.data)] = r.options;
                        r.setState(n && "select");
                        i || t(o.getSelectedPoints(), function(n) {
                            n.selected && n !== r && (n.selected = n.options.selected = !1, u.options.data[e(n, u.data)] = n.options, n.setState(""), n.firePointEvent("unselect"))
                        })
                    })
                },
                onMouseOver: function(n, t) {
                    var i = this.series,
                        r = i.chart,
                        u = r.tooltip,
                        f = r.hoverPoint;
                    this.series && (t || (f && f !== this && f.onMouseOut(), r.hoverSeries !== i && i.onMouseOver(), r.hoverPoint = this), !u || u.shared && !i.noSharedTooltip ? u || this.setState("hover") : (this.setState("hover"), u.refresh(this, n)), this.firePointEvent("mouseOver"))
                },
                onMouseOut: function() {
                    var n = this.series.chart,
                        t = n.hoverPoints;
                    this.firePointEvent("mouseOut");
                    t && -1 !== e(this, t) || (this.setState(), n.hoverPoint = null)
                },
                importEvents: function() {
                    if (!this.hasImportedEvents) {
                        var n = w(this.series.options.point, this.options).events,
                            t;
                        this.events = n;
                        for (t in n) o(this, t, n[t]);
                        this.hasImportedEvents = !0
                    }
                },
                setState: function(n, t) {
                    var h = Math.floor(this.plotX),
                        w = this.plotY,
                        i = this.series,
                        y = i.options.states[n] || {},
                        e = v[i.type].marker && i.options.marker,
                        b = e && !1 === e.enabled,
                        l = e && e.states && e.states[n] || {},
                        k = !1 === l.enabled,
                        r = i.stateMarkerGraphic,
                        a = this.marker || {},
                        c = i.chart,
                        o = i.halo,
                        s, p = e && i.markerAttribs;
                    n = n || "";
                    n === this.state && !t || this.selected && "select" !== n || !1 === y.enabled || n && (k || b && !1 === l.enabled) || n && a.states && a.states[n] && !1 === a.states[n].enabled || (p && (s = i.markerAttribs(this, n)), this.graphic ? (this.state && this.graphic.removeClass("highcharts-point-" + this.state), n && this.graphic.addClass("highcharts-point-" + n), this.graphic.attr(i.pointAttribs(this, n)), s && this.graphic.animate(s, f(c.options.chart.animation, l.animation, e.animation)), r && r.hide()) : (n && l && (e = a.symbol || i.symbol, r && r.currentSymbol !== e && (r = r.destroy()), r ? r[t ? "animate" : "attr"]({
                        x: s.x,
                        y: s.y
                    }) : e && (i.stateMarkerGraphic = r = c.renderer.symbol(e, s.x, s.y, s.width, s.height).add(i.markerGroup), r.currentSymbol = e), r && r.attr(i.pointAttribs(this, n))), r && (r[n && c.isInsidePlot(h, w, c.inverted) ? "show" : "hide"](), r.element.point = this)), (h = y.halo) && h.size ? (o || (i.halo = o = c.renderer.path().add(p ? i.markerGroup : i.group)), o[t ? "animate" : "attr"]({
                        d: this.haloPath(h.size)
                    }), o.attr({
                        "class": "highcharts-halo highcharts-color-" + f(this.colorIndex, i.colorIndex)
                    }), o.point = this, o.attr(u({
                        fill: this.color || i.color,
                        "fill-opacity": h.opacity,
                        zIndex: -1
                    }, h.attributes))) : o && o.point && o.point.haloPath && o.animate({
                        d: o.point.haloPath(0)
                    }), this.state = n)
                },
                haloPath: function(n) {
                    return this.series.chart.renderer.symbols.circle(Math.floor(this.plotX) - n, this.plotY - n, 2 * n, 2 * n)
                }
            });
            u(k.prototype, {
                onMouseOver: function() {
                    var t = this.chart,
                        n = t.hoverSeries;
                    n && n !== this && n.onMouseOut();
                    this.options.events.mouseOver && i(this, "mouseOver");
                    this.setState("hover");
                    t.hoverSeries = this
                },
                onMouseOut: function() {
                    var r = this.options,
                        n = this.chart,
                        t = n.tooltip,
                        u = n.hoverPoint;
                    n.hoverSeries = null;
                    u && u.onMouseOut();
                    this && r.events.mouseOut && i(this, "mouseOut");
                    !t || r.stickyTracking || t.shared && !this.noSharedTooltip || t.hide();
                    this.setState()
                },
                setState: function(n) {
                    var i = this,
                        u = i.options,
                        f = i.graph,
                        r = u.states,
                        e = u.lineWidth,
                        u = 0;
                    if (n = n || "", i.state !== n && (t([i.group, i.markerGroup], function(t) {
                            t && (i.state && t.removeClass("highcharts-series-" + i.state), n && t.addClass("highcharts-series-" + n))
                        }), i.state = n, !r[n] || !1 !== r[n].enabled) && (n && (e = r[n].lineWidth || e + (r[n].lineWidthPlus || 0)), f && !f.dashstyle))
                        for (r = {
                                "stroke-width": e
                            }, f.attr(r); i["zone-graph-" + u];) i["zone-graph-" + u].attr(r), u += 1
                },
                setVisible: function(n, r) {
                    var u = this,
                        f = u.chart,
                        o = u.legendItem,
                        e, s = f.options.chart.ignoreHiddenSeries,
                        h = u.visible;
                    e = (u.visible = n = u.options.visible = u.userOptions.visible = void 0 === n ? !h : n) ? "show" : "hide";
                    t(["group", "dataLabelsGroup", "markerGroup", "tracker", "tt"], function(n) {
                        u[n] && u[n][e]()
                    });
                    (f.hoverSeries === u || (f.hoverPoint && f.hoverPoint.series) === u) && u.onMouseOut();
                    o && f.legend.colorizeItem(u, n);
                    u.isDirty = !0;
                    u.options.stacking && t(f.series, function(n) {
                        n.options.stacking && n.visible && (n.isDirty = !0)
                    });
                    t(u.linkedSeries, function(t) {
                        t.setVisible(n, !1)
                    });
                    s && (f.isDirtyBox = !0);
                    !1 !== r && f.redraw();
                    i(u, e)
                },
                show: function() {
                    this.setVisible(!0)
                },
                hide: function() {
                    this.setVisible(!1)
                },
                select: function(n) {
                    this.selected = n = void 0 === n ? !this.selected : n;
                    this.checkbox && (this.checkbox.checked = n);
                    i(this, n ? "select" : "unselect")
                },
                drawTracker: n.drawTrackerGraph
            })
        }(n),
        function(n) {
            var i = n.Chart,
                r = n.each,
                u = n.inArray,
                f = n.isObject,
                t = n.pick,
                e = n.splat;
            i.prototype.setResponsive = function(n) {
                var t = this.options.responsive;
                t && t.rules && r(t.rules, function(t) {
                    this.matchResponsiveRule(t, n)
                }, this)
            };
            i.prototype.matchResponsiveRule = function(i, r) {
                var u = this.respRules,
                    f = i.condition,
                    e;
                e = f.callback || function() {
                    return this.chartWidth <= t(f.maxWidth, Number.MAX_VALUE) && this.chartHeight <= t(f.maxHeight, Number.MAX_VALUE) && this.chartWidth >= t(f.minWidth, 0) && this.chartHeight >= t(f.minHeight, 0)
                };
                void 0 === i._id && (i._id = n.uniqueKey());
                e = e.call(this);
                !u[i._id] && e ? i.chartOptions && (u[i._id] = this.currentOptions(i.chartOptions), this.update(i.chartOptions, r)) : u[i._id] && !e && (this.update(u[i._id], r), delete u[i._id])
            };
            i.prototype.currentOptions = function(n) {
                function t(n, i, r, o) {
                    var s, h;
                    for (s in n)
                        if (!o && -1 < u(s, ["series", "xAxis", "yAxis"]))
                            for (n[s] = e(n[s]), r[s] = [], h = 0; h < n[s].length; h++) r[s][h] = {}, t(n[s][h], i[s][h], r[s][h], o + 1);
                        else f(n[s]) ? (r[s] = {}, t(n[s], i[s] || {}, r[s], o + 1)) : r[s] = i[s] || null
                }
                var i = {};
                return t(n, this.options, i, 0), i
            }
        }(n),
        function(n) {
            var f = n.addEvent,
                t = n.Axis,
                e = n.Chart,
                o = n.css,
                r = n.dateFormat,
                s = n.defined,
                i = n.each,
                h = n.extend,
                c = n.noop,
                u = n.Series,
                l = n.timeUnits;
            n = n.wrap;
            n(u.prototype, "init", function(n) {
                var t;
                n.apply(this, Array.prototype.slice.call(arguments, 1));
                (t = this.xAxis) && t.options.ordinal && f(this, "updatedData", function() {
                    delete t.ordinalIndex
                })
            });
            n(t.prototype, "getTimeTicks", function(n, t, i, u, f, e, o, h) {
                var a = 0,
                    c, p, b = {},
                    y, k, d, v = [],
                    g = -Number.MAX_VALUE,
                    nt = this.options.tickPixelInterval,
                    w;
                if (!this.options.ordinal && !this.options.breaks || !e || 3 > e.length || void 0 === i) return n.call(this, t, i, u, f);
                for (k = e.length, c = 0; c < k; c++) {
                    if (d = c && e[c - 1] > u, e[c] < i && (a = c), c === k - 1 || e[c + 1] - e[c] > 5 * o || d) {
                        if (e[c] > g) {
                            for (p = n.call(this, t, e[a], e[c], f); p.length && p[0] <= g;) p.shift();
                            p.length && (g = p[p.length - 1]);
                            v = v.concat(p)
                        }
                        a = c + 1
                    }
                    if (d) break
                }
                if (n = p.info, h && n.unitRange <= l.hour) {
                    for (c = v.length - 1, a = 1; a < c; a++) r("%d", v[a]) !== r("%d", v[a - 1]) && (b[v[a]] = "day", y = !0);
                    y && (b[v[0]] = "day");
                    n.higherRanks = b
                }
                if (v.info = n, h && s(nt)) {
                    for (h = n = v.length, c = [], y = []; h--;) a = this.translate(v[h]), w && (y[h] = w - a), c[h] = w = a;
                    for (y.sort(), y = y[Math.floor(y.length / 2)], y < .6 * nt && (y = null), h = v[n - 1] > u ? n - 1 : n, w = void 0; h--;) a = c[h], u = Math.abs(w - a), w && u < .8 * nt && (null === y || u < .8 * y) ? (b[v[h]] && !b[v[h + 1]] ? (u = h + 1, w = a) : u = h, v.splice(u, 1)) : w = a
                }
                return v
            });
            h(t.prototype, {
                beforeSetTickPositions: function() {
                    var r, n = [],
                        f = !1,
                        t, o = this.getExtremes(),
                        s = o.min,
                        e = o.max,
                        u, h = this.isXAxis && !!this.options.breaks,
                        o = this.options.ordinal,
                        c = this.chart.options.chart.ignoreHiddenSeries;
                    if (o || h) {
                        if (i(this.series, function(t, i) {
                                if (!(c && !1 === t.visible || !1 === t.takeOrdinalPosition && !h) && (n = n.concat(t.processedXData), r = n.length, n.sort(function(n, t) {
                                        return n - t
                                    }), r))
                                    for (i = r - 1; i--;) n[i] === n[i + 1] && n.splice(i, 1)
                            }), r = n.length, 2 < r) {
                            for (t = n[1] - n[0], u = r - 1; u-- && !f;) n[u + 1] - n[u] !== t && (f = !0);
                            !this.options.keepOrdinalPadding && (n[0] - s > t || e - n[n.length - 1] > t) && (f = !0)
                        }
                        f ? (this.ordinalPositions = n, t = this.ordinal2lin(Math.max(s, n[0]), !0), u = Math.max(this.ordinal2lin(Math.min(e, n[n.length - 1]), !0), 1), this.ordinalSlope = e = (e - s) / (u - t), this.ordinalOffset = s - t * e) : this.ordinalPositions = this.ordinalSlope = this.ordinalOffset = void 0
                    }
                    this.isOrdinal = o && f;
                    this.groupIntervalFactor = null
                },
                val2lin: function(n, t) {
                    var r = this.ordinalPositions,
                        f, i, u;
                    if (r) {
                        for (f = r.length, i = f; i--;)
                            if (r[i] === n) {
                                u = i;
                                break
                            }
                        for (i = f - 1; i--;)
                            if (n > r[i] || 0 === i) {
                                n = (n - r[i]) / (r[i + 1] - r[i]);
                                u = i + n;
                                break
                            }
                        t = t ? u : this.ordinalSlope * (u || 0) + this.ordinalOffset
                    } else t = n;
                    return t
                },
                lin2val: function(n, t) {
                    var r = this.ordinalPositions;
                    if (r) {
                        var f = this.ordinalSlope,
                            e = this.ordinalOffset,
                            i = r.length - 1,
                            u;
                        if (t) 0 > n ? n = r[0] : n > i ? n = r[i] : (i = Math.floor(n), u = n - i);
                        else
                            for (; i--;)
                                if (t = f * i + e, n >= t) {
                                    f = f * (i + 1) + e;
                                    u = (n - t) / (f - t);
                                    break
                                } return void 0 !== u && void 0 !== r[i] ? r[i] + (u ? u * (r[i + 1] - r[i]) : 0) : n
                    }
                    return n
                },
                getExtendedPositions: function() {
                    var o = this.chart,
                        n = this.series[0].currentDataGrouping,
                        r = this.ordinalIndex,
                        e = n ? n.count + n.unitName : "raw",
                        s = this.getExtremes(),
                        u, f;
                    return r || (r = this.ordinalIndex = {}), r[e] || (u = {
                        series: [],
                        chart: o,
                        getExtremes: function() {
                            return {
                                min: s.dataMin,
                                max: s.dataMax
                            }
                        },
                        options: {
                            ordinal: !0
                        },
                        val2lin: t.prototype.val2lin
                    }, i(this.series, function(t) {
                        f = {
                            xAxis: u,
                            xData: t.xData,
                            chart: o,
                            destroyGroupedData: c
                        };
                        f.options = {
                            dataGrouping: n ? {
                                enabled: !0,
                                forced: !0,
                                approximation: "open",
                                units: [
                                    [n.unitName, [n.count]]
                                ]
                            } : {
                                enabled: !1
                            }
                        };
                        t.processData.apply(f);
                        u.series.push(f)
                    }), this.beforeSetTickPositions.apply(u), r[e] = u.ordinalPositions), r[e]
                },
                getGroupIntervalFactor: function(n, t, i) {
                    var r, f, u;
                    if (i = i.processedXData, f = i.length, u = [], r = this.groupIntervalFactor, !r) {
                        for (r = 0; r < f - 1; r++) u[r] = i[r + 1] - i[r];
                        u.sort(function(n, t) {
                            return n - t
                        });
                        u = u[Math.floor(f / 2)];
                        n = Math.max(n, i[0]);
                        t = Math.min(t, i[f - 1]);
                        this.groupIntervalFactor = r = f * u / (t - n)
                    }
                    return r
                },
                postProcessTickInterval: function(n) {
                    var t = this.ordinalSlope;
                    return t ? this.options.breaks ? this.closestPointRange : n / (t / this.closestPointRange) : n
                }
            });
            t.prototype.ordinal2lin = t.prototype.val2lin;
            n(e.prototype, "pan", function(n, t) {
                var r = this.xAxis[0],
                    p = t.chartX,
                    c = !1;
                if (r.options.ordinal && r.series.length) {
                    var u = this.mouseDownX,
                        h = r.getExtremes(),
                        l = h.dataMax,
                        a = h.min,
                        v = h.max,
                        e = this.hoverPoints,
                        y = r.closestPointRange,
                        u = (u - p) / (r.translationSlope * (r.ordinalSlope || y)),
                        f = {
                            ordinalPositions: r.getExtendedPositions()
                        },
                        y = r.lin2val,
                        w = r.val2lin,
                        s;
                    f.ordinalPositions ? 1 < Math.abs(u) && (e && i(e, function(n) {
                        n.setState()
                    }), 0 > u ? (e = f, s = r.ordinalPositions ? r : f) : (e = r.ordinalPositions ? r : f, s = f), f = s.ordinalPositions, l > f[f.length - 1] && f.push(l), this.fixedRange = v - a, u = r.toFixedRange(null, null, y.apply(e, [w.apply(e, [a, !0]) + u, !0]), y.apply(s, [w.apply(s, [v, !0]) + u, !0])), u.min >= Math.min(h.dataMin, a) && u.max <= Math.max(l, v) && r.setExtremes(u.min, u.max, !0, !1, {
                        trigger: "pan"
                    }), this.mouseDownX = p, o(this.container, {
                        cursor: "move"
                    })) : c = !0
                } else c = !0;
                c && n.apply(this, Array.prototype.slice.call(arguments, 1))
            });
            u.prototype.gappedPath = function() {
                var i = this.options.gapSize,
                    n = this.points.slice(),
                    t = n.length - 1;
                if (i && 0 < t)
                    for (; t--;) n[t + 1].x - n[t].x > this.closestPointRange * i && n.splice(t + 1, 0, {
                        isNull: !0
                    });
                return this.getGraphPath(n)
            }
        }(n),
        function(n) {
            function o() {
                return Array.prototype.slice.call(arguments, 1)
            }

            function f(n) {
                n.apply(this);
                this.drawBreaks(this.xAxis, ["x"]);
                this.drawBreaks(this.yAxis, r(this.pointArrayMap, ["y"]))
            }
            var r = n.pick,
                t = n.wrap,
                u = n.each,
                s = n.extend,
                h = n.isArray,
                e = n.fireEvent,
                i = n.Axis,
                c = n.Series;
            s(i.prototype, {
                isInBreak: function(n, t) {
                    var i = n.repeat || Infinity,
                        r = n.from,
                        u = n.to - n.from;
                    return t = t >= r ? (t - r) % i : i - (r - t) % i, n.inclusive ? t <= u : t < u && 0 !== t
                },
                isInAnyBreak: function(n, t) {
                    var i = this.options.breaks,
                        u = i && i.length,
                        f, e, o;
                    if (u) {
                        for (; u--;) this.isInBreak(i[u], n) && (f = !0, e || (e = r(i[u].showPoints, this.isXAxis ? !1 : !0)));
                        o = f && t ? f && !e : f
                    }
                    return o
                }
            });
            t(i.prototype, "setTickPositions", function(n) {
                if (n.apply(this, Array.prototype.slice.call(arguments, 1)), this.options.breaks) {
                    for (var i = this.tickPositions, u = this.tickPositions.info, r = [], t = 0; t < i.length; t++) this.isInAnyBreak(i[t]) || r.push(i[t]);
                    this.tickPositions = r;
                    this.tickPositions.info = u
                }
            });
            t(i.prototype, "init", function(n, t, r) {
                var u = this;
                r.breaks && r.breaks.length && (r.ordinal = !1);
                n.call(this, t, r);
                n = this.options.breaks;
                u.isBroken = h(n) && !!n.length;
                u.isBroken && (u.val2lin = function(n) {
                    for (var i = n, t, r = 0; r < u.breakArray.length; r++)
                        if (t = u.breakArray[r], t.to <= n) i -= t.len;
                        else if (t.from >= n) break;
                    else if (u.isInBreak(t, n)) {
                        i -= n - t.from;
                        break
                    }
                    return i
                }, u.lin2val = function(n) {
                    for (var t, i = 0; i < u.breakArray.length && !(t = u.breakArray[i], t.from >= n); i++) t.to < n ? n += t.len : u.isInBreak(t, n) && (n += t.len);
                    return n
                }, u.setExtremes = function(n, t, r, u, f) {
                    for (; this.isInAnyBreak(n);) n -= this.closestPointRange;
                    for (; this.isInAnyBreak(t);) t -= this.closestPointRange;
                    i.prototype.setExtremes.call(this, n, t, r, u, f)
                }, u.setAxisTranslation = function(n) {
                    var o, c;
                    i.prototype.setAxisTranslation.call(this, n);
                    o = u.options.breaks;
                    n = [];
                    var l = [],
                        a = 0,
                        f, t, s = u.userMin || u.min,
                        h = u.userMax || u.max,
                        r;
                    for (c in o) t = o[c], f = t.repeat || Infinity, u.isInBreak(t, s) && (s += t.to % f - s % f), u.isInBreak(t, h) && (h -= h % f - t.from % f);
                    for (c in o) {
                        for (t = o[c], r = t.from, f = t.repeat || Infinity; r - f > s;) r -= f;
                        for (; r < s;) r += f;
                        for (; r < h; r += f) n.push({
                            value: r,
                            move: "in"
                        }), n.push({
                            value: r + (t.to - t.from),
                            move: "out",
                            size: t.breakSize
                        })
                    }
                    n.sort(function(n, t) {
                        return n.value === t.value ? ("in" === n.move ? 0 : 1) - ("in" === t.move ? 0 : 1) : n.value - t.value
                    });
                    o = 0;
                    r = s;
                    for (c in n) t = n[c], o += "in" === t.move ? 1 : -1, 1 === o && "in" === t.move && (r = t.value), 0 === o && (l.push({
                        from: r,
                        to: t.value,
                        len: t.value - r - (t.size || 0)
                    }), a += t.value - r - (t.size || 0));
                    u.breakArray = l;
                    e(u, "afterBreaks");
                    u.transA *= (h - u.min) / (h - s - a);
                    u.min = s;
                    u.max = h
                })
            });
            t(c.prototype, "generatePoints", function(n) {
                n.apply(this, o(arguments));
                var r = this.xAxis,
                    u = this.yAxis,
                    f = this.points,
                    i, t = f.length,
                    s = this.options.connectNulls,
                    e;
                if (r && u && (r.options.breaks || u.options.breaks))
                    for (; t--;) i = f[t], e = null === i.y && !1 === s, e || !r.isInAnyBreak(i.x, !0) && !u.isInAnyBreak(i.y, !0) || (f.splice(t, 1), this.data[t] && this.data[t].destroyElements())
            });
            n.Series.prototype.drawBreaks = function(n, t) {
                var s = this,
                    c = s.points,
                    h, f, o, i;
                n && u(t, function(t) {
                    h = n.breakArray || [];
                    f = n.isXAxis ? n.min : r(s.options.threshold, n.min);
                    u(c, function(s) {
                        i = r(s["stack" + t.toUpperCase()], s[t]);
                        u(h, function(t) {
                            o = !1;
                            f < t.from && i > t.to || f > t.from && i < t.from ? o = "pointBreak" : (f < t.from && i > t.from && i < t.to || f > t.from && i > t.to && i < t.from) && (o = "pointInBreak");
                            o && e(n, o, {
                                point: s,
                                brk: t
                            })
                        })
                    })
                })
            };
            t(n.seriesTypes.column.prototype, "drawPoints", f);
            t(n.Series.prototype, "drawPoints", f)
        }(n),
        function(n) {
            var c = n.arrayMax,
                l = n.arrayMin,
                u = n.Axis,
                a = n.defaultPlotOptions,
                v = n.defined,
                e = n.each,
                y = n.extend,
                p = n.format,
                t = n.isNumber,
                o = n.merge,
                s = n.pick,
                w = n.Point,
                b = n.Tooltip,
                f = n.wrap,
                i = n.Series.prototype,
                k = i.processData,
                d = i.generatePoints,
                g = i.destroy,
                nt = {
                    approximation: "average",
                    groupPixelWidth: 2,
                    dateTimeLabelFormats: {
                        millisecond: ["%A, %b %e, %H:%M:%S.%L", "%A, %b %e, %H:%M:%S.%L", "-%H:%M:%S.%L"],
                        second: ["%A, %b %e, %H:%M:%S", "%A, %b %e, %H:%M:%S", "-%H:%M:%S"],
                        minute: ["%A, %b %e, %H:%M", "%A, %b %e, %H:%M", "-%H:%M"],
                        hour: ["%A, %b %e, %H:%M", "%A, %b %e, %H:%M", "-%H:%M"],
                        day: ["%A, %b %e, %Y", "%A, %b %e", "-%A, %b %e, %Y"],
                        week: ["Week from %A, %b %e, %Y", "%A, %b %e", "-%A, %b %e, %Y"],
                        month: ["%B %Y", "%B", "-%B %Y"],
                        year: ["%Y", "%Y", "-%Y"]
                    }
                },
                h = {
                    line: {},
                    spline: {},
                    area: {},
                    areaspline: {},
                    column: {
                        approximation: "sum",
                        groupPixelWidth: 10
                    },
                    arearange: {
                        approximation: "range"
                    },
                    areasplinerange: {
                        approximation: "range"
                    },
                    columnrange: {
                        approximation: "range",
                        groupPixelWidth: 10
                    },
                    candlestick: {
                        approximation: "ohlc",
                        groupPixelWidth: 10
                    },
                    ohlc: {
                        approximation: "ohlc",
                        groupPixelWidth: 5
                    }
                },
                tt = n.defaultDataGroupingUnits = [
                    ["millisecond", [1, 2, 5, 10, 20, 25, 50, 100, 200, 500]],
                    ["second", [1, 2, 5, 10, 15, 30]],
                    ["minute", [1, 2, 5, 10, 15, 30]],
                    ["hour", [1, 2, 3, 4, 6, 8, 12]],
                    ["day", [1]],
                    ["week", [1]],
                    ["month", [1, 3, 6]],
                    ["year", null]
                ],
                r = {
                    sum: function(n) {
                        var t = n.length,
                            i;
                        if (!t && n.hasNulls) i = null;
                        else if (t)
                            for (i = 0; t--;) i += n[t];
                        return i
                    },
                    average: function(n) {
                        var i = n.length;
                        return n = r.sum(n), t(n) && i && (n /= i), n
                    },
                    open: function(n) {
                        return n.length ? n[0] : n.hasNulls ? null : void 0
                    },
                    high: function(n) {
                        return n.length ? c(n) : n.hasNulls ? null : void 0
                    },
                    low: function(n) {
                        return n.length ? l(n) : n.hasNulls ? null : void 0
                    },
                    close: function(n) {
                        return n.length ? n[n.length - 1] : n.hasNulls ? null : void 0
                    },
                    ohlc: function(n, i, u, f) {
                        return n = r.open(n), i = r.high(i), u = r.low(u), f = r.close(f), t(n) || t(i) || t(u) || t(f) ? [n, i, u, f] : void 0
                    },
                    range: function(n, i) {
                        return n = r.low(n), i = r.high(i), t(n) || t(i) ? [n, i] : void 0
                    }
                };
            i.groupData = function(n, i, u, f) {
                var p = this.data,
                    d = this.options.data,
                    w = [],
                    b = [],
                    k = [],
                    l = n.length,
                    o, h, g = !!i,
                    s = [
                        [],
                        [],
                        [],
                        []
                    ],
                    c, a;
                f = "function" == typeof f ? f : r[f];
                for (var v = this.pointArrayMap, nt = v && v.length, y = 0, e = h = 0; e <= l && !(n[e] >= u[0]); e++);
                for (e; e <= l; e++) {
                    for (;
                        (void 0 !== u[y + 1] && n[e] >= u[y + 1] || e === l) && (o = u[y], this.dataGroupInfo = {
                            start: h,
                            length: s[0].length
                        }, h = f.apply(this, s), void 0 !== h && (w.push(o), b.push(h), k.push(this.dataGroupInfo)), h = e, s[0] = [], s[1] = [], s[2] = [], s[3] = [], y += 1, e !== l););
                    if (e === l) break;
                    if (v)
                        for (o = this.cropStart + e, o = p && p[o] || this.pointClass.prototype.applyOptions.apply({
                                series: this
                            }, [d[o]]), c = 0; c < nt; c++) a = o[v[c]], t(a) ? s[c].push(a) : null === a && (s[c].hasNulls = !0);
                    else o = g ? i[e] : null, t(o) ? s[0].push(o) : null === o && (s[0].hasNulls = !0)
                }
                return [w, b, k]
            };
            i.processData = function() {
                var n = this.chart,
                    r = this.options.dataGrouping,
                    e = !1 !== this.allowDG && r && s(r.enabled, n.options.isStock),
                    u = this.visible || !n.options.chart.ignoreHiddenSeries,
                    l;
                if (this.forceCrop = e, this.groupPixelWidth = null, this.hasProcessed = !0, !1 !== k.apply(this, arguments) && e && u) {
                    this.destroyGroupedData();
                    var f = this.processedXData,
                        t = this.processedYData,
                        c = n.plotSizeX,
                        n = this.xAxis,
                        h = n.options.ordinal,
                        o = this.groupPixelWidth = n.getGroupPixelWidth && n.getGroupPixelWidth();
                    if (o) {
                        if (this.isDirty = l = !0, u = n.getExtremes(), e = u.min, u = u.max, h = h && n.getGroupIntervalFactor(e, u, this) || 1, c = o * (u - e) / c * h, o = n.getTimeTicks(n.normalizeTimeTickInterval(c, r.units || tt), Math.min(e, f[0]), Math.max(u, f[f.length - 1]), n.options.startOfWeek, f, this.closestPointRange), f = i.groupData.apply(this, [f, t, o, r.approximation]), t = f[0], h = f[1], r.smoothed) {
                            for (r = t.length - 1, t[r] = Math.min(t[r], u); r-- && 0 < r;) t[r] += c / 2;
                            t[0] = Math.max(t[0], e)
                        }
                        this.currentDataGrouping = o.info;
                        this.closestPointRange = o.info.totalRange;
                        this.groupMap = f[2];
                        v(t[0]) && t[0] < n.dataMin && (n.min === n.dataMin && (n.min = t[0]), n.dataMin = t[0]);
                        this.processedXData = t;
                        this.processedYData = h
                    } else this.currentDataGrouping = this.groupMap = null;
                    this.hasGroupedData = l
                }
            };
            i.destroyGroupedData = function() {
                var n = this.groupedData;
                e(n || [], function(t, i) {
                    t && (n[i] = t.destroy ? t.destroy() : null)
                });
                this.groupedData = null
            };
            i.generatePoints = function() {
                d.apply(this);
                this.destroyGroupedData();
                this.groupedData = this.hasGroupedData ? this.points : null
            };
            f(w.prototype, "update", function(t) {
                this.dataGroup ? n.error(24) : t.apply(this, [].slice.call(arguments, 1))
            });
            f(b.prototype, "tooltipFooterHeaderFormatter", function(i, r, u) {
                var o = r.series,
                    h = o.tooltipOptions,
                    s = o.options.dataGrouping,
                    f = h.xDateFormat,
                    c, e = o.xAxis,
                    l = n.dateFormat;
                return e && "datetime" === e.options.type && s && t(r.key) ? (i = o.currentDataGrouping, s = s.dateTimeLabelFormats, i ? (e = s[i.unitName], 1 === i.count ? f = e[0] : (f = e[1], c = e[2])) : !f && s && (f = this.getXDateFormat(r, h, e)), f = l(f, r.key), c && (f += l(c, r.key + i.totalRange - 1)), p(h[(u ? "footer" : "header") + "Format"], {
                    point: y(r.point, {
                        key: f
                    }),
                    series: o
                })) : i.call(this, r, u)
            });
            i.destroy = function() {
                for (var n = this.groupedData || [], t = n.length; t--;) n[t] && n[t].destroy();
                g.apply(this)
            };
            f(i, "setOptions", function(n, t) {
                n = n.call(this, t);
                var i = this.type,
                    r = this.chart.options.plotOptions,
                    u = a[i].dataGrouping;
                return h[i] && (u || (u = o(nt, h[i])), n.dataGrouping = o(u, r.series && r.series.dataGrouping, r[i].dataGrouping, t.dataGrouping)), this.chart.options.isStock && (this.requireSorting = !0), n
            });
            f(u.prototype, "setScale", function(n) {
                n.call(this);
                e(this.series, function(n) {
                    n.hasProcessed = !1
                })
            });
            u.prototype.getGroupPixelWidth = function() {
                for (var t = this.series, i = t.length, r = 0, f = !1, u, n = i; n--;)(u = t[n].options.dataGrouping) && (r = Math.max(r, u.groupPixelWidth));
                for (n = i; n--;)(u = t[n].options.dataGrouping) && t[n].hasProcessed && (i = (t[n].processedXData || t[n].data).length, t[n].groupPixelWidth || i > this.chart.plotSizeX / r || i && u.forced) && (f = !0);
                return f ? r : 0
            };
            u.prototype.setDataGrouping = function(n, t) {
                var i;
                if (t = s(t, !0), n || (n = {
                        forced: !1,
                        units: null
                    }), this instanceof u)
                    for (i = this.series.length; i--;) this.series[i].update({
                        dataGrouping: n
                    }, !1);
                else e(this.chart.options.series, function(t) {
                    t.dataGrouping = n
                }, !1);
                t && this.chart.redraw()
            }
        }(n),
        function(n) {
            var t = n.each,
                r = n.Point,
                u = n.seriesType,
                i = n.seriesTypes;
            u("ohlc", "column", {
                lineWidth: 1,
                tooltip: {
                    pointFormat: '<span style="color:{point.color}">●<\/span> <b> {series.name}<\/b><br/>Open: {point.open}<br/>High: {point.high}<br/>Low: {point.low}<br/>Close: {point.close}<br/>'
                },
                threshold: null,
                states: {
                    hover: {
                        lineWidth: 3
                    }
                }
            }, {
                pointArrayMap: ["open", "high", "low", "close"],
                toYData: function(n) {
                    return [n.open, n.high, n.low, n.close]
                },
                pointValKey: "high",
                pointAttrToOptions: {
                    stroke: "color",
                    "stroke-width": "lineWidth"
                },
                pointAttribs: function(n, t) {
                    t = i.column.prototype.pointAttribs.call(this, n, t);
                    var r = this.options;
                    return delete t.fill, !n.options.color && r.upColor && n.open < n.close && (t.stroke = r.upColor), t
                },
                translate: function() {
                    var n = this,
                        r = n.yAxis,
                        u = !!n.modifyValue,
                        f = ["plotOpen", "yBottom", "plotClose"];
                    i.column.prototype.translate.apply(n);
                    t(n.points, function(i) {
                        t([i.open, i.low, i.close], function(t, e) {
                            null !== t && (u && (t = n.modifyValue(t)), i[f[e]] = r.toPixels(t, !0))
                        })
                    })
                },
                drawPoints: function() {
                    var n = this,
                        i = n.chart;
                    t(n.points, function(t) {
                        var u, e, s, o, f = t.graphic,
                            r, h = !f;
                        void 0 !== t.plotY && (f || (t.graphic = f = i.renderer.path().add(n.group)), f.attr(n.pointAttribs(t, t.selected && "select")), e = f.strokeWidth() % 2 / 2, r = Math.round(t.plotX) - e, s = Math.round(t.shapeArgs.width / 2), o = ["M", r, Math.round(t.yBottom), "L", r, Math.round(t.plotY)], null !== t.open && (u = Math.round(t.plotOpen) + e, o.push("M", r, u, "L", r - s, u)), null !== t.close && (u = Math.round(t.plotClose) + e, o.push("M", r, u, "L", r + s, u)), f[h ? "attr" : "animate"]({
                            d: o
                        }).addClass(t.getClassName(), !0))
                    })
                },
                animate: null
            }, {
                getClassName: function() {
                    return r.prototype.getClassName.call(this) + (this.open < this.close ? " highcharts-point-up" : " highcharts-point-down")
                }
            })
        }(n),
        function(n) {
            var t = n.defaultPlotOptions,
                i = n.each,
                r = n.merge,
                u = n.seriesType,
                f = n.seriesTypes;
            u("candlestick", "ohlc", r(t.column, {
                states: {
                    hover: {
                        lineWidth: 2
                    }
                },
                tooltip: t.ohlc.tooltip,
                threshold: null,
                lineColor: "#000000",
                lineWidth: 1,
                upColor: "#ffffff"
            }), {
                pointAttribs: function(n, t) {
                    var i = f.column.prototype.pointAttribs.call(this, n, t),
                        r = this.options,
                        u = n.open < n.close,
                        e = r.lineColor || this.color;
                    return i["stroke-width"] = r.lineWidth, i.fill = n.options.color || (u ? r.upColor || this.color : this.color), i.stroke = n.lineColor || (u ? r.upLineColor || e : e), t && (n = r.states[t], i.fill = n.color || i.fill, i.stroke = n.lineColor || i.stroke, i["stroke-width"] = n.lineWidth || i["stroke-width"]), i
                },
                drawPoints: function() {
                    var n = this,
                        t = n.chart;
                    i(n.points, function(i) {
                        var o = i.graphic,
                            r, s, f, c, e, u, h, l = !o;
                        void 0 !== i.plotY && (o || (i.graphic = o = t.renderer.path().add(n.group)), o.attr(n.pointAttribs(i, i.selected && "select")).shadow(n.options.shadow), e = o.strokeWidth() % 2 / 2, u = Math.round(i.plotX) - e, r = i.plotOpen, s = i.plotClose, f = Math.min(r, s), r = Math.max(r, s), h = Math.round(i.shapeArgs.width / 2), s = Math.round(f) !== Math.round(i.plotY), c = r !== i.yBottom, f = Math.round(f) + e, r = Math.round(r) + e, e = [], e.push("M", u - h, r, "L", u - h, f, "L", u + h, f, "L", u + h, r, "Z", "M", u, f, "L", u, s ? Math.round(i.plotY) : f, "M", u, r, "L", u, c ? Math.round(i.yBottom) : r), o[l ? "attr" : "animate"]({
                            d: e
                        }).addClass(i.getClassName(), !0))
                    })
                }
            })
        }(n),
        function(n) {
            var f = n.addEvent,
                t = n.each,
                e = n.merge,
                r = n.noop,
                o = n.Renderer,
                s = n.seriesType,
                h = n.seriesTypes,
                c = n.TrackerMixin,
                u = n.VMLRenderer,
                i = n.SVGRenderer.prototype.symbols;
            s("flags", "column", {
                pointRange: 0,
                shape: "flag",
                stackDistance: 12,
                textAlign: "center",
                tooltip: {
                    pointFormat: "{point.text}<br/>"
                },
                threshold: null,
                y: -30,
                fillColor: "#ffffff",
                lineWidth: 1,
                states: {
                    hover: {
                        lineColor: "#000000",
                        fillColor: "#ccd6eb"
                    }
                },
                style: {
                    fontSize: "11px",
                    fontWeight: "bold"
                }
            }, {
                sorted: !1,
                noSharedTooltip: !0,
                allowDG: !1,
                takeOrdinalPosition: !1,
                trackerGroups: ["markerGroup"],
                forceCrop: !0,
                init: n.Series.prototype.init,
                pointAttribs: function(n, t) {
                    var i = this.options,
                        r = n && n.color || this.color,
                        u = i.lineColor,
                        f = n && n.lineWidth;
                    return n = n && n.fillColor || i.fillColor, t && (n = i.states[t].fillColor, u = i.states[t].lineColor, f = i.states[t].lineWidth), {
                        fill: n || r,
                        stroke: u || r,
                        "stroke-width": f || i.lineWidth || 0
                    }
                },
                translate: function() {
                    h.column.prototype.translate.apply(this);
                    var i = this.options,
                        a = this.chart,
                        u = this.points,
                        c = u.length - 1,
                        n, e, v = i.onSeries;
                    n = v && a.get(v);
                    var i = i.onKey || "y",
                        v = n && n.options.step,
                        o = n && n.points,
                        f = o && o.length,
                        s = this.xAxis,
                        y = s.getExtremes(),
                        p = 0,
                        r, w, l;
                    if (n && n.visible && f)
                        for (p = (n.pointXOffset || 0) + (n.barW || 0) / 2, n = n.currentDataGrouping, w = o[f - 1].x + (n ? n.totalRange : 0), u.sort(function(n, t) {
                                return n.x - t.x
                            }), i = "plot" + i[0].toUpperCase() + i.substr(1); f-- && u[c] && !(n = u[c], r = o[f], r.x <= n.x && void 0 !== r[i] && (n.x <= w && (n.plotY = r[i], r.x < n.x && !v && (l = o[f + 1]) && void 0 !== l[i] && (n.plotY += (n.x - r.x) / (l.x - r.x) * (l[i] - r[i]))), c--, f++, 0 > c)););
                    t(u, function(n, t) {
                        var i;
                        void 0 === n.plotY && (n.x >= y.min && n.x <= y.max ? n.plotY = a.chartHeight - s.bottom - (s.opposite ? s.height : 0) + s.offset - a.plotTop : n.shapeArgs = {});
                        n.plotX += p;
                        (e = u[t - 1]) && e.plotX === n.plotX && (void 0 === e.stackIndex && (e.stackIndex = 0), i = e.stackIndex + 1);
                        n.stackIndex = i
                    })
                },
                drawPoints: function() {
                    for (var s = this.points, f = this.chart, y = f.renderer, r, u, i = this.options, p = i.y, o, n, t, h, c, l, a = this.yAxis, v = s.length; v--;) n = s[v], l = n.plotX > this.xAxis.len, r = n.plotX, t = n.stackIndex, o = n.options.shape || i.shape, u = n.plotY, void 0 !== u && (u = n.plotY + p - (void 0 !== t && t * i.stackDistance)), h = t ? void 0 : n.plotX, c = t ? void 0 : n.plotY, t = n.graphic, void 0 !== u && 0 <= r && !l ? (t || (t = n.graphic = y.label("", null, null, o, null, null, i.useHTML).attr(this.pointAttribs(n)).css(e(i.style, n.style)).attr({
                        align: "flag" === o ? "left" : "center",
                        width: i.width,
                        height: i.height,
                        "text-align": i.textAlign
                    }).addClass("highcharts-point").add(this.markerGroup), t.shadow(i.shadow)), 0 < r && (r -= t.strokeWidth() % 2), t.attr({
                        text: n.options.title || i.title || "A",
                        x: r,
                        y: u,
                        anchorX: h,
                        anchorY: c
                    }), n.tooltipPos = f.inverted ? [a.len + a.pos - f.plotLeft - u, this.xAxis.len - r] : [r, u]) : t && (n.graphic = t.destroy())
                },
                drawTracker: function() {
                    var n = this.points;
                    c.drawTrackerPoint.apply(this);
                    t(n, function(i) {
                        var r = i.graphic;
                        r && f(r.element, "mouseover", function() {
                            0 < i.stackIndex && !i.raised && (i._y = r.y, r.attr({
                                y: i._y - 8
                            }), i.raised = !0);
                            t(n, function(n) {
                                n !== i && n.raised && n.graphic && (n.graphic.attr({
                                    y: n._y
                                }), n.raised = !1)
                            })
                        })
                    })
                },
                animate: r,
                buildKDTree: r,
                setClip: r
            });
            i.flag = function(n, t, i, r, u) {
                return ["M", u && u.anchorX || n, u && u.anchorY || t, "L", n, t + r, n, t, n + i, t, n + i, t + r, n, t + r, "Z"]
            };
            t(["circle", "square"], function(n) {
                i[n + "pin"] = function(t, r, u, f, e) {
                    var o = e && e.anchorX;
                    return e = e && e.anchorY, "circle" === n && f > u && (t -= Math.round((f - u) / 2), u = f), t = i[n](t, r, u, f), o && e && t.push("M", o, r > e ? r : r + f, "L", o, e), t
                }
            });
            o === u && t(["flag", "circlepin", "squarepin"], function(n) {
                u.prototype.symbols[n] = i[n]
            })
        }(n),
        function(n) {
            function o(n, t, i) {
                this.init(n, t, i)
            }
            var a = n.addEvent,
                u = n.Axis,
                t = n.correctFloat,
                v = n.defaultOptions,
                s = n.defined,
                w = n.destroyObjectProperties,
                f = n.doc,
                h = n.each,
                i = n.fireEvent,
                b = n.hasTouch,
                y = n.isTouchDevice,
                c = n.merge,
                r = n.pick,
                k = n.removeEvent,
                e = n.wrap,
                l, p = {
                    height: y ? 20 : 14,
                    barBorderRadius: 0,
                    buttonBorderRadius: 0,
                    liveRedraw: n.svg && !y,
                    margin: 10,
                    minWidth: 6,
                    step: .2,
                    zIndex: 3,
                    barBackgroundColor: "#cccccc",
                    barBorderWidth: 1,
                    barBorderColor: "#cccccc",
                    buttonArrowColor: "#333333",
                    buttonBackgroundColor: "#e6e6e6",
                    buttonBorderColor: "#cccccc",
                    buttonBorderWidth: 1,
                    rifleColor: "#333333",
                    trackBackgroundColor: "#f2f2f2",
                    trackBorderColor: "#f2f2f2",
                    trackBorderWidth: 1
                };
            v.scrollbar = c(!0, p, v.scrollbar);
            n.swapXY = l = function(n, t) {
                var r = n.length,
                    i;
                if (t)
                    for (t = 0; t < r; t += 3) i = n[t + 1], n[t + 1] = n[t + 2], n[t + 2] = i;
                return n
            };
            o.prototype = {
                init: function(n, t, i) {
                    this.scrollbarButtons = [];
                    this.renderer = n;
                    this.userOptions = t;
                    this.options = c(p, t);
                    this.chart = i;
                    this.size = r(this.options.size, this.options.height);
                    t.enabled && (this.render(), this.initEvents(), this.addEvents())
                },
                render: function() {
                    var i = this.renderer,
                        n = this.options,
                        t = this.size,
                        r;
                    this.group = r = i.g("scrollbar").attr({
                        zIndex: n.zIndex,
                        translateY: -99999
                    }).add();
                    this.track = i.rect().addClass("highcharts-scrollbar-track").attr({
                        x: 0,
                        r: n.trackBorderRadius || 0,
                        height: t,
                        width: t
                    }).add(r);
                    this.track.attr({
                        fill: n.trackBackgroundColor,
                        stroke: n.trackBorderColor,
                        "stroke-width": n.trackBorderWidth
                    });
                    this.trackBorderWidth = this.track.strokeWidth();
                    this.track.attr({
                        y: -this.trackBorderWidth % 2 / 2
                    });
                    this.scrollbarGroup = i.g().add(r);
                    this.scrollbar = i.rect().addClass("highcharts-scrollbar-thumb").attr({
                        height: t,
                        width: t,
                        r: n.barBorderRadius || 0
                    }).add(this.scrollbarGroup);
                    this.scrollbarRifles = i.path(l(["M", -3, t / 4, "L", -3, 2 * t / 3, "M", 0, t / 4, "L", 0, 2 * t / 3, "M", 3, t / 4, "L", 3, 2 * t / 3], n.vertical)).addClass("highcharts-scrollbar-rifles").add(this.scrollbarGroup);
                    this.scrollbar.attr({
                        fill: n.barBackgroundColor,
                        stroke: n.barBorderColor,
                        "stroke-width": n.barBorderWidth
                    });
                    this.scrollbarRifles.attr({
                        stroke: n.rifleColor,
                        "stroke-width": 1
                    });
                    this.scrollbarStrokeWidth = this.scrollbar.strokeWidth();
                    this.scrollbarGroup.translate(-this.scrollbarStrokeWidth % 2 / 2, -this.scrollbarStrokeWidth % 2 / 2);
                    this.drawScrollbarButton(0);
                    this.drawScrollbarButton(1)
                },
                position: function(n, t, i, r) {
                    var u = this.options.vertical,
                        f = 0,
                        e = this.rendered ? "animate" : "attr";
                    this.x = n;
                    this.y = t + this.trackBorderWidth;
                    this.width = i;
                    this.xOffset = this.height = r;
                    this.yOffset = f;
                    u ? (this.width = this.yOffset = i = f = this.size, this.xOffset = t = 0, this.barWidth = r - 2 * i, this.x = n += this.options.margin) : (this.height = this.xOffset = r = t = this.size, this.barWidth = i - 2 * r, this.y += this.options.margin);
                    this.group[e]({
                        translateX: n,
                        translateY: this.y
                    });
                    this.track[e]({
                        width: i,
                        height: r
                    });
                    this.scrollbarButtons[1][e]({
                        translateX: u ? 0 : i - t,
                        translateY: u ? r - f : 0
                    })
                },
                drawScrollbarButton: function(n) {
                    var u = this.renderer,
                        f = this.scrollbarButtons,
                        r = this.options,
                        i = this.size,
                        t;
                    t = u.g().add(this.group);
                    f.push(t);
                    t = u.rect().addClass("highcharts-scrollbar-button").add(t);
                    t.attr({
                        stroke: r.buttonBorderColor,
                        "stroke-width": r.buttonBorderWidth,
                        fill: r.buttonBackgroundColor
                    });
                    t.attr(t.crisp({
                        x: -.5,
                        y: -.5,
                        width: i + 1,
                        height: i + 1,
                        r: r.buttonBorderRadius
                    }, t.strokeWidth()));
                    t = u.path(l(["M", i / 2 + (n ? -1 : 1), i / 2 - 3, "L", i / 2 + (n ? -1 : 1), i / 2 + 3, "L", i / 2 + (n ? 2 : -2), i / 2], r.vertical)).addClass("highcharts-scrollbar-arrow").add(f[n]);
                    t.attr({
                        fill: r.buttonArrowColor
                    })
                },
                setRange: function(n, i) {
                    var h = this.options,
                        c = h.vertical,
                        r = h.minWidth,
                        f = this.barWidth,
                        o, u, e = this.rendered && !this.hasDragged ? "animate" : "attr";
                    s(f) && (n = Math.max(n, 0), o = f * n, this.calculatedWidth = u = t(f * Math.min(i, 1) - o), u < r && (o = (f - r + u) * n, u = r), r = Math.floor(o + this.xOffset + this.yOffset), f = u / 2 - .5, this.from = n, this.to = i, c ? (this.scrollbarGroup[e]({
                        translateY: r
                    }), this.scrollbar[e]({
                        height: u
                    }), this.scrollbarRifles[e]({
                        translateY: f
                    }), this.scrollbarTop = r, this.scrollbarLeft = 0) : (this.scrollbarGroup[e]({
                        translateX: r
                    }), this.scrollbar[e]({
                        width: u
                    }), this.scrollbarRifles[e]({
                        translateX: f
                    }), this.scrollbarLeft = r, this.scrollbarTop = 0), 12 >= u ? this.scrollbarRifles.hide() : this.scrollbarRifles.show(!0), !1 === h.showFull && (0 >= n && 1 <= i ? this.group.hide() : this.group.show()), this.rendered = !0)
                },
                initEvents: function() {
                    var n = this;
                    n.mouseMoveHandler = function(t) {
                        var u = n.chart.pointer.normalize(t),
                            r = n.options.vertical ? "chartY" : "chartX",
                            f = n.initPositions;
                        !n.grabbedCenter || t.touches && 0 === t.touches[0][r] || (u = n.cursorToScrollbarPosition(u)[r], r = n[r], r = u - r, n.hasDragged = !0, n.updatePosition(f[0] + r, f[1] + r), n.hasDragged && i(n, "changed", {
                            from: n.from,
                            to: n.to,
                            trigger: "scrollbar",
                            DOMType: t.type,
                            DOMEvent: t
                        }))
                    };
                    n.mouseUpHandler = function(t) {
                        n.hasDragged && i(n, "changed", {
                            from: n.from,
                            to: n.to,
                            trigger: "scrollbar",
                            DOMType: t.type,
                            DOMEvent: t
                        });
                        n.grabbedCenter = n.hasDragged = n.chartX = n.chartY = null
                    };
                    n.mouseDownHandler = function(t) {
                        t = n.chart.pointer.normalize(t);
                        t = n.cursorToScrollbarPosition(t);
                        n.chartX = t.chartX;
                        n.chartY = t.chartY;
                        n.initPositions = [n.from, n.to];
                        n.grabbedCenter = !0
                    };
                    n.buttonToMinClick = function(r) {
                        var u = t(n.to - n.from) * n.options.step;
                        n.updatePosition(t(n.from - u), t(n.to - u));
                        i(n, "changed", {
                            from: n.from,
                            to: n.to,
                            trigger: "scrollbar",
                            DOMEvent: r
                        })
                    };
                    n.buttonToMaxClick = function(t) {
                        var r = (n.to - n.from) * n.options.step;
                        n.updatePosition(n.from + r, n.to + r);
                        i(n, "changed", {
                            from: n.from,
                            to: n.to,
                            trigger: "scrollbar",
                            DOMEvent: t
                        })
                    };
                    n.trackClick = function(t) {
                        var u = n.chart.pointer.normalize(t),
                            r = n.to - n.from,
                            f = n.y + n.scrollbarTop,
                            e = n.x + n.scrollbarLeft;
                        n.options.vertical && u.chartY > f || !n.options.vertical && u.chartX > e ? n.updatePosition(n.from + r, n.to + r) : n.updatePosition(n.from - r, n.to - r);
                        i(n, "changed", {
                            from: n.from,
                            to: n.to,
                            trigger: "scrollbar",
                            DOMEvent: t
                        })
                    }
                },
                cursorToScrollbarPosition: function(n) {
                    var t = this.options,
                        t = t.minWidth > this.calculatedWidth ? t.minWidth : 0;
                    return {
                        chartX: (n.chartX - this.x - this.xOffset) / (this.barWidth - t),
                        chartY: (n.chartY - this.y - this.yOffset) / (this.barWidth - t)
                    }
                },
                updatePosition: function(n, i) {
                    1 < i && (n = t(1 - t(i - n)), i = 1);
                    0 > n && (i = t(i - n), n = 0);
                    this.from = n;
                    this.to = i
                },
                update: function(n) {
                    this.destroy();
                    this.init(this.chart.renderer, c(!0, this.options, n), this.chart)
                },
                addEvents: function() {
                    var n = this.options.inverted ? [1, 0] : [0, 1],
                        t = this.scrollbarButtons,
                        i = this.scrollbarGroup.element,
                        r = this.mouseDownHandler,
                        u = this.mouseMoveHandler,
                        e = this.mouseUpHandler,
                        n = [
                            [t[n[0]].element, "click", this.buttonToMinClick],
                            [t[n[1]].element, "click", this.buttonToMaxClick],
                            [this.track.element, "click", this.trackClick],
                            [i, "mousedown", r],
                            [f, "mousemove", u],
                            [f, "mouseup", e]
                        ];
                    b && n.push([i, "touchstart", r], [f, "touchmove", u], [f, "touchend", e]);
                    h(n, function(n) {
                        a.apply(null, n)
                    });
                    this._events = n
                },
                removeEvents: function() {
                    h(this._events, function(n) {
                        k.apply(null, n)
                    });
                    this._events = void 0
                },
                destroy: function() {
                    var n = this.chart.scroller;
                    this.removeEvents();
                    h(["track", "scrollbarRifles", "scrollbar", "scrollbarGroup", "group"], function(n) {
                        this[n] && this[n].destroy && (this[n] = this[n].destroy())
                    }, this);
                    n && (n.scrollbar = null, w(n.scrollbarButtons))
                }
            };
            e(u.prototype, "init", function(n) {
                var t = this;
                n.apply(t, [].slice.call(arguments, 1));
                t.options.scrollbar && t.options.scrollbar.enabled && (t.options.scrollbar.vertical = !t.horiz, t.options.startOnTick = t.options.endOnTick = !1, t.scrollbar = new o(t.chart.renderer, t.options.scrollbar, t.chart), a(t.scrollbar, "changed", function(n) {
                    var i = Math.min(r(t.options.min, t.min), t.min, t.dataMin),
                        u = Math.max(r(t.options.max, t.max), t.max, t.dataMax) - i,
                        f;
                    t.horiz && !t.reversed || !t.horiz && t.reversed ? (f = i + u * this.to, i += u * this.from) : (f = i + u * (1 - this.from), i += u * (1 - this.to));
                    t.setExtremes(i, f, !0, !1, n)
                }))
            });
            e(u.prototype, "render", function(n) {
                var t = Math.min(r(this.options.min, this.min), this.min, this.dataMin),
                    u = Math.max(r(this.options.max, this.max), this.max, this.dataMax),
                    i = this.scrollbar,
                    f;
                n.apply(this, [].slice.call(arguments, 1));
                i && (this.horiz ? i.position(this.left, this.top + this.height + this.offset + 2 + (this.opposite ? 0 : this.axisTitleMargin), this.width, this.height) : i.position(this.left + this.width + 2 + this.offset + (this.opposite ? this.axisTitleMargin : 0), this.top, this.width, this.height), isNaN(t) || isNaN(u) || !s(this.min) || !s(this.max) ? i.setRange(0, 0) : (f = (this.min - t) / (u - t), t = (this.max - t) / (u - t), this.horiz && !this.reversed || !this.horiz && this.reversed ? i.setRange(f, t) : i.setRange(1 - t, 1 - f)))
            });
            e(u.prototype, "getOffset", function(n) {
                var i = this.horiz ? 2 : 1,
                    t = this.scrollbar;
                n.apply(this, [].slice.call(arguments, 1));
                t && (this.chart.axisOffset[i] += t.size + t.options.margin)
            });
            e(u.prototype, "destroy", function(n) {
                this.scrollbar && (this.scrollbar = this.scrollbar.destroy());
                n.apply(this, [].slice.call(arguments, 1))
            });
            n.Scrollbar = o
        }(n),
        function(n) {
            function l(n) {
                this.init(n)
            }
            var i = n.addEvent,
                s = n.Axis,
                h = n.Chart,
                d = n.color,
                g = n.defaultOptions,
                a = n.defined,
                nt = n.destroyObjectProperties,
                p = n.doc,
                t = n.each,
                o = n.erase,
                tt = n.error,
                w = n.extend,
                it = n.grep,
                rt = n.hasTouch,
                f = n.isNumber,
                ut = n.isObject,
                e = n.merge,
                r = n.pick,
                b = n.removeEvent,
                ft = n.Scrollbar,
                k = n.Series,
                v = n.seriesTypes,
                u = n.wrap,
                et = n.swapXY,
                y = [].concat(n.defaultDataGroupingUnits),
                c = function(n) {
                    var t = it(arguments, f);
                    if (t.length) return Math[n].apply(0, t)
                };
            y[4] = ["day", [1, 2, 3, 4]];
            y[5] = ["week", [1, 2, 3]];
            v = void 0 === v.areaspline ? "line" : "areaspline";
            w(g, {
                navigator: {
                    height: 40,
                    margin: 25,
                    maskInside: !0,
                    handles: {
                        backgroundColor: "#f2f2f2",
                        borderColor: "#999999"
                    },
                    maskFill: d("#6685c2").setOpacity(.3).get(),
                    outlineColor: "#cccccc",
                    outlineWidth: 1,
                    series: {
                        type: v,
                        color: "#335cad",
                        fillOpacity: .05,
                        lineWidth: 1,
                        compare: null,
                        dataGrouping: {
                            approximation: "average",
                            enabled: !0,
                            groupPixelWidth: 2,
                            smoothed: !0,
                            units: y
                        },
                        dataLabels: {
                            enabled: !1,
                            zIndex: 2
                        },
                        id: "highcharts-navigator-series",
                        className: "highcharts-navigator-series",
                        lineColor: null,
                        marker: {
                            enabled: !1
                        },
                        pointRange: 0,
                        shadow: !1,
                        threshold: null
                    },
                    xAxis: {
                        className: "highcharts-navigator-xaxis",
                        tickLength: 0,
                        lineWidth: 0,
                        gridLineColor: "#e6e6e6",
                        gridLineWidth: 1,
                        tickPixelInterval: 200,
                        labels: {
                            align: "left",
                            style: {
                                color: "#999999"
                            },
                            x: 3,
                            y: -4
                        },
                        crosshair: !1
                    },
                    yAxis: {
                        className: "highcharts-navigator-yaxis",
                        gridLineWidth: 0,
                        startOnTick: !1,
                        endOnTick: !1,
                        minPadding: .1,
                        maxPadding: .1,
                        labels: {
                            enabled: !1
                        },
                        crosshair: !1,
                        title: {
                            text: null
                        },
                        tickLength: 0,
                        tickWidth: 0
                    }
                }
            });
            l.prototype = {
                drawHandle: function(n, t, i, r) {
                    this.handles[t][r](i ? {
                        translateX: Math.round(this.left + this.height / 2 - 8),
                        translateY: Math.round(this.top + parseInt(n, 10) + .5)
                    } : {
                        translateX: Math.round(this.left + parseInt(n, 10)),
                        translateY: Math.round(this.top + this.height / 2 - 8)
                    })
                },
                getHandlePath: function(n) {
                    return et(["M", -4.5, .5, "L", 3.5, .5, "L", 3.5, 15.5, "L", -4.5, 15.5, "L", -4.5, .5, "M", -1.5, 4, "L", -1.5, 12, "M", .5, 4, "L", .5, 12], n)
                },
                drawOutline: function(n, t, i, r) {
                    var h = this.navigatorOptions.maskInside,
                        e = this.outline.strokeWidth() / 2,
                        o = this.outlineHeight,
                        s = this.scrollbarHeight,
                        c = this.size,
                        u = this.left - s,
                        f = this.top;
                    i ? (u -= e, i = f + t + e, t = f + n + e, n = ["M", u + o, f - s - e, "L", u + o, i, "L", u, i, "L", u, t, "L", u + o, t, "L", u + o, f + c + s].concat(h ? ["M", u + o, i - e, "L", u + o, t + e] : [])) : (n += u + s - e, t += u + s - e, f += e, n = ["M", u, f, "L", n, f, "L", n, f + o, "L", t, f + o, "L", t, f, "L", u + c + 2 * s, f].concat(h ? ["M", n - e, f, "L", t + e, f] : []));
                    this.outline[r]({
                        d: n
                    })
                },
                drawMasks: function(n, i, r, u) {
                    var f = this.left,
                        e = this.top,
                        o = this.height,
                        s, h, c, l;
                    r ? (c = [f, f, f], l = [e, e + n, e + i], h = [o, o, o], s = [n, i - n, this.size - i]) : (c = [f, f + n, f + i], l = [e, e, e], h = [n, i - n, this.size - i], s = [o, o, o]);
                    t(this.shades, function(n, t) {
                        n[u]({
                            x: c[t],
                            y: l[t],
                            width: h[t],
                            height: s[t]
                        })
                    })
                },
                renderElements: function() {
                    var n = this,
                        i = n.navigatorOptions,
                        f = i.maskInside,
                        o = n.chart,
                        s = o.inverted,
                        r = o.renderer,
                        u, e;
                    n.navigatorGroup = u = r.g("navigator").attr({
                        zIndex: 8,
                        visibility: "hidden"
                    }).add();
                    e = {
                        cursor: s ? "ns-resize" : "ew-resize"
                    };
                    t([!f, f, !f], function(t, f) {
                        n.shades[f] = r.rect().addClass("highcharts-navigator-mask" + (1 === f ? "-inside" : "-outside")).attr({
                            fill: t ? i.maskFill : "transparent"
                        }).css(1 === f && e).add(u)
                    });
                    n.outline = r.path().addClass("highcharts-navigator-outline").attr({
                        "stroke-width": i.outlineWidth,
                        stroke: i.outlineColor
                    }).add(u);
                    t([0, 1], function(t) {
                        n.handles[t] = r.path(n.getHandlePath(s)).attr({
                            zIndex: 7 - t
                        }).addClass("highcharts-navigator-handle highcharts-navigator-handle-" + ["left", "right"][t]).add(u);
                        var f = i.handles;
                        n.handles[t].attr({
                            fill: f.backgroundColor,
                            stroke: f.borderColor,
                            "stroke-width": 1
                        }).css(e)
                    })
                },
                update: function(n) {
                    this.destroy();
                    e(!0, this.chart.options.navigator, this.options, n);
                    this.init(this.chart)
                },
                render: function(n, t, i, u) {
                    var h = this.chart,
                        v, o, e = this.scrollbarHeight,
                        y, s = this.xAxis,
                        c, l, p;
                    if (v = this.navigatorEnabled, l = this.rendered, o = h.inverted, p = h.xAxis[0].minRange, !this.hasDragged || a(i)) {
                        if (!f(n) || !f(t))
                            if (l) i = 0, u = s.width;
                            else return;
                        if (this.left = r(s.left, h.plotLeft + e), o ? (this.size = c = y = r(s.len, h.plotHeight - 2 * e), h = e) : (this.size = c = y = r(s.len, h.plotWidth - 2 * e), h = y + 2 * e), i = r(i, s.toPixels(n, !0)), u = r(u, s.toPixels(t, !0)), f(i) && Infinity !== Math.abs(i) || (i = 0, u = h), n = s.toValue(i, !0), t = s.toValue(u, !0), Math.abs(t - n) < p)
                            if (this.grabbedLeft) i = s.toPixels(t - p, !0);
                            else if (this.grabbedRight) u = s.toPixels(n + p, !0);
                        else return;
                        this.zoomedMax = Math.min(Math.max(i, u, 0), c);
                        this.zoomedMin = Math.min(Math.max(this.fixedWidth ? this.zoomedMax - this.fixedWidth : Math.min(i, u), 0), c);
                        this.range = this.zoomedMax - this.zoomedMin;
                        c = Math.round(this.zoomedMax);
                        i = Math.round(this.zoomedMin);
                        v && (this.navigatorGroup.attr({
                            visibility: "visible"
                        }), l = l && !this.hasDragged ? "animate" : "attr", this.drawMasks(i, c, o, l), this.drawOutline(i, c, o, l), this.drawHandle(i, 0, o, l), this.drawHandle(c, 1, o, l));
                        this.scrollbar && (o ? (o = this.top - e, v = this.left - e + (v ? 0 : this.height), e = y + 2 * e) : (o = this.top + (v ? this.height : -e), v = this.left - e), this.scrollbar.position(v, o, h, e), this.scrollbar.setRange(i / y, c / y));
                        this.rendered = !0
                    }
                },
                addMouseEvents: function() {
                    var n = this,
                        f = n.chart,
                        e = f.container,
                        t = [],
                        r, u;
                    n.mouseMoveHandler = r = function(t) {
                        n.onMouseMove(t)
                    };
                    n.mouseUpHandler = u = function(t) {
                        n.onMouseUp(t)
                    };
                    t = n.getPartsEvents("mousedown");
                    t.push(i(e, "mousemove", r), i(p, "mouseup", u));
                    rt && (t.push(i(e, "touchmove", r), i(p, "touchend", u)), t.concat(n.getPartsEvents("touchstart")));
                    n.eventsToUnbind = t;
                    n.series && n.series[0] && t.push(i(n.series[0].xAxis, "foundExtremes", function() {
                        f.navigator.modifyNavigatorAxisExtremes()
                    }))
                },
                getPartsEvents: function(n) {
                    var r = this,
                        u = [];
                    return t(["shades", "handles"], function(f) {
                        t(r[f], function(t, e) {
                            u.push(i(t.element, n, function(n) {
                                r[f + "Mousedown"](n, e)
                            }))
                        })
                    }), u
                },
                shadesMousedown: function(n, t) {
                    n = this.chart.pointer.normalize(n);
                    var u = this.chart,
                        h = this.xAxis,
                        f = this.zoomedMin,
                        e = this.left,
                        o = this.size,
                        i = this.range,
                        r = n.chartX,
                        s;
                    u.inverted && (r = n.chartY, e = this.top);
                    1 === t ? (this.grabbedCenter = r, this.fixedWidth = i, this.dragOffset = r - f) : (n = r - e - i / 2, 0 === t ? n = Math.max(0, n) : 2 === t && n + i >= o && (n = o - i, s = this.getUnionExtremes().dataMax), n !== f && (this.fixedWidth = i, t = h.toFixedRange(n, n + i, null, s), u.xAxis[0].setExtremes(Math.min(t.min, t.max), Math.max(t.min, t.max), !0, null, {
                        trigger: "navigator"
                    })))
                },
                handlesMousedown: function(n, t) {
                    this.chart.pointer.normalize(n);
                    n = this.chart;
                    var i = n.xAxis[0],
                        r = n.inverted && !i.reversed || !n.inverted && i.reversed;
                    0 === t ? (this.grabbedLeft = !0, this.otherHandlePos = this.zoomedMax, this.fixedExtreme = r ? i.min : i.max) : (this.grabbedRight = !0, this.otherHandlePos = this.zoomedMin, this.fixedExtreme = r ? i.max : i.min);
                    n.fixedRange = null
                },
                onMouseMove: function(n) {
                    var t = this,
                        i = t.chart,
                        u = t.left,
                        e = t.navigatorSize,
                        f = t.range,
                        r = t.dragOffset,
                        o = i.inverted;
                    n.touches && 0 === n.touches[0].pageX || (n = i.pointer.normalize(n), i = n.chartX, o && (u = t.top, i = n.chartY), t.grabbedLeft ? (t.hasDragged = !0, t.render(0, 0, i - u, t.otherHandlePos)) : t.grabbedRight ? (t.hasDragged = !0, t.render(0, 0, t.otherHandlePos, i - u)) : t.grabbedCenter && (t.hasDragged = !0, i < r ? i = r : i > e + r - f && (i = e + r - f), t.render(0, 0, i - r, i - r + f)), t.hasDragged && t.scrollbar && t.scrollbar.options.liveRedraw && (n.DOMType = n.type, setTimeout(function() {
                        t.onMouseUp(n)
                    }, 0)))
                },
                onMouseUp: function(n) {
                    var u = this.chart,
                        t = this.xAxis,
                        r, i, f = n.DOMEvent || n;
                    (this.hasDragged || "scrollbar" === n.trigger) && (this.zoomedMin === this.otherHandlePos ? r = this.fixedExtreme : this.zoomedMax === this.otherHandlePos && (i = this.fixedExtreme), this.zoomedMax === this.navigatorSize && (i = this.getUnionExtremes().dataMax), t = t.toFixedRange(this.zoomedMin, this.zoomedMax, r, i), a(t.min) && u.xAxis[0].setExtremes(Math.min(t.min, t.max), Math.max(t.min, t.max), !0, this.hasDragged ? !1 : null, {
                        trigger: "navigator",
                        triggerOp: "navigator-drag",
                        DOMEvent: f
                    }));
                    "mousemove" !== n.DOMType && (this.grabbedLeft = this.grabbedRight = this.grabbedCenter = this.fixedWidth = this.fixedExtreme = this.otherHandlePos = this.hasDragged = this.dragOffset = null)
                },
                removeEvents: function() {
                    this.eventsToUnbind && (t(this.eventsToUnbind, function(n) {
                        n()
                    }), this.eventsToUnbind = void 0);
                    this.removeBaseSeriesEvents()
                },
                removeBaseSeriesEvents: function() {
                    var n = this.baseSeries || [];
                    this.navigatorEnabled && n[0] && !1 !== this.navigatorOptions.adaptToUpdatedData && (t(n, function(n) {
                        b(n, "updatedData", this.updatedDataHandler)
                    }, this), n[0].xAxis && b(n[0].xAxis, "foundExtremes", this.modifyBaseAxisExtremes))
                },
                init: function(n) {
                    var f = n.options,
                        r = f.navigator,
                        h = r.enabled,
                        l = f.scrollbar,
                        a = l.enabled,
                        f = h ? r.height : 0,
                        o = a ? l.height : 0;
                    this.handles = [];
                    this.shades = [];
                    this.chart = n;
                    this.setBaseSeries();
                    this.height = f;
                    this.scrollbarHeight = o;
                    this.scrollbarEnabled = a;
                    this.navigatorEnabled = h;
                    this.navigatorOptions = r;
                    this.scrollbarOptions = l;
                    this.outlineHeight = f + o;
                    var t = this,
                        h = t.baseSeries,
                        l = n.xAxis.length,
                        a = n.yAxis.length,
                        v = h && h[0] && h[0].xAxis || n.xAxis[0];
                    n.extraMargin = {
                        type: r.opposite ? "plotTop" : "marginBottom",
                        value: t.outlineHeight + r.margin
                    };
                    n.inverted && (n.extraMargin.type = r.opposite ? "marginRight" : "plotLeft");
                    n.isDirtyBox = !0;
                    t.navigatorEnabled ? (t.xAxis = new s(n, e({
                        breaks: v.options.breaks,
                        ordinal: v.options.ordinal
                    }, r.xAxis, {
                        id: "navigator-x-axis",
                        yAxis: "navigator-y-axis",
                        isX: !0,
                        type: "datetime",
                        index: l,
                        offset: 0,
                        keepOrdinalPadding: !0,
                        startOnTick: !1,
                        endOnTick: !1,
                        minPadding: 0,
                        maxPadding: 0,
                        zoomEnabled: !1
                    }, n.inverted ? {
                        offsets: [o, 0, -o, 0],
                        width: f
                    } : {
                        offsets: [0, -o, 0, o],
                        height: f
                    })), t.yAxis = new s(n, e(r.yAxis, {
                        id: "navigator-y-axis",
                        alignTicks: !1,
                        offset: 0,
                        index: a,
                        zoomEnabled: !1
                    }, n.inverted ? {
                        width: f
                    } : {
                        height: f
                    })), h || r.series.data ? t.addBaseSeries() : 0 === n.series.length && u(n, "redraw", function(i, r) {
                        0 < n.series.length && !t.series && (t.setBaseSeries(), n.redraw = i);
                        i.call(n, r)
                    }), t.renderElements(), t.addMouseEvents()) : t.xAxis = {
                        translate: function(t, i) {
                            var r = n.xAxis[0],
                                f = r.getExtremes(),
                                e = n.plotWidth - 2 * o,
                                u = c("min", r.options.min, f.dataMin),
                                r = c("max", r.options.max, f.dataMax) - u;
                            return i ? t * r / e + u : e * (t - u) / r
                        },
                        toPixels: function(n) {
                            return this.translate(n)
                        },
                        toValue: function(n) {
                            return this.translate(n, !0)
                        },
                        toFixedRange: s.prototype.toFixedRange,
                        fake: !0
                    };
                    n.options.scrollbar.enabled && (n.scrollbar = t.scrollbar = new ft(n.renderer, e(n.options.scrollbar, {
                        margin: t.navigatorEnabled ? 0 : 10,
                        vertical: n.inverted
                    }), n), i(t.scrollbar, "changed", function(i) {
                        var r = t.size,
                            u = r * this.to,
                            r = r * this.from;
                        t.hasDragged = t.scrollbar.hasDragged;
                        t.render(0, 0, r, u);
                        (n.options.scrollbar.liveRedraw || "mousemove" !== i.DOMType) && setTimeout(function() {
                            t.onMouseUp(i)
                        })
                    }));
                    t.addBaseSeriesEvents();
                    t.addChartEvents()
                },
                getUnionExtremes: function(n) {
                    var i = this.chart.xAxis[0],
                        t = this.xAxis,
                        u = t.options,
                        f = i.options,
                        e;
                    return n && null === i.dataMin || (e = {
                        dataMin: r(u && u.min, c("min", f.min, i.dataMin, t.dataMin, t.min)),
                        dataMax: r(u && u.max, c("max", f.max, i.dataMax, t.dataMax, t.max))
                    }), e
                },
                setBaseSeries: function(n) {
                    var i = this.chart,
                        r = this.baseSeries = [];
                    n = n || i.options && i.options.navigator.baseSeries || 0;
                    this.series && (this.removeBaseSeriesEvents(), t(this.series, function(n) {
                        n.destroy()
                    }));
                    t(i.series || [], function(t, i) {
                        (t.options.showInNavigator || (i === n || t.options.id === n) && !1 !== t.options.showInNavigator) && r.push(t)
                    });
                    this.xAxis && !this.xAxis.fake && this.addBaseSeries()
                },
                addBaseSeries: function() {
                    var n = this,
                        s = n.chart,
                        h = n.series = [],
                        c = n.baseSeries,
                        r, i, u = n.navigatorOptions.series,
                        f, o = {
                            enableMouseTracking: !1,
                            index: null,
                            group: "nav",
                            padXAxis: !1,
                            xAxis: "navigator-x-axis",
                            yAxis: "navigator-y-axis",
                            showInLegend: !1,
                            stacking: !1,
                            isInternal: !0,
                            visible: !0
                        };
                    c ? t(c, function(t, c) {
                        o.name = "Navigator " + (c + 1);
                        r = t.options || {};
                        f = r.navigatorOptions || {};
                        i = e(r, o, u, f);
                        c = f.data || u.data;
                        n.hasNavigatorData = n.hasNavigatorData || !!c;
                        i.data = c || r.data && r.data.slice(0);
                        t.navigatorSeries = s.initSeries(i);
                        h.push(t.navigatorSeries)
                    }) : (i = e(u, o), i.data = u.data, n.hasNavigatorData = !!i.data, h.push(s.initSeries(i)));
                    this.addBaseSeriesEvents()
                },
                addBaseSeriesEvents: function() {
                    var r = this,
                        n = r.baseSeries || [];
                    n[0] && n[0].xAxis && i(n[0].xAxis, "foundExtremes", this.modifyBaseAxisExtremes);
                    !1 !== this.navigatorOptions.adaptToUpdatedData && t(n, function(n) {
                        n.xAxis && (i(n, "updatedData", this.updatedDataHandler), n.userOptions.events = w(n.userOptions.event, {
                            updatedData: this.updatedDataHandler
                        }));
                        i(n, "remove", function() {
                            this.navigatorSeries && (o(r.series, this.navigatorSeries), this.navigatorSeries.remove(), delete this.navigatorSeries)
                        })
                    }, this)
                },
                modifyNavigatorAxisExtremes: function() {
                    var n = this.xAxis,
                        t;
                    n.getExtremes && (!(t = this.getUnionExtremes(!0)) || t.dataMin === n.min && t.dataMax === n.max || (n.min = t.dataMin, n.max = t.dataMax))
                },
                modifyBaseAxisExtremes: function() {
                    var n = this.chart.navigator,
                        t = this.getExtremes(),
                        s = t.dataMin,
                        h = t.dataMax,
                        t = t.max - t.min,
                        u = n.stickToMin,
                        o = n.stickToMax,
                        r, i, e = n.series && n.series[0],
                        c = !!this.setExtremes;
                    this.eventArgs && "rangeSelectorButton" === this.eventArgs.trigger || (u && (i = s, r = i + t), o && (r = h, u || (i = Math.max(r - t, e && e.xData ? e.xData[0] : -Number.MAX_VALUE))), c && (u || o) && f(i) && (this.min = this.userMin = i, this.max = this.userMax = r));
                    n.stickToMin = n.stickToMax = null
                },
                updatedDataHandler: function() {
                    var n = this.chart.navigator,
                        t = this.navigatorSeries;
                    n.stickToMin = f(this.xAxis.min) && this.xAxis.min <= this.xData[0];
                    n.stickToMax = Math.round(n.zoomedMax) >= Math.round(n.size);
                    t && !n.hasNavigatorData && (t.options.pointStart = this.xData[0], t.setData(this.options.data, !1, null, !1))
                },
                addChartEvents: function() {
                    i(this.chart, "redraw", function() {
                        var n = this.navigator,
                            t = n && (n.baseSeries && n.baseSeries[0] && n.baseSeries[0].xAxis || n.scrollbar && this.xAxis[0]);
                        t && n.render(t.min, t.max)
                    })
                },
                destroy: function() {
                    this.removeEvents();
                    this.xAxis && (o(this.chart.xAxis, this.xAxis), o(this.chart.axes, this.xAxis));
                    this.yAxis && (o(this.chart.yAxis, this.yAxis), o(this.chart.axes, this.yAxis));
                    t(this.series || [], function(n) {
                        n.destroy && n.destroy()
                    });
                    t("series xAxis yAxis shades outline scrollbarTrack scrollbarRifles scrollbarGroup scrollbar navigatorGroup rendered".split(" "), function(n) {
                        this[n] && this[n].destroy && this[n].destroy();
                        this[n] = null
                    }, this);
                    t([this.handles], function(n) {
                        nt(n)
                    }, this)
                }
            };
            n.Navigator = l;
            u(s.prototype, "zoom", function(n, t, i) {
                var r = this.chart,
                    u = r.options,
                    f = u.chart.zoomType,
                    o = u.navigator,
                    u = u.rangeSelector,
                    e;
                return this.isXAxis && (o && o.enabled || u && u.enabled) && ("x" === f ? r.resetZoomButton = "blocked" : "y" === f ? e = !1 : "xy" === f && (r = this.previousZoom, a(t) ? this.previousZoom = [this.min, this.max] : r && (t = r[0], i = r[1], delete this.previousZoom))), void 0 !== e ? e : n.call(this, t, i)
            });
            u(h.prototype, "init", function(n, t, r) {
                i(this, "beforeRender", function() {
                    var n = this.options;
                    (n.navigator.enabled || n.scrollbar.enabled) && (this.scroller = this.navigator = new l(this))
                });
                n.call(this, t, r)
            });
            u(h.prototype, "setChartSize", function(n) {
                var o = this.legend,
                    t = this.navigator,
                    i, u, f, e;
                n.apply(this, [].slice.call(arguments, 1));
                t && (u = o.options, f = t.xAxis, e = t.yAxis, i = t.scrollbarHeight, this.inverted ? (t.left = t.navigatorOptions.opposite ? this.chartWidth - i - t.height : this.spacing[3] + i, t.top = this.plotTop + i) : (t.left = this.plotLeft + i, t.top = t.navigatorOptions.top || this.chartHeight - t.height - i - this.spacing[2] - ("bottom" === u.verticalAlign && u.enabled && !u.floating ? o.legendHeight + r(u.margin, 10) : 0)), f && e && (this.inverted ? f.options.left = e.options.left = t.left : f.options.top = e.options.top = t.top, f.setAxisSize(), e.setAxisSize()))
            });
            u(k.prototype, "addPoint", function(n, t, i, r, u) {
                var f = this.options.turboThreshold;
                f && this.xData.length > f && ut(t, !0) && this.chart.navigator && tt(20, !0);
                n.call(this, t, i, r, u)
            });
            u(h.prototype, "addSeries", function(n, t, i, u) {
                return n = n.call(this, t, !1, u), this.navigator && this.navigator.setBaseSeries(), r(i, !0) && this.redraw(), n
            });
            u(k.prototype, "update", function(n, t, i) {
                n.call(this, t, !1);
                this.chart.navigator && this.chart.navigator.setBaseSeries();
                r(i, !0) && this.chart.redraw()
            });
            h.prototype.callbacks.push(function(n) {
                var t = n.navigator;
                t && (n = n.xAxis[0].getExtremes(), t.render(n.min, n.max))
            })
        }(n),
        function(n) {
            function f(n) {
                this.init(n)
            }
            var t = n.addEvent,
                h = n.Axis,
                y = n.Chart,
                c = n.css,
                p = n.createElement,
                w = n.dateFormat,
                u = n.defaultOptions,
                l = u.global.useUTC,
                b = n.defined,
                d = n.destroyObjectProperties,
                g = n.discardElement,
                e = n.each,
                a = n.extend,
                k = n.fireEvent,
                o = n.Date,
                i = n.isNumber,
                s = n.merge,
                r = n.pick,
                v = n.pInt,
                nt = n.splat,
                tt = n.wrap;
            a(u, {
                rangeSelector: {
                    buttonTheme: {
                        "stroke-width": 0,
                        width: 28,
                        height: 18,
                        padding: 2,
                        zIndex: 7
                    },
                    height: 35,
                    inputPosition: {
                        align: "right"
                    },
                    labelStyle: {
                        color: "#666666"
                    }
                }
            });
            u.lang = s(u.lang, {
                rangeSelectorZoom: "Zoom",
                rangeSelectorFrom: "From",
                rangeSelectorTo: "To"
            });
            f.prototype = {
                clickButton: function(n, u) {
                    var k = this,
                        c = k.chart,
                        w = k.buttonOptions[n],
                        f = c.xAxis[0],
                        a = c.scroller && c.scroller.getUnionExtremes() || f || {},
                        v = a.dataMin,
                        o = a.dataMax,
                        b, s = f && Math.round(Math.min(f.max, r(o, f.max))),
                        y = w.type,
                        p, a = w._range,
                        d, g, tt, it = w.dataGrouping;
                    if (null !== v && null !== o) {
                        if (c.fixedRange = a, it && (this.forcedDataGrouping = !0, h.prototype.setDataGrouping.call(f || {
                                chart: this.chart
                            }, it, !1)), "month" === y || "year" === y) f ? (y = {
                            range: w,
                            max: s,
                            dataMin: v,
                            dataMax: o
                        }, b = f.minFromRange.call(y), i(y.newMax) && (s = y.newMax)) : a = w;
                        else if (a) b = Math.max(s - a, v), s = Math.min(b + a, o);
                        else if ("ytd" === y)
                            if (f) void 0 === o && (v = Number.MAX_VALUE, o = Number.MIN_VALUE, e(c.series, function(n) {
                                n = n.xData;
                                v = Math.min(n[0], v);
                                o = Math.max(n[n.length - 1], o)
                            }), u = !1), s = k.getYTDExtremes(o, v, l), b = d = s.min, s = s.max;
                            else {
                                t(c, "beforeRender", function() {
                                    k.clickButton(n)
                                });
                                return
                            }
                        else "all" === y && f && (b = v, s = o);
                        k.setSelected(n);
                        f ? f.setExtremes(b, s, r(u, 1), null, {
                            trigger: "rangeSelectorButton",
                            rangeSelectorButton: w
                        }) : (p = nt(c.options.xAxis)[0], tt = p.range, p.range = a, g = p.min, p.min = d, t(c, "load", function() {
                            p.range = tt;
                            p.min = g
                        }))
                    }
                },
                setSelected: function(n) {
                    this.selected = this.options.selected = n
                },
                defaultButtons: [{
                    type: "month",
                    count: 1,
                    text: "1m"
                }, {
                    type: "month",
                    count: 3,
                    text: "3m"
                }, {
                    type: "month",
                    count: 6,
                    text: "6m"
                }, {
                    type: "ytd",
                    text: "YTD"
                }, {
                    type: "year",
                    count: 1,
                    text: "1y"
                }, {
                    type: "all",
                    text: "All"
                }],
                init: function(n) {
                    var i = this,
                        r = n.options.rangeSelector,
                        u = r.buttons || [].concat(i.defaultButtons),
                        f = r.selected,
                        o = function() {
                            var n = i.minInput,
                                t = i.maxInput;
                            n && n.blur && k(n, "blur");
                            t && t.blur && k(t, "blur")
                        };
                    i.chart = n;
                    i.options = r;
                    i.buttons = [];
                    n.extraTopMargin = r.height;
                    i.buttonOptions = u;
                    this.unMouseDown = t(n.container, "mousedown", o);
                    this.unResize = t(n, "resize", o);
                    e(u, i.computeButtonRange);
                    void 0 !== f && u[f] && this.clickButton(f, !1);
                    t(n, "load", function() {
                        t(n.xAxis[0], "setExtremes", function(t) {
                            this.max - this.min !== n.fixedRange && "rangeSelectorButton" !== t.trigger && "updatedData" !== t.trigger && i.forcedDataGrouping && this.setDataGrouping(!1, !1)
                        })
                    })
                },
                updateButtonStates: function() {
                    var n = this.chart,
                        t = n.xAxis[0],
                        r = Math.round(t.max - t.min),
                        h = !t.hasVisibleSeries,
                        n = n.scroller && n.scroller.getUnionExtremes() || t,
                        u = n.dataMin,
                        f = n.dataMax,
                        n = this.getYTDExtremes(f, u, l),
                        c = n.min,
                        a = n.max,
                        s = this.selected,
                        o = i(s),
                        v = this.options.allButtonsEnabled,
                        y = this.buttons;
                    e(this.buttonOptions, function(n, i) {
                        var e = n._range,
                            l = n.type,
                            w = n.count || 1,
                            p;
                        n = y[i];
                        p = 0;
                        i = i === s;
                        var d = e > f - u,
                            g = e < t.minRange,
                            b = !1,
                            k = !1,
                            e = e === r;
                        ("month" === l || "year" === l) && r >= 864e5 * {
                            month: 28,
                            year: 365
                        } [l] * w && r <= 864e5 * {
                            month: 31,
                            year: 366
                        } [l] * w ? e = !0 : "ytd" === l ? (e = a - c === r, b = !i) : "all" === l && (e = t.max - t.min >= f - u, k = !i && o && e);
                        l = !v && (d || g || k || h);
                        e = i && e || e && !o && !b;
                        l ? p = 3 : e && (o = !0, p = 2);
                        n.state !== p && n.setState(p)
                    })
                },
                computeButtonRange: function(n) {
                    var t = n.type,
                        i = n.count || 1,
                        r = {
                            millisecond: 1,
                            second: 1e3,
                            minute: 6e4,
                            hour: 36e5,
                            day: 864e5,
                            week: 6048e5
                        };
                    r[t] ? n._range = r[t] * i : ("month" === t || "year" === t) && (n._range = 864e5 * {
                        month: 30,
                        year: 365
                    } [t] * i)
                },
                setInputValue: function(n, t) {
                    var r = this.chart.options.rangeSelector,
                        i = this[n + "Input"];
                    b(t) && (i.previousValue = i.HCTime, i.HCTime = t);
                    i.value = w(r.inputEditDateFormat || "%Y-%m-%d", i.HCTime);
                    this[n + "DateBox"].attr({
                        text: w(r.inputDateFormat || "%b %e, %Y", i.HCTime)
                    })
                },
                showInput: function(n) {
                    var i = this.inputGroup,
                        t = this[n + "DateBox"];
                    c(this[n + "Input"], {
                        left: i.translateX + t.x + "px",
                        top: i.translateY + "px",
                        width: t.width - 2 + "px",
                        height: t.height - 2 + "px",
                        border: "2px solid silver"
                    })
                },
                hideInput: function(n) {
                    c(this[n + "Input"], {
                        border: 0,
                        width: "1px",
                        height: "1px"
                    });
                    this.setInputValue(n)
                },
                drawInput: function(n) {
                    function k() {
                        var s = t.value,
                            n = (e.inputDateParser || Date.parse)(s),
                            u = f.xAxis[0],
                            o = f.scroller && f.scroller.xAxis ? f.scroller.xAxis : u,
                            c = o.dataMin,
                            o = o.dataMax;
                        n !== t.previousValue && (t.previousValue = n, i(n) || (n = s.split("-"), n = Date.UTC(v(n[0]), v(n[1]) - 1, v(n[2]))), i(n) && (l || (n += 6e4 * (new Date).getTimezoneOffset()), h ? n > r.maxInput.HCTime ? n = void 0 : n < c && (n = c) : n < r.minInput.HCTime ? n = void 0 : n > o && (n = o), void 0 !== n && u.setExtremes(h ? n : u.min, h ? u.max : n, void 0, void 0, {
                            trigger: "rangeSelectorInput"
                        })))
                    }
                    var r = this,
                        f = r.chart,
                        w = f.renderer.style || {},
                        o = f.renderer,
                        e = f.options.rangeSelector,
                        d = r.div,
                        h = "min" === n,
                        t, b, y = this.inputGroup;
                    this[n + "Label"] = b = o.label(u.lang[h ? "rangeSelectorFrom" : "rangeSelectorTo"], this.inputGroup.offset).addClass("highcharts-range-label").attr({
                        padding: 2
                    }).add(y);
                    y.offset += b.width + 5;
                    this[n + "DateBox"] = o = o.label("", y.offset).addClass("highcharts-range-input").attr({
                        padding: 2,
                        width: e.inputBoxWidth || 90,
                        height: e.inputBoxHeight || 17,
                        stroke: e.inputBoxBorderColor || "#cccccc",
                        "stroke-width": 1,
                        "text-align": "center"
                    }).on("click", function() {
                        r.showInput(n);
                        r[n + "Input"].focus()
                    }).add(y);
                    y.offset += o.width + (h ? 10 : 0);
                    this[n + "Input"] = t = p("input", {
                        name: n,
                        className: "highcharts-range-selector",
                        type: "text"
                    }, {
                        top: f.plotTop + "px"
                    }, d);
                    b.css(s(w, e.labelStyle));
                    o.css(s({
                        color: "#333333"
                    }, w, e.inputStyle));
                    c(t, a({
                        position: "absolute",
                        border: 0,
                        width: "1px",
                        height: "1px",
                        padding: 0,
                        textAlign: "center",
                        fontSize: w.fontSize,
                        fontFamily: w.fontFamily,
                        left: "-9em"
                    }, e.inputStyle));
                    t.onfocus = function() {
                        r.showInput(n)
                    };
                    t.onblur = function() {
                        r.hideInput(n)
                    };
                    t.onchange = k;
                    t.onkeypress = function(n) {
                        13 === n.keyCode && k()
                    }
                },
                getPosition: function() {
                    var n = this.chart,
                        t = n.options.rangeSelector,
                        n = r((t.buttonPosition || {}).y, n.plotTop - n.axisOffset[0] - t.height);
                    return {
                        buttonTop: n,
                        inputTop: n - 10
                    }
                },
                getYTDExtremes: function(n, t, i) {
                    var r = new o(n),
                        u = r[o.hcGetFullYear]();
                    return i = i ? o.UTC(u, 0, 1) : +new o(u, 0, 1), t = Math.max(t || 0, i), r = r.getTime(), {
                        max: Math.min(n || r, r),
                        min: t
                    }
                },
                render: function(n, t) {
                    var i = this,
                        f = i.chart,
                        v = f.renderer,
                        nt = f.container,
                        h = f.options,
                        y = h.exporting && !1 !== h.exporting.enabled && h.navigation && h.navigation.buttonOptions,
                        s = h.rangeSelector,
                        tt = i.buttons,
                        h = u.lang,
                        o = i.div,
                        o = i.inputGroup,
                        w = s.buttonTheme,
                        it = s.buttonPosition || {},
                        k = s.inputEnabled,
                        c = w && w.states,
                        rt = f.plotLeft,
                        d, g = this.getPosition(),
                        l = i.group,
                        ut = i.rendered;
                    !1 !== s.enabled && (ut || (i.group = l = v.g("range-selector-buttons").add(), i.zoomText = v.text(h.rangeSelectorZoom, r(it.x, rt), 15).css(s.labelStyle).add(l), d = r(it.x, rt) + i.zoomText.getBBox().width + 5, e(i.buttonOptions, function(n, t) {
                        tt[t] = v.button(n.text, d, 0, function() {
                            i.clickButton(t);
                            i.isActive = !0
                        }, w, c && c.hover, c && c.select, c && c.disabled).attr({
                            "text-align": "center"
                        }).add(l);
                        d += tt[t].width + r(s.buttonSpacing, 5)
                    }), !1 !== k && (i.div = o = p("div", null, {
                        position: "relative",
                        height: 0,
                        zIndex: 1
                    }), nt.parentNode.insertBefore(o, nt), i.inputGroup = o = v.g("input-group").add(), o.offset = 0, i.drawInput("min"), i.drawInput("max"))), i.updateButtonStates(), l[ut ? "animate" : "attr"]({
                        translateY: g.buttonTop
                    }), !1 !== k && (o.align(a({
                        y: g.inputTop,
                        width: o.offset,
                        x: y && g.inputTop < (y.y || 0) + y.height - f.spacing[0] ? -40 : 0
                    }, s.inputPosition), !0, f.spacingBox), b(k) || (f = l.getBBox(), o[o.alignAttr.translateX < f.x + f.width + 10 ? "hide" : "show"]()), i.setInputValue("min", n), i.setInputValue("max", t)), i.rendered = !0)
                },
                update: function(n) {
                    var t = this.chart;
                    s(!0, t.options.rangeSelector, n);
                    this.destroy();
                    this.init(t)
                },
                destroy: function() {
                    var t = this.minInput,
                        i = this.maxInput,
                        n;
                    this.unMouseDown();
                    this.unResize();
                    d(this.buttons);
                    t && (t.onfocus = t.onblur = t.onchange = null);
                    i && (i.onfocus = i.onblur = i.onchange = null);
                    for (n in this) this[n] && "chart" !== n && (this[n].destroy ? this[n].destroy() : this[n].nodeType && g(this[n])), this[n] !== f.prototype[n] && (this[n] = null)
                }
            };
            h.prototype.toFixedRange = function(n, t, u, f) {
                var e = this.chart && this.chart.fixedRange;
                return n = r(u, this.translate(n, !0, !this.horiz)), t = r(f, this.translate(t, !0, !this.horiz)), u = e && (t - n) / e, .7 < u && 1.3 > u && (f ? n = t - e : t = n + e), i(n) || (n = t = void 0), {
                    min: n,
                    max: t
                }
            };
            h.prototype.minFromRange = function() {
                var t = this.range,
                    o = {
                        month: "Month",
                        year: "FullYear"
                    } [t.type],
                    n, u = this.max,
                    f, e, s = function(n, t) {
                        var i = new Date(n);
                        return i["set" + o](i["get" + o]() + t), i.getTime() - n
                    };
                return i(t) ? (n = u - t, e = t) : (n = u + s(u, -t.count), this.chart && (this.chart.fixedRange = u - n)), f = r(this.dataMin, Number.MIN_VALUE), i(n) || (n = f), n <= f && (n = f, void 0 === e && (e = s(n, t.count)), this.newMax = Math.min(n + e, this.dataMax)), i(u) || (n = void 0), n
            };
            tt(y.prototype, "init", function(n, i, r) {
                t(this, "init", function() {
                    this.options.rangeSelector.enabled && (this.rangeSelector = new f(this))
                });
                n.call(this, i, r)
            });
            y.prototype.callbacks.push(function(n) {
                function f() {
                    r = n.xAxis[0].getExtremes();
                    i(r.min) && u.render(r.min, r.max)
                }
                var r, u = n.rangeSelector,
                    e, o;
                u && (o = t(n.xAxis[0], "afterSetExtremes", function(n) {
                    u.render(n.min, n.max)
                }), e = t(n, "redraw", f), f());
                t(n, "destroy", function() {
                    u && (e(), o())
                })
            });
            n.RangeSelector = f
        }(n),
        function(n) {
            var k = n.arrayMax,
                d = n.arrayMin,
                i = n.Axis,
                l = n.Chart,
                o = n.defined,
                f = n.each,
                g = n.extend,
                nt = n.format,
                s = n.inArray,
                h = n.isNumber,
                a = n.isString,
                c = n.map,
                e = n.merge,
                t = n.pick,
                v = n.Point,
                tt = n.Renderer,
                y = n.Series,
                p = n.splat,
                w = n.SVGRenderer,
                b = n.VMLRenderer,
                r = n.wrap,
                u = y.prototype,
                it = u.init,
                rt = u.processData,
                ut = v.prototype.tooltipFormatter;
            n.StockChart = n.stockChart = function(i, r, u) {
                var v = a(i) || i.nodeName,
                    f = arguments[v ? 1 : 0],
                    b = f.series,
                    s = n.getOptions(),
                    y, w = t(f.navigator && f.navigator.enabled, s.navigator.enabled, !0),
                    k = w ? {
                        startOnTick: !1,
                        endOnTick: !1
                    } : null,
                    o = {
                        marker: {
                            enabled: !1,
                            radius: 2
                        }
                    },
                    h = {
                        shadow: !1,
                        borderWidth: 0
                    };
                return f.xAxis = c(p(f.xAxis || {}), function(n) {
                    return e({
                        minPadding: 0,
                        maxPadding: 0,
                        ordinal: !0,
                        title: {
                            text: null
                        },
                        labels: {
                            overflow: "justify"
                        },
                        showLastLabel: !0
                    }, s.xAxis, n, {
                        type: "datetime",
                        categories: null
                    }, k)
                }), f.yAxis = c(p(f.yAxis || {}), function(n) {
                    return y = t(n.opposite, !0), e({
                        labels: {
                            y: -2
                        },
                        opposite: y,
                        showLastLabel: !1,
                        title: {
                            text: null
                        }
                    }, s.yAxis, n)
                }), f.series = null, f = e({
                    chart: {
                        panning: !0,
                        pinchType: "x"
                    },
                    navigator: {
                        enabled: w
                    },
                    scrollbar: {
                        enabled: t(s.scrollbar.enabled, !0)
                    },
                    rangeSelector: {
                        enabled: t(s.rangeSelector.enabled, !0)
                    },
                    title: {
                        text: null
                    },
                    tooltip: {
                        shared: !0,
                        crosshairs: !0
                    },
                    legend: {
                        enabled: !1
                    },
                    plotOptions: {
                        line: o,
                        spline: o,
                        area: o,
                        areaspline: o,
                        arearange: o,
                        areasplinerange: o,
                        column: h,
                        columnrange: h,
                        candlestick: h,
                        ohlc: h
                    }
                }, f, {
                    isStock: !0
                }), f.series = b, v ? new l(i, f, u) : new l(f, r)
            };
            r(i.prototype, "autoLabelAlign", function(n) {
                var r = this.chart,
                    t = this.options,
                    r = r._labelPanes = r._labelPanes || {},
                    i = this.options.labels;
                return this.chart.options.isStock && "yAxis" === this.coll && (t = t.top + "," + t.height, !r[t] && i.enabled) ? (15 === i.x && (i.x = 0), void 0 === i.align && (i.align = "right"), r[t] = 1, "right") : n.call(this, [].slice.call(arguments, 1))
            });
            r(i.prototype, "getPlotLinePath", function(n, i, r, u, e, l) {
                var v = this,
                    ut = this.isLinked && !this.series ? this.linkedParent.series : this.series,
                    y = v.chart,
                    ft = y.renderer,
                    g = v.left,
                    nt = v.top,
                    p, w, b, k, tt = [],
                    it = [],
                    d, rt;
                return "colorAxis" === v.coll ? n.apply(this, [].slice.call(arguments, 1)) : (it = function(n) {
                    var t = "xAxis" === n ? "yAxis" : "xAxis";
                    return n = v.options[t], h(n) ? [y[t][n]] : a(n) ? [y.get(n)] : c(ut, function(n) {
                        return n[t]
                    })
                }(v.coll), f(v.isXAxis ? y.yAxis : y.xAxis, function(n) {
                    if (o(n.options.id) ? -1 === n.options.id.indexOf("navigator") : 1) {
                        var t = n.isXAxis ? "yAxis" : "xAxis",
                            t = o(n.options[t]) ? y[t][n.options[t]] : y[t][0];
                        v === t && it.push(n)
                    }
                }), d = it.length ? [] : [v.isXAxis ? y.yAxis[0] : y.xAxis[0]], f(it, function(n) {
                    -1 === s(n, d) && d.push(n)
                }), rt = t(l, v.translate(i, null, null, u)), h(rt) && (v.horiz ? f(d, function(n) {
                    var t;
                    w = n.pos;
                    k = w + n.len;
                    p = b = Math.round(rt + v.transB);
                    (p < g || p > g + v.width) && (e ? p = b = Math.min(Math.max(g, p), g + v.width) : t = !0);
                    t || tt.push("M", p, w, "L", b, k)
                }) : f(d, function(n) {
                    var t;
                    p = n.pos;
                    b = p + n.len;
                    w = k = Math.round(nt + v.height - rt);
                    (w < nt || w > nt + v.height) && (e ? w = k = Math.min(Math.max(nt, w), v.top + v.height) : t = !0);
                    t || tt.push("M", p, w, "L", b, k)
                })), 0 < tt.length ? ft.crispPolyLine(tt, r || 1) : null)
            });
            i.prototype.getPlotBandPath = function(n, t) {
                t = this.getPlotLinePath(t, null, null, !0);
                n = this.getPlotLinePath(n, null, null, !0);
                var r = [],
                    i;
                if (n && t)
                    if (n.toString() === t.toString()) r = n, r.flat = !0;
                    else
                        for (i = 0; i < n.length; i += 6) r.push("M", n[i + 1], n[i + 2], "L", n[i + 4], n[i + 5], t[i + 4], t[i + 5], t[i + 1], t[i + 2], "z");
                else r = null;
                return r
            };
            w.prototype.crispPolyLine = function(n, t) {
                for (var i = 0; i < n.length; i += 6) n[i + 1] === n[i + 4] && (n[i + 1] = n[i + 4] = Math.round(n[i + 1]) - t % 2 / 2), n[i + 2] === n[i + 5] && (n[i + 2] = n[i + 5] = Math.round(n[i + 2]) + t % 2 / 2);
                return n
            };
            tt === b && (b.prototype.crispPolyLine = w.prototype.crispPolyLine);
            r(i.prototype, "hideCrosshair", function(n, t) {
                n.call(this, t);
                this.crossLabel && (this.crossLabel = this.crossLabel.hide())
            });
            r(i.prototype, "drawCrosshair", function(n, i, r) {
                var s, e, u, h;
                if (n.call(this, i, r), o(this.crosshair.label) && this.crosshair.label.enabled && this.cross) {
                    n = this.chart;
                    u = this.options.crosshair.label;
                    h = this.horiz;
                    s = this.opposite;
                    e = this.left;
                    var c = this.top,
                        f = this.crossLabel,
                        l, a = u.format,
                        v = "",
                        w = "inside" === this.options.tickPosition,
                        y = !1 !== this.crosshair.snap,
                        p = 0;
                    i || (i = this.cross && this.cross.e);
                    l = h ? "center" : s ? "right" === this.labelAlign ? "right" : "left" : "left" === this.labelAlign ? "left" : "center";
                    f || (f = this.crossLabel = n.renderer.label(null, null, null, u.shape || "callout").addClass("highcharts-crosshair-label" + (this.series[0] && " highcharts-color-" + this.series[0].colorIndex)).attr({
                        align: u.align || l,
                        padding: t(u.padding, 8),
                        r: t(u.borderRadius, 3),
                        zIndex: 2
                    }).add(this.labelGroup), f.attr({
                        fill: u.backgroundColor || this.series[0] && this.series[0].color || "#666666",
                        stroke: u.borderColor || "",
                        "stroke-width": u.borderWidth || 0
                    }).css(g({
                        color: "#ffffff",
                        fontWeight: "normal",
                        fontSize: "11px",
                        textAlign: "center"
                    }, u.style)));
                    h ? (l = y ? r.plotX + e : i.chartX, c += s ? 0 : this.height) : (l = s ? this.width + e : 0, c = y ? r.plotY + c : i.chartY);
                    a || u.formatter || (this.isDatetimeAxis && (v = "%b %d, %Y"), a = "{value" + (v ? ":" + v : "") + "}");
                    i = y ? r[this.isXAxis ? "x" : "y"] : this.toValue(h ? i.chartX : i.chartY);
                    f.attr({
                        text: a ? nt(a, {
                            value: i
                        }) : u.formatter.call(this, i),
                        x: l,
                        y: c,
                        visibility: "visible"
                    });
                    i = f.getBBox();
                    h ? (w && !s || !w && s) && (c = f.y - i.height) : c = f.y - i.height / 2;
                    h ? (s = e - i.x, e = e + this.width - i.x) : (s = "left" === this.labelAlign ? e : 0, e = "right" === this.labelAlign ? e + this.width : n.chartWidth);
                    f.translateX < s && (p = s - f.translateX);
                    f.translateX + i.width >= e && (p = -(f.translateX + i.width - e));
                    f.attr({
                        x: l + p,
                        y: c,
                        anchorX: h ? l : this.opposite ? 0 : n.chartWidth,
                        anchorY: h ? this.opposite ? n.chartHeight : 0 : c + i.height / 2
                    })
                }
            });
            u.init = function() {
                it.apply(this, arguments);
                this.setCompare(this.options.compare)
            };
            u.setCompare = function(n) {
                this.modifyValue = "value" === n || "percent" === n ? function(t, i) {
                    var r = this.compareValue;
                    if (void 0 !== t && void 0 !== r) return t = "value" === n ? t - r : t / r * 100 - (100 === this.options.compareBase ? 0 : 100), i && (i.change = t), t
                } : null;
                this.userOptions.compare = n;
                this.chart.hasRendered && (this.isDirty = !0)
            };
            u.processData = function() {
                var n, t = -1,
                    u, i, f, r;
                if (rt.apply(this, arguments), this.xAxis && this.processedYData)
                    for (u = this.processedXData, i = this.processedYData, f = i.length, this.pointArrayMap && (t = s("close", this.pointArrayMap), -1 === t && (t = s(this.pointValKey || "y", this.pointArrayMap))), n = 0; n < f - 1; n++)
                        if (r = -1 < t ? i[n][t] : i[n], h(r) && u[n + 1] >= this.xAxis.min && 0 !== r) {
                            this.compareValue = r;
                            break
                        }
            };
            r(u, "getExtremes", function(n) {
                var t;
                n.apply(this, [].slice.call(arguments, 1));
                this.modifyValue && (t = [this.modifyValue(this.dataMin), this.modifyValue(this.dataMax)], this.dataMin = d(t), this.dataMax = k(t))
            });
            i.prototype.setCompare = function(n, i) {
                this.isXAxis || (f(this.series, function(t) {
                    t.setCompare(n)
                }), t(i, !0) && this.chart.redraw())
            };
            v.prototype.tooltipFormatter = function(i) {
                return i = i.replace("{point.change}", (0 < this.change ? "+" : "") + n.numberFormat(this.change, t(this.series.tooltipOptions.changeDecimals, 2))), ut.apply(this, [i])
            };
            r(y.prototype, "render", function(n) {
                this.chart.is3d && this.chart.is3d() || this.chart.polar || !this.xAxis || this.xAxis.isRadial || (!this.clipBox && this.animate ? (this.clipBox = e(this.chart.clipBox), this.clipBox.width = this.xAxis.len, this.clipBox.height = this.yAxis.len) : this.chart[this.sharedClipKey] ? this.chart[this.sharedClipKey].attr({
                    width: this.xAxis.len,
                    height: this.yAxis.len
                }) : this.clipBox && (this.clipBox.width = this.xAxis.len, this.clipBox.height = this.yAxis.len));
                n.call(this)
            })
        }(n), n
}),
function(n) {
    function u(n, t) {
        var l = this,
            f, e, u = [],
            h = [],
            c = [],
            o = [],
            s = [],
            i;
        for (f = r(n, t, 12), e = r(n, t, 26), i = 0; i < f.length; i++) e[i][1] == null ? u.push([n[i], null]) : u.push([n[i], f[i][1] - e[i][1]]);
        for (i = 0; i < u.length; i++) h.push(u[i][0]), c.push(u[i][1]);
        for (o = r(h, c, 9), i = 0; i < u.length; i++) u[i][1] == null ? s.push([u[i][0], null]) : s.push([u[i][0], u[i][1] - o[i][1]]);
        return [u, o, s]
    }

    function o(n, t) {
        for (var o = [], u = 0, a = 0, s = 0, h = 0, f = 0, v = 0, e = 0, c = 0, l = 0, y = 0, p = 0, r = n.length, i = 0; i < r; i++) u = u + n[i] * t[i], s = s + n[i], h = h + t[i], f = f + Math.pow(n[i], 2), e = e + n[i], l = l + t[i];
        return u = r * u, a = s * h, f = r * f, v = Math.pow(e, 2), c = (u - a) / (f - v), y = c * e, p = (l - y) / r, o.push([n[0], t[0]]), step10 = c * n[r - 1] + p, o.push([n[r - 1], step10]), o
    }

    function r(n, t, i) {
        for (var s, u = !1, h = i, c = 2 / (h + 1), l, o = [], e = [], a = t.length, v = n[0], r = 0; r < a; r++) t[r - 1] && e.push(t[r]), h == e.length ? (s = t[r], u ? (l = s * c + u * (1 - c), u = l) : u = f(e), o.push([n[r], u]), e.splice(0, 1)) : o.push([n[r], null]);
        return o
    }

    function s(n, t, i) {
        for (var u = [], e = [], o = t.length, s = n[0], r = 0; r < o; r++) u.push(t[r]), i == u.length ? (e.push([n[r], f(u)]), u.splice(0, 1)) : e.push([n[r], null]);
        return e
    }

    function f(n) {
        for (var t = 0, i = n.length, r = i; r--;) t = t + n[r];
        return t / i
    }
    var e = n.getOptions(),
        t = e.plotOptions,
        i = n.seriesTypes;
    t.trendline = n.merge(t.line, {
        marker: {
            enabled: !1
        },
        tooltip: {
            valueDecimals: 2
        }
    });
    i.trendline = n.extendClass(i.line, {
        type: "trendline",
        animate: null,
        requiresSorting: !1,
        processData: function() {
            var t;
            this.linkedParent && (t = [].concat(this.linkedParent.options.data), this.setData(this.runAlgorithm(), !1));
            n.Series.prototype.processData.call(this)
        },
        runAlgorithm: function() {
            var n = this.linkedParent.xData,
                t = this.linkedParent.yData,
                i = this.options.periods || 100,
                r = this.options.algorithm || "linear";
            return this[r](n, t, i)
        },
        MACD: function(n, t, i) {
            return u(n, t, i)[0]
        },
        signalLine: function(n, t, i) {
            return u(n, t, i)[1]
        },
        SMA: function(n, t, i) {
            return s(n, t, i)
        },
        EMA: function(n, t, i) {
            return r(n, t, i)
        },
        linear: function(n, t, i) {
            return o(n, t, i)
        }
    });
    t.histogram = n.merge(t.column, {
        borderWidth: 0,
        tooltip: {
            valueDecimals: 2
        }
    });
    i.histogram = n.extendClass(i.column, {
        type: "histogram",
        animate: null,
        requiresSorting: !1,
        processData: function() {
            var t;
            this.linkedParent && (t = [].concat(this.linkedParent.options.data), this.setData(this.runAlgorithm(), !1));
            n.Series.prototype.processData.call(this)
        },
        runAlgorithm: function() {
            var n = this.linkedParent.xData,
                t = this.linkedParent.yData,
                i = this.options.periods || 100,
                r = this.options.algorithm || "histogram";
            return this[r](n, t, i)
        },
        histogram: function(n, t, i) {
            return u(n, t, i)[2]
        }
    })
}(Highcharts);
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
    isSideMenuOpen = !0,
    chatModule = new ChatModule(".chat-menu"),
    favoriteMarkets = store.get("favorite-market") || [],
    showFavoriteMarkets = store.get("favorite-market-enabled") || !1,
    marketTableSortColumn = store.get("market-sort-col") || 5,
    marketTableSortDirection = store.get("market-sort-dir") || "desc",
    balanceTableSortColumn = store.get("balance-sort-col") || 1,
    balanceTableSortDirection = store.get("balance-sort-dir") || "asc",
    disableTradeConfirmationModal = store.get("disable-trade-confirmation") || !1;
$("#market-favorite-chk").attr("checked", showFavoriteMarkets);
$("#market-list > tbody").empty();
marketTable = $("#market-list").DataTable({
    dom: "<'row'<'col-sm-12'tr>>",
    order: [
        [marketTableSortColumn, marketTableSortDirection]
    ],
    lengthChange: !1,
    processing: !1,
    bServerSide: !1,
    searching: !0,
    paging: !1,
    scrollX: "100%",
    autoWidth: !1,
    sServerMethod: "POST",
    info: !1,
    language: {
        emptyTable: Resources.Exchange.MarketsLoadingMessage,
        sZeroRecords: Resources.Exchange.MarketsEmptyListMessage,
        search: "",
        searchPlaceholder: Resources.Exchange.MarketsSearchPlaceholder,
        paginate: {
            previous: Resources.General.Previous,
            next: Resources.General.Next
        }
    },
    columnDefs: [{
        targets: [1, 2, 7, 8, 9, 10],
        visible: !1
    }, {
        targets: [0],
        visible: !0,
        sortable: !1,
        render: function(n, t, i) {
            return '<div class="market-favorite market-favorite-' + i[1] + '" data-marketid="' + i[1] + '"><i class="fa fa-ellipsis-v" aria-hidden="true" style="margin-left:5px"><\/i><\/div>'
        }
    }, {
        targets: [3],
        visible: !0,
        render: function(n) {
            return '<div style="display:inline-block"><div class="sprite-small sprite-' + n + '-small-png"><\/div> ' + n + "<\/div>"
        }
    }, {
        targets: [4],
        visible: !0,
        render: function(n, t, i) {
            return '<div class="text-right">' + (+i[9] || 0).toFixed(8) + "<\/div>"
        }
    }, {
        targets: [5],
        visible: !0,
        render: function(n) {
            return '<div class="text-right">' + (+n || 0).toFixed(2) + "<\/div>"
        }
    }, {
        targets: [6],
        visible: !0,
        render: function(n, t, i) {
            return '<div class="text-right ' + (i[4] > 0 ? "text-success" : i[4] < 0 ? "text-danger" : "") + '">' + (+i[4] || 0).toFixed(2) + "%<\/div>"
        }
    }],
    fnRowCallback: function(n, t) {
        var i = t[1] == selectedTradePair.TradePairId ? "info text-bold " : "";
        $(n).data("name", t[2]).data("tradepairid", t[1]).data("market", t[3] + "_" + currentBaseMarket).addClass(i + "currencyData-tradepair currencyData-tradepair-" + t[1])
    }
});
changeBaseMarket(currentBaseMarket);
selectedTradePair.TradePairId && getTradePairInfo(selectedTradePair.TradePairId);
$("#wrapper").on("click", ".currencyData-btn", function() {
    var n = $(this);
    currentBaseMarket = n.data("currency");
    changeBaseMarket(currentBaseMarket);
    marketSummaryView && History.pushState({}, Resources.Exchange.MarketPageTitle + " - Cryptopia", "?baseMarket=" + currentBaseMarket)
});
$("#market-list_wrapper").on("click", ".currencyData-tradepair", function() {
    var n = $(this),
        t = n.data("market"),
        i;
    updateTitle({
        Symbol: t.split("_")[0],
        BaseSymbol: currentBaseMarket,
        Name: n.data("name")
    }, !0);
    marketSummaryView && ($("#market-main").show(), $("#market-summary").hide(), marketSummaryView = !1);
    i = n.data("tradepairid");
    $(".currencyData-tradepair").removeClass("info text-bold");
    n.addClass("info text-bold");
    getTradePairInfo(n.data("tradepairid"));
    t = n.data("market");
    History.pushState({}, Resources.Exchange.MarketPageTitle + " - Cryptopia", "?market=" + t)
});
$("#markets-search").keyup(function() {
    marketTable.search($(this).val()).draw()
});
$("#market-list_wrapper .dataTables_scrollHead th").on("click", function() {
    var n = $(this)[0].cellIndex + 2,
        t = $(this).hasClass("sorting_asc") ? "asc" : "desc";
    store.set("market-sort-col", n);
    store.set("market-sort-dir", t)
});
$("#market-favorite-chk").click(function() {
    var n = $(this);
    showFavoriteMarkets = n.is(":checked");
    store.set("favorite-market-enabled", showFavoriteMarkets);
    marketTable.draw()
});
$("#market-list").on("click", ".market-favorite", function(n) {
    var t, r, i;
    if (n.stopPropagation(), t = $(this), r = t.data("marketid"), t.hasClass("market-favorite-active"))
        for (t.removeClass("market-favorite-active"), i = favoriteMarkets.length - 1; i >= 0; i--) favoriteMarkets[i] === r && (favoriteMarkets.splice(i, 1), store.set("favorite-market", favoriteMarkets));
    else t.addClass("market-favorite-active"), favoriteMarkets.push(r), store.set("favorite-market", favoriteMarkets);
    showFavoriteMarkets && marketTable.draw()
});
$(".menu-btn").on("click", function() {
    toggleSideMenu()
});
$(".exchange-menu-btn").on("click", function() {
    $(".balance-menu, .orders-menu, .chat-menu").hide();
    $(".balance-menu-btn, .orders-menu-btn, .chat-menu-btn").removeClass("active");
    $(".exchange-menu").show();
    $(".exchange-menu-btn").addClass("active");
    isSideMenuOpen || toggleSideMenu()
});
$(".balance-menu-btn").on("click", function() {
    $(".exchange-menu, .orders-menu, .chat-menu").hide();
    $(".exchange-menu-btn, .orders-menu-btn, .chat-menu-btn").removeClass("active");
    $(".balance-menu").show();
    $(".balance-menu-btn").addClass("active");
    isSideMenuOpen || toggleSideMenu();
    setupBalances()
});
$(".orders-menu-btn").on("click", function() {
    $(".balance-menu, .exchange-menu, .chat-menu").hide();
    $(".balance-menu-btn, .exchange-menu-btn, .chat-menu-btn").removeClass("active");
    $(".orders-menu").show();
    $(".orders-menu-btn").addClass("active");
    isSideMenuOpen || toggleSideMenu();
    setupOpenOrders()
});
$(".chat-menu-btn").on("click", function() {
    $(".balance-menu, .exchange-menu, .orders-menu").hide();
    $(".balance-menu-btn, .exchange-menu-btn, .orders-menu-btn").removeClass("active");
    $(".chat-menu").show();
    $(".chat-menu-btn").addClass("active");
    isSideMenuOpen || toggleSideMenu();
    setupChatList();
    enableChat()
});
currentTradePairGroupId = null;
$.connection.hub.stateChanged(function(n) {
    n.newState == $.signalR.connectionState.connected && selectedTradePair && selectedTradePair.TradePairId && SetTradePairSubscription(selectedTradePair.TradePairId)
});
notificationHub.client.SendTradeDataUpdate = function(n) {
    n.DataType == 3 && updateMarketItem(n);
    n.TradePairId == selectedTradePair.TradePairId && (n.DataType == 0 ? updateOrderbook(n) : n.DataType == 1 ? addMarketHistory(n) : n.DataType == 3 && updateTicker(n))
};
notificationHub.client.SendUserTradeDataUpdate = function(n) {
    n.DataType == 2 && sideMenuOpenOrdersTable && updateOpenOrders(n);
    n.DataType == 4 && updateBalance(n.TradePairId, !0);
    selectedTradePair && n.TradePairId == selectedTradePair.TradePairId && (n.DataType == 1 && addUserTradeHistory(n), n.DataType == 2 && updateUserOpenOrders(n))
};
$("#balance-search").keyup(function() {
    sideMenuBalanceTable.search($(this).val()).draw()
});
$("#sideMenu-balance-hidezero").click(function() {
    var n = $(this).is(":checked");
    postJson(actionHideZeroBalances, {
        hide: n
    });
    showZeroBalances = !n;
    sideMenuBalanceTable.draw()
});
$("#sideMenu-balance-favorites").click(function() {
    var n = $(this).is(":checked");
    postJson(actionShowFavoriteBalances, {
        show: n
    });
    showFavoriteBalances = n;
    sideMenuBalanceTable.draw()
});
$("#userBalances").on("click", ".balance-favorite", function(n) {
    var t, i;
    n.stopPropagation();
    var r = $(this).data("balanceid"),
        u = sideMenuBalanceTable.rows().data(),
        f = $("#userBalances .balanceid-" + r);
    for (t = 0; t < u.length; t++)
        if (i = u[t], i[0] == r) {
            i[8] = !i[8];
            sideMenuBalanceTable.row(f).invalidate().draw();
            postJson(actionSetFavoriteBalance, {
                currencyId: r
            });
            break
        }
});
$.fn.dataTable.ext.search.push(balanceFilter);
$("#openorders-search").keyup(function() {
    sideMenuOpenOrdersTable.search($(this).val()).draw()
});
$.fn.dataTable.ext.search.push(marketFavoriteFilter);
$(window).resize(function() {
    setupMarketList(!0);
    setupChatList();
    setupBalanceList();
    setupOrderList();
    marketSummaryView ? adjustTableHeaders(marketSummaryTables[currentBaseMarket]) : (adjustTableHeaders(buyOrdersTable), adjustTableHeaders(sellOrdersTable), adjustTableHeaders(marketHistoryTable), adjustTableHeaders(userOpenOrdersTable), adjustTableHeaders(userOrderHistoryTable), setSellVolumeIndicator(), setBuyVolumeIndicator(), resizeCharts())
});
$(document).on("click", ".dropdown-menu", function(n) {
    n.stopPropagation()
});
$("#useropenorders, #sideMenuOpenOrders").on("click", ".trade-item-remove", function() {
    var n = $(this).data("orderid"),
        t = $(this).data("tradepairid") || selectedTradePair.TradePairId;
    n > 0 && t > 0 && cancelOrder(n, t)
});
$(".panel-container-useropenorders").on("click", ".trade-items-remove", function() {
    var n = selectedTradePair.TradePairId;
    n > 0 && cancelTradePairOrders(n)
});
$("#buysubmit").on("click", function() {
    var e = $(this),
        n = new Decimal($("#buyprice").val()),
        t = new Decimal($("#buyamount").val()),
        i = new Decimal($("#buytotal").val()),
        r = new Decimal($("#userBalanceBuy").text()),
        f = new Decimal(selectedTradePair.BaseMinTrade),
        u;
    if (n.lessThan(1e-8)) {
        sendNotification(Resources.Exchange.TradeNotificationTitle, String.format(Resources.Exchange.TradeMinPriceError, "0.00000001"), 2);
        return
    }
    if (t.lessThan(1e-8)) {
        sendNotification(Resources.Exchange.TradeNotificationTitle, String.format(Resources.Exchange.TradeMinPriceError, "0.00000001"), 2);
        return
    }
    if (i.lessThan(f)) {
        sendNotification(Resources.Exchange.TradeNotificationTitle, String.format(Resources.Exchange.TradeMinTotalError, selectedTradePair.BaseMinTrade, selectedTradePair.BaseSymbol), 2);
        return
    }
    if (r.isZero() || i.greaterThan(r)) {
        sendNotification(Resources.Exchange.TradeNotificationTitle, String.format(Resources.Exchange.TradeInsufficientFundsError, selectedTradePair.BaseSymbol), 2);
        return
    }
    u = {
        IsBuy: !0,
        Price: n.toFixed(8),
        Amount: t.toFixed(8),
        TradePairId: selectedTradePair.TradePairId
    };
    $(".buysell-button-loading").show();
    $("#sellsubmit, #buysubmit").attr("disabled", "disabled");
    sendNotification(Resources.Exchange.TradeNotificationTitle, Resources.Exchange.TradeBuyOrderSubmittedMessage);
    postJson(actionSubmitTrade, u, function(n) {
        $(".buysell-button-loading").hide();
        $("#sellsubmit, #buysubmit").removeAttr("disabled", "disabled");
        n.Message && sendNotification(Resources.Exchange.TradeNotificationTitle, n.Message, 2)
    })
});
$("#sellsubmit").on("click", function() {
    var e = $(this),
        t = new Decimal($("#sellprice").val()),
        n = new Decimal($("#sellamount").val()),
        u = new Decimal($("#selltotal").val()),
        i = new Decimal($("#userBalanceSell").text()),
        f = new Decimal(selectedTradePair.BaseMinTrade),
        r;
    if (t.lessThan(1e-8)) {
        sendNotification(Resources.Exchange.TradeNotificationTitle, String.format(Resources.Exchange.TradeMinPriceError, "0.00000001"), 2);
        return
    }
    if (n.lessThan(1e-8)) {
        sendNotification(Resources.Exchange.TradeNotificationTitle, String.format(Resources.Exchange.TradeMinPriceError, "0.00000001"), 2);
        return
    }
    if (u.lessThan(f)) {
        sendNotification(Resources.Exchange.TradeNotificationTitle, String.format(Resources.Exchange.TradeMinTotalError, selectedTradePair.BaseMinTrade, selectedTradePair.BaseSymbol), 2);
        return
    }
    if (i.isZero() || n.greaterThan(i)) {
        sendNotification(Resources.Exchange.TradeNotificationTitle, String.format(Resources.Exchange.TradeInsufficientFundsError, selectedTradePair.Symbol), 2);
        return
    }
    r = {
        IsBuy: !1,
        Price: t.toFixed(8),
        Amount: n.toFixed(8),
        TradePairId: selectedTradePair.TradePairId
    };
    $(".buysell-button-loading").show();
    $("#sellsubmit, #buysubmit").attr("disabled", "disabled");
    sendNotification(Resources.Exchange.TradeNotificationTitle, Resources.Exchange.TradeSellOrderSubmittedMessage);
    postJson(actionSubmitTrade, r, function(n) {
        $(".buysell-button-loading").hide();
        $("#sellsubmit, #buysubmit").removeAttr("disabled", "disabled");
        n.Message && sendNotification(Resources.Exchange.TradeNotificationTitle, n.Message, 2)
    })
});
$("#buyamount").on("keyup paste change", function() {
    var n = new Decimal($("#buyprice").val()),
        t = new Decimal($(this).val()),
        i = $("#buytotal"),
        r = n.mul(t);
    i.val(r.toFixed(8))
});
$("#buyprice").on("keyup paste change", function() {
    var n = new Decimal($(this).val()),
        t = new Decimal($("#buyamount").val()),
        i = n.mul(t),
        r = $("#buytotal");
    r.val(i.toFixed(8))
});
$("#buytotal").on("keyup paste change", function() {
    var n = new Decimal($(this).val()),
        t = new Decimal($("#buyprice").val()),
        i = n.div(t),
        r = $("#buyamount");
    r.val(i.toFixed(8))
});
$("#sellamount").on("keyup paste change", function() {
    var n = new Decimal($("#sellprice").val()),
        t = new Decimal($(this).val()),
        i = $("#selltotal");
    i.val(n.mul(t).toFixed(8))
});
$("#sellprice").on("keyup paste change", function() {
    var n = new Decimal($(this).val()),
        t = new Decimal($("#sellamount").val()),
        i = $("#selltotal");
    i.val(n.mul(t).toFixed(8))
});
$("#selltotal").on("keyup paste change", function() {
    var n = new Decimal($(this).val()),
        t = new Decimal($("#sellprice").val()),
        i = n.div(t),
        r = $("#sellamount");
    r.val(i.toFixed(8))
});
$("#buyamount, #buyprice, #sellamount, #sellprice").on("keyup change", function() {
    truncateInputDecimals($(this), 8);
    calculateFee(!0)
});
$("#buyamount, #buyprice, #sellamount, #sellprice").on("blur", function() {
    truncateInputDecimals($(this), 0);
    calculateFee(!0)
});
$("#buynettotal, #sellnettotal").on("blur", function() {
    truncateInputDecimals($(this), 0);
    calculateFee(!1)
});
$("#buynettotal").on("keyup paste change", function() {
    var n = new Decimal($(this).val()),
        t = new Decimal($("#buyprice").val());
    if (n.greaterThan(0) && t.greaterThan(0)) {
        var i = new Decimal(.2).div(100).plus(1),
            r = n.div(i),
            u = r.div(t);
        $("#buyamount").val(u.toFixed(8));
        calculateFee(!1)
    }
});
$("#sellnettotal").on("keyup paste change", function() {
    var n = new Decimal($(this).val()),
        t = new Decimal($("#sellprice").val());
    if (n.greaterThan(0) && t.greaterThan(0)) {
        var i = new Decimal(99.8).div(100),
            r = n.div(i),
            u = r.div(t);
        $("#sellamount").val(u.toFixed(8));
        calculateFee(!1)
    }
});
$("#buyorders").on("click", "tr", function() {
    var u = $(this),
        n = u.find("td:nth-child(2)").text(),
        t = 0,
        i, r;
    $("#buyorders > tbody  > tr").each(function() {
        var i = $(this),
            r = +i.find("td:nth-child(2)").text();
        r >= n && (t += +i.find("td:nth-child(3)").text())
    });
    i = new Decimal(t);
    r = new Decimal(n);
    $("#buyprice, #sellprice").val(r.toFixed(8));
    $("#buyamount, #sellamount").val(i.toFixed(8));
    calculateFee(!0)
});
$("#sellorders").on("click", "tr", function() {
    var u = $(this),
        n = u.find("td:nth-child(2)").text(),
        t = 0,
        i, r;
    $("#sellorders > tbody  > tr").each(function() {
        var i = $(this),
            r = +i.find("td:nth-child(2)").text();
        r <= n && (t += +i.find("td:nth-child(3)").text())
    });
    i = new Decimal(t);
    r = new Decimal(n);
    $("#buyprice, #sellprice").val(r.toFixed(8));
    $("#buyamount, #sellamount").val(i.toFixed(8));
    calculateFee(!0)
});
$("#userBalanceBuy").on("click", function() {
    var n = new Decimal($(this).text()),
        t = new Decimal($("#buyprice").val());
    if (t.greaterThan(0) && n.greaterThan(0)) {
        var i = new Decimal(.2).div(100).plus(1),
            r = n.div(i),
            u = r.div(t);
        $("#buyamount").val(u.toFixed(8));
        calculateFee(!0)
    }
});
$("#userBalanceSell").on("click", function() {
    var n = new Decimal($(this).text()),
        t = new Decimal($("#sellprice").val());
    t.greaterThan(0) && n.greaterThan(0) && ($("#sellamount").val(n.toFixed(8)), calculateFee(!0))
});
$("#sell-first-amount").on("click", function() {
    var n = $("#buyorders > tbody > tr:first > td:nth-child(3)").text();
    n && $("#sellamount, #buyamount").val(n).trigger("change")
});
$("#sell-first-price").on("click", function() {
    var n = $("#buyorders > tbody > tr:first > td:nth-child(2)").text();
    n && $("#sellprice, #buyprice").val(n).trigger("change")
});
$("#sell-total-amount").on("click", function() {
    var n = $("#buyorders > tbody > tr:first > td:nth-child(2)").text();
    n && ($("#sellprice").val(n), $("#userBalanceSell").trigger("click"))
});
$("#buy-first-amount").on("click", function() {
    var n = $("#sellorders > tbody > tr:first > td:nth-child(3)").text();
    n && $("#buyamount, #sellamount").val(n).trigger("change")
});
$("#buy-first-price").on("click", function() {
    var n = $("#sellorders > tbody > tr:first > td:nth-child(2)").text();
    n && $("#buyprice, #sellprice").val(n).trigger("change")
});
$("#buy-total-amount").on("click", function() {
    var n = $("#sellorders > tbody > tr:first > td:nth-child(2)").text();
    n && ($("#buyprice").val(n), $("#userBalanceBuy").trigger("click"))
});
$(".chart-option-chart").on("click", function() {
    selectedChart = "trade";
    $(".chart-option-btn").removeClass("active");
    $("#chart-orderbook, #chart-distribution, .chart-options-dropdown").hide();
    $("#chart-trade, .chart-options-dropdown-trade").show();
    $(this).addClass("active");
    updateTradeChart()
});
$(".chart-option-orderbook").on("click", function() {
    selectedChart = "orderbook";
    $(".chart-option-btn").removeClass("active");
    $("#chart-trade, #chart-distribution, .chart-options-dropdown").hide();
    $("#chart-orderbook, .chart-options-dropdown-orderbook").show();
    $(this).addClass("active");
    updateOrderBookChart()
});
$(".chart-option-distribution").on("click", function() {
    selectedChart = "distribution";
    $(".chart-option-btn").removeClass("active");
    $("#chart-orderbook, #chart-trade, .chart-options-dropdown").hide();
    $("#chart-distribution, .chart-options-dropdown-distribution").show();
    $(this).addClass("active");
    updateDistributionChart()
});
$('[name="chart-orderbook-options"]').on("click", function() {
    var n = $(this).val();
    orderBookChartPercent = n;
    updateOrderBookChart()
});
$('[name="chart-distribution-options"]').on("click", function() {
    distributionChartCount = $(this).val();
    updateDistributionChart()
});
$("#chart-options").on("click", ".chart-options-dropdown-trade .chart-extras", function() {
    var i = $(this),
        t = i.closest(".chart-extras-container"),
        r = t.data("series"),
        u = t.find(".chart-extras-update"),
        n = t.find(".chart-extras-value"),
        f = n.val() > 0 ? n.val() : 1,
        e = i.is(":checked");
    if (e) {
        n.removeAttr("disabled");
        u.removeAttr("disabled");
        toggleSeries(r, f, !0);
        return
    }
    n.attr("disabled", "disabled");
    u.attr("disabled", "disabled");
    toggleSeries(r, f, !1)
});
$("#chart-options").on("click", ".chart-options-dropdown-trade  .chart-extras-update", function() {
    var i = $(this),
        n = i.closest(".chart-extras-container"),
        r = n.data("series"),
        t = n.find(".chart-extras-value"),
        u = t.val() > 0 ? t.val() : 1;
    toggleSeries(r, u, !0)
});
$("#chart-options").on("click", ".chart-options-save", function() {
    saveChartSettings()
});
$(".chart-range-group").on("click", ".btn-default", function() {
    var n = $(this).data("range");
    $(".chart-range-group > .btn-default").removeClass("active");
    $(this).addClass("active");
    updateSeriesRange(n)
});
$(".chart-candles-group").on("click", ".btn-default", function() {
    var n = $(this).data("candles");
    $(".chart-candles-group > .btn-default").removeClass("active");
    $(this).addClass("active");
    updateChartData(selectedSeriesRange, n)
});
$("#chartdata").mousemove(drawHorizontalCrosshair);
$(".chart-container").height(fullChart ? 565 : 365);
$("#chart-extras-candlestick")[0].checked = candlestickChart;
$("#chart-extras-candlestick").parent().find("label > .fa-circle").css({
    color: candlestickChartUpColor
});
$("#chart-extras-stockprice")[0].checked = stockPriceChart;
$("#chart-extras-stockprice").parent().find("label > .fa-circle").css({
    color: stockPriceChartColor
});
$("#chart-extras-volume")[0].checked = volumeChart;
$("#chart-extras-volume").parent().find("label > .fa-circle").css({
    color: volumeChartColor
});
$("#chart-extras-macd")[0].checked = macdChart;
$("#chart-extras-macd").parent().find("label > .fa-circle").css({
    color: macdChartColor
});
$("#chart-extras-signal")[0].checked = signalChart;
$("#chart-extras-signal").parent().find("label > .fa-circle").css({
    color: signalChartColor
});
$("#chart-extras-histogram")[0].checked = histogramChart;
$("#chart-extras-histogram").parent().find("label > .fa-circle").css({
    color: histogramChartUpColor
});
$("#chart-extras-fibonacci")[0].checked = fibonacciChart;
$("#chart-extras-fibonacci").parent().find("label > .fa-circle").css({
    color: fibonacciChartColor
});
$("#chart-extras-sma")[0].checked = smaChart;
$("#chart-extras-sma-value").val(smaChartValue);
$("#chart-extras-sma-value")[0].disabled = !smaChart;
$("#chart-extras-sma").parent().find("label > .fa-circle").css({
    color: smaChartColor
});
$("#chart-extras-ema1")[0].checked = ema1Chart;
$("#chart-extras-ema1-value").val(ema1ChartValue);
$("#chart-extras-ema1-value")[0].disabled = !ema1Chart;
$("#chart-extras-ema1").parent().find("label > .fa-circle").css({
    color: ema1ChartColor
});
$("#chart-extras-ema2")[0].checked = ema2Chart;
$("#chart-extras-ema2-value").val(ema2ChartValue);
$("#chart-extras-ema2-value")[0].disabled = !ema2Chart;
$("#chart-extras-ema2").parent().find("label > .fa-circle").css({
    color: ema2ChartColor
});
$('.chart-options-dropdown-orderbook [value="' + orderBookChartPercent + '"]')[0].checked = !0;
$('.chart-options-dropdown-distribution [value="' + distributionChartCount + '"]')[0].checked = !0;
candlestickChart && $(".chart-candlestick-item").show();
volumeChart && $(".chart-volume-item").show();
stockPriceChart && $(".chart-stockprice-item").show();
histogramChart && $(".chart-histogram-item").show();
fibonacciChart && $(".chart-fibonacci-item").show();
macdChart && $(".chart-macd-item").show();
signalChart && $(".chart-signal-item").show();
smaChart && $(".chart-sma-item").show();
ema1Chart && $(".chart-ema1-item").show();
ema2Chart && $(".chart-ema2-item").show()