// Console-polyfill. MIT license.
// https://github.com/paulmillr/console-polyfill
// Make it safe to do console.log() always.
(function(con) {
  'use strict';
  var prop, method;
  var empty = {};
  var dummy = function() {};
  var properties = 'memory'.split(',');
  var methods = ('assert,clear,count,debug,dir,dirxml,error,exception,group,' +
     'groupCollapsed,groupEnd,info,log,markTimeline,profile,profiles,profileEnd,' +
     'show,table,time,timeEnd,timeline,timelineEnd,timeStamp,trace,warn').split(',');
  while (prop = properties.pop()) con[prop] = con[prop] || empty;
  while (method = methods.pop()) con[method] = con[method] || dummy;
})(this.console = this.console || {}); // Using `this` for web workers.


if (typeof ZZ == "undefined") { var ZZ = {}; }
ZZ.Widgets = {};

ZZ.Concept = false; //slot



ZZ.defaultGlyphURL = 'http://zimzam.dev.bratliensoftware.com/images/zimzam-white.png';


ZZ.WILD = -1; //wildcard id
var WILD = ZZ.WILD;

ZZ.keycodes = {
    TAB: 9,
    "RETURN": 13,
    ENTER: 13,
    PERIOD: 46,
    DOWN: 40,
    UP: 38,
    LEFT: 37,
    RIGHT: 39
  };


ZZ.zamForConceptPrefix = 'ZAM-FOR-CONCEPT-';
ZZ.zimForConceptPrefix = 'ZIM-FOR-CONCEPT-';









ZZ.getBase64Image = function(img) {
    // Create an empty canvas element
    var canvas = document.createElement("canvas");
    canvas.width = img.width;
    canvas.height = img.height;

    // Copy the image contents to the canvas
    var ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);

    // Get the data-URL formatted image
    // Firefox supports PNG and JPEG. You could check img.src to guess the
    // original format, but be aware the using "image/jpg" will re-encode the image.
    var dataURL = canvas.toDataURL("image/png");

    return dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
}


ZZ.getVars = function(url) {
      var vars = {};
      var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
          vars[key] = value;
      });
      return vars;
};



ZZ.Zim = function(spec) {

  ////console.log('constructing myself from spec',spec);

  /////var self = spec;
  //////self.spec = spec;
  var self = {};


  self.type = 'zim';
  self.zim_id = spec.zim_id;
  self.receiver = spec.receiver;
  self.message = spec.message;
  self.response = spec.response;


  self.hash = function() {
    return 'zim_id:' + spec.zim_id;
  };
  
  self.receiverHash = function() {
    return 'receiver:' + spec.receiver; 
  };

  self.receiverMessageHash = function() {
    return 'receiver:' + spec.receiver + ':message:' + spec.message;
  };
  

  self.receiverConcept = function() {
    return ZZ.Concept({ id: spec.receiver });
  };
  self.messageConcept = function() {
    return ZZ.Concept({ id: spec.message });
  };
  self.responseConcept = function() {
    return ZZ.Concept({ id: spec.response });
  };
  
  ////console.log('returning',self);
  
  return self;
};




ZZ.Zam = function(spec) {
  var self = spec;
  self.spec = spec;

  self.type = 'zam';

  self.hash = function() {
    return 'zam_id:' + spec.zam_id;
  };

  self.receiverHash = function() {
    return 'receiver:' + spec.receiver; 
  };
  
  self.receiverMessageHash = function() {
    return 'receiver:' + spec.receiver + ':message:' + spec.message;
  };

  self.receiverConcept = function() {
    return ZZ.Concept({ id: spec.receiver });
  };
  self.messageConcept = function() {
    return ZZ.Concept({ id: spec.message });
  };
  
  /*
  self.delete = function(callback) {
    return ZZ.deleteZam(self,callback);
  }
  */
  
  return self;
};






ZZ.cache = {};




ZZ.updateCacheArray = function(key,hashable) {
  if (typeof hashable.hash == "undefined") {
    return "not hashable " + JSON.stringify(hashable);
  }  
  var hit = ZZ.cache[key]; //should hit an array
  if (typeof hit == "undefined") {
    ZZ.cache[key] = []; //initialize
    ZZ.cache[key].push(hashable);
    return ZZ.cache[key];
  }  
  var filtered = hit.reject(function(o){
    return o.hash() == hashable.hash(); // leave out the one we're trying to update
  });  
  filtered.push(hashable); //do the update!! 
  ZZ.cache[key] = filtered;
  return ZZ.cache[key];
};



ZZ.cache.removeZim = function(z) {
  console.log('removeZim!!',z,'NEED TO REIMPLEMENT?!?');

/****
  function helper(conceptID) {
    if (typeof ZZ.cache.zimsInvolving[conceptID] == "undefined") {
      ZZ.cache.zimsInvolving[conceptID] = [];
    }
    var cached = ZZ.cache.zimsInvolving[conceptID];
    var reduced = cached.select(function(cz) { return cz.zim_id != z.zim_id; });
    ZZ.cache.zimsInvolving[conceptID] = reduced;
  }
  
  helper(z.spec.receiver);
  helper(z.spec.message);
  *******/
}




