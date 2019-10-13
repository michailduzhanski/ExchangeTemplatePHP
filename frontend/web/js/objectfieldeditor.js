function getListObjectContentComplex(url, request, element, page = false, callback = null, index = 0) {
    return $.post(url, {json: JSON.stringify(request)}).done(function (data) {
        var html = '';
        var structure = data['data']['structure']['data'];
        var structureMap = getStructureIDMapWithCheck(structure, data['data']['work']['stime'], page + "_" + element);
        var neededElements = templateMaps[element];
        $.each(data['data']['data'], function () {
            var item = this;
            var htmlRow = templates[element];
            $.each(neededElements, function (key) {
                var indexItem = neededElements[key];
                var fieldValue = getSingleElementFromDataByIDMap(item, structureMap, indexItem);
                htmlRow = htmlRow.split(`{${indexItem}}`).join(fieldValue);
            });
            html += htmlRow;
        });
        $('#table-' + element).html(html);
        if(callback){
            callback[index](callback);
        }
    });
}

function getListObjectContentSimple(url, request, element, page = false, callback = null, index = 0) {
    $.post(url, {json: JSON.stringify(request)}).done(function (data) {
        var html = '';
        var structure = data['data']['structure']['data'];
        $.each(data['data']['data'], function () {
            var item = this;
            var htmlRow = templates[element];
            $.each(structure, function (index, value) {
                var fieldName = value['name'];
                htmlRow = htmlRow.split(`{${fieldName}}`).join(item[index]);
            });
            html += htmlRow;
        });
        $('#table-' + element).html(html);
        if(callback){
            callback[index](callback);
        }
    });
}

function getIndexedArrayOfStructureLine(structure, suffixArray) {
    var resultArray = {};
    var index = 0;
    Object.keys(structure).forEach(function (key) {
        var nestedSuffix = null;
        if (suffixArray != null) {
            nestedSuffix = JSON.parse(JSON.stringify(suffixArray));
        }
        resultArray[structure[key]['id']] = updateArrayWithSuffix(index, nestedSuffix);
        if (structure[key]['nested'] != 'false') {
            Object.assign(resultArray, getIndexedArrayOfStructureLine(structure[key]['nested'], resultArray[structure[key]['id']]));
        }
        index++;
    });
    return resultArray;
}

function getStructureIDMapWithCheck(structure, stime, cookieTitle) {
    var result = getCookieByTitle(cookieTitle, stime);
    if (!result) {
        result = getIndexedArrayOfStructureLine(structure, null);
        updateCookie(cookieTitle, JSON.stringify({'data': result, 'stime': stime}));
    }
    return result;
}

function updateArrayWithSuffix(element, suffixArray) {
    if (suffixArray != null && Object.keys(suffixArray).length > 0) {
        var leng = Object.keys(suffixArray).length;
        var newElement = {[leng]: element};
        return Object.assign(suffixArray, newElement);
    } else {
        return {'0': element};
    }
}

function updateCookie(cookieTitle, cookieValue) {
    document.cookie = cookieTitle + "=" + encodeURIComponent(cookieValue) + "; path=/";
}

function getCookieByTitle(cookieTitle, time) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    if (matches) {
        var result = JSON.parse(matches[1]);
        if (time == null || result['stime'] > time) {
            return result['data'];
        }
    }
    return null;
}

function getSingleElementFromDataByIDMap(data, structureMap, idField) {
    var index = 0;
    var presentMap = structureMap[idField];
    var resultData = data[presentMap[0]];
    for (index = 1; index < Object.keys(presentMap).length; index++) {
        if (index & 1) {
            resultData = resultData[0][presentMap[index]];
        } else {
            resultData = resultData[presentMap[index]];
        }
    }
    return resultData;
}

function globalQueue(arrayOfFunctions){
    arrayOfFunctions[0](arrayOfFunctions);
}