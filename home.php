<?php 

require_once('class.Concept.php');
require_once('class.Zim.php');


add_filter('body_class',function($classes) {
  $classes[] = 'gallery';
  return $classes;
});

//pp($concept_id,'concept id');
////$concept = new Concept($concept_id);


get_header(); 



//require_language();
//$lang = current_language();







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
            <div id="recent-concepts-wrap"></div>
            
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
  add_action('wp_footer',function() {
?>

<script type="text/javascript">


    //restore recently viewed concept.
    ZZ.storage = BSD.Storage('local');



  ///ZZ.lang = <// //?/     ////php echo $lang; ?>;
  
  if (!ZZ.lang) { ZZ.lang = 1; }
  
  
  ZZ.langConcept = ZZ.Concept({ id: ZZ.lang });
  ZZ.langText = ZZ.langConcept.textResponsesToConcept(ZZ.langConcept).shift();


  function drawRecentFew() {
    var wrap = jQuery('#recent-concepts-wrap');
    wrap.empty();
    
    var ul = DOM.ul().addClass('search-results recent');
    
    
    wrap.append(DOM.h4('Recently Viewed'));
    ZZ.history.recentFew().each(function(o){
      var li = DOM.li();
      li.append(ZZ.badassLink(o));
      ul.append(li);
    });
    wrap.append(ul);
  }

  var campfire = BSD.PubSub({});
  jQuery(document).ready(function() {

    var galleryWrap = jQuery('#gallery-wrap');

    var gallery = false;



    var povWrap = jQuery('#pov-search-wrap');    
    ZZ.Widgets.ConceptSearch({
      choice: ZZ.langConcept,
      placeholder: 'Language / POV',
      callback: function(concept) {
        ZZ.lang = concept.id;
        ZZ.langConcept = concept;
        setLanguage(concept);
        if (gallery) {
          gallery.refresh();      
        }
      }
    }).renderOn(povWrap);

    var searchWrap = jQuery('#search-wrap');    
    ZZ.Widgets.ConceptSearch({
      placeholder: 'Search for a concept',
      callback: function(concept) {
        ZZ.conceptID = concept.id;
        ZZ.thisConcept = concept;
        if (!gallery) {
          gallery = ZZ.Widgets.Gallery({
            concept: ZZ.thisConcept
          });
          ZZ.gallery = gallery;



          campfire.publish('reveal-gallery-and-trainer',null);
        }
        //gallery.refresh();
        gallery.recenterTo(concept);
      }
    }).renderOn(searchWrap);


    var trainerWrap = jQuery('#trainer-wrap');    
    var trainer = false;    
    ///refreshTrainer(ZZ.thisConcept);

    ///console.log('gal',gallery);
    var newForm = ZZ.Widgets.NewConceptForm({
      lang: ZZ.lang,
      langText: ZZ.langText,
      callback: function(concept) {
        ZZ.conceptID = concept.id;
        ZZ.thisConcept = concept;
        if (!gallery) {
          gallery = ZZ.Widgets.Gallery({
            concept: concept
          });
          campfire.publish('reveal-gallery-and-trainer',null);
        }
        //gallery.refresh();
        gallery.recenterTo(concept);
      }
    });  
    var newFormWrap = jQuery('#new-concept-wrap');
    newForm.renderOn(newFormWrap);    

    /* UPLOADER */
    var uploaderWrap = jQuery('#uploader-wrap');
    var uploader = ZZ.Widgets.Uploader({});
    
    
    campfire.subscribe('reveal-gallery-and-trainer',function(o){
    
      if (!ZZ.thisConcept) { return false; }
    
      gallery.renderOn(galleryWrap);

      trainer = ZZ.Widgets.Trainer({
      concept: ZZ.thisConcept
        ///callback: gallery.refresh
      });
      trainer.subscribe('onSave',function(z) {
        //console.log('onSave!!');
        gallery.refresh();
      });

      trainer.renderOn(trainerWrap);
      gallery.subscribe('onRecenterTo',function(concept) {
        trainer.recenterTo(concept);
      });
      gallery.refresh();      
      uploader.renderOn(uploaderWrap);
      
      gallery.subscribe('recenter',function(concept){
        drawRecentFew();
      });
      drawRecentFew();
      
    });
    
    


    ZZ.lang = false;





    var recentLangID = ZZ.storage.getItem('recent-lang');
    if (recentLangID) {
       ZZ.lang = recentLangID;
    }


    ///ZZ.conceptID = ZZ.getVars(location.href).
    
    var urlParts = location.href.match(/\d+/);
    
    ////console.log('parts',urlParts);
    
    if (urlParts) {
      ZZ.conceptID = parseInt(urlParts[0],10);
      ZZ.thisConcept = ZZ.Concept({ id: ZZ.conceptID });  
    }
    else {

      var recentConcept = ZZ.history.recent();
      if (recentConcept) {
        ZZ.conceptID = recentConcept.id;  
        ZZ.thisConcept = recentConcept;
      }
      
    }

    if (!gallery) {
      gallery = ZZ.Widgets.Gallery({
        concept: ZZ.thisConcept
      });
      campfire.publish('reveal-gallery-and-trainer',null);
    }  

    /* wiring of event handlers */    
    


    /***    
    uploader.subscribe('new-upload-url',function(url) {
      console.log('gallery',gallery);//gallery.currentConcept
      newZam({ receiver: gallery.currentConcept.id, message: ZZ.cache.glyphURLConcept.id, response: url },function(id) {
        gallery.refresh();
        
      });
    });
    *****/


    uploader.subscribe('new-upload',function(o) {
      console.log('gallery',gallery);//gallery.currentConcept
      newZam({ receiver: gallery.currentConcept.id, message: o.concept.id, response: o.url },function(id) {
        gallery.refresh();
      });
    });
    
    if (gallery) {
      //console.log('hey i gotta gallery!',gallery);
      gallery.subscribe('recenter',function(concept){
        drawRecentFew();
      });
      drawRecentFew();

    }






  });
</script>
<?php
});

get_footer(); ?>