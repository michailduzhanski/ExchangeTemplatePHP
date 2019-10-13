function getImage(data, structure, insertNodeId, objectId, fieldId, size, imgOptions) {
    var nophotoPath = '/data/nophoto/no-photo.jpg';
    var map = getStructureIDMapWithCheck(structure, null, null);
    var file = null;
    var path = null;
    if(map[fieldId][0] != undefined){
        var index = map[fieldId][0];
        if(data[index] != undefined){
            file = data[index];
        }
    }
    if(file){
        var filename = file.split('.').slice(0, -1).join('.');
        var fileExtension = getExt(file);
        if(size){
            path = '/cache/objects/'+objectId+'/img/'+filename+'_'+size+'.'+fileExtension;
            if(!UrlExists(path)){
                path = nophotoPath;
            }
        } else {
            path = nophotoPath;
        }
    } else {
        path = nophotoPath;
    }
    var template = '<img src="' + path +'" ' + imgOptions + ' />';
    $('#'+insertNodeId).html(template);
}

function getStructureIDMapWithCheck(structure, stime, cookieTitle) {
    var result = getCookieByTitle(cookieTitle, stime);
    if (!result) {
        result = getIndexedArrayOfStructureLine(structure, null);
        updateCookie(cookieTitle, JSON.stringify({'data': result, 'stime': stime}));
    }
    return result;
}

function renderImage(objectId, tableId, owner, nophotoPath, options)
{
   if(!nophotoPath)
       nophotoPath = '/data/nophoto/no-photo.jpg';

   var table = $('#' + tableId);
   var img = table.text();
   var pathDir = '/data/objects/'+objectId+'/img/'+img;
   var filename = img.split('.').slice(0, -1).join('.');
   var fileExtension = getExt(img);

   if(owner){
      path = '/cache/objects/'+objectId+'/img/'+filename+'_'+owner+'.'+fileExtension;
   } else {
       path = pathDir;
   }

   if(!UrlExists(path)){
       path = nophotoPath;
       /*if(owner && UrlExists(pathDir)){

       } else {
           path = nophotoPath;
       }*/
   }

    img = '<img src="'+path+'" '+options+'/>';
    table.html(img);

   return path;
}

function UrlExists(url)
{
    var http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    http.send();
    return http.status!=404;
}


function getExt(fileName){
    return (fileName.lastIndexOf('.') < 1) ?   null : fileName.split('.').slice(-1);
}