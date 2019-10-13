var fs = require('fs');
var express = require('express');
var uuid = require('uuid4');
var app = express();
var https = require('https');
var log4js = require('log4js');
//var crypto = require('crypto');
var dateFormat = require('dateformat');
var request = require('request');
var server = https.createServer({
    key: fs.readFileSync('/etc/nginx/hysiope_cert/hysiope_com.key'),
    cert: fs.readFileSync('/etc/nginx/hysiope_cert/hysiope_com.crt'),
    ca: fs.readFileSync('/etc/nginx/hysiope_cert/hysiope_com_bundle.crt'),
    requestCert: false,
    rejectUnauthorized: false
}, app); 
// var server = http.createServer({
    // rejectUnauthorized: false
// }, app);
const { Client } = require('pg')
const pgclient = new Client({
    user: 'liteconstruct',
    host: 'localhost',
    database: 'liteconstruct_db',
    password: '9D!aD_GUZ;VU5Wst' 
})
// var currentCurrencyID = null;
// var baseCurrencyID = null;
// var userID = null;

pgclient.connect()

// pgclient.query('SELECT $1::text as message', ['Hello world!'], (err, res) => {
// console.log(err ? err.stack : res.rows[0].message) // Hello World!
// pgclient.end()
// })

var io = require('socket.io')(server); // Подключаем socket.io и указываем на сервер
// Подключаем наш логгер
var logger = log4js.getLogger(); // Подключаем с модуля log4js сам логгер
log4js.configure({
    appenders: {cheese: {type: 'file', filename: 'data/log/engine.log'}},
    categories: {default: {appenders: ['cheese'], level: 'debug'}}
});
var port = 3334; // Можно любой другой порт
logger.level = 'debug';
//logger.debug('Script has been started...'); // Логгируем.

server.listen(port, function () {
    console.log('listening on *:' + port);
}); // Теперь мы можем подключиться к нашему серверу через localhost:port при запущенном скрипте

//app.use(express.static(__dirname + '/socket.io')); // Отправляет "статические" файлы из папки public при коннекте // __dirname - путь по которому лежит chat.js

//users connections
var currentConnections = new Map();
var usedMarkets = new Map();
//var chartArrayCash = new Map();

io.on('connection', function (socket) {

    console.log('a user connected: ' + socket.id + ", ip: " + socket.request.connection.remoteAddress.split('::ffff:').join(''));

    socket.on('disconnect', function () {
        console.log('user disconnected');
        if (currentConnections.has(socket.id)) {
            currentConnections.delete(socket.id);
        }
    });

    socket.on('my_ping', function (data) {
		//console.log('my_ping')
        //console.log(currentConnections.get(socket.id))
        io.emit('my_pong', '');
    });

    socket.on('subscribe', function (data) {
        var remoteAddress = socket.request.connection.remoteAddress.split('::ffff:').join('');
        //console.log('catch ping value');
        console.log('subscribe data: ' + data + ", ip: " + remoteAddress);
        //console.log(socket.id);

        if (data !== undefined && data !== null) {
            try {
                //currentCurrencyID = null;
                //baseCurrencyID = null;
                //userID = null;
                var allJson = JSON.parse(data);
                //console.log(allJson);
                expr = /script|\*|<|>|<|>|\\\\|\/|UPDATE|INSERT/i;
                if (allJson.log.match(expr) !== null || allJson.market == undefined || allJson.market.match(expr) !== null || allJson.company == undefined || allJson.company.match(expr) !== null || allJson.service == undefined || allJson.service.match(expr) !== null) {
                    //if (allJson.error.match(expr) !== null) {
                    fs.appendFile('data/log/engine.log', dateFormat(new Date(), "yyyy/mm/dd HH:MM:ss") + " - [" + remoteAddress + "] exchanger: " + allJson + '\n', (err) => {
                        if (err) {
                            console.log(err);
                        }
                        //logger.debug(dateFormat(new Date(), "yyyy/mm/dd HH:MM:ss") + " - [" + remoteAddress + "] exchanger: " + allJson.log);
                    });

                    //fs.appendFileSync('/var/www/msp-coin-dev/log/fieldinputhack.log', dateFormat(new Date(), "yyyy/mm/dd HH:MM:ss") + " - [" + remoteAddress + "] exchanger: " + allJson.log);
                } else {
                    if (currentConnections.has(socket.id)) {
                        //var currenObject = currentConnections.get(socket.id);
                        //currentConnections.delete(socket.id);
                        //currentConnections.set(socket.id, [allJson.log, new Date(), remoteAddress, allJson.market, allJson.company, allJson.service, currentCurrencyID, baseCurrencyID]);
                        currentConnections.get(socket.id) = [allJson.log, new Date(), remoteAddress, allJson.market, allJson.company, allJson.service, null, null, null];
                    }else{
						for (var key of currentConnections.keys()) {
							if(currentConnections.get(key)[0] == allJson.log){
								currentConnections.delete(key);
								break;
							}
						}
                        currentConnections.set(socket.id, [allJson.log, new Date(), remoteAddress, allJson.market, allJson.company, allJson.service, null, null, null]);
                    }
					//console.log('found socket: ' + currentConnections.has(socket.id))
					updateUsersSocket(socket.id, allJson.log, allJson.market, allJson.company, allJson.service, remoteAddress)
                }

            } catch (io) {
                console.log('error present!');
                console.log(io);
            }
        }
    });

	// socket.on('order_operation', function (data) {
		// var socketID = socket.id;
		// //console.log(data)
		// if (currentConnections.has(socketID) && currentConnections.get(socketID)[4] != null && currentConnections.get(socketID)[5] != null && currentConnections.get(socketID)[6] != null && currentConnections.get(socketID)[7] != null && currentConnections.get(socketID)[8] != null) {
			// setRequestToDB(socketID, currentConnections.get(socketID)[4], currentConnections.get(socketID)[5], currentConnections.get(socketID)[6], currentConnections.get(socketID)[7], currentConnections.get(socketID)[8], data);
		// }
    // });
	
	socket.on('yours_history', function (data) {
		var socketID = socket.id;
		//console.log(data)
		if (currentConnections.has(socketID) && currentConnections.get(socketID)[4] != null && currentConnections.get(socketID)[5] != null && currentConnections.get(socketID)[6] != null && currentConnections.get(socketID)[7] != null && currentConnections.get(socketID)[8] != null) {
			getOrdersHistory(socketID, currentConnections.get(socketID)[4], currentConnections.get(socketID)[5], currentConnections.get(socketID)[6], currentConnections.get(socketID)[7], currentConnections.get(socketID)[8])
		}
    });
	
	socket.on('trade_history', function (data) {
		var socketID = socket.id;
		//console.log(data)
		if (currentConnections.has(socketID) && currentConnections.get(socketID)[4] != null && currentConnections.get(socketID)[5] != null && currentConnections.get(socketID)[6] != null && currentConnections.get(socketID)[7] != null && currentConnections.get(socketID)[8] != null) {
			getCommonHistory(socketID, currentConnections.get(socketID)[4], currentConnections.get(socketID)[5], currentConnections.get(socketID)[6], currentConnections.get(socketID)[7])
		}
    });
	
	socket.on('chart_data', function (data) {
        //console.log('catch chart_data: ');
        //console.log(JSON.parse(data));
		var work = JSON.parse(data);
		getChartInfoByMarketID(socket.id, work.marketid, work.history);
	});
});

