BSD.remoteStorage = BSD.RemoteStorage({ 
  prefix: 'ZZ::',
  url: ZZ.baseURL + '/ws'
});

var campfire = BSD.PubSub({});


BSD.import = function (url, success,error) {
  jQuery.ajax({
    type : 'GET',
    url : url,
    success: success,
    error: error
  });
};

BSD.importJSON = function(url, success,error) {
  jQuery.ajax({
    type: 'GET',
    url: url,
    dataType: 'json',
    success: success,
    error: error
  });
};

BSD.chunkify = function(ary,chunkSize) {
  var chunks = [];
  var aryCopy = ary.select(function(o){ return true; }); 
  while (aryCopy.length > chunkSize) {
    chunks.push(aryCopy.splice(0,chunkSize));
  }
  chunks.push(aryCopy);
  return chunks;
};
