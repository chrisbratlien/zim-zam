<?php

date_default_timezone_set('America/Chicago');
define('WILD',-1);

ini_set('display_errors',1);

require_once 'local.php';
require_once 'core.php';
require_once 'class.Concept.php';
require_once 'class.Zim.php';
require_once 'class.Zam.php';
require_once 'class.phpmailer.php';





if (!function_exists('pp')) { //Pretty Print
  function pp($obj,$label = '') {  
    $data = json_encode(print_r($obj,true));
    ?>
    <style type="text/css">
      #bsdLogger {
      position: absolute;
      top: 90px;
      right: 20px;
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

function pr($obj,$label = '') {
  echo sprintf('%s: %s',$label,print_r($obj,true));
}


function echo_if($cond,$str) {
  if ($cond) {
    echo $str;
  }
}


function is_language_known() {
  return array_key_exists('language',$_SESSION);  
}


function current_language() {
  if (!is_language_known()) {
    return false;
  }
  return $_SESSION['language'];
}



function require_language() {
    if (!empty($_POST)) {
      $_SESSION['language'] = $_POST['language'];  
    }

  if (!is_language_known()) {
      show_language_form();
      exit;
  }
}



function show_language_form() {  
  $lang = current_language();
  ////pp($lang,'lang');
?>
<form method="post" autocomplete="off">
    <label>Language
    <select id="language-picker" name="language">
      <option value="">(choose)</option>
      <option <?php echo_if($lang == 1,'selected="selected"'); ?> value="1">English</option>
      <option <?php echo_if($lang == 2,'selected="selected"'); ?> value="2">Norwegian (bokmaal)</option>
      <option <?php echo_if($lang == 3,'selected="selected"'); ?> value="3">Norwegian (nynorsk)</option>
      <option <?php echo_if($lang == 26,'selected="selected"'); ?> value="26">Spanish</option>
    </select>
    </label>
    <script type="text/javascript">
      jQuery('#language-picker').change(function() { 
        console.log('hhasfdadsf');
        jQuery(this).parents('form').submit();
      });    
    </script>    
</form>
<?php
}


function text_translations_of_concept($receiver,$message) {
  $receiver_id = ($receiver instanceof Concept) ? $receiver->id : $receiver;
  $message_id = ($message instanceof Concept) ? $message->id : $message;
  return get_zam_responses($receiver_id,$message_id);
}

function translate_zim_receiver($zim,$language) {
  return text_translations_of_concept($zim->receiver,$language);
}
function translate_zim_message($zim,$language) {
  return text_translations_of_concept($zim->message,$language);
}
function translate_zim_response($zim,$language) {
  return text_translations_of_concept($zim->response,$language);
}

function get_zims_where($wheres = Array(),$conjunction) {
  if (empty($conjunction)) { die('empty conjunction'); }

  $sql = sprintf('SELECT * FROM zim WHERE 1');
  foreach($wheres as $w) {
    $sql .= sprintf(' %s %s',$conjunction,$w);
  }  
  /**
  pp($wheres,'wheres');
  pp($sql,'sql');
  exit;
  **/
	$mysql_result = mysql_query($sql);	
  $result = array();
	while ($row = mysql_fetch_object($mysql_result)) {
		array_push($result,new Zim($row));
	}  
  return $result;
}
function get_zims_with_response($id) {
  return askzim(WILD,WILD,$id);
}
function get_zims_with_message($id) {
  return askzim(WILD,$id,WILD);
}
function get_zims_with_receiver($id) {
  return askzim($id,WILD,WILD);
}
function get_zims_involving($id) {
  /**
    $a = askzim($id,WILD,WILD);
    $b = askzim(WILD,$id,WILD);
    $c = askzim(WILD,WILD,$id);
  ***/
  $sql = sprintf('SELECT * FROM zim WHERE (receiver = %d) OR (message = %d) OR (response = %d)',$id,$id,$id);
	$mysql_result = mysql_query($sql);	
  $result = array();
	while ($row = mysql_fetch_object($mysql_result)) {
		array_push($result,new Zim($row));
	}  
  return $result;
}

function get_all_zims() {
  return askzim(WILD,WILD,WILD);
}


/* ZAM */

function get_zams_where($wheres = Array(),$conjunction) {
  if (empty($conjunction)) { die('empty conjunction'); }
  $sql = sprintf('SELECT * FROM zam WHERE 1');
  foreach($wheres as $w) {
    $sql .= sprintf(' %s %s',$conjunction,$w);
  }  
  
  ///print_r($sql);
  ///exit;
  
  
	$mysql_result = mysql_query($sql);	
  $result = array();
	while ($row = mysql_fetch_object($mysql_result)) {
		array_push($result,new Zam($row));
	}  
  return $result;
}



function constraint($fname,$fval) {
  $type = gettype($fval);
  /////pp($type,'gettype fval');
  if ($fval == WILD) {
    return 'TRUE'; //dont add any sql constraint
  }
  if ($type == 'integer') {
    return sprintf('`%s` = %d',$fname,$fval);
  }
  if ($type == 'string') {
    return sprintf('`%s` = "%s"',$fname,mysql_real_escape_string($fval));
  }
  return 'constraint doesnt know what to do.  ';
}
function askzim($a,$b,$c) {
  $wheres = Array();
  array_push($wheres,
    constraint('receiver',$a),
    constraint('message',$b),
    constraint('response',$c)
  );
  $them = get_zims_where($wheres,'AND');
  return $them;  
}
function askzam($a,$b,$c) {
  $wheres = Array();
  array_push($wheres,
    constraint('receiver',$a),
    constraint('message',$b),
    constraint('response',$c)
  );
  $them = get_zams_where($wheres,'AND');
  return $them;  
}
function askzimzam($a,$b,$c) {
  return (object) Array(
    'zims' => askzim($a,$b,$c),
    'zams' => askzam($a,$b,$c)    
  );
}

function get_all_zams() {
  return askzam(WILD,WILD,WILD);
}
function get_zams_with_message($id) {
  return askzam(WILD,$id,WILD);
}
function get_zams_with_response($str) {
  return askzam(WILD,WILD,$str);
}

function get_zams_with_message_and_response($msg,$response) {
 return askzam(WILD,$msg,$response); 
}

function get_zams_with_receiver_and_message($recv,$msg) {
 return askzam($recv,$msg,WILD);
}

function get_zam_receivers($msg,$response) {
  $receivers = array_map(function($zam) { 
    return $zam->spec->receiver; 
  },askzam(WILD,$msg,$response));
  return $receivers;
}

function get_first_zam_receiver($msg,$response) {
  $them = get_zam_receivers($msg,$response);
  return array_shift($them);
}

function get_zam_responses($recv,$msg) {
  $responses = array_map(function($zam) { 
    return $zam->spec->response; 
  },askzam($recv,$msg,WILD));
  return $responses;
}

function get_first_zam_response($recv,$msg) {
  return array_shift(get_zam_responses($recv,$msg));
}



function get_zams_involving($id) {
  $sql = sprintf('SELECT * FROM zam WHERE (receiver = %d) OR (message = %d) ',$id,$id);
	$mysql_result = mysql_query($sql);	
  $result = array();
	while ($row = mysql_fetch_object($mysql_result)) {
		array_push($result,new Zam($row));
	}  
  return $result;
}

function batch_get_zams_involving($comma_separated_ids) {

  $sql = sprintf('SELECT * FROM zam WHERE receiver IN (%s) OR message IN (%s) ',$comma_separated_ids,$comma_separated_ids);
  
  //print_r($sql);
  //exit;  
	$mysql_result = mysql_query($sql);	
  $result = array();
	while ($row = mysql_fetch_object($mysql_result)) {
		array_push($result,new Zam($row));
	}  
  return $result;
}

function concept_url($id) {
  return sprintf('%s/concept/%d',get_bloginfo('url'),$id);
}

function linkify_concept($id,$lang) {
  $trans = text_translations_of_concept($id,$lang);
  if (empty($trans)) {
    return sprintf('<a href="%s"><img class="zigzag" src="%s/images/zimzam-white.png" /></a>',concept_url($id),get_bloginfo('template_url'));
  }
  return sprintf('<a href="%s">%s</a>',concept_url($id),array_shift($trans));
}

function linkify_zim($zim,$lang) {
  return sprintf('%s <- %s => %s',
    linkify_concept($zim->receiver,$lang),
    linkify_concept($zim->message,$lang),
    linkify_concept($zim->response,$lang)    
  );
}


function linkify_zim_for_table($zim,$lang) {
  return sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
    linkify_concept($zim->receiver,$lang),
    linkify_concept($zim->message,$lang),
    linkify_concept($zim->response,$lang)    
  );
}





function linkify_zam($zam,$lang) {
  return sprintf('%s <- %s => %s',
    linkify_concept($zam->receiver,$lang),
    linkify_concept($zam->message,$lang),
    $zam->response    
  );
}

function linkify_zam_for_table($zam,$lang) {
  return sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
    linkify_concept($zam->receiver,$lang),
    linkify_concept($zam->message,$lang),
    $zam->response    
  );
}