function updateUsersSocket(socketID, hash, market, company, service, remoteAddress){
	pgclient.query('SELECT coinmarkets_data_use.basecurrencyid, coinmarkets_data_use.currentcurrencyid FROM coinmarkets_data_use join coinmarkets_record_own on coinmarkets_record_own.id = coinmarkets_data_use.id WHERE name = \'' + market + '\' and coinmarkets_record_own.company_id = \'' + company + '\' and coinmarkets_record_own.service_id = \'' + service + '\' limit 1', (err, res) => {
		if (err || res.rowCount < 1) {
			//baseCurrencyID = null;
			//currentCurrencyID = null;
			currentConnections.delete(socketID);
		}
		else {
			//console.log('found values: ' + res.rows[0].currentcurrencyid)
			currentConnections.get(socketID)[6] = res.rows[0].currentcurrencyid;
			currentConnections.get(socketID)[7] = res.rows[0].basecurrencyid;
			// var usersql = 'SELECT uid FROM site_contact_auth WHERE hash = \'' + hash + '\' AND ip = \'' + remoteAddress + '\' limit 1';
			// //console.log(usersql)
			// pgclient.query(usersql, (err, res) => {
				// if (err || res.rowCount < 1) {
					// //userID = null;
					// currentConnections.delete(socketID);
				// }
				// else {
					// currentConnections.get(socketID)[8] = res.rows[0].uid;
					// if(currentConnections.get(socketID)[6] == null || currentConnections.get(socketID)[7] == null || currentConnections.get(socketID)[8] == null){
						// console.log('not found: ' + currentConnections.get(socketID)[6] + ', ' + currentConnections.get(socketID)[7] + ', ' + currentConnections.get(socketID)[8])
						// //currentConnections.delete(socketID);
						// return;
					// }else{
						// //console.log('for['+allJson.log+'] - found: ')
						// //console.log(currentConnections.get(socketID))
					// }
					// //currentConnections.set(socketID, [allJson.log, new Date(), remoteAddress, allJson.market, allJson.company, allJson.service, currentCurrencyID, baseCurrencyID, userID]);
					// // getBalances(socketID, company, service, currentConnections.get(socketID)[6], currentConnections.get(socketID)[7], currentConnections.get(socketID)[8])
					// // getMyOrders(socketID, company, service, currentConnections.get(socketID)[6], currentConnections.get(socketID)[7], currentConnections.get(socketID)[8])
					// // getOrdersHistory(socketID, company, service, currentConnections.get(socketID)[6], currentConnections.get(socketID)[7], currentConnections.get(socketID)[8]);
				// }
			// })
			//baseCurrencyID = res.rows[0].basecurrencyid;
			//currentCurrencyID = res.rows[0].currentcurrencyid;
		}

	})
	
}

