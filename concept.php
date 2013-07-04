<?php

require_once('class.Concept.php');
require_once('class.Zim.php');


add_filter('body_class',function($classes) {
  $classes[] = 'concept';
  return $classes;
});


$concept = new Concept($concept_id);


get_header(); 
require_language();
$lang = current_language();


?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12">
          <?php show_language_form(); ?>

            <div style="float: right; width: 40%;" ><h2><?php echo linkify_concept($concept_id,$lang); ?></h2></div>

<?php

foreach(glyph_urls($concept) as $url) {
  echo sprintf('<img width="200px" src="%s" />',$url);
}


foreach(youtube_urls($concept) as $url) {
  parse_str(parse_url($url, PHP_URL_QUERY), $vars);
  ////pp($vars,'vars');
  echo sprintf('<iframe width="560" height="315" frameborder="0" allowfullscreen="true" src="http://www.youtube.com/embed/%s" ></iframe>',$vars['v']);
}


?>

          <div id="my-involved-wrap"></div><!-- my-involved-wrap -->
          <div id="trainer-wrap"></div>
          <div id="new-concept-wrap"></div><!-- new-concept-wrap -->          
        </div><!-- span -->
      </div><!-- rowfluid -->
      <hr>
      <footer>
        <p>&copy; Company 2012</p>
      </footer>
    </div><!--/.fluid-container-->
<?php 

add_action('wp_footer',function() use($lang,$concept_id) {
?>
<script type="text/javascript">
  ZZ.lang = <?php echo $lang; ?>;
  ZZ.langConcept = ZZ.Concept({ id: ZZ.lang });
  
  
  ZZ.conceptID = <?php echo $concept_id; ?>;
  ZZ.thisConcept = ZZ.Concept({ id: <?php echo $concept_id;?> });

  jQuery(document).ready(function() {

    jQuery('#submit').click(function() {
    
      var message = jQuery('#message-select').val();
      var responseText = jQuery('#response-text').val();
      var responseID = jQuery('#response-id').val();
    
    
      if (responseText.length > 0) {
        //newConceptAndZam({ message: 1, response: 'All of Me' },function(r) { console.log(r); })
        newZam({ receiver: ZZ.conceptID, message: message, response: responseText },function(id) {
          //console.log('wooo',id);
        });
      }
      else {
        newZim({ receiver: ZZ.conceptID, message: message, response: responseID },function(id) {
          //console.log('woo',id);
        });
      }    
    
      setTimeout(function(){
        location.reload();
      
      },2000);
    
    
    });


    var trainerWrap = jQuery('#trainer-wrap');    
    ZZ.Widgets.Trainer({
      concept: ZZ.thisConcept
    }).renderOn(trainerWrap);

    var messageSearchWrap = jQuery('#message-search-wrap');    
    ZZ.Widgets.ConceptSearch({
      callback: function(concept) {
        alert('yay, i got pickedd');
      }
    }).renderOn(messageSearchWrap);



    var newForm = ZZ.Widgets.NewConceptForm({
      lang: <?php echo $lang; ?>,
      langText: '<?php echo array_shift(translate_concept($lang,$lang)); ?>'
    });  
    var newFormWrap = jQuery('#new-concept-wrap');
    newForm.renderOn(newFormWrap);

    var myWrap = jQuery('#my-involved-wrap');
    var involvedList = ZZ.Widgets.Involved({ concept: ZZ.thisConcept });
    involvedList.renderOn(myWrap);

  });


</script>
<?php
});

get_footer();