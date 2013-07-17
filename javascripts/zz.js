if (typeof ZZ == "undefined") { var ZZ = {}; }
ZZ.Widgets = {};

ZZ.Concept = false; //slot



ZZ.keycodes = {
    TAB: 9,
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
    return resp;
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
      newConcept(function(concept) {
        ///console.log('concept',concept);
        window.location.href = concept.linkify();
      });
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
          console.log('got an Oh',o);
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
    var defaultGlyphURL = 'http://zimzam.dev.bratliensoftware.com/images/zigzag-line7.gif';

    var glyphs = concept.glyphURLs();
    /////console.log('glyphs',glyphs);
    
    if (glyphs.length == 0) {
      glyphs.push(defaultGlyphURL);
    }    
    var link = DOM.a().attr('href',concept.linkify());
    glyphs.each(function(url) {     
      link.append(DOM.img().attr('src',url).attr('width','20px'));
    });

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
  var backplane = BSD.PubSub({});

  var self = {};


  var results = [];
  var choice = false;

  var textBox = DOM.input();
  var resultsUL = DOM.ul().addClass('search-results');

  var stoner = ZZ.Widgets.Procrastinator({
    callback: function() {
    
      ////console.log(textBox.val());
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
  
      var conjured = zams.map(function(spec) { return ZZ.Zam(spec).receiverConcept(); });
      ///////ZZ.cache.zamsInvolving[spec.id] = conjured;
      //console.log('conjured',conjured);
      backplane.publish('search-results',conjured);
    }  
  });

  self.renderOn = function(wrap) {
    textBox.keyup(function(e){
      
    
      stoner.beg();
    });
    
    textBox.keydown(function(e){
      var c = e.keyCode || e.which;
      ////console.log('c',c);
      if (c == ZZ.keycodes.TAB) {
          console.log('tab');
        if (results.length == 1) {
            choice = results[0];
            ////handle.html('lose it');
            textBox.hide();
            spec.callback(choice);

        }
        return false;
      }
    });
    
    
    

    backplane.subscribe('search-results',function(concepts) {
      results = concepts;
    
    
      ///console.log('payload',concepts);
      resultsUL.empty();
      
      
      
            
      concepts.each(function(concept) {
        var li = DOM.li();
        var resultDiv = DOM.div().addClass('concept-search-result');
        var link = ZZ.badassLink(concept);        
        var handle = DOM.button('use it').addClass('btn btn-mini pull-right');
        handle.click(function() {
          if (!choice) { 
            //we're making the choice now
            choice = concept;
            handle.html('lose it');
            textBox.hide();
            li.siblings().hide();
            spec.callback(choice);
          }
          else {
            //un-choose
            choice = false;
            handle.html('use it');
            textBox.show();
            li.siblings().show();
          }        
        });
        resultDiv.append(link);
        resultDiv.append(handle);
        li.append(resultDiv);
        resultsUL.append(li);
      });
    });

    wrap.append(textBox);
    wrap.append(DOM.div('&nbsp;').css('clear','both'));
    wrap.append(resultsUL);    

  };
  
  return self;

};