function getBalances(socketID, company, service, currentCurrencyID, baseCurrencyID, userID) {
	var precision = 8;
	var sql = 'SELECT trunc(walletcoin_data_use.balance::numeric, ' + precision + ') as balance, walletcoin_data_use.currencyid FROM walletcoin_data_use join walletcoin_record_own on walletcoin_record_own.id = walletcoin_data_use.id where (walletcoin_data_use.currencyid = \'' + currentCurrencyID + '\' or walletcoin_data_use.currencyid = \'' + baseCurrencyID + '\') and walletcoin_record_own.company_id = \'' + company + '\' and walletcoin_record_own.service_id = \'' + service + '\' and walletcoin_record_own.contact_id = \'' + userID + '\'';
	//console.log(sql)
    pgclient.query(sql, (err, res) => {
        //console.log('SELECT * FROM walletcoin_data_use join walletcoin_record_own on walletcoin_record_own.id = walletcoin_data_use.id where (walletcoin_data_use.currencyid = \'' + currentCurrencyID + '\' or walletcoin_data_use.currencyid = \'' + baseCurrencyID + '\') and walletcoin_record_own.company_id = \'' + company + '\' and walletcoin_record_own.service_id = \'' + service + '\' and walletcoin_record_own.contact_id = \'' + userID + '\'')
        //console.log(res.rowCount)
        var basecurrency = 0;
        var currency = 0;
        if(err){
            basecurrency = 0;
            currency = 0;
        }else{
            if(res.rowCount > 0 && res.rows[0].currencyid == baseCurrencyID){
                basecurrency = res.rows[0].balance;
            }else if(res.rowCount > 1 && res.rows[1].currencyid == baseCurrencyID){
                basecurrency = res.rows[1].balance;
            }else{
                basecurrency = 0;
            }
            if(res.rowCount > 0 && res.rows[0].currencyid == currentCurrencyID){
                currency = res.rows[0].balance;
            }else if(res.rowCount > 1 && res.rows[1].currencyid == currentCurrencyID){
                currency = res.rows[1].balance;
            }else{
                currency = 0;
            }
        }
        //console.log('for ['+socketID+'] '+session + ' {"data": {"basecurrency": "' + basecurrency + '", "currency": "' + currency + '"}}')
        io.to(socketID).emit('my_balance', ('{"data": {"basecurrency": "' + basecurrency + '", "currency": "' + currency + '"}}'));

    })

}

function getMyOrders(socketID, company, service, currentCurrencyID, baseCurrencyID, userID) {
	var precision = 8;
    var fieldQueryes = 'trunc(cryptoorders_data_use.amount::numeric, ' + precision + ') as amount, trunc(cryptoorders_data_use.baseamount::numeric, ' + precision + ') as baseamount, to_char(to_timestamp(cryptoorders_data_use.date_create)::timestamp,\'YYYY-MM-DD HH24:MI:SS.ms\') as datestamp, cryptoorders_data_use.id as orderid, cryptoorders_data_use.tradetype as ordertype, trunc(cryptoorders_data_use.price::numeric, ' + precision + ') as price, trunc(cryptoorders_data_use.baseamount::numeric, ' + precision + ') as basevalue, cryptoorders_data_use.date_create as unix_t_orderdate ';
	var sql = 'SELECT jsonb_agg (query) from (SELECT ' + fieldQueryes + ' FROM cryptoorders_data_use join cryptoorders_record_own on cryptoorders_record_own.id = cryptoorders_data_use.id where cryptoorders_data_use.currencyid = \'' + currentCurrencyID + '\' and cryptoorders_data_use.basecurrencyid = \'' + baseCurrencyID + '\' and cryptoorders_record_own.company_id = \'' + company + '\' and cryptoorders_record_own.service_id = \'' + service + '\' and cryptoorders_record_own.contact_id = \'' + userID + '\' order by cryptoorders_data_use.date_create desc limit 100)query';
	pgclient.query(sql, (err, res) => {
        var myOrdersResult = '';
        if (err || res.rowCount < 1 || res.rows[0].jsonb_agg == null) {
            myOrdersResult = '[]';
        } else
			myOrdersResult = JSON.stringify(res.rows[0].jsonb_agg);
		io.to(socketID).emit('my_orders', ('{"data": {"status": "ok", "type": "MyOrders", "orders": ' + myOrdersResult + '}}'));
    }); 
}
 
function getOrdersHistory(socketID, company, service, currentCurrencyID, baseCurrencyID, userID) {
	var precision = 8;
	var fieldQueryes = 'trunc(cryptotransactions_data_use.baseamount::numeric, ' + precision + ') as basevolume, trunc(cryptotransactions_data_use.price::numeric, ' + precision + ') as price, to_char(to_timestamp(cryptotransactions_data_use.date_create)::timestamp,\'YYYY-MM-DD HH24:MI:SS.ms\') as ticker, cryptotransactions_data_use.id as tickerid, cryptotransactions_data_use.tradetype as tradetype, cryptotransactions_data_use.date_create as unix_t_orderdate, trunc(cryptotransactions_data_use.amount::numeric, ' + precision + ') as volume ';
	
	var sql = 'SELECT jsonb_agg (query) from (SELECT ' + fieldQueryes + ' FROM cryptotransactions_data_use join cryptotransactions_record_own on cryptotransactions_record_own.id = cryptotransactions_data_use.id where cryptotransactions_data_use.currencyid = \'' + currentCurrencyID + '\' and cryptotransactions_data_use.basecurrencyid = \'' + baseCurrencyID + '\' and cryptotransactions_record_own.company_id = \'' + company + '\' and cryptotransactions_record_own.service_id = \'' + service + '\' and (cryptotransactions_data_use.ownercurrencyid = \'' + userID + '\' or cryptotransactions_data_use.ownerbasecurrencyid = \'' + userID + '\') order by cryptotransactions_data_use.date_create desc limit 100)query';
	//console.log(sql)
	pgclient.query(sql, (err, res) => {
        //console.log(sql)
        //return;
        var historyResult = '';
        if (err || res.rowCount < 1 || res.rows[0].jsonb_agg == null) {
            historyResult = '[]';
        } else
			historyResult = JSON.stringify(res.rows[0].jsonb_agg);
		io.to(socketID).emit('yours_history', ('{"data": {"status": "ok", "history_order": [' + 0 + '], "history": ' + historyResult + '}}'));
    }); 
}

