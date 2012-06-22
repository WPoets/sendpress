<?php
require( '../../../wp-load.php' );
$sp = new SendPress();
$sp->fetch_mail_from_queue();

//$test = $sp->render_template(24, false);

//echo $test;


?>
