if (typeof ZZ == "undefined") { var ZZ = {}; }
ZZ.Widgets = {};

ZZ.Concept = false; //slot



ZZ.defaultGlyphURL = 'http://zimzam.dev.bratliensoftware.com/images/zimzam-white.png';


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
  
  /*
  self.delete = function(callback) {
    return ZZ.deleteZam(self,callback);
  }
  */
  
  return self;
};


ZZ.cache = {};


ZZ.cache.zam_id = {};
ZZ.cache.zim_id = {};



ZZ.cache.zimsInvolving = {};
ZZ.cache.zamsInvolving = {};



ZZ.cache.addZim = function(zim) {
  if (typeof ZZ.cache.zimsInvolving[zim.spec.receiver] == "undefined") {
    ZZ.cache.zimsInvolving[zim.spec.receiver] = [];
  }
  if (typeof ZZ.cache.zimsInvolving[zim.spec.message] == "undefined") {
    ZZ.cache.zimsInvolving[zim.spec.message] = [];
  }
  if (typeof ZZ.cache.zimsInvolving[zim.spec.response] == "undefined") {
    ZZ.cache.zimsInvolving[zim.spec.response] = [];
  }

  ZZ.cache.zimsInvolving[zim.spec.receiver].push(zim);
  ZZ.cache.zimsInvolving[zim.spec.message].push(zim);
  ZZ.cache.zimsInvolving[zim.spec.response].push(zim);
}

ZZ.cache.addZam = function(zam) {
  if (typeof ZZ.cache.zamsInvolving[zam.spec.receiver] == "undefined") {
    ZZ.cache.zamsInvolving[zam.spec.receiver] = [];
  }
  if (typeof ZZ.cache.zamsInvolving[zam.spec.message] == "undefined") {
    ZZ.cache.zamsInvolving[zam.spec.message] = [];
  }

  ZZ.cache.zamsInvolving[zam.spec.receiver].push(zam);
  ZZ.cache.zamsInvolving[zam.spec.message].push(zam);
}

ZZ.cache.removeZim = function(z) {
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
  }  

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


