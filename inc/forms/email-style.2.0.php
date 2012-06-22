<?php 

$default_styles_id = $this->template_post('user-style');

if( isset($emailID) ){
	if(false == get_post_meta( $default_styles_id , 'body_bg', true) ){
		$default_styles_id = $this->template_post('default-style');

	}

	$display_content = $post->post_content;
	$display_content = apply_filters('the_content', $display_content);
	$display_content = str_replace(']]>', ']]>', $display_content);

} else {
	global $post_id;
	$post =  get_post( $default_styles_id );
	$post_id = $post->ID;
	$default_styles_id = $this->template_post('default-style');

	$display_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas eget libero nisi. Donec pretium pellentesque rutrum. Fusce viverra dapibus nisi in aliquet. Aenean quis convallis quam. Praesent eu lorem mi, in congue augue. Fusce vitae elementum sapien. Vivamus non nisi velit, interdum auctor nulla. Morbi at sem nec ligula gravida elementum. Morbi ornare est et nunc tristique posuere.

Morbi iaculis fermentum magna, <a href="#">nec laoreet erat commodo vel</a>. Donec arcu justo, varius at porta eget, aliquet varius ipsum. Aliquam at lacus magna. Curabitur ullamcorper viverra turpis, vitae egestas mi tincidunt sed. Quisque fringilla adipiscing feugiat. In magna lectus, lacinia in suscipit sit amet, varius sed justo. Suspendisse vehicula, magna vitae porta pretium, massa ipsum commodo metus, eget feugiat massa leo in elit.';

						
	
}


$default_post = get_post( $default_styles_id );


/*
if(!isset($post)){

	$post = $default_post;
}
*/
if(isset($post) && false == get_post_meta( $post->ID , 'body_bg', true) ) {

	update_post_meta( $post->ID , 'body_bg',  get_post_meta( $default_post->ID , 'body_bg', true) );
	update_post_meta( $post->ID , 'body_text',  get_post_meta( $default_post->ID , 'body_text', true) );
	update_post_meta( $post->ID , 'body_link',  get_post_meta( $default_post->ID , 'body_link', true) );
	
	update_post_meta( $post->ID , 'header_bg',  get_post_meta( $default_post->ID , 'header_bg', true) );
	update_post_meta( $post->ID , 'header_text_color',  get_post_meta( $default_post->ID , 'header_text_color', true) );

	update_post_meta( $post->ID , 'content_bg',  get_post_meta( $default_post->ID , 'content_bg', true) );
	update_post_meta( $post->ID , 'content_text',  get_post_meta( $default_post->ID , 'content_text', true) );
	update_post_meta( $post->ID , 'sp_content_link_color',  get_post_meta( $default_post->ID , 'sp_content_link_color', true) );
	update_post_meta( $post->ID , 'content_border',  get_post_meta( $default_post->ID , 'content_border', true) );
	update_post_meta( $post->ID , 'upload_image',  get_post_meta( $default_post->ID , 'upload_image', true) );
	update_post_meta( $post->ID , 'image_header_url',  get_post_meta( $default_post->ID , 'image_header_url', true) );

	update_post_meta( $post->ID , 'header_text',  get_post_meta( $default_post->ID , 'header_text', true) );
	update_post_meta( $post->ID , 'header_link',  get_post_meta( $default_post->ID , 'header_link', true) );
	update_post_meta( $post->ID , 'sub_header_text',  get_post_meta( $default_post->ID , 'sub_header_text', true) );
	
	update_post_meta( $post->ID , 'active_header',  get_post_meta( $default_post->ID , 'active_header', true) );

} 

$body_bg = array(
	'value' => get_post_meta( $post->ID , 'body_bg', true),
	'std' => get_post_meta( $default_post->ID , 'body_bg', true),
);

$body_text = array(
	'value' => get_post_meta( $post->ID , 'body_text', true),
	'std' => get_post_meta( $default_post->ID , 'body_text', true),
);

$body_link = array(
	'value' => get_post_meta( $post->ID , 'body_link', true),
	'std' => get_post_meta( $default_post->ID , 'body_link', true),
);

$content_bg = array(
	'value' => get_post_meta( $post->ID , 'content_bg', true),
	'std' => get_post_meta( $default_post->ID , 'content_bg', true),
);

$content_border = array(
	'value' => get_post_meta( $post->ID , 'content_border', true),
	'std' => get_post_meta( $default_post->ID , 'content_border', true),
);

$content_text = array(
	'value' => get_post_meta( $post->ID , 'content_text', true),
	'std' => get_post_meta( $default_post->ID , 'content_text', true),
);

$content_link = array(
	'value' => get_post_meta( $post->ID , 'sp_content_link_color', true),
	'std' => get_post_meta( $default_post->ID , 'sp_content_link_color', true),
);

