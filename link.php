<?php
$link = $_GET['url'];
if( isset( $_GET['fxti'] ) && isset( $_GET['report'] ) ){

require_once( '../../../wp-load.php' );
$sp = new SendPress();

$sp->register_click($_GET['fxti'], $_GET['report'], $link);

}




header( 'Location: '.$link ) ;