ZZ.Widgets.ConceptSearch = function(spec) {
  var self = {};
  var backplane = BSD.PubSub({});
  self.publish = backplane.publish;
  self.subscribe = backplane.subscribe;



  var results = [];
  var choice = spec.choice || false;
  
  var placeholder = spec.placeholder || 'concept search';
  

  var textBox = DOM.input().attr('type','text').attr('placeholder',placeholder).addClass('search-query');
  textBox.blur(function() {
    self.publish('blur',self);
  });
  
  self.focus = function() {
    ///console.log('i was told to focus');
    
    textBox.focus();
  }

    
  var resultsUL = DOM.ul().addClass('search-results');

  var stoner = ZZ.Widgets.Procrastinator({
    callback: function() {

      var query = textBox.val();
      if (query.length == 0 ) { 
        backplane.publish('search-results',[]); //empty    
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
  
      var conjured = filtered.map(function(spec) { return ZZ.Zam(spec).receiverConcept(); });
      backplane.publish('search-results',conjured);
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
          backplane.publish('search-results',concepts);
        }
        else {
          backplane.publish('use-search-result',concept);
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

  backplane.subscribe('search-results',function(concepts) {
    results = concepts;
    choice = false;
    ///console.log('payload',concepts);
    textBox.show();
    self.redrawUL(concepts);
  });
  
  
  backplane.subscribe('use-search-result',function(o) {
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
      if (c == ZZ.keycodes.TAB) { 
        if (results.length == 1) {
          return false; //we're already at the result we want. keydown would have already handled this stoner.beg
        }
      }
      ////console.log('KEYUP!!!');
      stoner.beg();
    });
    
    textBox.keydown(function(e){
      var c = e.keyCode || e.which;
      
      ////console.log('c',c);
      if (c == ZZ.keycodes.TAB) {
        ///console.log('TAB');
        
        
        
        if (results.length == 1) {
          e.preventDefault();
          ///console.log('PREVENTING DEFAULT!!!!');
          self.publish('blur',self);
        
        
          backplane.publish('use-search-result',results[0]);
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
      backplane.publish('use-search-result',choice);
    }
  };
  
  return self;

};

ZZ.Widgets.Trainer = function(spec) {
  var self = {};

  var backplane = BSD.PubSub({});
  self.publish = backplane.publish;
  self.subscribe = backplane.subscribe;

  var inputs = {
    receiver: spec.concept,
    message: false,
    response: false
  };
  
  var messageSearchWidget = ZZ.Widgets.ConceptSearch({
    placeholder: 'message search',
    callback: function(concept) {
      inputs.message = concept;
      ///console.log('inputs',inputs);
    }
  });

  var responseSearchWidget = ZZ.Widgets.ConceptSearch({
    placeholder: 'response search',
    callback: function(concept) {
      inputs.response = concept;
      ////console.log('inputs',inputs);
    }
  });
  
  self.recenterTo = function(concept) {
    inputs.receiver = concept;
    inputs.message = false;
    inputs.response = false;
    self.refresh();
  };
  
  
  self.save = function() {
    inputs.response = inputs.response || responseSearchWidget.text(); /// dual purpose!! :D
    var failure = ['receiver','message','response'].detect(function(prop){
      return inputs[prop] == false; 
    });
    if (failure) {
      alert('missing ' + failure);
      return false;
    }      


    //console.log('inputs',inputs);
    //return false;



    if (typeof inputs.response == "string") {
      newZam({ receiver: inputs.receiver.id, message: inputs.message.id, response: inputs.response },function(z) {
        backplane.publish('onSave',z);
      });
    }
    else {
      newZim({ receiver: inputs.receiver.id, message: inputs.message.id, response: inputs.response.id },function(z) {
        backplane.publish('onSave',z);
      });      
    }      
  };



  var header = DOM.h2();
    var tdReceiver = DOM.td();


  self.refresh = function() {
    ////console.log('refresh called asdf');
  
    header.empty();
    var translations = inputs.receiver.textResponsesToConcept(ZZ.langConcept);
    header.append(DOM.span('Train '));
    if (translations.length == 0) {
      header.append(ZZ.badassLink(inputs.receiver));
    }
    else {
      header.append(DOM.a(translations[0]).attr('href',inputs.receiver.linkify()));
    }
    header.append(DOM.span(' how to respond'));

    tdReceiver.html(ZZ.badassLink(inputs.receiver));

    messageSearchWidget.reset();
    responseSearchWidget.reset();
  };

  self.renderOn = function(wrap) {

  
    var table = DOM.table().addClass('table table-striped table-bordered table-condensed');
    
    var headerRow = DOM.tr();    
    headerRow.append(DOM.td('<strong>receiver</strong> (it)'));
    headerRow.append(DOM.td('<strong>message</strong> (when asked...)'));
    headerRow.append(DOM.td('<strong>response</strong> (responds with...)'));
    
    table.append(headerRow);
    
    var row = DOM.tr();
    
        
    var tdMessage = DOM.td();
    
    backplane.subscribe('onSave',function(o){
      ///console.log('wheee');
      setTimeout(function(){
            messageSearchWidget.focus();
      },300);
    });
    
    messageSearchWidget.renderOn(tdMessage); 
    
    
    var tdResponse = DOM.td();
    
    var saveButton = DOM.button('Save').addClass('btn btn-primary pull-right');
    saveButton.click(function() {
      self.save();
    });
    
    messageSearchWidget.subscribe('blur',function(o){
      ////console.log('OMG I HEARD THAT');
      responseSearchWidget.focus();
    });
    
    
    responseSearchWidget.subscribe('enter/return pressed',function(o) {
      self.save();
    });



    responseSearchWidget.renderOn(tdResponse);


    tdResponse.append(DOM.div('&nbsp;').css('clear','both'));
    
    var tdSave = DOM.td();
    tdSave.append(saveButton);

    row.append(tdReceiver);    
    row.append(tdMessage);    
    row.append(tdResponse);    
    row.append(tdSave);    
    table.append(row);
    
    wrap.append(header);
    wrap.append(table);


    messageSearchWidget.focus();
    self.refresh(wrap);  

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
        var zam = ZZ.Zam(o);
        ZZ.cache.addZam(zam);        
        callback(zam);
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
        var zim = ZZ.Zim(o);
        ZZ.cache.addZim(zim);        
        callback(zim);      
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
  
  function getZimsWhere(o) {
    var thisData = { action: 'get_zims_where' };
    if (typeof o.receiver != "undefined") { thisData.receiver = o.receiver; }
    if (typeof o.message != "undefined") { thisData.message = o.message; }
    if (typeof o.response != "undefined") { thisData.response = o.response; }
    var r = jQuery.ajax({
      type: 'POST',
      url: ZZ.baseURL + '/ws',
      data: thisData,
      async: false
    }).responseText;
    var zims = eval('(' + r + ')');
    var conjured = zims.map(function(spec) { return ZZ.Zim(spec); });
    conjured.each(function(zim) {
      ZZ.cache.zim_id[zim.zim_id] = zim;
    });
    return conjured;      
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
    conjured.each(function(zam) {
      ZZ.cache.zam_id[zam.zam_id] = zam;
      ZZ.cache.addZam(zam);      
    });
    return conjured;      
  }

  function getZamReceivers(msg,resp) { 
    var them = getZamsWhere({
      message: msg,
      response: resp
    });
    
    them.each(function(z) { ZZ.cache.addZam(z); });
        
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
ZZ.cache.latitudeConcept = ZZ.Concept({ id: getZamReceivers(1,'latitude')[0] });
ZZ.cache.longitudeConcept = ZZ.Concept({ id: getZamReceivers(1,'longitude')[0] });


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
      
      ///console.log('responses',responses);
      
      
      
      
      
      ///var conceptResponses = accum.conceptResponsesToConcept(concept);
      ////var textResponses = accum.textResponsesToConcept(concept);
      ////console.log('cR',conceptResponses);
      ////console.log('tR',textResponses);
        ///accum = conceptResponses.shift(); 
    });


    return accum;    
    
    /* logic programming, hal abelson, sicp 8A: Logic Programming
    logic to express what is true
    logic to check what is true
    logic to find out what is true
    */
    
    
    

    ///console.log('result',result);
    
    
    ///return result;
    
    /*  
    eachify(tokens).eachPCN(function(o){
        console.log('current',o.current,'next',o.next);
    
      var receiverConcepts = getZamReceivers(ZZ.lang,o.current).collect(function(id) { return ZZ.Concept({ id: id }); });
      console.log('receiverConcepts',receiverConcepts);
    });
  
  
    return 'OK';
    */
  
  };
  
  
  return self;
}


ZZ.Widgets.Uploader = function(spec) {
  var self = {};
  var thumb = DOM.img().attr('display','none').css('width','40px');
  var logoFileInput = DOM.input().attr('type','file');
  var glyphURLInput = DOM.input().addClass('search-box search-query').attr('placeholder','URL');


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
    xhr.send(formData);  // multipart/form-data
  };

  self.renderOn = function(wrap) {
    wrap.append(DOM.h1('glyph uploader'));
    wrap.append(thumb);
    logoFileInput.change(function() {
      self.uploadFiles(ZZ.baseURL + '/ws', this.files);
    });

    glyphURLInput.change(function() {
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
    });

    wrap.append(glyphURLInput);
    //wrap.append(DOM.div().css('clear','both'));
    wrap.append(logoFileInput);


  };
  self.reveal = function(url) {
    thumb.attr('src',url);
    thumb.show();  
    spec.gossip.publish('new-upload-url',url);
    logoFileInput.val('');
    glyphURLInput.val('');  
  }
  return self;
};

ZZ.Widgets.Gallery = function(spec) {
  var self = {};

  var columns = [];
  var history = [];

  var backplane = BSD.PubSub({});

  self.publish = backplane.publish;
  self.subscribe = backplane.subscribe;



  self.currentConcept = spec.concept;

  self.newColumn = function() {
    return DOM.div().addClass('span2');
  }


  var link = DOM.a();



  //var thumb = DOM.img().addClass('thumb');

  var thumbsDiv = DOM.div().addClass('thumbs');
  var thumbs = [];




  var table = DOM.table().addClass('table');
  var back = DOM.button('Back').addClass('btn btn-mini btn-inverse');

  back.click(function(){
    console.log('back click');
    if (history.length < 2) { return false; }
    history.pop();
    self.recenterTo(history.pop());    
  });


  self.refreshThumb = function(concept) {
    thumbsDiv.empty();        
    var glyphURLs = concept.glyphURLs();
    var thumbsClass = 'thumb-' + glyphURLs.length;
    glyphURLs.each(function(gu){
      var thumb = DOM.img().addClass(thumbsClass);
      thumb.attr('src',gu);
      thumbsDiv.append(thumb);
    });
  };


  self.recenterTo = function(concept) {
    self.currentConcept = concept;
    history.push(concept);
    
  
    self.refreshThumb(concept);  


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
      var row = DOM.tr();

      var recvTile = DOM.td().addClass('tile');
      var recvC = z.receiverConcept();
      recvTile.append(ZZ.badassLink(recvC));
      recvTile.click(function() { self.recenterTo(recvC); });
      row.append(recvTile);


      var mTile = DOM.td().addClass('tile');
      var mc = z.messageConcept();
      mTile.append(ZZ.badassLink(mc));
      mTile.click(function() { self.recenterTo(mc); });
      row.append(mTile);
      
      var rTile = DOM.td().addClass('tile');
      var rc = z.responseConcept();
      rTile.append(ZZ.badassLink(rc));
      rTile.click(function() { self.recenterTo(rc); });
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

    return false;////////////////ZING!!!
  };


  self.refresh = function() {
    self.recenterTo(self.currentConcept);
  };

  self.renderOn = function(wrap) {
    ////wrap.append(DOM.h1('Gallery'));
    wrap.append(thumbsDiv);
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