ZZ.cache.removeZam = function(z) {
  function helper(conceptID) {
    if (typeof ZZ.cache.zamsInvolving[conceptID] == "undefined") {
      ZZ.cache.zamsInvolving[conceptID] = [];
    }
    var cached = ZZ.cache.zamsInvolving[conceptID];
    var reduced = cached.select(function(cz) { return cz.zam_id != z.zam_id; });
    ZZ.cache.zamsInvolving[conceptID] = reduced;
  }
  
  helper(z.spec.receiver);
  helper(z.spec.message);
}




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
  self.type = 'concept';

  self.linkify = function() {
    return ZZ.baseURL + '/' + spec.id;
  };  
  
  self.hash = function() {
    return 'concept:' + spec.id;
  };


  self.zimsInvolved = function() {
    ///console.log('ZIMS INVOLVED CALLED');

    var hash = ZZ.zimForConceptPrefix + self.hash(); //the hash of this concept!!
    if (typeof ZZ.cache[hash] != "undefined") {
      ///console.log('[using cache]');
      return ZZ.cache[hash];
    }
    console.log('[looking up...]');
    var iReceive = askZim(spec.id,WILD,WILD);
    var iMessage = askZim(WILD,spec.id,WILD); 
    var iRespond = askZim(WILD,WILD,spec.id);
    var result = [];
    result = result.concat(iReceive);
    result = result.concat(iMessage);
    result = result.concat(iRespond);
		

		if (result.length == 0) {
			ZZ.cache[hash] = result;
			return result;
		}
    result.each(function(z){ 
			ZZ.updateCacheArray(hash,z); 
		});
    return result;
  };


  self.zamsInvolved = function() {
    ////console.log('ZAMS INVOLVED CALLED');
    
    var hash = ZZ.zamForConceptPrefix + self.hash(); //the hash of this concept!!
    if (typeof ZZ.cache[hash] != "undefined") {
      //console.log('[using cache]');
      return ZZ.cache[hash];
    }
    console.log('[looking up...]');


    //FIXME: consider a control flow where askzam calls can be batched!!!  optimize network traffic!
    // wherever that fix may belong...
    
    

    var iReceive = askZam(spec.id,WILD,WILD);
    var iMessage = askZam(WILD,spec.id,WILD); 
    //no need to ask on a zam.....var iRespond = askZam(WILD,WILD,spec.id);

    var result = [];
    result = result.concat(iReceive);
    result = result.concat(iMessage);

    if (result.length == 0) {
      ZZ.cache[hash] = result;
      return result;
    }
    result.each(function(z){
      ZZ.updateCacheArray(hash,z);
    });

    result.each(function(z){ ZZ.updateCacheArray(hash,z); });
    return result;
  };

  self.wasZimsInvolved = function() {


    var hashed = 'zimsInvolved-' + self.hash();   
    if (typeof ZZ.cache[hashed] != "undefined") {
      return ZZ.cache[hashed];
    }
    
    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { action: 'get_zims_involving', concept_id: spec.id },
      async: false
    }).responseText;
    var zims = eval('(' + r + ')');    
  
    var conjured = zims.map(function(spec) { return ZZ.Zim(spec); });
    ZZ.cache[hashed] = conjured;
    return conjured;      
  };







  self.WASZamsInvolved = function() {
    var hashed = 'zamsInvolved-' + self.hash();   
    if (typeof ZZ.cache[hashed] != "undefined") {
      /////console.log('FOUUUUND THE STRINGY IN THE ZAMS-INVOLVED CASHY');
      return ZZ.cache[hashed];
    }

    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { action: 'batch_get_zams_involving', concept_ids: spec.id },
      async: false
    }).responseText;

    var zams = eval('(' + r + ')');
    var conjured = zams.map(function(spec) { return ZZ.Zam(spec); });

    ZZ.cache[hashed] = conjured;

    return conjured;      
  };

  self.conceptResponsesToConcept = function(other) {

    ///////////console.log('spec.id',spec.id,'other.id',other.id,'wild',ZZ.WILD);
    var relevantZims = askZim(spec.id,other.id,ZZ.WILD);
    if (relevantZims.length > 0) {
      console.log('RELEVANT LENGTH *****',relevantZims.length);
    }    
    ////////console.log('conceptResponsesToConcept relevantZims',relevantZims);
    var result = relevantZims.map(function(z) { return ZZ.Concept({ id: z.response}); });
    return result;
    /*****
    return "RARHGHGH";
    var relevantZims = self.zimsInvolved().select(function(zim) { return zim.receiver == self.id && zim.message == other.id; });
    //console.log('relevantZims',relevantZims);      
    var result = relevantZims.map(function(z) { return ZZ.Concept({ id: z.response}); });
    return result;
    *****/
  };

  self.textResponsesToConcept = function(other) {
    var involved = self.zamsInvolved();
    ///console.log('involved',involved);
    var relevantZams = self.zamsInvolved().select(function(zam) { 
      ////console.log('zam',zam);
      return zam.receiver == self.id && zam.message == other.id; 
    });
    //////console.log('relevantZams',relevantZams);      
    
    var result = relevantZams.map(function(z) { return z.response; });
    return result;
  };


  self.addResponse = function(message,response,callback) {
    console.log('Concept.addResponse ****/m/r:',message,response);
    if (typeof message != "object") { return "message must be object"};
    if (typeof response == "object") {
      newZim({ receiver: self.id, message: message.id, response: response.id },callback);
    }
    else {
      newZam({ receiver: self.id, message: message.id, response: response },callback);      
    }
  };

  self.luckyGet = function(msg) {
    var messageConcepts = luckyGet(msg);
    
    console.log('messageConcepts',messageConcepts);

    var result = messageConcepts.map(function(mc) {
      return self.receive(mc);
    });
    
    return result;

    /****
    return false;
    
    
    
    var filteredMsgs = them.select(function(o){
      console.log('o',o,'spec.id',spec.id);
      return o.spec.receiver == spec.id;
    });
    return filtered;
    ********/
  };
  
  self.receive = function(messageConcept) {
    var result = {
      conceptResponses: self.conceptResponsesToConcept(messageConcept),
      textResponses: self.textResponsesToConcept(messageConcept),
    };
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
    if (resp.length == 0) {
      resp.push(ZZ.defaultGlyphURL);
    }  
    
    var filtered = resp.select(function(u){
      return u.length > 0;
    });
      
    return filtered;
  };



  self.regularURLs = function() {
    var resp = self.textResponsesToConcept(ZZ.cache.regularURLConcept);
    ////console.log('resp',resp);
    var filtered = resp.select(function(u){
      return u.length > 0;
    });
      
    return filtered;
  };


  self.youtubeURLs = function() {
    var resp = self.textResponsesToConcept(ZZ.cache.youtubeURLConcept);
    ////console.log('resp',resp);
    var filtered = resp.select(function(u){
      return u.length > 0;
    });
      
    return filtered;
  };
  
  self.pdfURLs = function() {
    var resp = self.textResponsesToConcept(ZZ.cache.pdfURLConcept);
    ////console.log('resp',resp);
    var filtered = resp.select(function(u){
      return u.length > 0;
    });
      
    return filtered;
  };
    

  return self;
};



