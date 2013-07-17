<?php 

require_once('class.Concept.php');
require_once('class.Zim.php');


add_filter('body_class',function($classes) {
  $classes[] = 'gallery';
  return $classes;
});

//pp($concept_id,'concept id');
$concept = new Concept($concept_id);


get_header(); 
require_language();
$lang = current_language();
?>
    <div class="container-fluid">
      <div class="row-fluid" id="gallery-wrap">
        <div class="span3">
          <?php ////show_language_form(); ?>     
          
            <div id="pov"><strong>Language / POV</strong>
              <div id="pov-search-wrap"></div>          
            </div>
            <div id="search"><strong>Search</strong>
              <div id="search-wrap"></div>          
            </div>
            <div id="new-concept-wrap"></div>
            
            <div id="all-wrap"></div>
            <?php ////////do_action('new_concept_form',$lang); ?>
        </div><!-- span -->
      </div><!-- row-fluid -->
      <div class="row-fluid">
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

    var povWrap = jQuery('#pov-search-wrap');    
    ZZ.Widgets.ConceptSearch({
      default: ZZ.langConcept,
      callback: function(concept) {
        ZZ.lang = concept.id;
        ZZ.langConcept = concept;
        setLanguage(concept);
      }
    }).renderOn(povWrap);


    var galleryWrap = jQuery('#gallery-wrap');
    var gallery = ZZ.Widgets.Gallery({
      concept: ZZ.thisConcept
    }).renderOn(galleryWrap);


    var newForm = ZZ.Widgets.NewConceptForm({
      lang: ZZ.lang,
      langText: '<?php echo array_shift(text_translations_of_concept($lang,$lang)); ?>'
    });  
    var newFormWrap = jQuery('#new-concept-wrap');
    newForm.renderOn(newFormWrap);

    /***
    var myWrap = jQuery('#all-wrap');
    var AllList = ZZ.Widgets.AllConcepts({});
    AllList.renderOn(myWrap);
    ***/
  });
</script>
<?php
});

get_footer(); ?>