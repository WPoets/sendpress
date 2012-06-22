<?php
class SendPressSugnupShortcode{

	function load_form( $attr, $content = null ) {

		global $load_signup_js;
		$load_signup_js = true;

	    ob_start();

	    $s = new SendPress;

	    $lists = $s->getData($s->lists_table());

	    foreach($lists as $list){
			if( $list->public == 1 ){
				$default_list_id = $list->listID;
			}
		}

	    extract(shortcode_atts(array(
			'firstname_label' => 'First Name',
			'lastname_label' => 'Last Name',
			'email_label' => 'E-Mail',
			'listids' => '',
			'display_firstname' => false,
			'display_lastname' => false,
			'label_display' => false,
			'desc' => '',
			'label_width' => 100,
			'thank_you'=>'Thank you for subscribing!',
			'button_text' => 'Submit'
		), $attr));

		$label = filter_var($label_display, FILTER_VALIDATE_BOOLEAN);

	    ?>
	    
	    <div class="sendpress-signup-form">
			<form id="sendpress_signup" action="" method="POST" class="sendpress-signup">
				<input type="hidden" name="list" id="list" value="<?php echo $listids; ?>" />
				<div id="error"></div>
				<div id="thanks"><?php echo $thank_you; ?></div>
				<div id="form-wrap">
					<p><?php echo $desc; ?></p>
					<?php if( filter_var($display_firstname, FILTER_VALIDATE_BOOLEAN)  ): ?>
						<fieldset name="firstname">
							<?php if( !$label ): ?>
								<label for="firstname"><?php echo $firstname_label; ?>:</label>
							<?php endif; ?>
							<input type="text" id="firstname" orig="<?php echo $firstname_label; ?>" value="<?php if($label){ echo $firstname_label; } ?>" tabindex="50" name="firstname" />
						</fieldset>
					<?php endif; ?>

					<?php if( filter_var($display_lastname, FILTER_VALIDATE_BOOLEAN) ): ?>
						<fieldset name="lastname">
							<?php if( !$label ): ?>
								<label for="lastname"><?php echo $lastname_label; ?>:</label>
							<?php endif; ?>
							<input type="text" id="lastname" orig="<?php echo $lastname_label; ?>" value="<?php if($label){ echo $lastname_label; } ?>" tabindex="50" name="lastname" />
						</fieldset>
					<?php endif; ?>

					<fieldset name="email">
						<?php if( !$label ): ?>
							<label for="email"><?php echo $email_label; ?>:</label>
						<?php endif; ?>
						<input type="text" id="email" orig="<?php echo $email_label; ?>" value="<?php if($label){ echo $email_label; } ?>" tabindex="50" name="email" />
					</fieldset>

					<fieldset class="submit">
						<input value="<?php echo $button_text; ?>" class="signup-submit" type="submit" tabindex="53" id="submit" name="submit">
					</fieldset>
				</div>
			</form>
		</div> 
	
	    <?php

	    $output = ob_get_contents();
	    ob_end_clean();
	    return $output;
	}

}

add_shortcode('sendpress-signup', array('SendPressSugnupShortcode','load_form'));