function getCommonHistory(socketID, company, service, currentCurrencyID, baseCurrencyID) {
	var precision = 8;
	var fieldQueryes = 'trunc(cryptotransactions_data_use.baseamount::numeric, ' + precision + ') as basevolume, trunc(cryptotransactions_data_use.price::numeric, ' + precision + ') as price, to_char(to_timestamp(cryptotransactions_data_use.date_create)::timestamp,\'YYYY-MM-DD HH24:MI:SS.ms\') as ticker, cryptotransactions_data_use.id as tickerid, cryptotransactions_data_use.tradetype as tradetype, cryptotransactions_data_use.date_create as unix_t_orderdate, trunc(cryptotransactions_data_use.amount::numeric, ' + precision + ') as volume ';
	
	var sql = 'SELECT jsonb_agg (query) from (SELECT ' + fieldQueryes + ' FROM cryptotransactions_data_use join cryptotransactions_record_own on cryptotransactions_record_own.id = cryptotransactions_data_use.id where cryptotransactions_data_use.currencyid = \'' + currentCurrencyID + '\' and cryptotransactions_data_use.basecurrencyid = \'' + baseCurrencyID + '\' and cryptotransactions_record_own.company_id = \'' + company + '\' and cryptotransactions_record_own.service_id = \'' + service + '\' order by cryptotransactions_data_use.date_create desc limit 100)query';
	//console.log(sql)
	pgclient.query(sql, (err, res) => {
        //console.log(sql)
        //return;
        var historyResult = '';
        if (err || res.rowCount < 1 || res.rows[0].jsonb_agg == null) {
            historyResult = '[]';
        } else
			historyResult = JSON.stringify(res.rows[0].jsonb_agg);
		if(socketID != null)
			io.emit('trade_history', ('{"data": {"status": "ok", "history_order": [' + 0 + '], "history": ' + historyResult + '}}'));
		else 
			for (var key of currentConnections.keys()) {
				if(currentConnections.get(key)[6] == null || currentConnections.get(key)[7] == null) continue;
				if(company == currentConnections.get(key)[4] && service == currentConnections.get(key)[5] && currentCurrencyID == currentConnections.get(key)[6] && baseCurrencyID == currentConnections.get(key)[7] ){
						io.to(key).emit('trade_history', ('{"data": {"status": "ok", "history_order": [' + 0 + '], "history": ' + historyResult + '}}'));
				}
			}
			// io.to(socketID).emit('trade_history', ('{"data": {"status": "ok", "history_order": [' + 0 + '], "history": ' + historyResult + '}}'));
    }); 
}

function getBuyOrders(company, service, currentCurrencyID, baseCurrencyID) {
				var fieldQueryes = 'MD5(cryptoorders_data_use.ownerid::character varying) as hashid, cryptoorders_data_use.amount as amount, cryptoorders_data_use.baseamount as basevalue, cryptoorders_data_use.price as price, cryptoorders_data_use.date_create as unix_t_orderdate';
				var sql = 'SELECT jsonb_agg (query) from (SELECT ' + fieldQueryes + ' FROM cryptoorders_data_use join cryptoorders_record_own on cryptoorders_record_own.id = cryptoorders_data_use.id where cryptoorders_data_use.currencyid = \'' + currentCurrencyID + '\' and cryptoorders_data_use.basecurrencyid = \'' + baseCurrencyID + '\' and cryptoorders_record_own.company_id = \'' + company + '\' and cryptoorders_record_own.service_id = \'' + service + '\' and cryptoorders_data_use.tradetype = \'0\' order by cryptoorders_data_use.price desc, cryptoorders_data_use.date_create desc)query';
				pgclient.query(sql, (err, res) => {
					//console.log(sql)
					//return;
					var buyResult = '';
					if (err || res.rowCount < 1 || res.rows[0].jsonb_agg == null) {
						buyResult = '[]';
					} else
						buyResult = JSON.stringify(res.rows[0].jsonb_agg);
					//console.log('{"data": {"status": "ok", "type": "buyOrders", "totalCurr": "' + 0 + '", "totalBase": "' + 0 + '", "orders": ' + buyResult + '}}')
					for (var key of currentConnections.keys()) {
						if(currentConnections.get(key)[6] == null || currentConnections.get(key)[7] == null) continue;
						if(company == currentConnections.get(key)[4] && service == currentConnections.get(key)[5] && currentCurrencyID == currentConnections.get(key)[6] && baseCurrencyID == currentConnections.get(key)[7] ){
								io.to(key).emit('buy_orders', ('{"data": {"status": "ok", "type": "buyOrders", "totalCurr": "' + 0 + '", "totalBase": "' + 0 + '", "orders": ' + buyResult + '}}'));
						}
					}
				}); 
		
}

