<?php

$lang = $_SESSION['language'];

if (!empty($_POST) && array_key_exists('language',$_POST)) {
  $lang = $_POST['language'];
  $_SESSION['language'] = $lang;
}

add_filter('body_class',function($classes) {
  $classes[] = 'concept';
  return $classes;
});



if (empty($concept_id)) { $concept_id = $_GET['id']; }

add_action('wp_footer',function() use($lang,$concept_id) {




?>
<script type="text/javascript">

  var ZZ = {};
  
  ZZ.lang = <?php echo $lang; ?>;
  ZZ.conceptID = <?php echo $concept_id; ?>

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
      success: callback
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
  
  
  
   
   /*   
        var results = eval('(' + response + ')');
        console.log('results',results);
        
        var table = DOM.table();
        results.each(function(result) {
          var tr = DOM.tr();


          var imageUrl = '/images/fs.jpg';
          if (result.logo.length > 0) {
            imageUrl = '/data/' + result.logo;
          }

          tr.append(DOM.td(DOM.image().attr('src',imageUrl)));
          tr.append(DOM.td(result.Vendor_Name));
          tr.append(DOM.td(result.item));
          tr.append(DOM.td(result.details));
          table.append(tr);
        });
        
        resultsDiv.append(table);
      
        //////console.log('resp',resp);
     */


  jQuery(document).ready(function() {

    jQuery('#submit').click(function() {
    
      var message = jQuery('#message-select').val();
      var responseText = jQuery('#response-text').val();
      var responseID = jQuery('#response-id').val();
    
    
      if (responseText.length > 0) {
        //newConceptAndZam({ message: 1, response: 'All of Me' },function(r) { console.log(r); })
        newZam({ receiver: ZZ.conceptID, message: message, response: responseText },function(id) {
          console.log('wooo',id);
        });
      }
      else {
        newZim({ receiver: ZZ.conceptID, message: message, response: responseID },function(id) {
          console.log('woo',id);
        });
      }    
    });
    
  });


</script>
<?php
});


get_header();

require_language();


?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span9">
          <?php show_language_form(); ?>
          <div class="hero-unit">
            <?php
            
              echo '<ul class="concept-list">';
              foreach(get_zims_involving($concept_id) as $zim) {
                //////pp($zim,'zim');
                
                echo sprintf('<li>%s</li>',linkify_zim($zim,$lang));
                
                /***
                echo sprintf('<li><a href="%s">%s</a> <a href="%s">%s</a> <a href="%s">%s</a></li>',
                  concept_url($zim->receiver),array_shift(translate_zim_receiver($zim,$lang)),
                  concept_url($zim->message),array_shift(translate_zim_message($zim,$lang)),
                  concept_url($zim->response),array_shift(translate_zim_response($zim,$lang))
                );
                ***/
              }
              foreach(get_zams_involving($concept_id) as $zam) {
                //////pp($zim,'zim');
                echo sprintf('<li>%s</li>',linkify_zam($zam,$lang));                
              }
              echo "</ul>";
            ?>
            
            <h2>Teach <?php echo linkify_concept($concept_id,$lang); ?> a trick</h2>
            <div class="well">  
   
            <div class="control-group">  
              <label class="control-label" for="select01">When asked (<?php echo array_shift(translate_concept($lang,$lang)); ?>)…</label>  
              <div class="controls">  
                <select id="message-select">
                  <?php foreach(get_zams_with_message($lang) as $zam) {
                      echo sprintf('<option value="%s">%s</option>',$zam->receiver,$zam->response);                            
                  }
                  ?>
              </select>  
              </div>  
            </div>  

            <label class="control-label" for="response-text">Respond with...
              <input type="text" id="response-text" />
            </label>  
            <label class="control-label" for="response-id">OR better yet…
              <select id="response-id">
                <?php foreach(get_zams_with_message($lang) as $zam) {
                    echo sprintf('<option value="%s">%s</option>',$zam->receiver,$zam->response);                            
                }
                ?>
              </select>              
            </label>  
            <br/>
            <button class="btn" id="submit">Submit</button>  
            </div>  
          </div><!-- hero-unit -->       
        </div><!-- span -->
      </div><!-- rowfluid -->
      <hr>

      <footer>
        <p>&copy; Company 2012</p>
      </footer>

    </div><!--/.fluid-container-->
<?php get_footer();

