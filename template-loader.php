<?php
/**
*
*	SendPress Email Template Loader
*
**/
global $post; 
$sp = New SendPress(); 
$inline = false;
if(isset($_GET['inline']) ){
	$inline = true;
}
$status = get_post_meta($post->ID,'_sendpress_status', true);

//if($status !== 'private' || is_user_logged_in() || $sp->has_identity_key() ){
$sp->render_template(false, true, $inline );

//} else {
//	wp_die('Sorry you are not allow to view this email in a browser.');
//}
