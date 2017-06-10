<!DOCTYPE html>
<html>
  <title>Random Notes</title>
<head>
<script src="//code.jquery.com/jquery.min.js"></script>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
  <meta charset="utf-8">
  <title>JS Bin</title>
  
  <script type="text/javascript" src="//cdn.dev.bratliensoftware.com/javascript/array.js"></script>
  <script type="text/javascript" src="//cdn.dev.bratliensoftware.com/javascript/dom.js"></script>
  <script src="//cdn.dev.bratliensoftware.com/javascript/color.js"></script>
  <script src="//cdn.dev.bratliensoftware.com/javascript/bsd.pubsub.js"></script>
  <script src="//cdn.dev.bratliensoftware.com/javascript/bsd.widgets.procrastinator.js"></script>
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <style type="text/css">
    table.my-table tr th  {  padding: 3px; background: purple; color: white; text-align: center;}
    table td { padding-bottom: 1.1em; }
    .checkbox-cell { width: 60px;  }
    .checkbox-cell { vertical-align: middle; }
    table.my-table tr th.other-head  { background: teal; }
    
    
    table.my-table tr.header th { background: purple; }
    
    
    textarea { width: 100%; height: auto; }
    
  </style>




</head>
<body>
  <textarea id="notes"></textarea>

  <script type="text/javascript">
  
  
  var notes = localStorage.notes;
  if (!notes) {
    notes = '';
  }
 
  var myEditor = false; 
  var waiter = BSD.Widgets.Procrastinator({ timeout: 4000 });
  
  var campfire = BSD.PubSub({});
  
  var notesInput = jQuery('#notes');
  
  notesInput.text(notes);
  
  notesInput.keyup(function(){
    waiter.beg(campfire,'notes-update',this.value);
    ////console.log(this.value);
  });
  
  
  notesInput.height(jQuery(window).height());
  
  
  campfire.subscribe('notes-update',function(o){
    console.log('update!!');
    localStorage.notes = o;
  });
 
 campfire.subscribe('save-from-editor',function() {
			localStorage.notes = myEditor.getContent();
	});
  
  
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
	<script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
  <script>
myEditor = false;
	tinymce.init({ 
		selector:'textarea',
		init_instance_callback: function (editor) {
			myEditor = editor;
    	editor.on('click', function (e) {
      	console.log('Element clicked:', e.target.nodeName);
    	});
			editor.on('keyup',function(e) {
				///console.log('keyup',e);
				waiter.beg(campfire,'save-from-editor');		
			});
  	}
	});

</script>
</body>
</html>
