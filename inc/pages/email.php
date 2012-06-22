<?php
/*
*
*	This file is loaded by sendpress.php
*		
*	@function page_subscribers 
*
*	$this = reference to main SendPress
*/
global $post_ID, $post;

$view = isset($_GET['view']) ? $_GET['view'] : '' ;

$list ='';

if(isset($_GET['emailID'])){
	$emailID = $_GET['emailID'];
	$post = get_post( $_GET['emailID'] );
	$post_ID = $post->ID;
}


switch($view){
case 'create-email': 
	// Show post form.
	$post = get_default_post_to_edit( $this->_email_post_type, true );
	$post_ID = $post->ID;
	require_once( SENDPRESS_PATH. 'inc/forms/email-create.php' );
break;

case 'send-email-confirm':?>
<form action="admin.php?page=<?php echo $this->_page; ?>" method="POST" name="post" id="post">

	<?php require_once( SENDPRESS_PATH. 'inc/forms/email-confirm-send.php' ); ?>
	
	</form>
	<?php

break;


case 'style-email': ?>
	<form action="admin.php?page=<?php echo $this->_page; ?>" method="POST" name="post" id="post">
	<!--
	<div style="float:left">
		<a href="?page=sp-emails" class="spbutton supersize" >Edit Content</a>
	</div>
	-->
	<?php $this->styler_menu('style'); ?>	
	<?php require_once( SENDPRESS_PATH. 'inc/forms/email-style.2.0.php' ); ?>
	</form>
	<?php
break;

case 'edit-email':
	require_once( SENDPRESS_PATH. 'inc/forms/email-create-edit.php' );
break;
case 'send-email': ?>
	<form action="admin.php?page=<?php echo $this->_page; ?>" method="POST" name="post" id="post">
	<!--
	<div style="float:left">
		<a href="?page=sp-emails" class="spbutton supersize" >Edit Content</a>
	</div>
	-->
	<?php $this->styler_menu('send'); ?>	
	<?php require_once( SENDPRESS_PATH. 'inc/forms/email-send.php' ); ?>
	</form>
	<?php
break;

default: 
	//Create an instance of our package class...
	$testListTable = new SendPress_Emails_Table();
	//Fetch, prepare, sort, and filter our data...
	$testListTable->prepare_items();

	?>
	<div id="taskbar" class="lists-dashboard rounded group"> 

		<div id="button-area">  
			<a class="btn btn-primary btn-large" href="?page=<?php echo $_REQUEST['page']; ?>&view=create-email">Create Email</a>
		</div>
		<h2>Emails</h2>
	</div>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="email-filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	    <!-- Now we can render the completed list table -->
	    <?php $testListTable->display() ?>
	    <?php wp_nonce_field($this->_nonce_value); ?>
	</form>
<?php }