function linkify_zam_for_table_with_glyphs($zam,$lang) {
  ////pp($zam,'zammy');


  return sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
    linkify_concept_with_glyphs($zam->spec->receiver,$lang),
    linkify_concept_with_glyphs($zam->spec->message,$lang),
    $zam->spec->response    
  );
}


//careful with these

function glyph_urls($c) {
  $glyph_url_concept = new Concept(get_first_zam_receiver(1,'glyph url')); //FIXME: hardcoded 1
  $glyph_urls = $c->text_responses_to_concept($glyph_url_concept);
  return $glyph_urls;
}

function youtube_urls($c) {
  $glyph_url_concept = new Concept(get_first_zam_receiver(1,'youtube url')); //FIXME: hardcoded 1
  $glyph_urls = $c->text_responses_to_concept($glyph_url_concept);
  return $glyph_urls;
}




function linkify_concept_with_glyphs($id,$lang) {
  $trans = text_translations_of_concept($id,$lang);
  if (empty($trans)) {
    return sprintf('<a href="%s"><img class="zigzag" src="%s/images/zimzam-white.png" /></a>',concept_url($id),get_bloginfo('template_url'));
  }

  $r = '';  
  $c = new Concept($id);
  foreach (glyph_urls($c) as $url) {
    $r .= sprintf('<img width="20px" src="%s" />',$url);
  }

    
  
  return sprintf('<a href="%s">%s %s</a>',concept_url($id),$r,array_shift($trans));
}



