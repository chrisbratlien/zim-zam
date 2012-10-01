<?php

date_default_timezone_set('America/Chicago');

ini_set('display_errors',1);

require_once 'local.php';
require_once 'core.php';
require_once 'class.phpmailer.php';

if (!function_exists('pp')) { //Pretty Print
  function pp($obj,$label = '') {  
    $data = json_encode(print_r($obj,true));
    ?>
    <style type="text/css">
      #bsdLogger {
      position: absolute;
      top: 0px;
      right: 0px;
      border-left: 4px solid #bbb;
      padding: 6px;
      background: white;
      color: #444;
      z-index: 999;
      font-size: 1.25em;
      width: 400px;
      height: 800px;
      overflow: scroll;
      }
    </style>    
    <script type="text/javascript">
      var doStuff = function(){
        var obj = <?php echo $data; ?>;
        var logger = document.getElementById('bsdLogger');
        if (!logger) {
          logger = document.createElement('div');
          logger.id = 'bsdLogger';
          document.body.appendChild(logger);
        }
        ////console.log(obj);
        var pre = document.createElement('pre');
        var h2 = document.createElement('h2');
        pre.innerHTML = obj;
 
        h2.innerHTML = '<?php echo addslashes($label); ?>';
        logger.appendChild(h2);
        logger.appendChild(pre);      
      };
      window.addEventListener ("DOMContentLoaded", doStuff, false);
 
    </script>
    <?php
  }
}


function tags_for_coupon_id($coupon_id) {

	$tags = Array();

	$sql = "SELECT tags.* FROM coupons, tagmap, tags WHERE
			tags.tag_id = tagmap.tag_id AND
			coupons.coupon_id = tagmap.coupon_id AND
			coupons.coupon_id = $coupon_id";
		
	$result = mysql_query($sql);
	
	while ($row = mysql_fetch_array($result)) {
		array_push($tags,$row['name']);
	}

	return $tags;

}

function tag_id_of_string($str) {
	$sql = sprintf('SELECT tag_id FROM tags WHERE name = "%s"',$str);
	////print_r($sql);
	
	$result = mysql_query($sql);
	echo mysql_error();
	
	if (mysql_num_rows($result) == 0)
		return -1;
	$row = mysql_fetch_array($result);
		return $row['tag_id'];
}

function create_coupon_tagmap($coupon_id,$tag_id) {

	$sql = "SELECT tagmap_id FROM tagmap where coupon_id=$coupon_id and tag_id=$tag_id";
	//print "SQL IS $sql";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) == 0) {
	
		$sql2 = "INSERT INTO tagmap (`coupon_id`,`tag_id`) VALUES($coupon_id,$tag_id);";
		//print "SQL2 IS $sql2";
		mysql_query($sql2);
	}
}


function create_tag($str) {

	if (tag_id_of_string($str) == -1) {	
		$sql = sprintf("INSERT INTO tags (`name`) VALUES('%s')",$str);		
		mysql_query($sql);
	}
	return tag_id_of_string($str);
}

function strtotags($str) {

	$tags = Array();

	$myfor = $str;
	$myfor = str_replace(',',' ',$myfor);
	$myfor = str_replace('+',' ',$myfor);
	$mycats = split(' ',$myfor);

	foreach ($mycats as $cat) {
		$cat = trim(strtolower($cat));
		if (!in_array($cat,$tags) && $cat != '' ) {
			array_push($tags,$cat);
		}
	}

	return $tags; //array

}


add_action('ws_new_coupon',function($opts){

  $tags = $opts['tags'];
  $vendor_id = $opts['vendor_id'];  

  $sql = sprintf("INSERT INTO  `coupons` (`vendor_id`,`item`,`details`) " .
    " VALUES (%s, '%s', '%s')",
    $vendor_id,
    mysql_escape_string($opts['item']),
    mysql_escape_string($opts['details'])
  );

  $q = mysql_query($sql);

  echo mysql_error();
  $coupon_id = mysql_insert_id();

	foreach ($tags as $tagstring) {
		$tag_id = create_tag($tagstring);
		create_coupon_tagmap($coupon_id,$tag_id);
	}


  echo $coupon_id;
  exit;

});

