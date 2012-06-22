<?php
global $current_user;

wp_enqueue_script('post');

$post_type = $this->_email_post_type;
$post_type_object = get_post_type_object($this->_email_post_type);

?>
<form action="admin.php?page=<?php echo $this->_page; ?>" method="POST" name="post" id="post">
<?php $this->styler_menu('edit'); ?>
<h2>Create Email</h2>
<div id="poststuff" class="metabox-holder"> 
<!--
has-right-sidebar">
<div id="side-info-column" class="inner-sidebar">
	
	<div class="clear"><br>
	<?php echo do_action('do_meta_boxes', $this->_email_post_type, 'side', $post); 
	do_meta_boxes($post_type, 'side', $post);?>
	</div>
</div>
-->
<div id="post-body">
<div id="post-body-content">
<input type="hidden" value="save-create" name="save-action" id="save-action" />
<input type="hidden" value="save-email" name="action" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo $current_user->ID; ?>" />
<input type="hidden" value="default" name="target-location" id="target-location" />
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo $post->ID; ?>" />
<div class="boxer">
<div class="boxer-inner">
	<!--
	<h2>Email Template Name</h2>
	-->
	<input type="hidden" name="post_title" size="30" tabindex="1" value="<?php  echo $this->random_code();  //echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title" autocomplete="off" />
<!--<br><br>-->
	<h2>Subject</h2>
	<input type="text" name="post_subject" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( get_post_meta($post->ID,'_sendpress_subject',true ) )); ?>" id="email-subject" autocomplete="off" />

</div>
</div>
</div>
<input value="simple.php" name="template" type="hidden" />
<br><br>
<?php //wp_editor($post->post_content,'textversion'); ?>

 <?php wp_nonce_field($this->_nonce_value); ?><br><br>
 </form>
 </div> </div>
