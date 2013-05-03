<?php

require_once('class.Concept.php');
require_once('class.Zim.php');


$lang = $_SESSION['language'];

if (!empty($_POST) && array_key_exists('language',$_POST)) {
  $lang = $_POST['language'];
  $_SESSION['language'] = $lang;
}

add_filter('body_class',function($classes) {
  $classes[] = 'concept';
  return $classes;
});







/*
foreach(get_zims_involving($concept_id) as $spec) {
  $zim = new Zim($spec);
  pp($zim,'zee zim');
}
*/


//pp(ZimCollection::from_specs(get_zims_involving($concept_id)),'ZimColl?');
///$all = ZimCollection::all();
/////pp(count($all),'count of all');
///pp($first,'first?');

///$sub = $all->involving_concept_id($concept_id);
//pp(count($sub),'count of subset');
////pp($sub,'subset');

/** CONCEPT OBJECT TEST
$in_english = new Concept(1);
$orange_fruit = new Concept(18);
$color = new Concept(19);

$responses = $orange_fruit->concept_responses_to_concept($color);
//pp($responses,'responses to: orange fruit x color');

foreach($responses as $concept) {
  $text_responses = $concept->text_responses_to_concept($in_english);
  pp(array_shift($text_responses),'in english');
  //pp($concept->text_responses_to_concept($in_english),'text responses');
}
***/

$concept = new Concept($concept_id);

pp($concept,'concept');

pp($concept->url(),'url');






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
    
      setTimeout(function(){
        location.reload();
      
      },2000);
    
    
    });


    var newForm = ZZ.Widgets.NewConceptForm({
      lang: <?php echo $lang; ?>,
      langText: '<?php echo array_shift(translate_concept($lang,$lang)); ?>'
    });  
    var newFormWrap = jQuery('#new-concept-wrap');
    newForm.renderOn(newFormWrap);
    
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
            <table class="table table-striped table-bordered table-condensed">
            <tr><th>receiver (it)</th><th>message (when asked…)</th><th>response (responds with…)</th></tr>
            <?php
              foreach(get_zims_involving($concept_id) as $zim) {
                echo sprintf('%s',linkify_zim_for_table($zim,$lang));
                
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
                echo sprintf('%s',linkify_zam_for_table($zam,$lang));                
              }
              
            ?>
            </table>
            <h2>Train <?php echo linkify_concept($concept_id,$lang); ?> how to respond</h2>
            <div class="well">  
              <table class="table table-striped table-bordered table-condensed">
                <tr><th>receiver</th><th>message</th><th>response</th></tr>
                <tr>
                  <td><?php echo linkify_concept($concept_id,$lang); ?></td>
                  <td>
                    <select id="message-select">
                      <option value="">(choose)</option>
                      <?php foreach(get_zams_with_message($lang) as $zam) {
                          echo sprintf('<option value="%s">%s (%s)</option>',$zam->receiver,$zam->response,$zam->receiver);                            
                      }
                      ?>
                    </select>  
                  </td>
                  <td>
                    <select id="response-id">
                      <option value="">(choose)</option>
                      <?php foreach(get_zams_with_message($lang) as $zam) {
                        //pp($zam,'zam');
                          echo sprintf('<option value="%s">%s (%s)</option>',$zam->receiver,$zam->response,$zam->receiver);                            
                      }
                      ?>
                    </select><br />
                    or the text<br />
                    <input type="text" id="response-text" /><br/>
                    <button class="btn" id="submit">Submit</button>              
                  </td>
                </tr>
              </table>
            </div><!-- well -->
            <?php ///do_action('obsoleted-----new_concept_form',$lang); ?>
            <div id="new-concept-wrap"></div><!-- new-concept-wrap -->          
  
          </div><!-- hero-unit -->       
        </div><!-- span -->
      </div><!-- rowfluid -->
      <hr>
      <footer>
        <p>&copy; Company 2012</p>
      </footer>
    </div><!--/.fluid-container-->
<?php get_footer();