function linkify_zim_for_table_with_glyphs($zim,$lang) {
  return sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
    linkify_concept_with_glyphs($zim->spec->receiver,$lang),
    linkify_concept_with_glyphs($zim->spec->message,$lang),
    linkify_concept_with_glyphs($zim->spec->response,$lang)    
  );
}


function new_concept() {
  $sql = "INSERT INTO `concepts` (`concept_id`) VALUES (NULL);";
  $q = mysql_query($sql);
  $concept_id = mysql_insert_id();
  return new Concept($concept_id);
}

function new_zam($opts) {
  
  $zam_receiver = $opts['receiver'];
  $zam_message = $opts['message'];
  $zam_response = $opts['response'];
  preg_match('/^\d+$/',$zam_message) and preg_match('/^\d+$/',$zam_receiver) or die('zam not numeric');

  $sql = sprintf("INSERT INTO `zam` (receiver,message,response) VALUES (%d,%d,'%s')",
    $zam_receiver,
    $zam_message,
    mysql_real_escape_string($zam_response)
  );
  $q = mysql_query($sql);
  $zam_id = mysql_insert_id();

  $them = get_zams_where(array(sprintf('zam_id = %d',$zam_id)),'AND');
  return array_shift($them);
}


function new_zim($opts) {
  
  $zim_receiver = $opts['receiver'];
  $zim_message = $opts['message'];
  $zim_response = $opts['response'];
  preg_match('/^\d+$/',$zim_message) and preg_match('/^\d+$/',$zim_receiver) or die('zim not numeric');

  $sql = sprintf("INSERT INTO `zim` (receiver,message,response) VALUES (%d,%d,%d)",
    $zim_receiver,
    $zim_message,
    $zim_response
  );
  $q = mysql_query($sql);
  $zim_id = mysql_insert_id();
  
  
  $them = get_zims_where(array(sprintf('zim_id = %d',$zim_id)),'AND');
  
  ///return $zim_id;
  return array_shift($them);
}


