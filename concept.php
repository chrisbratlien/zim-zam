<?php

require_once('class.Concept.php');
require_once('class.Zim.php');


add_filter('body_class',function($classes) {
  $classes[] = 'concept';
  return $classes;
});


$concept = new Concept($concept_id);


$javascript_concept = new Concept(get_first_zam_receiver(1,'javascript'));

//pp($javascript_concept,'jsc');


$ohboy = $concept->text_responses_to_concept($javascript_concept);

////pp($ohboy,'ohboy');

add_action('wp_head',function() use($ohboy) {
?>
<script type="text/javascript">
<?php
foreach($ohboy as $js) {
  echo sprintf($js,"%s\n");
  echo "\n";
}
?>
</script>
<?php
});


get_header(); 
//require_language();
$lang = current_language();
?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12">
          <div id="pov">
            <div id="pov-search-wrap"></div>          
          </div><!-- pov -->

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
          <div id="uploader-wrap"></div><!-- uploader-wrap -->
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
  ZZ.langText = ZZ.langConcept.textResponsesToConcept(ZZ.langConcept).shift();
  
  
  ZZ.conceptID = <?php echo $concept_id; ?>;
  ZZ.thisConcept = ZZ.Concept({ id: <?php echo $concept_id;?> });

  jQuery(document).ready(function() {

    var campfire = BSD.PubSub({});

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


    var povWrap = jQuery('#pov-search-wrap');    
    ZZ.Widgets.ConceptSearch({
      choice: ZZ.langConcept,
      placeholder: 'Language / POV',
      callback: function(concept) {
        ZZ.lang = concept.id;
        ZZ.langConcept = concept;
        setLanguage(concept);
      }
    }).renderOn(povWrap);

    var trainerWrap = jQuery('#trainer-wrap');    
    ZZ.Widgets.Trainer({
      concept: ZZ.thisConcept
    }).renderOn(trainerWrap);

    var newForm = ZZ.Widgets.NewConceptForm({
      lang: <?php echo $lang; ?>,
      langText: ZZ.langText,
      callback: function(concept) {
        ///console.log('concept',concept);
        window.location.href = concept.linkify();
      }
    });  
    var newFormWrap = jQuery('#new-concept-wrap');
    newForm.renderOn(newFormWrap);

    var myWrap = jQuery('#my-involved-wrap');
    var involvedList = ZZ.Widgets.Involved({ concept: ZZ.thisConcept });
    involvedList.renderOn(myWrap);

    var uploader = ZZ.Widgets.Uploader({});
    var uploaderWrap = jQuery('#uploader-wrap');
    uploader.renderOn(uploaderWrap);
    
    uploader.subscribe('new-upload-url',function(url) {
      newZam({ receiver: ZZ.conceptID, message: ZZ.cache.glyphURLConcept.id, response: url },function(id) {
        setTimeout(function() {
          window.location.reload(); 
          
        },1000);
      });
    });
  });


</script>
<?php
});

get_footer();