$upload_image = array(
	'value' => get_post_meta( $post->ID , 'upload_image', true),
	'std' => get_post_meta( $default_post->ID , 'upload_image', true),
);

$header_bg = array(
	'value' => get_post_meta( $post->ID , 'header_bg', true),
	'std' => get_post_meta( $default_post->ID , 'header_bg', true),
);

$header_text_color = array(
	'value' => get_post_meta( $post->ID , 'header_text_color', true),
	'std' => get_post_meta( $default_post->ID , 'header_text_color', true),
);

$header_text = array(
	'value' => get_post_meta( $post->ID , 'header_text', true),
	'std' => get_post_meta( $default_post->ID , 'header_text', true),
);

$header_link = array(
	'value' => get_post_meta( $post->ID , 'header_link', true),
	'std' => get_post_meta( $default_post->ID , 'header_link', true),
);

$image_header_url = array(
	'value' => get_post_meta( $post->ID , 'image_header_url', true),
	'std' => get_post_meta( $default_post->ID , 'image_header_url', true),
);

$sub_header_text = array(
	'value' => get_post_meta( $post->ID , 'sub_header_text', true),
	'std' => get_post_meta( $default_post->ID , 'sub_header_text', true),
);

$active_header = array(
	'value' => get_post_meta( $post->ID , 'active_header', true),
	'std' => get_post_meta( $default_post->ID , 'active_header', true),
);

if(strlen($upload_image['value']) > 0){
	$myimage= $upload_image['value'];
} else {
	$myimage= $upload_image['std'];
}

if(strlen($header_text['value']) > 0){
	$my_header_text = $header_text['value'];
} else {
	$my_header_text = $header_text['std'];
}

if(strlen($header_link['value']) > 0){
	$my_header_link = $header_link['value'];
} else {
	$my_header_link = $header_link['std'];
}

if(strlen($image_header_url['value']) > 0 ){
	$my_image_header_url = $image_header_url['value'];
} else {
	$my_image_header_url = $image_header_url['std'];
}

if(strlen($sub_header_text['value']) > 0){
	$my_sub_header_text = $sub_header_text['value'];
} else {
	$my_sub_header_text = $sub_header_text['std'];
}

if(strlen($active_header['value']) > 0){
	$my_active_header = $active_header['value'];
} else {
	$my_active_header = $active_header['std'];
}

?>
<input type="hidden" name="post_ID" id="post_ID" value="<?php echo $post->ID; ?>" />
<input type="hidden" value="save-style" name="save-action" id="save-action" />
<input type="hidden" value="save-style" name="action" />

<input type="hidden" value="<?php echo $my_active_header; ?>" name="active_header" id="active_header" />
<?php if( isset($emailID) ){ ?>
<h2>Edit & Style Email</h2>
<input value="simple.php" name="template" type="hidden" />
<?php } ?>
<div class="boxer">
<div class="boxer-inner">
	<?php if( isset($emailID) ){ ?>
<h2>Subject</h2>
	<p><input type="text" name="post_subject" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( get_post_meta($post->ID,'_sendpress_subject',true ) )); ?>" id="email-subject" autocomplete="off" /></p><br>