ZZ.Widgets.NewConceptForm = function(spec) {
  var self = {};
  self.renderOn = function(wrap) {
    var well = DOM.div().addClass('well');

    ////console.log('hay');

    var createButton = DOM.button('New Concept').addClass('btn btn-primary');
    createButton.click(function() {
      newConcept(spec.callback);
    });
    well.append(createButton);
    wrap.append(well);
  };
  return self;
};

ZZ.Widgets.NewConceptFormV1 = function(spec) {
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
          ///console.log('got an Oh',o);
          responseSpan.append(DOM.a().attr('href','/concept/' + o.id).html(responseText));
        });
      }
    });
    wrap.append(well);
  };
  return self;
};



ZZ.badassLink = function(concept) {
    var translations = concept.textResponsesToConcept(ZZ.langConcept);

    var glyphs = concept.glyphURLs();
    var link = DOM.a().attr('href',concept.linkify());


    if (glyphs.length > 0) {
      var url = glyphs.shift();
      link.append(DOM.img().attr('src',url).attr('width','20px'));
    }
    /**
    glyphs.each(function(url) {     
      link.append(DOM.img().attr('src',url).attr('width','20px'));
    });
    **/

    /*****
    if (translations.length == 0) {
      var zigzag = DOM.img().attr('src',defaultGlyphURL).addClass('zigzag');
      link.append(zigzag);
    }
    ***/

    if (translations.length > 0) {
      link.append(' ' + translations[0]);
    }
    return link;
};






ZZ.renderZimRowOn = function(table,z) {
  var row = DOM.tr();
  row.append(DOM.td(ZZ.badassLink(z.receiverConcept())));
  row.append(DOM.td(ZZ.badassLink(z.messageConcept())));
  row.append(DOM.td(ZZ.badassLink(z.responseConcept())));
  table.append(row);        
};


ZZ.renderZamRowOn = function(table,z) {
    var row = DOM.tr();
    row.append(DOM.td(ZZ.badassLink(z.receiverConcept())));
    row.append(DOM.td(ZZ.badassLink(z.messageConcept())));
    
    
    
    var responseCell = DOM.td(z.response);
    
    responseCell.click(function(e) {
      responseCell.empty();
      var editor = DOM.input().attr('type','text').val(z.response);

      
      
      editor.click(function(e){
        e.preventDefault();
        return false;
      });
      
      
      var save = DOM.button('save').addClass('btn btn-small btn-success');
      save.click(function(e) {
        e.preventDefault();
        var newVal = editor.val();
        updateZamResponse({ zam_id: z.zam_id, response: newVal });
        responseCell.empty();
        responseCell.html(newVal);
        return false;
      });

      var cancel = DOM.button('cancel').addClass('btn btn-small btn-inverse');;
      cancel.click(function(){
        e.preventDefault();
        responseCell.empty();
        responseCell.html(z.response);
        return false;
      });
      
      responseCell.append(editor);
      responseCell.append(save);
      responseCell.append(cancel);
    
    });

    
    row.append(responseCell);




    table.append(row);        
  };



ZZ.Widgets.Involved = function(spec) {
  var self = {};
  self.renderOn = function(wrap){
    var table = DOM.table().addClass('table table-striped table-bordered table-condensed');
    
    var headerRow = DOM.tr();
    
    headerRow.append(DOM.td('<strong>receiver</strong> (it)'));
    headerRow.append(DOM.td('<strong>message</strong> (when asked...)'));
    headerRow.append(DOM.td('<strong>response</strong> (responds with...)'));
    
    table.append(headerRow);
    spec.concept.zimsInvolved().each(function(z) {
      ZZ.renderZimRowOn(table,z);
    });
    spec.concept.zamsInvolved().each(function(z) {
      ZZ.renderZamRowOn(table,z);
    });
    wrap.append(table);
  };
  return self;
};

ZZ.Widgets.AllConcepts = function(spec) {
  var self = {};
  self.renderOn = function(wrap){
    var table = DOM.table().addClass('table table-striped table-bordered table-condensed');
    
    var headerRow = DOM.tr();
    
    headerRow.append(DOM.td('<strong>receiver</strong> (it)'));
    headerRow.append(DOM.td('<strong>message</strong> (when asked...)'));
    headerRow.append(DOM.td('<strong>response</strong> (responds with...)'));
    
    table.append(headerRow);
    getAllZims().each(function(z) {
      ZZ.renderZimRowOn(table,z);
    });
    getAllZams().each(function(z) {
      ZZ.renderZamRowOn(table,z);
    });
    wrap.append(table);
  };
  return self;
};

ZZ.Widgets.Procrastinator = function(spec) {

  ///console.log('spec',spec);

  var updateRequests = [];
  var lastStamp = new Date().getTime();
  var updateThresh = 1200;///500;
  
  function checkRequests() {
    ////console.log('cr updateRequests.length',updateRequests.length);
    if (updateRequests.length > 1) {
      updateRequests.pop();
      return;
    }
    spec.callback();
    updateRequests.pop();
  }

  var self = {};
  self.beg = function() {
    if (updateRequests.length > 5) { return; }
    updateRequests.push('blah');
    setTimeout(function() { checkRequests(); } ,updateThresh);
  };
  
  return self;
};



