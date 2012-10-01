<?php 

add_filter('body_class',function($classes) {
  $classes[] = 'home';
  return $classes;
});
get_header(); ?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <?php get_sidebar(); ?>          
        </div><!--/span-->
        <div class="span9">
          <div class="hero-unit">
            <input type="text" id="tags" /><a class="btn btn-primary btn-large" id="search-button">Search</a>
            <div id="results"></div>
          </div><!-- hero-unit -->       
        </div><!-- span -->
      </div><!-- rowfluid -->
      <hr>

      <footer>
        <p>&copy; Company 2012</p>
      </footer>

    </div><!--/.fluid-container-->
<script type="text/javascript">


  function couponSearch(tags) {
    var resultsDiv = jQuery('#results');
    resultsDiv.empty();


    jQuery.ajax({
      url: '/ws',
      data: { 
        action: 'tag_search', 
        "tags": tags
      },
      success: function(response) {
      
        var results = eval('(' + response + ')');
        console.log('results',results);
        
        var table = DOM.table();
        results.each(function(result) {
          var tr = DOM.tr();


          var imageUrl = '/images/fs.jpg';
          if (result.logo.length > 0) {
            imageUrl = '/data/' + result.logo;
          }

          tr.append(DOM.td(DOM.image().attr('src',imageUrl)));
          tr.append(DOM.td(result.Vendor_Name));
          tr.append(DOM.td(result.item));
          tr.append(DOM.td(result.details));
          table.append(tr);
        });
        
        resultsDiv.append(table);
      
        //////console.log('resp',resp);
      }
    });
    
  }
    
  function searchInputtedTags() {
    var tags = jQuery('#tags').val().split(/\ +/g);
    couponSearch(tags);      
  }

  jQuery(document).ready(function() {
  
    jQuery('#search-button').click(searchInputtedTags);
    jQuery('#tags').blur(searchInputtedTags);  
    jQuery('#tags').keypress(function(event) {
      if (event.keyCode == 13) { //return/enter
        searchInputtedTags();  
      }
    });
  });



</script>
<?php get_footer(); ?>