ZZ.Widgets.Trainer = function(spec) {
  var self = {};

  var inputs = {
    receiver: spec.concept,
    message: false,
    response: false
  };

  self.renderOn = function(wrap) {

    var translations = spec.concept.textResponsesToConcept(ZZ.langConcept);
    var header = DOM.h2();
    header.append(DOM.span('Train '));
    
    if (translations.length == 0) {
      header.append(ZZ.badassLink(spec.concept));
    }
    else {
      header.append(DOM.a(translations[0]).attr('href',spec.concept.linkify()));
    }
    header.append(DOM.span(' how to respond'));
    wrap.append(header);
  
  
  
    var table = DOM.table().addClass('table table-striped table-bordered table-condensed');
    
    var headerRow = DOM.tr();    
    headerRow.append(DOM.td('<strong>receiver</strong> (it)'));
    headerRow.append(DOM.td('<strong>message</strong> (when asked...)'));
    headerRow.append(DOM.td('<strong>response</strong> (responds with...)'));
    
    table.append(headerRow);
    
    var row = DOM.tr();
    
    var tdReceiver = DOM.td();
    tdReceiver.html(ZZ.badassLink(spec.concept));
        
    var tdMessage = DOM.td();
    var messageSearchWidget = ZZ.Widgets.ConceptSearch({
      callback: function(concept) {
        inputs.message = concept;
        console.log('inputs',inputs);
      }
    });
    messageSearchWidget.renderOn(tdMessage); 
    
    
    var tdResponse = DOM.td();
    
    var saveButton = DOM.button('Save').addClass('btn btn-primary pull-right');
    saveButton.click(function() {
      ///console.log('save',inputs);
      var failure = ['receiver','message','response'].detect(function(prop){
        return inputs[prop] == false; 
      });
      if (failure) {
        alert('missing ' + failure);
        return false;
      }      
      if (typeof inputs.response == "string") {
        newZam({ receiver: inputs.receiver.id, message: inputs.message.id, response: inputs.response },function(id) {
          console.log('wooo, new ZAM',id);
        });
      }
      else {
        newZim({ receiver: inputs.receiver.id, message: inputs.message.id, response: inputs.response.id },function(id) {
          console.log('wooo, new ZIM',id);
        });      
      }
      setTimeout(function(){
        window.location.reload();
      },2000);
    });
    var responseSearchWidget = ZZ.Widgets.ConceptSearch({
      callback: function(concept) {
        inputs.response = concept;
        console.log('inputs',inputs);
      }
    });
    responseSearchWidget.renderOn(tdResponse);

    var responseTextBox = DOM.input();
    responseTextBox.change(function(){
      inputs.response = this.value;
        console.log('inputs',inputs);
    });
    
    
    var textLabel = DOM.label('or the text <br/>');
    textLabel.append(responseTextBox);
    tdResponse.append(textLabel);    
    
    
    tdResponse.append(DOM.div('&nbsp;').css('clear','both'));
    
    var tdSave = DOM.td();
    tdSave.append(saveButton);

    row.append(tdReceiver);    
    row.append(tdMessage);    
    row.append(tdResponse);    
    row.append(tdSave);    
    table.append(row);
    
    
    wrap.append(table);
  };

  return self;  
};




  function newConcept(callback) {
    jQuery.ajax({
      url: '/ws',
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
        callback(result);
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


  function updateZamResponse(o,callback) {
    jQuery.ajax({
      url: '/ws',
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
        console.log(r);
      }
    });
      
  }


ZZ.cache.glyphURLConcept = ZZ.Concept({ id: getZamReceivers(1,'glyph url')[0] });


ZZ.Reader = function(spec) {
  var self = {};
  
  self.eval = function(stream) {
  
    var tokens = stream.split(/\ +/);
  
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
    
    concepts.eachPCN(function(o) {
      ///console.log('o',o);
      
      var responses = accum.receive(o.current);
      
      if (responses.textResponses.length > 0) {
        return responses.textResponses[0];
      }
      else if (responses.conceptResponses.length > 0) {
        accum = responses.conceptResponses[0]
      }
      else {
        console.log('died at',o.current,o);
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


/*
function get_zam_receivers($msg,$response) {
  $receivers = array_map(function($zam) { return $zam->receiver; },get_zams_with_message_and_response($msg,$response));
  return $receivers;
}



  
get_first_zam_receiver(1,'glyph url'));  
*/




ZZ.Widgets.Uploader = function(spec) {
  var self = {};
  var thumb = DOM.img().attr('display','none').css('width','40px');
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
    var logoFileInput = DOM.input().attr('type','file');
    logoFileInput.change(function() {
      self.uploadFiles('/ws', this.files);
    });
    wrap.append(logoFileInput);
    var urlLabel = DOM.label('or, an image URL');
    var glyphURLInput = DOM.input();
    glyphURLInput.change(function() {
      jQuery.ajax({
        url: '/ws',
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
    urlLabel.append(glyphURLInput);
    wrap.append(urlLabel);
  };
  self.reveal = function(url) {
    thumb.attr('src',url);
    thumb.show();  
    spec.gossip.publish('new-upload-url',url);
  }
  return self;
};

ZZ.Widgets.Gallery = function(spec) {
  var self = {};

  var columns = [];
  var history = [];

  var currentConcept = spec.concept;


  self.newColumn = function() {
    return DOM.div().addClass('span2');
  }


  var recvCol = self.newColumn();
  var msgCol = self.newColumn();
  var respCol = self.newColumn();

  

  columns.push(self.newColumn());
  var currentColumn = columns[0];

  self.recenterTo = function(concept) {
    recvCol.empty();
    msgCol.empty();
    respCol.empty();

    currentConcept = concept;
    ///recvCol.append(ZZ.badassLink(currentConcept));

    concept.zimsInvolved().each(function(z) {

      var recvTile = DOM.div().addClass('tile');
      var recvC = z.receiverConcept();
      recvTile.append(ZZ.badassLink(recvC));
      recvTile.click(function() { self.recenterTo(recvC); });
      recvCol.append(recvTile);


      var mTile = DOM.div().addClass('tile');
      var mc = z.messageConcept();
      mTile.append(ZZ.badassLink(mc));
      mTile.click(function() { self.recenterTo(mc); });
      msgCol.append(mTile);
      
      var rTile = DOM.div().addClass('tile');
      var rc = z.responseConcept();
      rTile.append(ZZ.badassLink(rc));
      rTile.click(function() { self.recenterTo(rc); });
      respCol.append(rTile);
    });

    concept.zamsInvolved().each(function(z) {

      var recvTile = DOM.div().addClass('tile');
      var recvC = z.receiverConcept();
      recvTile.append(ZZ.badassLink(recvC));
      recvTile.click(function() { self.recenterTo(recvC); });
      recvCol.append(recvTile);


      var mTile = DOM.div().addClass('tile');
      var mc = z.messageConcept();
      mTile.append(ZZ.badassLink(mc));
      mTile.click(function() { self.recenterTo(mc); });
      msgCol.append(mTile);
            
      var rTile = DOM.div().addClass('tile');
      var rc = z.spec.response;
      rTile.append(rc);
      respCol.append(rTile);
    });
  }

  self.renderOn = function(wrap) {
    wrap.append(DOM.h1('Gallery'));


    wrap.append(recvCol);
    wrap.append(msgCol);     
    wrap.append(respCol);     
    self.recenterTo(currentConcept);
    /**
    currentConcept.messageConceptsInvolved().each(function(mc){
      msgCol.append(ZZ.badassLink(mc));
    });
    **/
  };
  return self;
}