ZZ.youtubeEmbed = function(url) {
  var iframe = DOM.iframe();
  iframe.attr('width',560);
  iframe.attr('height',315);
  iframe.attr('frameborder',0); 
  iframe.attr('allowfullscreen',true); 
  iframe.attr('src','http://www.youtube.com/embed/{id}?wmode=transparent'.supplant({ id: ZZ.getVars(url).v }));  
  return iframe;
}






ZZ.Widgets.ConceptSearch = function(spec) {
  var self = BSD.PubSub({});
  var backplane = self;


  var results = [];
  var choice = spec.choice || false;
  
  var placeholder = spec.placeholder || 'concept search';
  

  var textBox = DOM.input().attr('type','text').attr('placeholder',placeholder).addClass('search-query');
  textBox.blur(function() {
    self.publish('blur',self);
  });
  
  self.focus = function() {
    console.log('ConceptSearch: i was told to focus');
    
    textBox.focus();
  }

    
  var resultsUL = DOM.ul().addClass('search-results');

  var stoner = ZZ.Widgets.Procrastinator({
    callback: function() {

      var query = textBox.val();
      if (query.length == 0 ) { 
        self.publish('search-results',[]); //empty    
        return false; 
      }
      
      
      var r = jQuery.ajax({
        type: 'POST',
        url: ZZ.baseURL + '/ws',
        data: { action: 'foo_zam_search', query: query },
        async: false
      }).responseText;
      var zams = eval('(' + r + ')');

      
      //////console.log('zams',zams);
      var filtered = [];
      var dupe = {};
      zams.each(function(zam){
        if (typeof dupe[zam.receiver] == "undefined") {
          filtered.push(zam);
          dupe[zam.receiver] = 'ok';
        }
      });
      
      //////console.log('filtered',filtered);
      
  
      var conjured = filtered.map(function(spec) { return ZZ.Zam(spec).receiverConcept(); });
      self.publish('search-results',conjured);
    }  
  });
  
  
  self.text = function() {
    return textBox.val();
  }

  self.redrawUL = function(concepts) {
    resultsUL.empty();
    concepts.each(function(concept) {
      var li = DOM.li();
      var resultDiv = DOM.div().addClass('concept-search-result');
      var link = ZZ.badassLink(concept);        
      var handle = DOM.button().addClass('btn btn-mini pull-right');

     /////// console.log('concept',concept,'choice',choice);
      handle.click(function() {
        if (concept == choice) {
          self.publish('search-results',concepts);
        }
        else {
          self.publish('use-search-result',concept);
        }
      });      


      if (concept == choice) {
          handle.html('lose it');      
      }
      else {
          handle.html('use it');      
      }


      resultDiv.append(link);
      resultDiv.append(handle);
      li.append(resultDiv);
      resultsUL.append(li);
    });
  };


  self.reset = function() {
    self.publish('search-results',[]);
    textBox.val('');
    choice = false;
  };

  self.subscribe('search-results',function(concepts) {
    results = concepts;
    choice = false;
    ///console.log('payload',concepts);
    textBox.show();
    self.redrawUL(concepts);
  });
  
  
  self.subscribe('use-search-result',function(o) {
    textBox.val(null);
    textBox.hide();
    choice = o;
    self.redrawUL([choice]);
    spec.callback(choice);
  });
  
  self.renderOn = function(wrap) {
    textBox.keyup(function(e){
      var c = e.keyCode || e.which;
        //console.log('KEYUP EVENT!!!!',c);        
        
        
      if (c == ZZ.keycodes.RETURN) {
        self.publish('enter/return pressed',self);
      }
      if (c == ZZ.keycodes.TAB || c == ZZ.keycodes.RETURN) { 
        if (results.length == 1) {
          return false; //we're already at the result we want. keydown would have already handled this stoner.beg
        }
      }
      ////console.log('KEYUP!!!');

      console.log('this',this);
      if (this.value.length > 2) {
        stoner.beg();
      }
    });
    
    textBox.keydown(function(e){
      var c = e.keyCode || e.which;
      
      ////console.log('c',c);
      if (c == ZZ.keycodes.TAB || c == ZZ.keycodes.RETURN) {
        ///console.log('TAB');
        
        
        
        if (results.length == 1) {
          e.preventDefault();
          ///console.log('PREVENTING DEFAULT!!!!');
          self.publish('blur',self);
        
        
          self.publish('use-search-result',results[0]);
          return false;
        }
        return false;
      }
    });


    wrap.append(textBox);
    wrap.append(DOM.div('&nbsp;').css('clear','both'));
    wrap.append(resultsUL);    
    if (choice) { 
      ///console.log('weeee');
      self.publish('use-search-result',choice);
    }
  };
  
  return self;

};





  function newConcept(callback) {
    jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'new_concept'
      },
      success: function(foo) {
        var spec = eval('(' + foo + ')');
        var result = ZZ.Concept(spec);
        ////console.log('spec',spec,typeof spec);
        ////console.log('foo',foo);
        callback(result);
      }
    });
  }   




  function newZam(spec,callback) {
    jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'new_zam', 
        receiver: spec.receiver,
        message: spec.message,
        response: spec.response
      },
      success: function(r) {
        var o = eval('(' + r + ')');
        var z = ZZ.Zam(o);

        //also, update the concept's hash's cached array... this should help the uploader refresh right...
        ZZ.updateCacheArray(ZZ.zamForConceptPrefix + z.receiverConcept().hash(),z);
        callback(z);
      }
    });
  }


  function makeConceptIfNotExists(englishText,callback) {
    var id = getFirstZamReceiver(1,englishText);
    if (!id) {
      newConceptAndZam({
        message: 1, //in English
        response: englishText
      },callback);
    }
  }


  ZZ.deleteZim = function(o,callback) {
    var zim_id = false;
    switch(typeof o) {
      case 'object':
        zim_id = o.zim_id;      
        break;
      case 'number':
        zim_id = o;      
        break;
      default:
        break;
    };
    if (! zim_id) {
      return "COULD NOT DELETE!!!!";
    }
    jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'delete_zim', 
        zim_id: zim_id,
        zzak: prompt('API Key')
      },
      success: callback
    });
  };
  
  
  ZZ.deleteZam = function(o,callback) {
    var zam_id = false;
    switch(typeof o) {
      case 'object':
        zam_id = o.zam_id;      
        break;
      case 'number':
        zam_id = o;      
        break;
      default:
        break;
    };
    if (! zam_id) {
      return "COULD NOT DELETE!!!!";
    }
    jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'delete_zam', 
        zam_id: zam_id,
        zzak: prompt('API Key')
      },
      success: callback
    });
  };
  
  
  

  
  function newConceptAndZam(spec,callback) {  
    jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'new_concept_and_zam', 
        ////won't need to supply receiver,the new concept receives the message
        message: spec.message,
        response: spec.response
      },
      success: function(r) { 
        var o = eval('(' + r + ')');      
        var result = ZZ.Concept(o);
        callback(result);
      }
    });
  }   
  
  function newZim(spec,callback) {
  
    jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'new_zim', 
        receiver: spec.receiver,
        message: spec.message,
        response: spec.response
      },
      success: function(r) {
        var o = eval('(' + r + ')');        
        var z = ZZ.Zim(o);
        //also, update the concept's hash's cached array... this should help the uploader refresh right...
        ZZ.updateCacheArray(ZZ.zimForConceptPrefix + z.receiverConcept().hash(),z);
        callback(z);      
      }
    });
  }
  
  
  
  


  function updateZamResponse(o,callback) {
    jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'update_zam_response', 
        zam_id: o.zam_id,
        response: o.response
      },
      success: callback
    });
  }
  
  
  function askZim(a,b,c) {
    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'askzim',
        receiver: a,
        message: b,
        response: c
      },
      async: false
    }).responseText;
    var evaluated = eval('(' + r + ')');
    
    ///////console.log('evaluated',evaluated);
    var conjured = evaluated.collect(function(o) { return ZZ.Zim(o); });
    return conjured; 
  }

  function askZam(a,b,c) {
    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { 
        action: 'askzam',
        receiver: a,
        message: b,
        response: c
      },
      async: false
    }).responseText;
    var evaluated = eval('(' + r + ')');
    
    ///////console.log('evaluated',evaluated);
    var conjured = evaluated.collect(function(o) { return ZZ.Zam(o); });
    return conjured; 
  }


  function luckyGet(request) {
    var hits = askZam(WILD,1,request);
    return hits.map(function(o){ return o.receiverConcept(); });
  }
  function luckyGetV1(request) {
    var hits = askZam(WILD,1,request);
    if (hits.length == 0) { return false; }
    var first = hits.shift();
    return first.receiverConcept();
  }
  
  function getZimsWhere(o) {
    var thisData = { action: 'get_zims_where' };
    if (typeof o.receiver != "undefined") { thisData.receiver = o.receiver; }
    if (typeof o.message != "undefined") { thisData.message = o.message; }
    if (typeof o.response != "undefined") { thisData.response = o.response; }

    var hashed = 'getZimsWhere-' + JSON.stringify(o); 
    
    console.log('getZimsWhere',hashed);
    
       
    if (typeof ZZ.cache[hashed] != "undefined") {
      ////console.log('FOUUUUND THE STRINGY IN THE ZIM CASHY',ZZ.cache[hashed]);
      var copy = ZZ.cache[hashed].collect(function(z) { return z; });
      return copy; //IMPORTANT NOT TO YIELD WHAT'S IN THE CACHE... you could shift() it by mistake!!
    }

    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: thisData,
      async: false
    }).responseText;
    
    
    ////console.log('ARRRR R R R',r);
    var evaluated = eval('(' + r + ')');   
    //console.log('evaluated',evaluated); 
    var conjured = evaluated.collect(function(o) { return ZZ.Zim(o); });
    //console.log('conjured',conjured); 
    ZZ.conjured = conjured;
    //console.log('STORing:::::::hashed',hashed,'conjured',conjured);
    ZZ.cache[hashed] = conjured;
    //console.log('VERIFY STOR:',ZZ.cache[hashed]);
    return conjured;    
  }

  function getZamsWhere(o) {
  
    var thisData = { action: 'get_zams_where' };
    if (typeof o.receiver != "undefined") { thisData.receiver = o.receiver; }
    if (typeof o.message != "undefined") { thisData.message = o.message; }
    if (typeof o.response != "undefined") { thisData.response = o.response; }
    
    
    var hashed = 'zAms-where-' + JSON.stringify(o);    
    if (typeof ZZ.cache[hashed] != "undefined") {
      /////console.log('FOUUUUND THE STRINGY IN THE ZAM CASHY');
      //////return ZZ.cache[hashed];
      var copy = ZZ.cache[hashed].collect(function(z) { return z; });
      return copy; //IMPORTANT NOT TO YIELD WHAT'S IN THE CACHE... you could shift() it by mistake!!
    }
    
    
    
    
    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: thisData,
      async: false
    }).responseText;
    var zams = eval('(' + r + ')');
    var conjured = zams.map(function(spec) { return ZZ.Zam(spec); });
    conjured.each(function(zam) {
      ///ZZ.cache.zam_id[zam.zam_id] = zam;
    });
    
    
    ZZ.cache[hashed] = conjured;
    
    return conjured;      
  }

  function getZamReceivers(msg,resp) { 
    var them = getZamsWhere({
      message: msg,
      response: resp
    });
    
        
    return them.map(function(z) { return z.receiver; });
  }
  
  function getFirstZamReceiver(msg,resp) {
    var them = getZamReceivers(msg,resp);
    if (them.length == 0) { return false; }
    return them.shift();
  }
  
  
  
  function getAllZams() {
    return getZamsWhere({});
  }

  function getAllZims() {
    return getZimsWhere({});
  }

  function setLanguage(concept) {
    jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: { action: 'set_language', language: concept.id },
      success: function(r) {
        ////console.log(r);
      }
    });
      
  }