function new_concept_and_zam($opts) {
  $concept = new_concept();
  $opts['receiver'] = $concept->id;
  $zam_id = new_zam($opts);
  return $concept;   
}

function update_zam_response($opts) {
  $zam_id = $opts['zam_id'];
  $response = $opts['response'];
  $sql = sprintf('UPDATE `zam` SET response = "%s" WHERE zam_id = %d',
    mysql_real_escape_string($response),
    $zam_id);
    
  //print_r($sql);
  //exit;
    
  $q = mysql_query($sql);
  return mysql_error();
}
add_action('ws_update_zam_response',function($opts){  
  $result = update_zam_response($opts);
  echo json_encode($result);
  exit;
});

add_action('ws_new_concept',function($opts){  
  $result = new_concept();
  echo json_encode($result);
  exit;
});

add_action('ws_new_zam',function($opts){  
  $result = new_zam($opts);
  ///////print_r($result);
  echo json_encode($result->spec);
  exit;
});




function delete_zam($opts) {


  $zam_id = $opts['zam_id'];
  $api_key = $opts['zzak'];

  preg_match('/^\d+$/',$zam_id) or die('invalid zam_id');
  (ZZ_API_KEY == $api_key) or die('invalid zzak');
  
  $sql = sprintf('DELETE FROM `zam` WHERE zam_id = %d',$zam_id);
  //print_r($sql);
  //exit;
  $q = mysql_query($sql);
  //return mysql_error();
  return 'OK';
}
add_action('ws_delete_zam',function($opts){  
  $result = delete_zam($opts);
  ///////print_r($result);
  echo json_encode($result);
  exit;
});



function delete_zim($opts) {
  $zim_id = $opts['zim_id'];
  $api_key = $opts['zzak'];

  preg_match('/^\d+$/',$zim_id) or die('invalid zim_id');
  (ZZ_API_KEY == $api_key) or die('invalid zzak');
  
  $sql = sprintf('DELETE FROM `zim` WHERE zim_id = %d',$zim_id);
  //print_r($sql);
  //exit;
  $q = mysql_query($sql);
  //return mysql_error();
  return 'OK';
}
add_action('ws_delete_zim',function($opts){  
  $result = delete_zim($opts);
  ///////print_r($result);
  echo json_encode($result);
  exit;
});





add_action('ws_askzim',function($opts){
  $result = askzim($opts['a'],$opts['b'],$opts['c']);
  $specs = array_map(function ($e) { return $e->spec; },$result);
  echo json_encode($specs);
  exit;
});

add_action('ws_askzam',function($opts){
  $result = askzam($opts['a'],$opts['b'],$opts['c']);
  $specs = array_map(function ($e) { return $e->spec; },$result);
  echo json_encode($specs);
  exit;
});









add_action('ws_new_concept_and_zam',function($opts){  
  $result = new_concept_and_zam($opts);
  echo json_encode($result);
  exit;
});


add_action('ws_new_zim',function($opts){  
  $result = new_zim($opts);
  ///////print_r($result);
  echo json_encode($result->spec);
  exit;
});

add_action('ws_get_zims_involving',function($opts){  
  $concept_id = $opts['concept_id'];
  $result = get_zims_involving($concept_id);
  $specs = array_map(function ($e) { return $e->spec; },$result);
  echo json_encode($specs);
  exit;  
});

