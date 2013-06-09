<?php 


if (!empty($_POST) && array_key_exists('language',$_POST)) {
  $lang = $_POST['language'];
  $_SESSION['language'] = $lang;
}


require_language();
$lang = $_SESSION['language'];




add_filter('body_class',function($classes) {
  $classes[] = 'home';
  return $classes;
});




add_action('wp_footer',function() use($lang) {
?>

<script type="text/javascript">

  ZZ.lang = <?php echo $lang; ?>;

  jQuery(document).ready(function() {
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


///pp($lang,'lang');

?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span9">
          <?php show_language_form(); ?>        
          <div class="hero-unit">
            <table class="table table-striped table-bordered table-condensed all-concepts">
              <tr><th>receiver</th><th>message</th><th>response</th></tr>
              <?php
                foreach(get_all_zims() as $zim) {
                  echo sprintf('%s',linkify_zim_for_table_with_glyphs($zim,$lang));
                }
                foreach(get_all_zams() as $zam) {
                  //////pp($zim,'zim');
                  echo sprintf('%s',linkify_zam_for_table_with_glyphs($zam,$lang));
                }
              ?>
            </table>
            <div id="results"></div>            
            <?php do_action('new_concept_form',$lang); ?>
            
          </div><!-- hero-unit -->       
        </div><!-- span -->
      </div><!-- rowfluid -->
      <hr>

      <footer>
        <p>&copy; Company 2012</p>
      </footer>

    </div><!--/.fluid-container-->
<?php get_footer(); ?>