ZZ.cache.glyphURLConcept = ZZ.Concept({ id: getZamReceivers(1,'glyph url')[0] });
ZZ.cache.youtubeURLConcept = ZZ.Concept({ id: getZamReceivers(1,'youtube url')[0] });
ZZ.cache.pdfURLConcept = ZZ.Concept({ id: getZamReceivers(1,'pdf url')[0] });
ZZ.cache.regularURLConcept = ZZ.Concept({ id: getZamReceivers(1,'url')[0] });



ZZ.cache.latitudeConcept = ZZ.Concept({ id: getZamReceivers(1,'latitude')[0] });
ZZ.cache.longitudeConcept = ZZ.Concept({ id: getZamReceivers(1,'longitude')[0] });


ZZ.Poller = function(spec) {
  var self = {};
  var pov = spec.pov;

  self.searchZamsViaGZR = function(str) {
    //get all (ids of) receivers  whom, when sent the message <pov.id>, respond with <str>
    var them = getZamReceivers(pov.id,str);   
    return them; 
  };
  self.search = function(str) {
    //get all (ids of) receivers  whom, when sent the message <pov.id>, respond with <str>
    var them = getZamsWhere({
      message: pov.id,
      response: str
    });
    ///console.log('Poller.search: them',them);
    var result = them.map(function(z){  return z.receiverConcept(); });
    return result;    
  };
  
  self.searchMR = function(msg,resp) {
    var msgID = getFirstZamReceiver(pov.id,msg);
    var respID = getFirstZamReceiver(pov.id,resp);


    var zams = getZamsWhere({
      message: msgID,
      response: resp
    });

    var zims = getZimsWhere({
      message: msgID,
      response: respID
    });
    
    var result = [];
    var zimConcepts = zims.map(function(z){  return z.receiverConcept(); });
    var zamConcepts = zams.map(function(z){  return z.receiverConcept(); }); 
    result = result.concat(zimConcepts);
    result = result.concat(zamConcepts);
    return result;
  };
  
  self.hookupConcept = function(concept,messageText,response,callback) {
    var msgID = getFirstZamReceiver(pov.id,messageText);
    var respID = getFirstZamReceiver(pov.id,response);
    msgID = parseInt(msgID,10);
    ///console.log('msgID',msgID,typeof msgID);
    var cb = callback || function(){};
    if (respID) {
      concept.addResponse(ZZ.Concept({ id: msgID }),ZZ.Concept({ id: respID }),cb);
    }
    else {
      concept.addResponse(ZZ.Concept({ id: msgID }),response,cb);
    }
  };
  
  return self;
};


