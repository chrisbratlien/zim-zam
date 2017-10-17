<!DOCTYPE html>
<html lang="en">
  <title>Todo</title>
<head>
<!-- include libraries(jQuery, bootstrap) -->
<link href="//netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script> 
<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 


<!-- old bootstrap and jquery 
<script src="//code.jquery.com/jquery.min.js"></script>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
-->
  <meta charset="utf-8">
 
  <script type="text/javascript" src="//cdn.dev.bratliensoftware.com/javascript/array.js"></script>
  <script type="text/javascript" src="//cdn.dev.bratliensoftware.com/javascript/dom.js"></script>
  <script src="//cdn.dev.bratliensoftware.com/javascript/color.js"></script>
  <script src="//cdn.dev.bratliensoftware.com/javascript/bsd.pubsub.js"></script>
  <script src="//cdn.dev.bratliensoftware.com/javascript/bsd.widgets.procrastinator.js"></script>
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
    .task { margin-left: 4rem; }    
  </style>




</head>
<body>
  
<label>Key <input type="text" class="key" value="todo" /> </label>
<label>Local <input type="radio" class="storage-local"  name="storage-where" value="local" checked="checked" /> </label>
<label>Remote <input type="radio" class="storage-remote" name="storage-where"  value="remote" /> </label>
</label>
  <div class="btn-group">
    <button class="btn btn-primary btn-load">Load</button>
    <button class="btn btn-primary btn-save">Set Key</button>
  </div>
  <script type="text/javascript">

  BSD.storage = BSD.Storage('ZZ::summernote::');

  BSD.remoteZZ = BSD.RemoteStorage({ 
    prefix: 'ZZ::summernote::', 
    url: location.href.replace(/summernote/,'ws')
  });



  BSD.key = 'todo';
  var inputKey = jQuery('input.key');
  inputKey.change(function(){
  });

  var vault = BSD.storage;
  var storageWhere = jQuery('input[type="radio"][name="storage-where"]');
  storageWhere.change(function(){
    vault = (this.value.match(/local/)) ? BSD.storage : BSD.remoteZZ;
    console.log('vault',vault);
  });



  var btnLoad = jQuery('.btn-load');
  btnLoad.click(function(){
    BSD.key = inputKey.val();
    vault.getItem(BSD.key,function(data){
      notesInput.summernote('code',data);
    },function(e){
      console.log(e,'e?');
      alert(e);
    });
  });

  var btnSave = jQuery('.btn-save');
  btnSave.click(function(){
    BSD.key = inputKey.val();
  });


  var waiter = BSD.Widgets.Procrastinator({ timeout: 4000 });
  var campfire = BSD.PubSub({});

BSD.Task = function(spec) {
  var subtasks = [];
  var self = BSD.PubSub({});
  var inner = DOM.div().addClass('inner task');
  self.addSubtask = function(task) {
    subtasks.push(task);
    self.refresh();
  };
  self.refresh = function() {
    inner.empty();
    var cb = DOM.input().attr('type','checkbox');
    inner.append(cb);
    inner.append('&nbsp;');
    inner.append(spec.name);
    if (subtasks.length > 0) {
      var panelSubs = DOM.div().addClass('subtasks');
      inner.append(panelSubs);
      subtasks.forEach(function(o){
        o.renderOn(panelSubs);
      });
    }    
  };
  self.renderOn = function(wrap) {
    wrap.append(inner);
    self.refresh();
  };
  return self;
};
 
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
