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



if (empty($concept_id)) { $concept_id = $_GET['concept_id']; }

add_action('wp_footer',function() use($lang,$concept_id) {




?>
<script type="text/javascript">

  ZZ.lang = <?php echo $lang; ?>;
  ZZ.conceptID = <?php echo $concept_id; ?>

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


/////////$email_concept_id = get_first_zam_receiver($lang,'email address');
////$email_address = array_shift(text_translations_of_concept($concept_id,$email_concept_id));

/////////$email_address = get_first_zam_response($concept_id,$email_concept_id);
$email_address = get_first_zam_response($concept_id,get_first_zam_receiver($lang,'email address'));

?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span9">
          <?php show_language_form(); ?>
          <div class="hero-unit">
            USER PLAY
              <label class="control-label" for="response-text">email
                <input type="text" id="email" value="<?php echo $email_address; ?>" />
              </label>              
              <button class="btn" id="submit">Submit</button>  
          </div><!-- hero-unit -->       
        </div><!-- span -->
      </div><!-- rowfluid -->
      <hr>

      <footer>
        <p>&copy; Company 2012</p>
      </footer>

    </div><!--/.fluid-container-->
<?php get_footer();

