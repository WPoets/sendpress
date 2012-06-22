<?php

$sp_install = NEW SendPress();
// Create Stats Table
$subscriber_status_table =  $sp_install->subscriber_status_table();



require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if($wpdb->get_var("show tables like '$subscriber_status_table'") != $subscriber_status_table) {
	$sqltable = "CREATE TABLE ".$subscriber_status_table." (
			  `statusid` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `status` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`statusid`)
			)"; 
	

	dbDelta($sqltable); 	


		$insert = "INSERT INTO ".$subscriber_status_table." (`statusid`, `status`)
VALUES
	(1,'Unconfirmed'),
	(2,'Active'),
	(3,'Unsubscribed'),
	(4,'Bounced')";
	 
	$results = $wpdb->query( $insert );

}