ZZ.Reader = function(spec) {
  var self = {};
  
  self.eval = function(stream) {
  
    var tokens = stream.split(/\ +/);
  
    console.log('tokens',tokens);
  
    /*
    var result = tokens.map(function(token) {
      var receiverConcepts = getZamReceivers(ZZ.lang,token).map(function(id) { return ZZ.Concept({ id: id }); });
      return receiverConcepts;  
    });
    */

    var concepts = tokens.map(function(token) {
      var recv = getFirstZamReceiver(ZZ.lang,token);
      /////console.log('recv',recv);
      return recv;
    }).map(function(id) {
      if (id) {
        return ZZ.Concept({ id: id });    
      }
      return false;
    });


    var accum = concepts.shift();
    console.log('concepts??',concepts,concepts.length);
    eachify(concepts).eachPCN(function(o) {
      ///console.log('o',o);
      
      var responses = accum.receive(o.current);
      
      if (responses.textResponses.length > 0) {
        return responses.textResponses[0];
      }
      else if (responses.conceptResponses.length > 0) {
        accum = responses.conceptResponses[0]
      }
      else {
        ////console.log('died at',o.current,o);
        return "ERROR";
      }
      
    });


    return accum;    
    
    /* logic programming, hal abelson, sicp 8A: Logic Programming
    logic to express what is true
    logic to check what is true
    logic to find out what is true
    */
  };
  
  
  return self;
}


