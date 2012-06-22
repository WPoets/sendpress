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
	$testListTable = new SendPress_Queue_Table();
	//Fetch, prepare, sort, and filter our data...
	$testListTable->prepare_items();
	$this->update_option('no_cron_send', 'false');
	//$this->fetch_mail_from_queue();
	$this->cron_start();
	?>

	<div id="taskbar" class="lists-dashboard rounded group"> 

	<div id="button-area">  
	<a id="send-now" class="btn btn-primary btn-large " data-toggle="modal" href="#myModal"   ><i class="icon-white icon-refresh"></i> Send Emails Now</a>
	</div>
	
		
		<h2>Queue</h2>
		</div>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="email-filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	    <!-- Now we can render the completed list table -->
	    <?php $testListTable->display() ?>
	    <?php wp_nonce_field($this->_nonce_value); ?>
	</form>
	<div class="modal hide fade" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Sending Emails</h3>
  </div>
  <div class="modal-body">
    <div id="sendbar" class="progress progress-striped
     active">
  <div id="sendbar-inner" class="bar"
       style="width: 40%;"></div>
</div>
	Sent <span id="queue-sent">-</span> of <span id="queue-total">-</span> emails.
  </div>
  <div class="modal-footer">
   if you close this window sending will stop. <a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
  </div>
</div>
<?php }