add_action('ws_get_zams_involving',function($opts){  
  $concept_id = $opts['concept_id'];
  $result = get_zams_involving($concept_id);
  $specs = array_map(function ($e) { return $e->spec; },$result);
  
  ////print_r($specs);
  
  
  echo json_encode($specs);
  exit;  
});


add_action('ws_batch_get_zams_involving',function($opts){  
  //echo json_encode(Array());
  //exit;
  
  $concept_ids = $opts['concept_ids'];
  $result = batch_get_zams_involving($concept_ids);
  $specs = array_map(function ($e) { return $e->spec; },$result);
  
  ////print_r($specs);
  
  
  echo json_encode($specs);
  exit;  
});

add_action('ws_get_zims_where',function($opts) { 
  $wheres = Array();
  if (array_key_exists('receiver',$opts)) {
    array_push($wheres,sprintf('receiver = %d',$opts['receiver']));
  }
  if (array_key_exists('message',$opts)) {
    array_push($wheres,sprintf('message = %d',$opts['message']));
  }
  if (array_key_exists('response',$opts)) {
    array_push($wheres,sprintf('response = %d',$opts['response']));
  }

  $result = get_zims_where($wheres,'AND');
  
  $specs = array_map(function ($e) { return $e->spec; },$result);
  echo json_encode($specs);
  exit;  
});

add_action('ws_get_zams_where',function($opts) { 
  $wheres = Array();
  if (array_key_exists('receiver',$opts)) {
    array_push($wheres,sprintf('receiver = %d',$opts['receiver']));
  }
  if (array_key_exists('message',$opts)) {
    array_push($wheres,sprintf('message = %d',$opts['message']));
  }
  if (array_key_exists('response',$opts)) {
    array_push($wheres,sprintf('response = "%s"',mysql_real_escape_string($opts['response'])));
  }

  $result = get_zams_where($wheres,'AND');
  $specs = array_map(function ($e) { return $e->spec; },$result);
  echo json_encode($specs);
  exit;  
});



function foo_zam_search($str) {
  $wheres = Array();
  $lang = 1;
  array_push($wheres,sprintf('response LIKE "%s"','%' . mysql_real_escape_string($str) . '%'));
  ///NOTE: why limit this?!? ///array_push($wheres,sprintf('message = %d',$lang));
  $result = get_zams_where($wheres,'AND');
  $specs = array_map(function ($e) { return $e->spec; },$result);
  return $specs; 
}


add_action('ws_foo_zam_search',function($opts) {
  $query = $opts['query'];
  echo json_encode(foo_zam_search($query));
  exit;
});


/*
add_action('ws_get_zam_receivers',function($opts) {
  echo json_encode(get_zam_receivers($opts['message'],$opts['response']));
  exit;
});
*/

/** BOILERPLATE ***********************************************/



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
    mysql_real_escape_string($opts['item']),
    mysql_real_escape_string($opts['details'])
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
    mysql_real_escape_string($opts['vendor_name']),
    mysql_real_escape_string($opts['logo'])
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

function get_gibberish() {
  $result = substr(scramble(),0,8);
  return $result;
}



add_action('ws_upload_glyph_url',function($opts) {
  //print_r($opts);
  //print_r($_FILES);

  foreach($_FILES as $k => $v) {
    ///$uploadfile = dirname(__FILE__) . '/data/' . $v['name'];
    ////print_r($uploadfile);
    
    $ext = preg_replace('/^.*\./','',$v['name']);    
    
    
    $gibberish = get_gibberish();
    
    $newfile = sprintf('%s/%s.%s',dirname(__FILE__) . '/uploads',$gibberish,$ext);
    $justfile = sprintf('%s.%s',$gibberish,$ext);
  
    $result = move_uploaded_file($v['tmp_name'], $newfile);
  
  }

  echo $justfile;
  exit;
});


