<?php

$sendpress_db_version = "1.0";
 
global $wpdb;
global $sendpress_db_version;

$table = 'sendpress_';

$sp_install = NEW SendPress();
// Create Stats Table
$subscriber_table =  $sp_install->subscriber_table();



require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if($wpdb->get_var("show tables like '$subscriber_table'") != $subscriber_table) {
	$sql2 = "CREATE TABLE ".$subscriber_table." (
		  subscriberID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  email varchar(100) NOT NULL DEFAULT '',
		  join_date datetime NOT NULL,
		  status int(1) NOT NULL DEFAULT '1',
		  registered datetime NOT NULL,
		  registered_ip varchar(20) NOT NULL DEFAULT '',
		  identity_key varchar(60) NOT NULL DEFAULT '',
		  bounced int(1) NOT NULL DEFAULT '0',
		  firstname varchar(250) NOT NULL DEFAULT '',
		  lastname varchar(250) NOT NULL DEFAULT '',
		  PRIMARY KEY (`subscriberID`),
		  UNIQUE KEY  (`email`) ,
		  UNIQUE KEY (`identity_key`) 
		)"; 
	

	dbDelta($sql2); 	
}

$subscriber_list_subscribers = $sp_install->list_subcribers_table();

if($wpdb->get_var("show tables like '$subscriber_list_subscribers'") != $subscriber_list_subscribers) {



	$sql3 = "CREATE TABLE ".$subscriber_list_subscribers." (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `listID` int(11) DEFAULT NULL,
		  `subscriberID` int(11) DEFAULT NULL,
		  `status` int(1) DEFAULT NULL,
		  `updated` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY (`listID`) ,
		  KEY (`subscriberID`) ,
		  KEY (`status`) 
		)";

	dbDelta($sql3);
}


$subscriber_lists = $sp_install->lists_table();

if($wpdb->get_var("show tables like '$subscriber_lists'") != $subscriber_lists) {



	$sql4 = "CREATE TABLE ". $subscriber_lists ." (
	  `listID` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `name` varchar(255) DEFAULT NULL,
	  `created` datetime DEFAULT NULL,
	  `last_send_date` datetime DEFAULT NULL,
	  `public` TINYINT(1) DEFAULT 0,
	  PRIMARY KEY (`listID`)
	)";

	dbDelta($sql4);

	$wpdb->insert($subscriber_lists, array('name'=>'My First List','created'=> date('Y-m-d H:i:s') ));

}


$subscriber_queue = $sp_install->queue_table();

if($wpdb->get_var("show tables like '$subscriber_queue'") != $subscriber_queue) {



	$sql5 = "CREATE TABLE ".$subscriber_queue." (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `subscriberID` int(11) DEFAULT NULL,
	  `listID` int(11) DEFAULT NULL,
	  `from_name` varchar(64) DEFAULT NULL,
	  `from_email` varchar(128) NOT NULL,
	  `to_email` varchar(128) NOT NULL,
	  `subject` varchar(255) NOT NULL,
	  `messageID` varchar(400) NOT NULL,
	  `emailID` int(11) NOT NULL,
	  `max_attempts` int(11) NOT NULL DEFAULT '3',
	  `attempts` int(11) NOT NULL DEFAULT '0',
	  `success` tinyint(1) NOT NULL DEFAULT '0',
	  `date_published` datetime DEFAULT NULL,
	  `inprocess` int(1) DEFAULT '0',
	  `last_attempt` datetime DEFAULT NULL,
	  `date_sent` datetime DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `to_email` (`to_email`),
	  KEY `subscriberID` (`subscriberID`),
	  KEY `listID` (`listID`)
	)";

	dbDelta($sql5);

}









add_option("sendpress_db_version", $sendpress_db_version);
