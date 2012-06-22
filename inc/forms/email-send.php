<?php
global $current_user;


$post_type = $this->_email_post_type;
$post_type_object = get_post_type_object($this->_email_post_type);

?>

<input type="hidden" value="save-confirm-send" name="save-action" id="save-action" />
<input type="hidden" value="save-send" name="action" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo $current_user->ID; ?>" />
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo $post->ID; ?>" />
<h2>Send Email</h2>
<div class="boxer">
<div class="boxer-inner">

<h2>Subject</h2>
<p><input type="text" name="post_subject" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( get_post_meta($post->ID,'_sendpress_subject',true ) )); ?>" id="email-subject" autocomplete="off" /></p>
<br>
<div class="leftcol">
		
		<div class="style-unit">
<h4>Lists</h4>
<?php

$current_lists = $this->get_lists();
foreach($current_lists as $list){
	echo "<input name='listIDS[]' type='checkbox' id='listIDS' value=" . $list->listID. "> ".$list->name ."<br>";
}
?>
</div>
<div class="style-unit">
<h4>Test Emails</h4>
<textarea name="test-add" cols='26' rows='10'></textarea>
<?php wp_nonce_field($this->_nonce_value); ?><br><br>



</div>
</div>
<div class="widerightcol">
<iframe src="<?php echo get_permalink( $post->ID ); ?>?inline=true" width="100%" height="600px"></iframe>
</div>
</div>
