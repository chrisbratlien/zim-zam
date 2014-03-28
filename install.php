<?php

$sql = "
CREATE TABLE IF NOT EXISTS `concepts` (
  `concept_id` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`concept_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
mysql_query($sql);
echo sprintf(mysql_error(),'ERROR? %s');

$sql = "
CREATE TABLE IF NOT EXISTS `zam` (
  `zam_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `receiver` bigint(20) NOT NULL,
  `message` bigint(20) NOT NULL,
  `response` text NOT NULL,
  PRIMARY KEY (`zam_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
mysql_query($sql);
echo sprintf(mysql_error(),'ERROR? %s');

$sql = "
CREATE TABLE IF NOT EXISTS `zim` (
  `zim_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `receiver` bigint(20) NOT NULL,
  `message` bigint(20) NOT NULL,
  `response` bigint(20) NOT NULL,
  PRIMARY KEY (`zim_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
mysql_query($sql);
echo sprintf(mysql_error(),'ERROR? %s');

echo "INSTALLED";

?>