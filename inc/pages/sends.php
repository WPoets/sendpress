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



switch($view){

default: 
	//Create an instance of our package class...
	$testListTable = new SendPress_Reports_Table();
	//Fetch, prepare, sort, and filter our data...
	$testListTable->prepare_items();

	?>
	<div id="taskbar" class="lists-dashboard rounded group"> 

		
		<h2>Reports</h2>
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
