ZZ.Widgets.Trainer = function(spec) {
  var self = BSD.PubSub({});
  var backplane = self;

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
      //alert('missing ' + failure);
      return false;
    }      


    //console.log('inputs',inputs);
    //return false;



    if (typeof inputs.response == "string") {
      newZam({ receiver: inputs.receiver.id, message: inputs.message.id, response: inputs.response },function(z) {
        self.publish('onSave',z);
      });
    }
    else {
      newZim({ receiver: inputs.receiver.id, message: inputs.message.id, response: inputs.response.id },function(z) {
        self.publish('onSave',z);
      });      
    }      
  };



  var header = DOM.h2();
    var tdReceiver = DOM.td();


  self.refresh = function() {
    //////console.log('refresh called asdf');
  
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
    
    self.subscribe('onSave',function(o){
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


    //////messageSearchWidget.focus();
    self.refresh(wrap);  

  };

  return self;  
};
