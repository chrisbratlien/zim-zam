if (typeof BSD ==  "undefined") { var BSD = {}; }
BSD.RemoteStorage = function(spec) {
  
  var prefix = spec.prefix;
  var url = spec.url;

  if (!url) { throw('configuration error: BSD.RemoteStorage needs url '); 
    return false;
  }
  if (!prefix) { throw('configuration error: BSD.RemoteStorage needs prefix'); 
    return false;
  }
  var self = {};
  
  
  self.morlock = function(action,data,success,error) {
    jQuery.ajax({
      type: action == 'getItem'? 'GET' : 'POST',
      url: url,
      data: { action: action, data: data },
      success: success,
      error: error
    });
  };
  
  self.setItem = function(k,v,success,error) {
    self.morlock('setItem',{ key: prefix+k, value: v },success,error);
  };
  self.getItem = function(k,success,error) {
    self.morlock('getItem',{ key: prefix+k },success,error);
  };
  self.removeItem = function(k,success,error) {
    self.morlock('removeItem',{ key: prefix+k, value: null },success,error);
  };
  self.clear = function() {
    return "clear: not yet implemented";
  }
  
  return self;
};
