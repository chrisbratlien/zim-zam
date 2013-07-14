CACHE MANIFEST 
CACHE:
<?php

header("Content-Type: text/cache-manifest");


function glyph_url_cache_list() {
  $glyph_url_id = get_first_zam_receiver(1,'glyph url');
  $them = get_zams_where(Array(
    sprintf('message = %d',$glyph_url_id)
  ));
  
  
  $base = get_bloginfo('url');
  
  $result = array_map(function($zam) {
    return preg_replace('/^.*uploads/','/uploads',$zam->spec->response);
  },$them);
  
  //print_r($result);
  //exit;
  
  return $result;
}


foreach (glyph_url_cache_list() as $url) {
  if (!empty($url)) {
    echo sprintf("%s\n",$url);  
  }
}
