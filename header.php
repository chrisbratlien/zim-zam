<!DOCTYPE html>
<html lang="en">
  <head>
  <!-- Le styles -->
  <link href="<?php bloginfo('template_url'); ?>/lib/bootstrap/css/bootstrap.css" rel="stylesheet">
  <style type="text/css">
    body {
    padding-top: 60px;
    padding-bottom: 40px;
    }
    .sidebar-nav {
    padding: 9px 0;
    }
  </style>
  <link href="<?php bloginfo('template_url'); ?>/lib/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
  <link href="<?php bloginfo('template_url'); ?>/stylesheets/style.css" rel="stylesheet">
  
  
  
  
  

  <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
    <meta charset="utf-8">
  <title><?php echo apply_filters('wp_title','ZimZam'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- JS
  ================================================== -->
  <script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
  <?php echo base_url_js(); ?>
  <script src="<?php bloginfo('template_url'); ?>/javascripts/array.js"></script>
  <script src="<?php bloginfo('template_url'); ?>/javascripts/string.supplant.js"></script>
  <script src="<?php bloginfo('template_url'); ?>/lib/bootstrap/js/bootstrap.js"></script>  
  <script src="<?php bloginfo('template_url'); ?>/javascripts/dom.js"></script>  
  <script src="<?php bloginfo('template_url'); ?>/javascripts/bsd.pubsub.js"></script>  
  <script src="<?php bloginfo('template_url'); ?>/javascripts/eachify.js"></script>  
  <script src="<?php bloginfo('template_url'); ?>/javascripts/bsd.storage.js"></script>  
  <script src="<?php bloginfo('template_url'); ?>/javascripts/bsd.widgets.lightbox.js"></script>  




   <link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/stylesheets/placeholder_polyfill.css">
   <script src="<?php bloginfo('template_url'); ?>/javascripts/placeholder_polyfill.jquery.js" charset="utf-8"></script>
  
  


 <script type="text/javascript">
  if (typeof ZZ == "undefined") { var ZZ = {}; }
  ZZ.baseURL = '<?php bloginfo('url'); ?>';
  
  
  ZZ.storage = BSD.Storage('local');
  
  
  
  BSD.scrollTop = function() {
    var doc = document.documentElement, body = document.body;
    var left = (doc && doc.scrollLeft || body && body.scrollLeft || 0);
    var top = (doc && doc.scrollTop  || body && body.scrollTop  || 0);
  
    /////console.log('scrollTop, top is',top);
  
    return top;
  };
  
  
  BSD.documentWidth = function() {
  var w=window.innerWidth
  || document.documentElement.clientWidth
  || document.body.clientWidth;
  
  /*
  var h=window.innerHeight
  || document.documentElement.clientHeight
  || document.body.clientHeight;
  */
    return w;
  }

  
  
 </script>
  <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/javascripts/zz.js"></script>
  <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/javascripts/zz.widgets.trainer.js"></script>

  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?php bloginfo('url'); ?>">ZimZam <img width="20px" src="<?php bloginfo('template_url'); ?>/images/zimzam-white.png" /></a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li><a href="https://github.com/chrisbratlien/zim-zam"><img width="20px" src="<?php bloginfo('template_url'); ?>/images/github-logo.png" /></a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div><!-- navbar -->


	<!-- Primary Page Layout
	================================================== -->

	<!-- Delete everything in this .container and get started on your own site! -->

	<div class="container">
    
	