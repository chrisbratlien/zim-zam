<!DOCTYPE html>
<html lang="en">
  <title>Summer Notes</title>
<head>


<!-- include libraries(jQuery, bootstrap) -->
<link href="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script> 
<script src="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 

<!-- include summernote css/js-->
<link href="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.6/summernote.css" rel="stylesheet">
<script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.6/summernote.js"></script>


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
  notesInput.summernote('code',notes);
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
  <script>
  /**
      save.click(function(){
        var markupStr = textarea.summernote('code');
        console.log(markupStr,opts);
        jQuery.ajax({
            type: 'POST',
            ///url: TTI.themeURL + '/ws',
            url: TTI.baseURL + '/wp-admin/admin-ajax.php',
            data: { action: 'update_box', box: boxFilename, content: markupStr },
            success: function(a) {
              msg.html('saved!');
            },
            error: function(e) {
              msg.html('uh oh: ' + e);
              console.log(e);
            }
        });
      });
      inner.append(save);
      inner.append(msg);
      **/
      /**






  /********
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
********/



</script>
</body>
</html>