ZZ.Widgets.Uploader = function(spec) {

  var self = BSD.PubSub({});
  
  
  var thumb = DOM.img().attr('display','none').css('width','40px');
  var logoFileInput = DOM.input().attr('type','file');
  var glyphURLInput = DOM.input().addClass('search-box search-query').attr('placeholder','URL').attr('maxlength',9999999);

  var messageConcept = ZZ.cache.glyphURLConcept;



  var search = ZZ.Widgets.ConceptSearch({
      choice: messageConcept,
      placeholder: 'Language / POV',
      callback: function(concept) {
        messageConcept = concept;
      }
    });///.renderOn(povWrap);




  self.uploadFiles = function(url, files) {
    //found some of this here: http://www.html5rocks.com/en/tutorials/file/xhr2/
    var formData = new FormData();
    for (var i = 0, file; file = files[i]; ++i) {
      formData.append(file.name, file);
    }
    formData.append('action','upload_glyph_url');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.onload = function(e) { 
      var justTheFile = e.target.responseText;
      var fullURL = ZZ.baseURL + '/uploads/' + justTheFile;      
      self.reveal(fullURL);    
    };
    console.log('formData',formData);
    xhr.send(formData);  // multipart/form-data
  };

  self.renderOn = function(wrap) {
    wrap.append(DOM.h1('uploader'));
    search.renderOn(wrap);
    wrap.append(thumb);
    logoFileInput.change(function() {
      self.uploadFiles(ZZ.baseURL + '/ws', this.files);
    });

    glyphURLInput.change(function(e) {
      console.log('this.value length',this.value.length,e);
      ///console.log("DEBUG");
      ////return false;
    
      if (this.value.substr(0,5) == 'data:') {
        jQuery.ajax({
          type: 'POST',
          url: ZZ.baseURL + '/ws',
          data: { 
          action: 'get_glyph_from_datauri', 
            url: this.value
          },
          success: function(resp) {
            var fullURL = ZZ.baseURL + '/uploads/' + resp;          
            /////console.log('okay so that happened');////full URL',fullURL);
            self.reveal(fullURL);
          }
        });
      }
      else {
        jQuery.ajax({
          type: 'POST',
          url: ZZ.baseURL + '/ws',
          data: { 
          action: 'get_glyph_from_url', 
            url: this.value
          },
          success: function(resp) {
            var fullURL = ZZ.baseURL + '/uploads/' + resp;          
            //console.log('full URL',fullURL);
            self.reveal(fullURL);
          }
        });
      }
    });

    wrap.append(glyphURLInput);
    //wrap.append(DOM.div().css('clear','both'));
    wrap.append(logoFileInput);


  };
  self.reveal = function(url) {
    thumb.attr('src',url);
    thumb.show();  
    
    
    self.publish('new-upload-url',url); //deprecated


    self.publish('new-upload',{ concept: messageConcept, url: url});
    
    
    
    logoFileInput.val('');
    glyphURLInput.val('');  
  }
  return self;
};



ZZ.History = function(spec) {

  var max = 20;

  var self = BSD.PubSub({});

  self.past = [];

  self.push = function(concept) {
  
    var mr = self.mostRecent();
    ///console.log('mr',mr);
    if (mr && self.mostRecent().hash() == concept.hash()) {
      return false;/// nothing to push!
    }
    
    self.past = self.past.reject(function(o){ return o.hash() == concept.hash(); }); //git rid of earlier instances of this...most recent = most useful
    
    

    self.past.push(concept);

    while (self.past.length > max) {
      self.past.shift();    
    }


    self.save();
  };
  
  self.save = function() {
    ZZ.storage.setItem('concept-history-length',self.past.length);
    for (var i = 0; i < self.past.length; i += 1) {
      ZZ.storage.setItem('concept-history-' + i, self.past[i].id);
    }
  };
  
  self.load = function() {
    self.past = [];
    
    var length = ZZ.storage.getItem('concept-history-length');
    if (!length) { return false; } 
    for (var i = 0; i < length; i += 1) {
      var id = ZZ.storage.getItem('concept-history-' + i);
      self.past.push(ZZ.Concept({ id: id }));  
    }
  };
  
  
  self.mostRecent = function() {
    return self.past[self.past.length-1];
  };
  self.recent = self.mostRecent;
  
  
  self.recentFew = function() {
    var them = self.past.map(function(o){ return o; });
    them.reverse();
    ///console.log('them!!! them!!!',them);
    return them;
  };

  return self;
};

ZZ.history = ZZ.History();
ZZ.history.load();



