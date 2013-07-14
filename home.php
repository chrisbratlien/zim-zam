<?php 

require_once('class.Concept.php');
require_once('class.Zim.php');


add_filter('body_class',function($classes) {
  $classes[] = 'home';
  return $classes;
});

get_header(); 
require_language();
$lang = current_language();

//pp($lang,'lang');

?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span9">
          <?php show_language_form(); ?>     
            <div id="all-wrap"></div>
            <?php do_action('new_concept_form',$lang); ?>
            <div id="new-concept-wrap">…or, a 100% un-translated (for now) concept;</div>
        </div><!-- span -->
      </div><!-- rowfluid -->
      <hr>

      <footer>
        <p>&copy; Company 2012</p>
      </footer>

    </div><!--/.fluid-container-->
<?php 
  add_action('wp_footer',function() use($lang) {
?>

<script type="text/javascript">
  ZZ.lang = <?php echo $lang; ?>;
  ZZ.langConcept = ZZ.Concept({ id: ZZ.lang });
    var myWrap = jQuery('#all-wrap');
    var AllList = ZZ.Widgets.AllConcepts({});
    AllList.renderOn(myWrap);

  jQuery(document).ready(function() {
    var newForm = ZZ.Widgets.NewConceptForm({
      lang: ZZ.lang,
      langText: '<?php echo array_shift(text_translations_of_concept($lang,$lang)); ?>'
    });  
    var newFormWrap = jQuery('#new-concept-wrap');
    newForm.renderOn(newFormWrap);













  });
</script>
<?php
});



get_footer(); ?>