function getSellOrders(company, service, currentCurrencyID, baseCurrencyID) {
			var fieldQueryes = 'MD5(cryptoorders_data_use.ownerid::character varying) as hashid, cryptoorders_data_use.amount as amount, cryptoorders_data_use.baseamount as basevalue, cryptoorders_data_use.price as price, cryptoorders_data_use.date_create as unix_t_orderdate';
			var sql = 'SELECT jsonb_agg (query) from (SELECT ' + fieldQueryes + ' FROM cryptoorders_data_use join cryptoorders_record_own on cryptoorders_record_own.id = cryptoorders_data_use.id where cryptoorders_data_use.currencyid = \'' + currentCurrencyID + '\' and cryptoorders_data_use.basecurrencyid = \'' + baseCurrencyID + '\' and cryptoorders_record_own.company_id = \'' + company + '\' and cryptoorders_record_own.service_id = \'' + service + '\' and cryptoorders_data_use.tradetype = \'1\' order by cryptoorders_data_use.price asc, cryptoorders_data_use.date_create desc)query'; 
			pgclient.query(sql, (err, res) => {
				//console.log(sql)
				//return;
				var buyResult = '';
				if (err || res.rowCount < 1 || res.rows[0].jsonb_agg == null) {
					buyResult = '[]';
				} else
					buyResult = JSON.stringify(res.rows[0].jsonb_agg); 
				for (var key of currentConnections.keys()) {
		if(currentConnections.get(key)[6] == null || currentConnections.get(key)[7] == null) continue;
		if(company == currentConnections.get(key)[4] && service == currentConnections.get(key)[5] && currentCurrencyID == currentConnections.get(key)[6] && baseCurrencyID == currentConnections.get(key)[7] ){
				io.sockets.connected[key].emit('sell_orders', ('{"data": {"status": "ok", "type": "sellOrders", "totalCurr": "' + 0 + '", "totalBase": "' + 0 + '", "orders": ' + buyResult + '}}'));
		}	
				}
			}); 
}

function setRequestToDB(socketID, company, service, currentCurrencyID, baseCurrencyID, userID, json_request){
	expr = /script|\*|<|>|<|>|\\\\|\/|UPDATE|INSERT/i;
	if (json_request == null || JSON.stringify(json_request).match(expr) !== null) {
		//if (allJson.error.match(expr) !== null) {
		fs.appendFile('data/log/engine.log', dateFormat(new Date(), "yyyy/mm/dd HH:MM:ss") + " - [" + remoteAddress + "] exchanger: " + allJson + '\n', (err) => {
			if (err) {
				console.log(err);
			}
		});
		return;
	}else{
		var sql = 'insert into requestlist_data_use (id, json_request) values (\''+uuid()+'\',\''+JSON.stringify(json_request)+'\') returning requestlist_data_use.id';
		pgclient.query(sql, (err, res) => {
			if(err){
				console.log(err)
			}else{
				var sql = 'insert into requestlist_record_own (id, company_id, service_id, contact_id) values (\'' + res.rows[0].id + '\', \''+company+'\', \''+service+'\', \''+userID+'\') returning requestlist_record_own.id';
				pgclient.query(sql, (err, res) => {
					if(err){
						console.log(err)
					}else{
						console.log('insert new record: ' + res.rows[0].id)
					}
				});
			}
		});
	}
}

// function getValueResultFromDB(socketID, company, service, currentCurrencyID, baseCurrencyID, userID){
	
	// var sql = 'SELECT responselist_data_use.id, responselist_data_use.jsonrespond FROM responselist_data_use join responselist_record_own on responselist_record_own.id = responselist_data_use.id where responselist_record_own.company_id = \'' + company + '\' and responselist_record_own.service_id = \'' + service + '\' and responselist_record_own.contact_id = \'' + userID + '\' order by date_create asc limit 1';
	// //console.log(sql)
	// pgclient.query(sql, (err, res) => {
		// if(err || res.rowCount < 1){
			// console.log(err)
		// }else{
			// var sql = 'delete from responselist_data_use where responselist_data_use.id = \'' + res.rows[0].id + '\'';
			// pgclient.query(sql);
			// var sql = 'delete from responselist_record_own where responselist_record_own.id = \'' + res.rows[0].id + '\'';
			// pgclient.query(sql);
			// io.emit('operation_result', (res.rows[0].jsonrespond));
		// }
	// });
// }

// function getResultFromDB(){
	
	// var sql = 'SELECT responselist_data_use.id, responselist_data_use.jsonrespond, responselist_data_use.currentcurrencyid, responselist_data_use.basecurrencyid, responselist_record_own.company_id, responselist_record_own.service_id, responselist_record_own.contact_id FROM responselist_data_use join responselist_record_own on responselist_record_own.id = responselist_data_use.id order by date_create asc';
	// //console.log(sql)
	// pgclient.query(sql, (err, res) => {
		// if(err || res.rowCount < 1){
			// //console.log(err)
		// }else{
			// for(var i = 0; i < res.rowCount ; i++){
				
				// var sql = 'delete from responselist_data_use where responselist_data_use.id = \'' + res.rows[i].id + '\'';
				// pgclient.query(sql);
				// var sql = 'delete from responselist_record_own where responselist_record_own.id = \'' + res.rows[i].id + '\'';
				// pgclient.query(sql);
				// var socketID = findSocketByUserID(res.rows[i].contact_id, res.rows[i].currentcurrencyid, res.rows[i].basecurrencyid);
				// io.to(socketID).emit('operation_result', (res.rows[i].jsonrespond));
				// // if(!marketsHasUpdate(res.rows[i].currentcurrencyid, res.rows[i].basecurrencyid)){
					// // console.log("update markets: " + res.rows[i].currentcurrencyid + " - " + res.rows[i].basecurrencyid)
					// // getBuyOrders(res.rows[i].company_id, res.rows[i].service_id, res.rows[i].currentcurrencyid, res.rows[i].basecurrencyid);
					// // getSellOrders(res.rows[i].company_id, res.rows[i].service_id, res.rows[i].currentcurrencyid, res.rows[i].basecurrencyid);
				// // }
				// // cyclAllSockets(userID, currentCurrencyID, baseCurrencyID);
				// //console.log(res.rows[i].jsonrespond)
				// var respondObject = res.rows[i].jsonrespond;//JSON.parse(res.rows[i].jsonrespond);
				
				// if(respondObject.type == 0 || respondObject.type == 1){
					// try{
						// for(var i = 0; i < respondObject.tradeinfo.length ; i++){
							// //console.log('respond object: ')
							// //console.log(res.rows[i])
							// //if(res.rows[i].contact_id != null && res.rows[i].contact_id != undefined)
							// updateAllUsersFromOrdersHistory(respondObject.type, respondObject.tradeinfo[i], res.rows[i].contact_id, res.rows[i].currentcurrencyid, res.rows[i].basecurrencyid);
						// }
					// }catch(error) {
						// console.error(error);
					// }
				// }
			// }
		// }
	// });
