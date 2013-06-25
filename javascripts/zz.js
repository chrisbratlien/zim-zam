if (typeof ZZ == "undefined") { var ZZ = {}; }
ZZ.Widgets = {};

ZZ.Concept = false; //slot


ZZ.Zim = function(spec) {
  var self = spec;
  self.spec = spec;
  self.receiverConcept = function() {
    return ZZ.Concept({ id: spec.receiver });
  };
  self.messageConcept = function() {
    return ZZ.Concept({ id: spec.message });
  };

  self.responseConcept = function() {
    return ZZ.Concept({ id: spec.response });
  };


  return self;
};
ZZ.Zam = function(spec) {
  var self = spec;
  self.spec = spec;
  self.receiverConcept = function() {
    return ZZ.Concept({ id: spec.receiver });
  };
  self.messageConcept = function() {
    return ZZ.Concept({ id: spec.message });
  };
  return self;
};


ZZ.cache = {};
ZZ.cache.zam_id = {};
ZZ.cache.zim_id = {};
ZZ.cache.zimsInvolving = {};
ZZ.cache.zamsInvolving = {};


/*
ZZ.cache.zims = function() {
  var result = [];  
  for (var i in ZZ.cache.zim_id) {
    result.push(ZZ.cache.zim_id[i]);
  }
  return result;
}

ZZ.cache.zams = function() {
  var result = [];  
  for (var i in ZZ.cache.zam_id) {
    result.push(ZZ.cache.zam_id[i]);
  }
  return result;
}
*/



ZZ.Concept = function(spec) { //spec needs an id
  var self = spec;
  self.spec = spec;
  self.linkify = function() {
    return ZZ.baseURL + '/concept/' + spec.id;
  };  


  self.zimsInvolved = function() {
    var hits = ZZ.cache.zimsInvolving[spec.id];
    if (typeof hits != "undefined" && hits.length > 0) {
      return hits;
    }
    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { action: 'get_zims_involving', concept_id: spec.id },
      async: false
    }).responseText;
    var zims = eval('(' + r + ')');    
  
    var conjured = zims.map(function(spec) { return ZZ.Zim(spec); });
    ZZ.cache.zimsInvolving[spec.id] = conjured;
    return conjured;      
  };

  self.zamsInvolved = function() {
    var hits = ZZ.cache.zamsInvolving[spec.id];
    if (typeof hits != "undefined" && hits.length > 0) {
      return hits;
    }
    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { action: 'batch_get_zams_involving', concept_ids: spec.id },
      async: false
    }).responseText;
    var zams = eval('(' + r + ')');

    var conjured = zams.map(function(spec) { return ZZ.Zam(spec); });
    ZZ.cache.zamsInvolving[spec.id] = conjured;
    return conjured;      
  };

  self.conceptResponsesToConcept = function(other) {
    var relevantZims = self.zimsInvolved().select(function(zim) { return zim.receiver == self.id && zim.message == other.id; });
    //console.log('relevantZims',relevantZims);      
    var result = relevantZims.map(function(z) { return ZZ.Concept({ id: z.response}); });
    return result;
  };

  self.textResponsesToConcept = function(other) {
    var involved = self.zamsInvolved();
    ///console.log('involved',involved);

    var relevantZams = self.zamsInvolved().select(function(zam) { 
      ////console.log('zam',zam);
      return zam.receiver == self.id && zam.message == other.id; 
    
    });
    /////console.log('relevantZams',relevantZams);      
    var result = relevantZams.map(function(z) { return z.response; });
    return result;
  };
  
  
  self.messageConceptsInvolved = function() {
    var result = [];
    var dupe = {};
    self.zimsInvolved().select(function(z) { return z.receiver == self.id; }).each(function(z) {
      ///console.log('z.m',z.message);
      if (typeof dupe[z.message] == "undefined") {
        dupe[z.message] = true;
        result.push(z.messageConcept());
      }
    });
    self.zamsInvolved().select(function(z) { return z.receiver == self.id; }).each(function(z) {
      if (typeof dupe[z.message] == "undefined") {
        dupe[z.message] = true;
        result.push(z.messageConcept());
      }
    });
    
    return result;    
  
  };
  
  self.glyphURLs = function() {
    var resp = self.textResponsesToConcept(ZZ.cache.glyphURLConcept);
    ////console.log('resp',resp);
    return resp;
  }  

  return self;
};


ZZ.Widgets.NewConceptForm = function(spec) {
  var self = {};
  self.renderOn = function(wrap) {
    var well = DOM.div().addClass('well');
    var label = DOM.label('Create the concept of ').addClass('control-label');
    
    var input = DOM.input().attr('type','text');
    label.append(input);
    label.append(' (' + spec.langText + ')');  
    well.append(label);
    well.append(DOM.br());

    var createButton = DOM.button('Create Concept').addClass('btn btn-primary');
    var responseSpan = DOM.span();
    well.append(createButton);
    well.append(responseSpan);

    createButton.click(function() {
      var responseText = input.val();        
      if (responseText.length > 0) {
        newConceptAndZam({ message: spec.lang, response: responseText },function(o) { 
          console.log('got an Oh',o);
          responseSpan.append(DOM.a().attr('href','/concept/' + o.id).html(responseText));
        });
      }
    });
    wrap.append(well);
  };
  return self;
};





