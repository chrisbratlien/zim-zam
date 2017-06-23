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