// }

// function findSocketByUserID(userID, currentCurrencyID, baseCurrencyID){
	// var resultKey = null;
	// for (var key of currentConnections.keys()) {
		// if(currentConnections.get(key)[6] == null || currentConnections.get(key)[7] == null || currentConnections.get(key)[8] == null) continue;
        // if(currentCurrencyID == currentConnections.get(key)[6] && baseCurrencyID == currentConnections.get(key)[7] && userID == currentConnections.get(key)[8]){
			// getBalances(key, currentConnections.get(key)[4], currentConnections.get(key)[5], currentConnections.get(key)[6], currentConnections.get(key)[7], currentConnections.get(key)[8])
			// getMyOrders(key, currentConnections.get(key)[4], currentConnections.get(key)[5], currentConnections.get(key)[6], currentConnections.get(key)[7], currentConnections.get(key)[8])
			// getOrdersHistory(key, currentConnections.get(key)[4], currentConnections.get(key)[5], currentConnections.get(key)[6], currentConnections.get(key)[7], currentConnections.get(key)[8]);
			// return key;
		// }
    // }
	// return null;
// }

// function cyclAllSockets(userID, currentCurrencyID, baseCurrencyID){
	
	// for (var key of currentConnections.keys()) {
		// if(currentConnections.get(key)[6] == null || currentConnections.get(key)[7] == null || currentConnections.get(key)[8] == null) continue;
        // if(currentCurrencyID == currentConnections.get(key)[6] && baseCurrencyID == currentConnections.get(key)[7] && userID == currentConnections.get(key)[8]){
			// getBalances(key, currentConnections.get(key)[4], currentConnections.get(key)[5], currentConnections.get(key)[6], currentConnections.get(key)[7], currentConnections.get(key)[8])
			// getMyOrders(key, currentConnections.get(key)[4], currentConnections.get(key)[5], currentConnections.get(key)[6], currentConnections.get(key)[7], currentConnections.get(key)[8])
			// getOrdersHistory(key, currentConnections.get(key)[4], currentConnections.get(key)[5], currentConnections.get(key)[6], currentConnections.get(key)[7], currentConnections.get(key)[8]);
			// return;
		// }
    // }
// }

// function marketsHasUpdate(currentCurrencyID, baseCurrencyID){
	// if(usedMarkets.has((currentCurrencyID + baseCurrencyID))){
		// return true;
	// }
	// usedMarkets.set((currentCurrencyID + baseCurrencyID), [true]);
	// return false;
// }

// function updateAllUsersFromOrdersHistory(tradetype, tradeHistory, userID, currentCurrencyID, baseCurrencyID){
	    // //console.log(tradeHistory)
		// var socketID = null;
		// if(tradetype = 0){
			// var sql = "SELECT * FROM cryptotransactions_data_use WHERE id = '" + tradeHistory.key + "' AND ownercurrencyid = '" + userID + "'";
			// pgclient.query(sql, (err, res) => {
				// if(err || res.rowCount < 1){
					// console.log('found point 1 ' + sql)
					// console.log(err)
				// }else{
					// socketID = findSocketByUserID(res.rows[0].ownerbasecurrencyid, res.rows[0].currencyid, res.rows[0].basecurrencyid);
				// }
			// })
		// }else if(tradetype = 1){
			// sql = "SELECT * FROM cryptotransactions_data_use WHERE id = '" + tradeHistory.key + "' AND ownerbasecurrencyid = '" + userID + "'";
			// pgclient.query(sql, (err, res) => {
				// if(err || res.rowCount < 1){
					// console.log('found point 2 ' + sql)
					// console.log(err)
				// }else{
					// socketID = findSocketByUserID(res.rows[0].ownercurrencyid, res.rows[0].currencyid, res.rows[0].basecurrencyid);
				// }
			// })
		// }
		// if(socketID != null){
			// getBalances(socketID, currentConnections.get(socketID)[4], currentConnections.get(socketID)[5], currentConnections.get(socketID)[6], currentConnections.get(socketID)[7], currentConnections.get(socketID)[8])
			// getMyOrders(socketID, currentConnections.get(socketID)[4], currentConnections.get(socketID)[5], currentConnections.get(socketID)[6], currentConnections.get(socketID)[7], currentConnections.get(socketID)[8])
			// getOrdersHistory(socketID, currentConnections.get(socketID)[4], currentConnections.get(socketID)[5], currentConnections.get(socketID)[6], currentConnections.get(socketID)[7], currentConnections.get(socketID)[8]);
		// }
	// //});
