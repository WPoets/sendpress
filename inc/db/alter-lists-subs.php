<?php
global $wpdb;
$list_subs = $this->list_subcribers_table();
if( $wpdb->get_var("SHOW COLUMNS FROM ". $list_subs ." LIKE 'updated'") == false) {
	$wpdb->query("ALTER TABLE ".$this->list_subcribers_table()." ADD COLUMN `updated` datetime DEFAULT NULL");
}