ZZ.Widgets.Gallery = function(spec) {


  var uniqueID = Math.random().toString(16);


  var columns = [];
  var history = [];

  var self = BSD.PubSub({});
  var backplane = self;


  ///ZZ.storage = BSD.Storage('local');


  self.currentConcept = spec.concept;

  self.newColumn = function() {
    return DOM.div().addClass('span2');
  }


  var link = DOM.a();



  //var thumb = DOM.img().addClass('thumb');

  var thumbsDiv = DOM.div().addClass('thumbs');
  var thumbsLightbox = false;
  var lightboxContent = DOM.div();
  
  thumbsLightbox = BSD.Widgets.Lightbox({
    content: lightboxContent,
    top: (BSD.scrollTop() * 1.3) + 'px',
    //left: '5%',
    //width: '40%',
    //width: '90%',
    scrollTopCallback: function() { return (BSD.scrollTop() * 1.3) + 'px' }
  });
  
  
  var thumbs = [];


  var youtubeDiv = DOM.div().addClass('youtube');
  var youtubeVids = [];


  var pdfDiv = DOM.div().addClass('pdf');
  var pdfs = [];
  
  var urlDiv = DOM.div().addClass('url');



  var table = DOM.table().addClass('table');
  var back = DOM.button('Back').addClass('btn btn-mini btn-inverse');

  back.click(function(){
    console.log('back click');
    if (history.length < 2) { return false; }
    history.pop();
    self.recenterTo(history.pop());    
  });


  self.refreshThumb = function(concept) {
    //console.log('refreshThumb',concept);
    thumbsDiv.empty();        
    var glyphURLs = concept.glyphURLs();
    

    

    
    var img = DOM.img();
    lightboxContent.empty();
    lightboxContent.append(img);
    
    var linked = glyphURLs.map(function(o){
      return { url: o };
    });
    eachify(linked).eachPCN(function(o){
      var me = o.current;
      me.prev = o.prev;
      me.next = o.next;
    });
    
    //console.log('updated linked',linked);
    
    var present = linked[0];

    img.attr('src',present.url);
    img.click(function(){
      present = present.next;
      img.attr('src',present.url);
    });
    
    
    ////console.log('lightbox create: ' + uniqueID);
    


      
    
    var thumbsClass = 'thumb thumb-' + glyphURLs.length;
    glyphURLs.each(function(gu){
      var thumb = DOM.img().addClass(thumbsClass);
      thumb.attr('src',gu);
      thumbsDiv.append(thumb);
    });
    thumbsDiv.click(function(){
      thumbsLightbox.show();
      console.log('lightbox show',thumbsLightbox);
    });
  };
  
  self.refreshYouTube = function(concept) {
    //console.log('refreshYouTube',concept);
    youtubeDiv.empty();
    var youtubeURLs = concept.youtubeURLs();
    var countClass = 'youtube-' + youtubeURLs.length;
    youtubeURLs.each(function(o){
      var embed = ZZ.youtubeEmbed(o);
      youtubeDiv.append(embed);
    });
  };



  self.refreshPDFs = function(concept) {
    //console.log('refreshPDFs',concept);
    pdfDiv.empty();
    var pdfURLs = concept.pdfURLs();
    pdfURLs.each(function(o){
      var img = DOM.img().attr('src','http://zimzam.dev.bratliensoftware.com/uploads/6yqf.png'); //FIXME: make generic
      var a = DOM.a().attr('href',o).attr('target','_blank');
      a.append(img);
      pdfDiv.append(a);
    });
  };

  self.refreshRegularURLs = function(concept) {
    //console.log('refreshRegularURLs',concept);
    urlDiv.empty();
    var ul = DOM.ul();
    
    var regularURLs = concept.regularURLs();
    regularURLs.each(function(o){
      var a = DOM.a(o).attr('href',o).attr('target','_blank');
      var li = DOM.li(a);
      ul.append(li);
    });
      urlDiv.append(ul);
  };





  self.getZimRow = function(z) {
    var row = DOM.tr();
    var recvTile = DOM.td().addClass('tile');
    var recvC = z.receiverConcept();
    recvTile.append(ZZ.badassLink(recvC));
    //////recvTile.click(function() { self.recenterTo(recvC); });
    row.append(recvTile);

    var mTile = DOM.td().addClass('tile');
    var mc = z.messageConcept();
    mTile.append(ZZ.badassLink(mc));
    /////mTile.click(function() { self.recenterTo(mc); });
    row.append(mTile);
    
    var rTile = DOM.td().addClass('tile');
    var rc = z.responseConcept();
    rTile.append(ZZ.badassLink(rc));
    //////rTile.click(function() { self.recenterTo(rc); });
    row.append(rTile);
    
    var tdDelete = DOM.td();
    var deleteButton = DOM.button('Delete').addClass('btn btn-mini btn-danger');
    deleteButton.click(function(){
      ZZ.deleteZim(z,function(response) {
        ///console.log('response',response);
        if (response.match(/OK/)) {
          ZZ.cache.removeZim(z);
          self.refresh();  
        }
      });
    });
    tdDelete.append(deleteButton);
    row.append(tdDelete);
    return row;
  };


  self.recenterTo = function(concept) {
  
    if (!concept) { return false; }
  
    self.currentConcept = concept;
    history.push(concept);
    
    ///console.log('recent!!!!',concept);

    ZZ.history.push(concept);
    
  
    self.refreshThumb(concept);  
    self.refreshPDFs(concept);
    self.refreshRegularURLs(concept);
    self.refreshYouTube(concept);


    link.html('');
    var langAnswer = concept.receive(ZZ.langConcept);
    link.html(langAnswer.textResponses.shift() || '');
    link.attr('href',concept.linkify());



    table.empty();
    
    //currentConcept = concept;
    ///recvCol.append(ZZ.badassLink(currentConcept));
    var headerRow = DOM.tr();
    headerRow.append(DOM.td('<strong>receiver</strong> (it)'));
    headerRow.append(DOM.td('<strong>message</strong> (when asked...)'));
    headerRow.append(DOM.td('<strong>response</strong> (responds with...)'));
    headerRow.append(DOM.td('&nbsp;'));    
    table.append(headerRow);

    concept.zimsInvolved().each(function(z) {
      var row = self.getZimRow(z);
      table.append(row);
    });

    concept.zamsInvolved().each(function(z) {

      var row = DOM.tr();
      var recvTile = DOM.td().addClass('tile');
      var recvC = z.receiverConcept();
      recvTile.append(ZZ.badassLink(recvC));
      recvTile.click(function() { 
        ////console.log('cliiick');
        self.recenterTo(recvC); 
      });
      row.append(recvTile);


      var mTile = DOM.td().addClass('tile');
      var mc = z.messageConcept();
      mTile.append(ZZ.badassLink(mc));
      mTile.click(function() { self.recenterTo(mc); });
      row.append(mTile);
            
      var rTile = DOM.td().addClass('tile');
      var rc = z.spec.response;
      rTile.append(rc);
      row.append(rTile);


      var tdDelete = DOM.td();
      var deleteButton = DOM.button('Delete').addClass('btn btn-mini btn-danger');
      deleteButton.click(function(){        
        ZZ.deleteZam(z,function(response) {
          ///console.log('response',response);
          if (response.match(/OK/)) {
            ZZ.cache.removeZam(z);
            self.refresh();  
          }
        });
      });
      tdDelete.append(deleteButton);
      row.append(tdDelete);


      table.append(row);
    });

    self.publish('onRecenterTo',concept);
    self.publish('recenter',concept);


    return false;////////////////ZING!!!
  };


  self.refresh = function() {
    self.recenterTo(self.currentConcept);
  };

  self.renderOn = function(wrap) {


    ////wrap.append(DOM.h1('Gallery'));
    wrap.append(thumbsDiv);
    wrap.append(pdfDiv);
    wrap.append(urlDiv);
    wrap.append(youtubeDiv);
    wrap.append(link);
    wrap.append(DOM.div().css('clear','both'));
    wrap.append(back);
    wrap.append(table);
    self.recenterTo(self.currentConcept);
    /**
    currentConcept.messageConceptsInvolved().each(function(mc){
      msgCol.append(ZZ.badassLink(mc));
    });
    **/
  };
  return self;
}




ZZ.Widgets.ImageGallery = function(spec){
  var self = {};
  self.renderOn = function(wrap) {
    var button = DOM.button('View Gallery').addClass('btn btn-primary');

    spec.images.each(function(image){
    
    
    });

  
  
  }
  return self;
}