// }

// function getChartInfoByMarketID(socketID, marketID, typeChart){
	// var sql = 'SELECT coinmarkets_data_use.basecurrencyid, coinmarkets_data_use.currentcurrencyid, coinmarkets_record_own.company_id, coinmarkets_record_own.service_id FROM coinmarkets_data_use join coinmarkets_record_own on coinmarkets_record_own.id = coinmarkets_data_use.id WHERE coinmarkets_data_use.id = \'' + marketID + '\' limit 1';
	// //console.log(sql)
	// pgclient.query(sql, (err, res) => {
		// if (err || res.rowCount < 1) {
			// //baseCurrencyID = null;
			// //currentCurrencyID = null;
		// }else{
			// getChartInfo(socketID, res.rows[0].company_id, res.rows[0].service_id, res.rows[0].currentcurrencyid, res.rows[0].basecurrencyid, typeChart);
		// }
	// });
// }

// function getChartInfo(socketID, company, service, currentCurrencyID, baseCurrencyID, typeChart){
	// var first = new Date(); // Get the first date epoch object
	
	// var precision = 8;
	// var contact_id = '00000000-0000-0000-0000-000000000015';
	// //console.log('type chart: ' + typeChart)
	// switch (typeChart){
		// case '172800':
		// contact_id = '00000000-0000-0000-0000-000000000030';
		// break;
		// case '604800':
		// contact_id = '00000000-0000-0000-0000-000000000060';
		// break;
		// case '1209600':
		// contact_id = '00000000-0000-0000-0000-000000000120';
		// break;
		// case '2419200':
		// contact_id = '00000000-0000-0000-0000-000000000240';
		// break;
		// case '4838400':
		// contact_id = '00000000-0000-0000-0000-000000000720';
		// break;
		// case '14515200':
		// contact_id = '00000000-0000-0000-0000-000000001440';
		// break;
		// default :
		// typeChart = '86400';
		// break;
	// }
	// if(contact_id == '00000000-0000-0000-0000-000000000015')
    // console.log("start get chart " + first)
	// var intervalValue = parseInt(contact_id.substring(24, contact_id.length)) * 60000; 
	// var sql = "SELECT jsonb_agg (query) from (select (intervaltransactions_data_use.interval * 1000) as \"0\", trunc(intervaltransactions_data_use.open::numeric, " + precision + ") as \"1\", trunc(intervaltransactions_data_use.high::numeric, " + precision + ") as \"2\", trunc(intervaltransactions_data_use.low::numeric, " + precision + ") as \"3\", trunc(intervaltransactions_data_use.close::numeric, " + precision + ") as \"4\", trunc(intervaltransactions_data_use.volume::numeric, " + precision + ") as \"5\", trunc(intervaltransactions_data_use.average::numeric, " + precision + ") as \"6\" from intervaltransactions_data_use join intervaltransactions_record_own on intervaltransactions_data_use.id = intervaltransactions_record_own.id where  intervaltransactions_data_use.close > '0' and intervaltransactions_record_own.company_id = '" + company + "' and intervaltransactions_record_own.service_id = '" + service + "' and intervaltransactions_data_use.currencyid = '" + currentCurrencyID + "' and intervaltransactions_data_use.basecurrencyid = '" + baseCurrencyID + "' and intervaltransactions_record_own.contact_id = '" + contact_id + "' order by intervaltransactions_data_use.date_create)query";
	// //console.log(sql)
	// var resultVar = [];
	// pgclient.query(sql, (err, res) => {
		// if(err || res.rowCount < 1){
			// //console.log(err)
		// }else{
			// resultVar = res.rows[0].jsonb_agg;
			// io.to(socketID).emit('chart_data', ('{"data": {"status": "ok", "type": "' + typeChart + '", "chartdata": ' + JSON.stringify(resultVar) + '}}'));
			// var second = new Date(); // Get the first date epoch object
			// var diff= second - first ;
			// if(contact_id == '00000000-0000-0000-0000-000000000015')
			// console.log('end get chart ' + second + ', interval: ' + diff);
			// return;
		// }
		// sql = "select (intervaltransactions_data_use.interval * 1000) as \"0\", trunc(intervaltransactions_data_use.open::numeric, " + precision + ") as \"1\", trunc(intervaltransactions_data_use.high::numeric, " + precision + ") as \"2\", trunc(intervaltransactions_data_use.low::numeric, " + precision + ") as \"3\", trunc(intervaltransactions_data_use.close::numeric, " + precision + ") as \"4\", trunc(intervaltransactions_data_use.volume::numeric, " + precision + ") as \"5\", trunc(intervaltransactions_data_use.average::numeric, " + precision + ") as \"6\" from intervaltransactions_data_use join intervaltransactions_record_own on intervaltransactions_data_use.id = intervaltransactions_record_own.id where intervaltransactions_data_use.close > '0' and intervaltransactions_record_own.company_id = '" + company + "' and intervaltransactions_record_own.service_id = '" + service + "' and intervaltransactions_data_use.currencyid = '" + currentCurrencyID + "' and intervaltransactions_data_use.basecurrencyid = '" + baseCurrencyID + "' and intervaltransactions_record_own.contact_id = '" + contact_id + "' order by intervaltransactions_data_use.date_create desc limit 1";
		// //console.log(sql + '\r\n==============')
			// pgclient.query(sql, (err, resss) => {
				// if(err || resss.rowCount < 1){
					// io.to(socketID).emit('chart_data', ('{"data": {"status": "ok", "type": "' + typeChart + '", "chartdata": ' + JSON.stringify(resultVar) + '}}'));
				// }else{
					// sql = "SELECT sum(amount) as volume, avg(amount) as average, min(price) as low, max(price) as high FROM cryptotransactions_data_use join cryptotransactions_record_own on cryptotransactions_data_use.id = cryptotransactions_record_own.id where cryptotransactions_record_own.company_id = '" + company + "' and cryptotransactions_record_own.service_id = '" + service + "' and cryptotransactions_data_use.currencyid = '" + currentCurrencyID + "' and cryptotransactions_data_use.basecurrencyid = '" + baseCurrencyID + "' and date_create > '" + (resss.rows[0][0] / 1000) + "'";
					// //console.log(sql + '\r\n==============')
					// pgclient.query(sql, (err, lastres) => {
						// sql = "SELECT cryptotransactions_data_use.price FROM cryptotransactions_data_use join cryptotransactions_record_own on cryptotransactions_data_use.id = cryptotransactions_record_own.id where cryptotransactions_record_own.company_id = '" + company + "' and cryptotransactions_record_own.service_id = '" + service + "' and cryptotransactions_data_use.currencyid = '" + currentCurrencyID + "' and cryptotransactions_data_use.basecurrencyid = '" + baseCurrencyID + "' and date_create > '" + (resss.rows[0][0] / 1000) + "' order by cryptotransactions_data_use.date_create desc limit 1";
						// //console.log(sql)
						// pgclient.query(sql, (err, lastprice) => { 
							// if(err || lastres.rowCount < 1 || lastres.rows[0].volume == null){
								// //do nothing
							// }else if(err || lastprice.rowCount < 1 || lastprice.rows[0].price == null){
								// resultVar.push({'0': (resss.rows[0][0] + intervalValue), '1': resss.rows[0][4], '2': lastres.rows[0].high, '3': lastres.rows[0].low, '4': resss.rows[0][4], '5': lastres.rows[0].volume, '6': resss.rows[0][4]});
							// }else{ 
								// resultVar.push({'0': (resss.rows[0][0] + intervalValue), '1': resss.rows[0][4], '2': lastres.rows[0].high, '3': lastres.rows[0].low, '4': lastprice.rows[0].price, '5': lastres.rows[0].volume, '6': lastres.rows[0].average});
							// }
							// //console.log('start emit to socket: ' + socketID)
							// io.to(socketID).emit('chart_data', ('{"data": {"status": "ok", "type": "' + typeChart + '", "chartdata": ' + JSON.stringify(resultVar) + '}}'));
						// })
						// // if(err || lastres.rowCount < 1 || lastres.rows[0].volume == null){
							
							// // //io.to(socketID).emit('chart_data', ('{"data": {"status": "ok", "type": "carts", "data": ' + resultVar + '}}'));
						// // }else{
							// // //select last price
							
							// // //resultVar.push({'0': (resss.rows[0][0]), '1': resss.rows[0][4], '2': lastres.rows[0].high, '3': lastres.rows[0].low, '4': resss.rows[0][4], 'volume': lastres.rows[0].volume, 'average': lastres.rows[0].average});
						// // }
						// //console.log('start emit to socket: ' + socketID)
						// //io.to(socketID).emit('chart_data', ('{"data": {"status": "ok", "type": "charts", "chartdata": ' + JSON.stringify(resultVar) + '}}'));
					// })
				// }
			// })
	// })