add_action('ws_new_vendor',function($opts) {
  /////print_r($opts);

  $sql = sprintf("INSERT INTO vendors (vendor_name,logo) VALUES ('%s','%s')",
    mysql_escape_string($opts['vendor_name']),
    mysql_escape_string($opts['logo'])
  );
  
  /////echo $sql;
  
  mysql_query($sql);
  echo mysql_error();

});


function get_vendors() {
  $sql = "SELECT * FROM vendors order by vendor_name ASC";
  $r = mysql_query($sql);
  $result = array();
  while ($row = mysql_fetch_object($r)) {
    $result[] = $row;  
  }
  return $result;
}

add_action('ws_get_vendors',function() {
  echo json_encode(get_vendors());
});



function tag_search($tags) {
  $tagcnt = count($tags);

  $sql = "SELECT * FROM coupons WHERE 1" ;//default
  if ($tagcnt > 0) {
  	$s = '';
  	foreach($tags as $tag) {
  		$s .= "'" . $tag . "',";
  	}
  	$s = trim($s,',');
    

    $sql = sprintf("SELECT A.*,vendors.* FROM (SELECT coupons.*
    		FROM coupons, tagmap, tags
    		WHERE tagmap.tag_id = tags.tag_id
    		AND (tags.name IN (%s))
    		AND tagmap.coupon_id = coupons.coupon_id
    		GROUP BY coupons.coupon_id
    		HAVING COUNT( coupons.coupon_id )=%d) AS A
    JOIN vendors ON
    vendors.vendor_id = A.vendor_id",
      $s,
      $tagcnt);
  }
	
  //print_r($sql);

	$r = mysql_query($sql);
	
	$result = array();
	while ($row = mysql_fetch_object($r)) {
		array_push($result,$row);
	}

	return $result;

}


add_action('ws_tag_search',function($opts) {
  $tags = $opts['tags']; //should be an array  
  echo json_encode(tag_search($tags));
  exit;
});




function scramble() {

  $str = 'abcdefghijklmnopqrstuvwxyz0123456789';
  
  return str_shuffle($str);
  ///////return $str;

}


add_action('ws_upload_vendor_logo',function($opts) {
  //print_r($opts);
  //print_r($_FILES);

  foreach($_FILES as $k => $v) {
    ///$uploadfile = dirname(__FILE__) . '/data/' . $v['name'];
    ////print_r($uploadfile);
    
    $ext = preg_replace('/^.*\./','',$v['name']);    
    
    
    $gibberish = substr(scramble(),0,4);
    
    $newfile = sprintf('%s/%s.%s',dirname(__FILE__) . '/data',$gibberish,$ext);
    $justfile = sprintf('%s.%s',$gibberish,$ext);
  
    $result = move_uploaded_file($v['tmp_name'], $newfile);
  
  }

  echo $justfile;
  exit;
});



add_action('ws_get_vendor_logo_from_url',function($opts) {
  //print_r($opts);
  //print_r($_FILES);
  $url = $opts['url'];
  $ext = preg_replace('/^.*\./','',$url);    
  $gibberish = substr(scramble(),0,4);
  $newfile = sprintf('%s/%s.%s',dirname(__FILE__) . '/data',$gibberish,$ext);
  $justfile = sprintf('%s.%s',$gibberish,$ext);
  file_put_contents($newfile,file_get_contents($url));
  echo $justfile;
  exit;
});





function base_url_js() {
?>
<script type="text/javascript">
  if (typeof DIBS == "undefined") {
    var DIBS = {};
  }
  DIBS.baseURL = '<?php bloginfo('url'); ?>';
</script>
<?php
}

function curl_get($url, array $get = array(), array $options = array())
{
    $defaults = array(
        CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => I35TIED_CURL_TIMEOUT
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

