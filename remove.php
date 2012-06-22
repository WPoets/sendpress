<?php 
if( isset( $_GET['fxti'] ) && isset( $_GET['report']) && isset( $_GET['list'] ) ){

require_once( '../../../wp-load.php' );
$sp = new SendPress();

$sp->register_unsubscribe($_GET['fxti'], $_GET['report'] ,$_GET['list']);

}



?>
