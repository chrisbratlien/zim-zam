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
      <div class="row-fluid">
        <div class="span3">
          <?php ////show_language_form(); ?>
            <div id="pov">
              <div id="pov-search-wrap"></div>          
            </div><!-- pov -->
            <div style="clear: both;">&nbsp;</div>
            <div style="clear: both;">&nbsp;</div>            
            <div id="search">
              <div id="search-wrap"></div>          
            </div>
            <div style="clear: both;">&nbsp;</div>
            <div style="clear: both;">&nbsp;</div>
            <div id="new-concept-wrap"></div>
            
            <?php ////////do_action('new_concept_form',$lang); ?>
        </div><!-- span3 -->

        <div class="span9">
          <div id="gallery-wrap"></div>      
          <div id="trainer-wrap"></div><!-- trainer-wrap -->
          <div id="uploader-wrap"></div><!-- uploader-wrap -->
        </div><!-- span9 -->

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

  var campfire = BSD.PubSub({});
  jQuery(document).ready(function() {

    var galleryWrap = jQuery('#gallery-wrap');
    var gallery = ZZ.Widgets.Gallery({
      concept: ZZ.thisConcept
    });
    gallery.renderOn(galleryWrap);
    //gallery.refresh();
    ZZ.gallery = gallery;

    var povWrap = jQuery('#pov-search-wrap');    
    ZZ.Widgets.ConceptSearch({
      choice: ZZ.langConcept,
      placeholder: 'Language / POV',
      callback: function(concept) {
        ZZ.lang = concept.id;
        ZZ.langConcept = concept;
        setLanguage(concept);
        gallery.refresh();
      }
    }).renderOn(povWrap);

    var searchWrap = jQuery('#search-wrap');    
    ZZ.Widgets.ConceptSearch({
      placeholder: 'Search for a concept',
      callback: function(concept) {
        gallery.recenterTo(concept);
      }
    }).renderOn(searchWrap);




      var trainerWrap = jQuery('#trainer-wrap');    
      var trainer = ZZ.Widgets.Trainer({
        concept: ZZ.thisConcept
        ///callback: gallery.refresh
      });
      trainer.renderOn(trainerWrap);
      trainer.subscribe('onSave',function(z) {
        //console.log('onSave!!');
        gallery.refresh();
      });
    gallery.refresh();

    ///refreshTrainer(ZZ.thisConcept);
    gallery.subscribe('onRecenterTo',function(concept) {
        trainer.recenterTo(concept);
    });





    var newForm = ZZ.Widgets.NewConceptForm({
      lang: ZZ.lang,
      langText: '<?php echo array_shift(text_translations_of_concept($lang,$lang)); ?>',
      callback: function(concept) {
        gallery.recenterTo(concept);
      }      
    });  
    var newFormWrap = jQuery('#new-concept-wrap');
    newForm.renderOn(newFormWrap);    

    /* UPLOADER */
    var uploader = ZZ.Widgets.Uploader({
      gossip: campfire
    });
    var uploaderWrap = jQuery('#uploader-wrap');
    uploader.renderOn(uploaderWrap);
    
    campfire.subscribe('new-upload-url',function(url) {
    
      console.log('gallery',gallery);//gallery.currentConcept
    
    
      newZam({ receiver: gallery.currentConcept.id, message: ZZ.cache.glyphURLConcept.id, response: url },function(id) {
        gallery.refresh();
        /*
        setTimeout(function() {
          window.location.reload(); 
          
        },1000);
        */        
        
      });
    });





  });
</script>
<?php
});

get_footer(); ?>