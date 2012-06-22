<?php 
if( isset( $_GET['fxti'] ) && isset( $_GET['report'] ) ){

require_once( '../../../wp-load.php' );
$sp = new SendPress();

$sp->register_open($_GET['fxti'], $_GET['report']);

}

header('Content-type: image/gif'); 
include('./im/clear.gif'); 




?>
