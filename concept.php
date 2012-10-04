<?php



if (!empty($_POST) && array_key_exists('language',$_POST)) {
  $lang = $_POST['language'];
  $_SESSION['language'] = $lang;
}

add_filter('body_class',function($classes) {
  $classes[] = 'concept';
  return $classes;
});

get_header();

require_language();
$lang = $_SESSION['language'];


?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <?php get_sidebar(); ?>          
        </div><!--/span-->
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
              echo "</ul>";
            ?>
            <input type="text" id="tags" />
            <input type="text" id="tags2" />
            <input type="text" id="tags3" /><a class="btn btn-primary btn-large" id="search-button">Search</a>
            <div id="results"></div>
          </div><!-- hero-unit -->       
        </div><!-- span -->
      </div><!-- rowfluid -->
      <hr>

      <footer>
        <p>&copy; Company 2012</p>
      </footer>

    </div><!--/.fluid-container-->
<?php get_footer();

