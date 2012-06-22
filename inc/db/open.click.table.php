<?php

$sp_install = NEW SendPress();
// Create Stats Table
$subscriber_open_table =  $sp_install->subscriber_open_table();



require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if($wpdb->get_var("show tables like '$subscriber_open_table'") != $subscriber_open_table) {
	$sqltable = "CREATE TABLE ".$subscriber_open_table." (
  `openID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subscriberID` int(11) DEFAULT NULL,
  `reportID` int(11) DEFAULT NULL,
  `messageID` varchar(400) NOT NULL,
  `sendat` datetime DEFAULT NULL,
  `openat` datetime DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  PRIMARY KEY (`openID`),
  KEY `subscriberID` (`subscriberID`),
  KEY `reportID` (`reportID`)
)"; 
	

	dbDelta($sqltable); 	

}

$subscriber_click_table =  $sp_install->subscriber_click_table();




if($wpdb->get_var("show tables like '$subscriber_click_table'") != $subscriber_click_table) {
	$sqltable = "CREATE TABLE ".$subscriber_click_table." (
  `clickID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subscriberID` int(11) unsigned NOT NULL,
  `reportID` int(11) unsigned NOT NULL,
  `urlID` int(11) unsigned NOT NULL,
  `clickedat` datetime DEFAULT NULL,
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`clickID`),
  KEY `subscriberID` (`subscriberID`),
  KEY `reportID` (`reportID`),
  KEY `urlID` (`urlID`)
)"; 
	

	dbDelta($sqltable); 	

}
$report_url_table =  $sp_install->report_url_table();





if($wpdb->get_var("show tables like '$report_url_table'") != $report_url_table) {
  $sqltable = "CREATE TABLE ".$report_url_table." (
  `urlID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(500) DEFAULT '',
  `reportID` int(11) DEFAULT NULL,
  PRIMARY KEY (`urlID`),
  KEY `url` (`url`),
  KEY `reportID` (`reportID`)
)"; 
  

  dbDelta($sqltable);   

}
