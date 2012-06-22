<?php
class SendPressUnsubscribeShortcode{

	function load_form( $attr, $content = null ) {

	    ob_start();

	    extract(shortcode_atts(array(
			'firstname_label' => 'First Name'
		), $attr));

	    ?>
	    
	    <div class="unsubscribe">
	    	unsubscribe goes here...
	    </div>
	
	    <?php

	    $output = ob_get_contents();
	    ob_end_clean();
	    return $output;
	}

}

add_shortcode('sendpress-unsubscribe', array('SendPressUnsubscribeShortcode','load_form'));
