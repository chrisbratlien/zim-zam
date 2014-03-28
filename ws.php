<?php

require_once('functions.php');

$opts = array();
$opts = array_merge($opts,$_GET);
$opts = array_merge($opts,$_POST);

do_action(sprintf('ws_%s',$opts['action']),$opts);