// }

var TICK_TIME = 1000;
var generalUpdateValues = 0;
setInterval(function () {
	//getResultFromDB();
	if(generalUpdateValues >= 3){
		generalUpdateValues = 0;
		var sql = "SELECT coinmarkets_data_use.*, coinmarkets_record_own.company_id, coinmarkets_record_own.service_id FROM coinmarkets_data_use join coinmarkets_record_own on coinmarkets_data_use.id = coinmarkets_record_own.id WHERE status = '200'";
		pgclient.query(sql, (err, res) => {
			
			if(err || res.rowCount < 1){
				console.log(err)
			}else{
				for(var i = 0 ; i < res.rowCount; i++){
					for (var key of currentConnections.keys()) { 
						if(currentConnections.get(key)[6] == null || currentConnections.get(key)[7] == null) continue;
						if(res.rows[i].company_id == currentConnections.get(key)[4] && res.rows[i].service_id == currentConnections.get(key)[5] && res.rows[i].currentcurrencyid == currentConnections.get(key)[6] && res.rows[i].basecurrencyid == currentConnections.get(key)[7] ){
							getSellOrders(res.rows[i].company_id, res.rows[i].service_id, res.rows[i].currentcurrencyid, res.rows[i].basecurrencyid);
							getBuyOrders(res.rows[i].company_id, res.rows[i].service_id, res.rows[i].currentcurrencyid, res.rows[i].basecurrencyid);
							getCommonHistory(null, res.rows[i].company_id, res.rows[i].service_id, res.rows[i].currentcurrencyid, res.rows[i].basecurrencyid)
						}
					}
				}
			}
		})
	}else{
		generalUpdateValues++;
	}
}, TICK_TIME);
