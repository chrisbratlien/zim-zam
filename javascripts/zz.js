if (typeof ZZ == "undefined") { var ZZ = {}; }
ZZ.Widgets = {};


ZZ.Concept = function(spec) {
  var self = spec;
  self.spec = spec;
  self.linkify = function() {
    return ZZ.baseURL + '/concept/' + spec.id;
  };  
  return self;
};


ZZ.Widgets.NewConceptForm = function(spec) {

  //console.log('spec',spec);

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
  
  
  
  
   
