if (typeof DIBS == "undefined") { var DIBS = {}; }
if (typeof DIBS.Widgets == "undefined") { DIBS.Widgets = {}; }

baseURL = 'http://dibs.dev.bratliensoftware.com';

DIBS.getVendors = function() {
    var response = jQuery.ajax({
      url: '/ws',
      type: 'POST',
      data: { 
        'action': 'get_vendors'
      },
      async: false
    }).responseText;
    return eval('(' + response + ')');
};

