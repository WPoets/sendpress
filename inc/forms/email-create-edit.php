<?php
global $current_user;

wp_enqueue_script('post');

$post_type = $this->_email_post_type;
$post_type_object = get_post_type_object($this->_email_post_type);

?><form action="admin.php?page=<?php echo $this->_page; ?>" method="POST" name="post" id="post">
<?php $this->styler_menu('edit'); ?>
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
<input type="hidden" value="save-edit" name="save-action" id="save-action" />
<input type="hidden" value="save-email" name="action" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo $current_user->ID; ?>" />
<input type="hidden" value="default" name="target-location" id="target-location" />
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo $post->ID; ?>" />
<div id="titlediv">
	<h2>Title</h2>
<div id="titlewrap">
	<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php echo apply_filters( 'enter_title_here', __( 'Enter Title here' ), $post ); ?></label>
	<input type="text" name="post_title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title" autocomplete="off" />

</div>
<div class="inside">
<?php
$sample_permalink_html = $post_type_object->public ? get_sample_permalink_html($post->ID) : '';
$shortlink = wp_get_shortlink($post->ID, 'post');
if ( !empty($shortlink) )
    $sample_permalink_html .= '<input id="shortlink" type="hidden" value="' . esc_attr($shortlink) . '" /><a href="#" class="button" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val()); return false;">' . __('Get Shortlink') . '</a>';

if ( $post_type_object->public && ! ( 'pending' == $post->post_status && !current_user_can( $post_type_object->cap->publish_posts ) ) ) { ?>
	<div id="edit-slug-box">
	<?php
		if ( ! empty($post->ID) && ! empty($sample_permalink_html) && 'auto-draft' != $post->post_status )
			echo $sample_permalink_html;
	?>
	</div>
<?php
}
?>
</div><br>
	<h2>Subject</h2>
	<input type="text" name="post_subject" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( get_post_meta($post->ID,'_sendpress_subject',true ) )); ?>" id="email-subject" autocomplete="off" />

</div>
<div id='test'>test</div>
<?php
wp_nonce_field( 'samplepermalink', 'samplepermalinknonce', false );

wp_nonce_field( 'autosave', 'autosavenonce', false );

?>
<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(stripslashes(wp_get_referer())); ?>" />
<div style="width: 580px;">
<?php


$template = get_post_meta($post->ID,'_sendpress_template', true);
//echo '<pre>';
//print_r($template);
//echo '</pre>';
/*
echo "<select name='template'>";
$this->email_template_dropdown($template);
echo '</select><br><br>';
*/

wp_editor($post->post_content,'content');




 ?>
</div>
 <input value="simple.php" name='template' type="hidden" />
<br><br>
<?php //wp_editor($post->post_content,'textversion'); ?>

 <?php wp_nonce_field($this->_nonce_value); ?><br><br>
 </form>
 </div> </div>