<?php } ?>


	<div class="leftcol">
		
		<div class="style-unit">
		<h4>Body Styles</h4>
		Background
		<p><?php $this->create_color_picker( array('id'=>'body_bg','value'=>$body_bg['value'],'std'=>$body_bg['std'], 'link'=>'#html-view' ,'css'=>'background-color' ) ); ?></p>
		
		Body Text Color<br>
		<?php $this->create_color_picker( array('id'=>'body_text','value'=>$body_text['value'],'std'=>$body_text['std'], 'link'=>'.html-view-outer-text' ,'css'=>'color' ) ); ?>
		<br><br>
		Body Link Color<br>
		<?php $this->create_color_picker( array('id'=>'body_link','value'=>$body_link['value'],'std'=>$body_link['std'], 'link'=>'.html-view-outer-text a' ,'css'=>'color' ) ); ?>
		</div>
		
		<div class="style-unit">
			<h4>Header Styles</h4>
		 Background
		<p><?php $this->create_color_picker( array('id'=>'header_bg','value'=>$header_bg['value'],'std'=>$header_bg['std'], 'link'=>'#html-header' ,'css'=>'background-color' ) ); ?></p>
		
		 Text Color<br>
		<?php $this->create_color_picker( array('id'=>'header_text_color','value'=>$header_text_color['value'],'std'=>$header_text_color['std'], 'link'=>'#html-header' ,'css'=>'color' ) ); ?>

		</div>
		
		<div class="style-unit">
			<h4>Content Styles</h4>
		 Background<br>
		<?php $this->create_color_picker( array('id'=>'content_bg','value'=>$content_bg['value'],'std'=>$content_bg['std'], 'link'=>'#html-content','css'=>'background-color' ) ); ?>
		<br><br>
		Border<br>
		<?php $this->create_color_picker( array('id'=>'content_border','value'=>$content_border['value'],'std'=>$content_border['std'], 'link'=>'.html-wrapper','css'=>'border-color' ) ); ?>
		<br><br>
		Text Color<br>
		<?php $this->create_color_picker( array('id'=>'content_text','value'=>$content_text['value'],'std'=>$content_text['std'], 'link'=>'#html-content' ,'css'=>'color' ) ); ?>
		<br><br>
		Link Color<br>
		<?php $this->create_color_picker( array('id'=>'sp_content_link_color','value'=>$content_link['value'],'std'=>$content_link['std'],'link'=>'#html-content a' ,'css'=>'color' ) ); ?>
		
		</div>
		</div>



	<div class="widerightcol">
		<div id="imageaddbox" class="inputbox">
			
			<label for="upload_image">Enter an URL or upload an image for the banner.<br>
				<input id="upload_image" type="text" size="36" name="upload_image" value="<?php echo $myimage; ?>"  /><br><a href="#" id="addimageupload" rel="<?php echo $post->ID; ?>" class="btn">Upload Image</a><span class="error">Image path required to activate image</span>
			</label>
			<br>
			<small>Width: 600px or less.</small><br>
			<small>Height: 200px recommmended but any height will work.</small>
			<br><br>
			<label for="image_header_url">Link:</label><input value="<?php echo $my_image_header_url; ?>" type="text" name="image_header_url" style="width: 100%;"><br><br>
			<a href="" id="activate-image" class="btn btn-primary"><?php if( $my_active_header === 'image' ){ echo 'Update'; }else{ echo 'Activate'; } ?></a>
			<a href="" id="close-image" class="btn">Close</a>
		</div>
		<div id="textaddbox" class="inputbox">
			<strong>Header Text:</strong><br><input type="text" name="header_text" value="<?php echo $my_header_text; ?>" style="width: 100%;"><br><br>
			<strong>Sub Header Text:</strong><br><input type="text" name="sub_header_text" value="<?php echo $my_sub_header_text; ?>" style="width: 100%;"><br><br>
			<strong>Header Link:</strong><br><input type="text" name="header_link" value="<?php echo $my_header_link; ?>" style="width: 100%;"><br><br>
			
			<a href="" id="activate-text" class="btn btn-primary">Save and Activate</a> <a href="" id="save-text" class="btn">Save</a>
		</div>

		<div id="html-view" class="html-view">

			<div class="html-view-holder">
				<div class="html-view-outer-text">Is this email not displaying correctly? <a href="#">View it in your browser</a>.
				</div>
				<div class="html-wrapper" class="html-wrapper">
					<div id='html-header' class='header-holder empty'>
						<div id="header-image"<?php if($my_active_header !== 'image'){ echo ' class="hide"'; } ?>>
							<?php if( strlen($my_image_header_url) > 0 ){ echo '<a href="'.$my_image_header_url.'">'; } ?>
								<img id="header-image-preview" src="<?php echo $myimage; ?>" />
							<?php if( strlen($my_image_header_url) > 0 ){ echo '</a>'; } ?>
						</div>
						<div id="header-text"<?php if($my_active_header !== 'text'){ echo ' class="hide"'; } ?>>
							<?php if( strlen($my_header_link) > 0 ){ echo '<a href="'.$my_header_link.'">'; } ?>
							<div id="header-text-title"><?php echo $my_header_text; ?></div>
							<?php if( strlen($my_header_link) > 0 ){ echo '</a>'; } ?>
							<div id="header-text-tagline"><?php echo $my_sub_header_text; ?></div>
						</div>
						<div id="header-controls">
							<div class="btn-group">
								<a href="" id="addimage" class="btn"><i class="icon-picture"></i> Image</a> <a href="" id="addtext" class="btn"><i class="icon-pencil	"></i> Text</a>
							</div>
						</div>
					</div>
					<div id='html-content' class="html-wrapper-inner">
				
						<div>

							<?php
							if( isset($emailID) ){ 
								if(function_exists('wp_editor')){ //Added Check for 3.2.1
									wp_editor($post->post_content,'content');
								} else {
									the_editor($post->post_content,'content');
								}
							} else {
								echo $display_content; 
							}
							?>
						</div>

					</div>
				</div>
				<div  class="html-view-outer-text">
					 <div id="can-spam-template">
					 	<?if ( false !== $this->get_option('canspam') ){
					 		echo wpautop( $this->get_option('canspam') );

					 	} else { ?>	
					 	Blog/Company Name<br>
                                Street Address<br>
                                Anywhere, USA 01234<br>
                               <?php } ?>
                            </div><br>
                                Not interested anymore? <a href="#" >Unsubscribe</a> Instantly.
				</div>
		  </div>
		</div>
	</div>
	<br class='clear'>
</div>
</div>
 <?php wp_nonce_field($this->_nonce_value); ?>