ZZ.Widgets.Involved = function(spec) {
  var self = {};
  ////console.log('spec',spec);


  self.badassLink = function(concept) {
    ////console.log('**concept',concept,'langConcept',ZZ.langConcept);
  
  
  
  
  
    var translated = concept.textResponsesToConcept(ZZ.langConcept)[0];
    
    ///console.log('translated',translated);
    
    
    var glyphs = concept.glyphURLs();
    /////console.log('glyphs',glyphs);
    
    var link = DOM.a().attr('href',concept.linkify());
    glyphs.each(function(url) {     
      link.append(DOM.img().attr('src',url).attr('width','20px'));
    });
    

    if (typeof translated == "undefined") {
      var zigzag = DOM.img().attr('src','http://zimzam.dev.bratliensoftware.com/images/zigzag-line7.gif').addClass('zigzag');
      link.append(zigzag);
    }
    else {
      link.append(' ' + translated);
    }

    return link;
  };



  self.renderZimRowOn = function(table,z) {
    ///console.log('z',z);
  
  
    var row = DOM.tr();
    row.append(DOM.td(self.badassLink(z.receiverConcept())));
    row.append(DOM.td(self.badassLink(z.messageConcept())));
    row.append(DOM.td(self.badassLink(z.responseConcept())));
    table.append(row);        
  };
  self.renderZamRowOn = function(table,z) {
    var row = DOM.tr();
    row.append(DOM.td(self.badassLink(z.receiverConcept())));
    row.append(DOM.td(self.badassLink(z.messageConcept())));
    row.append(DOM.td(z.response));
    table.append(row);        
  };

  self.renderZimTableOn = function(wrap){
    var table = DOM.table().addClass('table table-striped table-bordered table-condensed');
    
    var headerRow = DOM.tr();
    
    headerRow.append(DOM.td('<strong>receiver</strong> (it)'));
    headerRow.append(DOM.td('<strong>message</strong> (when asked...)'));
    headerRow.append(DOM.td('<strong>response</strong> (responds with...)'));
    
    table.append(headerRow);
    spec.concept.zimsInvolved().each(function(z) {
      self.renderZimRowOn(table,z);
    });
    spec.concept.zamsInvolved().each(function(z) {
      self.renderZamRowOn(table,z);
    });
    wrap.append(table);
  };


  self.renderOn = function(wrap) {
    self.renderZimTableOn(wrap);
  };
  return self;
};

  function newZam(spec,callback) {
    jQuery.ajax({
      url: '/ws',
      data: { 
        action: 'new_zam', 
        receiver: spec.receiver,
        message: spec.message,
        response: spec.response
      },
      success: callback
    });
  }   
  function newConceptAndZam(spec,callback) {  
    jQuery.ajax({
      url: '/ws',
      data: { 
        action: 'new_concept_and_zam', 
        ////won't need to supply receiver,the new concept receives the message
        message: spec.message,
        response: spec.response
      },
      success: function(r) { 
      
        var o = eval('(' + r + ')');
      
        var result = ZZ.Concept(o);
        
        console.log('nzjanzja',o);
        console.log('nzjanzjar',result);
        callback(result);
      
        //callback(o);


      
      }
    });
  }   
  
  function newZim(spec,callback) {
  
    jQuery.ajax({
      url: '/ws',
      data: { 
        action: 'new_zim', 
        receiver: spec.receiver,
        message: spec.message,
        response: spec.response
      },
      success: callback
    });
  }






  
  function getZamsWhere(o) {
    var thisData = { action: 'get_zams_where' };
    if (typeof o.receiver != "undefined") { thisData.receiver = o.receiver; }
    if (typeof o.message != "undefined") { thisData.message = o.message; }
    if (typeof o.response != "undefined") { thisData.response = o.response; }
    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: thisData,
      async: false
    }).responseText;
    var zams = eval('(' + r + ')');
    var conjured = zams.map(function(spec) { return ZZ.Zam(spec); });
    
    ////console.log('conjured',conjured);
    
    
    conjured.each(function(zam) {
      ZZ.cache.zam_id[zam.zam_id] = zam;
    });
    return conjured;      
  }

  function getZamReceivers(msg,resp) { 
    var them = getZamsWhere({
      message: msg,
      response: resp
    });
    
    return them.map(function(z) { return z.receiver; });
  
  }



ZZ.cache.glyphURLConcept = ZZ.Concept({ id: getZamReceivers(1,'glyph url')[0] });


/*
function get_zam_receivers($msg,$response) {
  $receivers = array_map(function($zam) { return $zam->receiver; },get_zams_with_message_and_response($msg,$response));
  return $receivers;
}



  
get_first_zam_receiver(1,'glyph url'));  
*/



  
   
