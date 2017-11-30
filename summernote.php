<!DOCTYPE html>
<html lang="en">
  <title>Summer Notes</title>
<head>
<!-- include libraries(jQuery, bootstrap) -->
<link href="//netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script> 
<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 

<!-- include summernote css/js-->
<link href="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.6/summernote.css" rel="stylesheet">
<script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.6/summernote.js"></script>


<!-- old bootstrap and jquery 
<script src="//code.jquery.com/jquery.min.js"></script>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
-->
  <meta charset="utf-8">
 
  <script type="text/javascript" src="js/array.js"></script>
  <script type="text/javascript" src="js/dom.js"></script>
  <script src="//cdn.dev.bratliensoftware.com/javascript/color.js"></script>
  <script src="js/bsd.pubsub.js"></script>
  <script src="js/bsd.widgets.procrastinator.js"></script>
  <script src="js/bsd.storage.js"></script>
  <script src="js/bsd.remotestorage.js"></script>
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <style type="text/css">
    table.my-table tr th  {  padding: 3px; background: purple; color: white; text-align: center;}
    table td { padding-bottom: 1.1em; }
    .checkbox-cell { width: 60px;  }
    .checkbox-cell { vertical-align: middle; }
    table.my-table tr th.other-head  { background: teal; }
    
    
    table.my-table tr.header th { background: purple; }
    
    
    textarea { width: 100%; height: auto; }
    
    .key-tab { 
      background: #abf;
      font-weight: bold; 
      padding: 0.5rem;
    }

  </style>




</head>
<body>
  
<label>Key <input type="text" class="key" value="notes" /> </label>
<select class="toc"></select>
<label>Local <input type="radio" class="storage-local"  name="storage-where" value="local" checked="checked" /> </label>
<label>Remote <input type="radio" class="storage-remote" name="storage-where"  value="remote" /> </label>
</label>
  <div class="btn-group">
    <button class="btn btn-primary btn-load">Load</button>
    <button class="btn btn-primary btn-save">Set Key</button>
  </div>
  <div class="notes-wrap">
    <span class="key-tab"></span>
    <textarea id="notes"></textarea>    
  </div>
  <script type="text/javascript">


//FIXME: move this
BSD.sorter = function(selectorFunc) {
  var sortFunc = function(a,b) {
    var sA = selectorFunc(a);
    var sB = selectorFunc(b);
    if (sA < sB) { return -1; }
    if (sA > sB) { return 1; }
    return 0;
  };
  return sortFunc;
};
  



  var campfire = BSD.PubSub({});
  
  BSD.storage = BSD.Storage('ZZ::summernote::');

  BSD.remoteZZ = BSD.RemoteStorage({ 
    prefix: 'ZZ::summernote::', 
    url: location.href.replace(/summernote/,'ws')
  });


  
  BSD.key = 'notes';
  var inputKey = jQuery('input.key');
  inputKey.change(function(){
  });

  var vault = BSD.storage;
  var storageWhere = jQuery('input[type="radio"][name="storage-where"]');
  storageWhere.change(function(){
    vault = (this.value.match(/local/)) ? BSD.storage : BSD.remoteZZ;
    console.log('vault',vault);
    campfire.publish('get-toc');
  });



function loadKey(key) {
    BSD.key = key;
    campfire.publish('key-change',key);

    vault.getItem(BSD.key,function(data){
      notesInput.summernote('code',data);
    },function(e){
      notesInput.summernote('reset');
      campfire.publish('insert-toc',BSD.key);
      //console.log(e,'e?');
      //alert(e);
    });
}


  campfire.subscribe('key-change',function(key){
    jQuery('.key-tab').html(key);
  });

  var btnLoad = jQuery('.btn-load');
  btnLoad.click(function(){
    loadKey(inputKey.val());
  });

  var btnSave = jQuery('.btn-save');
  btnSave.click(function(){
    BSD.key = inputKey.val();
  });
 

  var ddTOC = jQuery('.toc');



  var waiter = BSD.Widgets.Procrastinator({ timeout: 4000 });
  
  var notesInput = jQuery('#notes');
  
//  notesInput.text(notes);
  //notesInput.summernote();
  notesInput.summernote({
    onChange: function() {
      console.log('woaotoatow');
    },
    onBlur: function() {
      console.log('woo?')
    }
  })
  //////notesInput.summernote('code',notes);
  notesInput.on('summernote.change',function(e){
    //console.log('s.c',e);
    var content = notesInput.summernote('code');
    //console.log("content",content);
    waiter.beg(campfire,'notes-update',content);
  })
 
  /**
  notesInput.keyup(function(){
    waiter.beg(campfire,'notes-update',this.value);
    ////console.log(this.value);
  });
  **/
  
  notesInput.height(jQuery(window).height());
  
  
  campfire.subscribe('notes-update',function(o){
    vault.setItem(BSD.key,o);
    ////campfire.publish('insert-toc',BSD.key);
  });
 
  campfire.subscribe('init-toc',function(toc,selected){
    vault.setItem('toc',JSON.stringify(toc),function(){
      campfire.publish('toc-updated',toc,selected);
    });
  });

  campfire.subscribe('get-toc',function(){
    vault.getItem('toc',function(data){
      var toc = JSON.parse(data);
      campfire.publish('toc-updated',toc,'notes');    
    });
  });

  campfire.subscribe('insert-toc',function(key){
    vault.getItem('toc',function(data) {
      var toc = JSON.parse(data);
      toc.push(key);
      /////toc.sort().unique();
      toc = toc.sort(BSD.sorter(function(item){ return item.toUpperCase(); })).unique();
      vault.setItem('toc',JSON.stringify(toc));
      campfire.publish('toc-updated',toc,key);
    },function(){
      campfire.publish('init-toc',[key]);
    });
  });

  campfire.subscribe('toc-updated',function(toc,selected){
    ddTOC.empty();
    toc = toc.sort(BSD.sorter(function(item){ return item.toUpperCase(); })).unique();
    toc.forEach(function(key){
      var opt = DOM.option(key);
      if (selected && key == selected) {
        opt.attr('selected',true);
      }
      ddTOC.append(opt);
    });
  });


  ddTOC.change(function(){
    inputKey.val(this.value);
  });



  campfire.publish('get-toc');


/**
 campfire.subscribe('save-from-editor',function() {
			localStorage.notes = myEditor.getContent();
	});
  **/
  
  
  function getRandomArbitrary(min, max) {
      return Math.random() * (max - min) + min;
  }
  
  function gimme(len) {
    var qass = [];
    
    for (var i = 0; i < len; i += 1) {
  qass.push(String.fromCharCode(getRandomArbitrary(32,126)));
    }
    return qass.join("");   
  }
  
  function wall(len,wid) {
    var result = [];
    
    for (var i = 0; i < len; i += 1) {
      result.push(gimme(wid));
  
    }
    return result;
  }

</script>
</body>
</html>
