<?php 

ini_set('display_errors',1);
require_once('local.php');

$actions = array();
$filters = array();


function bloginfo($param) {
  echo get_bloginfo($param);
}


function clean_uri($uri) {
  $result = preg_replace('/\/$/','',$uri);
  $result = preg_replace('/\?.*$/','',$result);
  return $result;
}



function is_logged_in() {
  return array_key_exists('username',$_SESSION);  
}


function require_authentication() {
  if (!is_logged_in()) {
  
    if (credentials_authenticate($_POST)) {
      $_SESSION['username'] = $_POST['username'];  
    }
    else {
      show_login_form();
    }
  }
}



function route($uri) {

  global $dbhost, $dbname, $dbuser, $dbpass;


  $protected_mode = false;
  
  
  
  
  // start the session
  session_start();
  header("Cache-control: private"); //IE 6 Fix
  ////include('vars.php'); // defines dbhost,dbuser,dbpass
  if (!mysql_connect($dbhost,$dbuser,$dbpass)) {
    echo "DB error";
    session_destroy();
    die();
  }
  mysql_select_db($dbname);


  $uri = clean_uri($uri);
  
  //print_r($uri);
  //exit;
  
  
  if ($uri == base_uri()) {
    ////echo "HOME!!!";
    require_once('home.php');
    return null;
  }

  //pp('URI: ' . $uri);  
  //pp('base_uri: ' . base_uri());
  $path = substr($uri,strlen(base_uri()));
  /////pp('path: ' . $path);
  //pp('dirname: ' . dirname(__FILE__));
  
  $tests = Array();  
  $tests[] = dirname(__FILE__) . $path . '/index.php';
  $tests[] = dirname(__FILE__) . $path . '.php';

  foreach($tests as $test) {
    if (file_exists($test)) {
      include_once($test);
      return null;
      ///break;
    }
  }
  
  
  
  $routes = Array(
    Array('pattern' => '/\/concept\/(\d+)$/', 'file' => 'concept', 'var' => 'id')
  
  );
  
  
  
  if (preg_match('/\/concept\/(\d+)$/',$uri,$matches)) {
      ////print_r($matches);
      /////die('ok');
      $concept_id = $matches[1];
      require_once('concept.php');
  }
  else {
    require_once('404.php');
  }  
  
}




function get_header() {
  require_once('./header.php');
}
function get_footer() {
  require_once('./footer.php');
}
function get_sidebar() {
  require_once('./sidebar.php');
}
function run_tests() {
  require_once('./tests.php');
}


function wp_head() {
  do_action('wp_head');
}

function wp_footer() {
  do_action('wp_footer');
}

function add_action($tag,$fn) {
  global $actions;
  
  if (!array_key_exists($tag,$actions)) {
    $actions[$tag] = Array();
  }
  $actions[$tag][] = $fn;
}

function do_action($tag,$arg = '') {
  global $actions;
  if (!array_key_exists($tag,$actions)) { return false; }
  $funcs = $actions[$tag];
  if (!empty($funcs)) {
    foreach($funcs as $func) {
      call_user_func($func,$arg);    
    }
  }
}

function add_filter($tag,$fn) {
  global $filters;
  
  if (!array_key_exists($tag,$filters)) {
    $filters[$tag] = Array();
  }
  $filters[$tag][] = $fn;
}

function apply_filters($tag,$default) {
  global $filters;
  
  if (!array_key_exists($tag,$filters)) { return $default; }
  $funcs = $filters[$tag];

  $result = $default;

  if (!empty($funcs)) {
    foreach($funcs as $func) {
      $result = call_user_func($func,$result);    
    }
  }  
  return $result;
}




function body_class() {
  $uri = clean_uri($_SERVER['REQUEST_URI']);
  $parts = preg_split('/\//',$uri);
  //pp($parts,'parts');
  
  $parts = apply_filters('body_class',$parts);
  if (empty($parts)) {
    return false;
  }
  
  $class_str = trim(join(' ',$parts));
  //pp($class_str,'class_str');
  echo sprintf('class="%s"',$class_str);
}


?>