add_action('ws_upload_datauri',function($opts) {

  $gibberish = get_gibberish();

  $newfile = sprintf('%s/%s.%s',dirname(__FILE__) . '/uploads',$gibberish,$ext);
  $justfile = sprintf('%s.%s',$gibberish,$ext);
  
  $uri = $opts['uri'];
  
  pr($uri,'uri, fool');
  exit;
  
  $content = 'data://' . substr($uri, 5);
  $binary = file_get_contents($uriPhp);
  file_put_contents($newfile,$binary);
});











add_action('ws_get_glyph_from_datauri',function($opts) {
  $url = $opts['url'];
  
  if (substr($url,0,5) == 'data:') {
    $gibberish = get_gibberish();
  
    $parts = preg_split('/:|;/',$url);
    $mimetype = $parts[1];
    $more_parts =  preg_split('/\//',$mimetype);
    $ext = $more_parts[1];
 

 
  
    $newfile = sprintf('%s/%s.%s',dirname(__FILE__) . '/uploads',$gibberish,$ext);
    $justfile = sprintf('%s.%s',$gibberish,$ext);
    
    
    //pr($url,'url, fool');
    ///exit;
    
    $content = 'data://' . substr($url, 5);
    $binary = file_get_contents($content);
    file_put_contents($newfile,$binary);
    echo $justfile;
    exit;
  }

});

add_action('ws_get_glyph_from_url',function($opts) {
  //print_r($opts);
  //print_r($_FILES);
  $url = $opts['url'];
  
  if (substr($url,0,5) == 'data:') {
    $gibberish = get_gibberish();
  
    $newfile = sprintf('%s/%s.%s',dirname(__FILE__) . '/uploads',$gibberish,$ext);
    $justfile = sprintf('%s.%s',$gibberish,$ext);
    
    
    pr($url,'url, fool');
    exit;
    
    $content = 'data://' . substr($url, 5);
    $binary = file_get_contents($uriPhp);
    file_put_contents($newfile,$binary);
    echo $justfile;
    exit;
  }
  
	$data = file_get_contents($url);
  
  $file_info = new finfo(FILEINFO_MIME_TYPE);
  $mime_type = $file_info->buffer($data);
  $short_mime = preg_replace('/.*\//','',$mime_type);
  $ext = $short_mime;

  
  $gibberish = get_gibberish();
  $newfile = sprintf('%s/%s.%s',dirname(__FILE__) . '/uploads',$gibberish,$ext);
  $justfile = sprintf('%s.%s',$gibberish,$ext);
  file_put_contents($newfile,$data);
  echo $justfile;
  exit;
});


add_action('ws_set_language',function($opts){
  $_SESSION['language'] = $opts['language'];
  echo json_encode($_SESSION);
  exit;
});





add_action('ws_get_vendors',function() {
  echo json_encode(get_vendors());
  exit;
});





add_action('new_concept_form',function($lang){
?>
  <div class="well">
    <label class="control-label" for="new-concept-response-text">Create the concept of 
      <input type="text" id="new-concept-response-text" /> (<?php echo array_shift(text_translations_of_concept($lang,$lang)); ?>)
    </label>  
    <br/>
    <button class="btn btn-primary" id="new-concept-submit">Create Concept</button>  
    <span id="new-concept-submission-response"></span>
  </div><!-- well -->
<script type="text/javascript">
  ZZ.lang = <?php echo $lang; ?>;
  jQuery(document).ready(function() {
    jQuery('#new-concept-submit').click(function() {
      var responseText = jQuery('#new-concept-response-text').val();        
      if (responseText.length > 0) {
        newConceptAndZam({ message: ZZ.lang, response: responseText },function(id) { 
        //newZam({ receiver: ZZ.conceptID, message: message, response: responseText },function(id) {
          /////console.log('wooo',id);
          jQuery('#new-concept-submission-response').append(DOM.a().attr('href','/concept/' + id).html(responseText));
        });
      }
    });
  });
</script>
<?php
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





