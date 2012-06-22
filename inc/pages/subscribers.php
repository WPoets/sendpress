<?php
/*
*
*	This file is loaded by sendpress.php
*		
*	@function page_subscribers 
*
*	$this = reference to main SendPress
*/

$view = isset($_GET['view']) ? $_GET['view'] : '' ;

$list ='';
if(isset($_GET['listID'])){
	$listinfo = $this->getDetail( $this->lists_table(),'listID', $_GET['listID'] );	

	//print_r($listinfo);
	$list = '&listID='.$_REQUEST['listID'];
	$listname = 'for '.$listinfo[0]->name;
}

switch($view){
	
case 'subscriber':?>
	<div id="taskbar" class="lists-dashboard rounded group"> 
	<h2>Edit Subscriber</h2>
	</div>
<?php
	$sub = $this->getSubscriber($_GET['subscriberID'],$_GET['listID']);
	
	?>
	<form id="subscriber-edit" method="post">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="action" value="edit-subscriber" />
	    <input type="hidden" name="listID" value="<?php echo $_GET['listID']; ?>" />
	    <input type="hidden" name="subscriberID" value="<?php echo $_GET['subscriberID']; ?>" />
	    Email: <input type="text" name="email" value="<?php echo $sub->email; ?>" /><br>
	    Firstname: <input type="text" name="firstname" value="<?php echo $sub->firstname; ?>" /><br>
	    Lastname: <input type="text" name="lastname" value="<?php echo $sub->lastname; ?>" /><br>
	    Status: <select name="status">
	    			<?php 
	    				$results = $this->getData($this->subscriber_status_table());
	    				foreach($results as $status){
	    					$selected = '';
	    					if($status->status == $sub->status){
	    						$selected = 'selected';
	    					}
	    					echo "<option value='$status->statusid' $selected>$status->status</option>";

	    				}


	    			?>

	    		</select>
	    		<br>
	   <input type="submit" value="submit"/>
	   <?php wp_nonce_field($this->_nonce_value); ?>

	</form>
	<?php

break;


case 'subscribers':

	//Create an instance of our package class...
	$testListTable = new SendPress_Subscribers_Table();
	//Fetch, prepare, sort, and filter our data...
	$testListTable->prepare_items();

	?>
	<div id="taskbar" class="lists-dashboard rounded group"> 
		<div id="button-area">  
			
			<a class="btn btn-primary btn-large" href="?page=<?php echo $_REQUEST['page']; ?>&view=addsubscriber<?php echo $list; ?>">Add Subscriber</a>
		</div>
		<h2>Subscribers <?php echo $listname ?> </h2>
	</div>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="movies-filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	    <!-- Now we can render the completed list table -->
	    <?php $testListTable->display() ?>
	    <?php wp_nonce_field($this->_nonce_value); ?>
	</form>

	<?php

break;

case 'addsubscriber': ?>

	<div id="taskbar" class="lists-dashboard rounded group"> 
	<h2>Add Subscriber</h2>
	</div>
<div class="boxer">
	<div class="boxer-inner">
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="subscriber-create" method="post">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="action" value="create-subscriber" />
	    <input type="hidden" name="listID" value="<?php echo $_GET['listID']; ?>" />
	    Email: <input type="text" name="email" value="" /><br>
	    Firstname: <input type="text" name="firstname" value="" /><br>
	    Lastname: <input type="text" name="lastname" value="" /><br>
	    Status: <select name="status">
	    			<?php 
	    				$results = $this->getData($this->subscriber_status_table());
	    				foreach($results as $status){
	    					$selected = '';
	    					if($status->status == 'Active'){
	    						$selected = 'selected';
	    					}
	    					echo "<option value='$status->statusid' $selected>$status->status</option>";

	    				}


	    			?>

	    		</select>
	    		<br>
	  <button type="submit" class="btn btn-primary">Submit</button>
	   <?php wp_nonce_field($this->_nonce_value); ?>

	</form>
	</div>
</div>
	
	<h2>Add Subscribers</h2>
<div class="boxer">
	<div class="boxer-inner">	
<div style="width: 300px; margin-right: 25%; padding: 15px;" class="rounded box float-right">
Emails shoud be written in separate lines. A line could also include a name, which is separated from the email by a comma.<br><br>
<strong>Correct formats:</strong><br>
john@gmail.com<br>
john@gmail.com, John<br>
john@gmail.com, John, Doe<br>
</div>

<form id="subscribers-create" method="post">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="action" value="create-subscribers" />
	    <input type="hidden" name="listID" value="<?php echo $_GET['listID']; ?>" />
	   	<textarea name="csv-add" cols='50' rows='25'></textarea>
	   	<button type="submit" class="btn btn-primary">Submit</button>
	   	<?php wp_nonce_field($this->_nonce_value); ?>
</form>
</div>
</div>
<?php


break;


case 'listcreate':
	?>
	<div id="taskbar" class="lists-dashboard rounded group"> 
	<h2>Create List</h2>
	</div>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="list-create" method="post">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="action" value="create-list" />
	    <p><input type="text" name="name" value="" /></p>
	    <p><input type="checkbox" class="edit-list-checkbox" name="public" value="0" /><label for="public">Allow user to sign up to this list</label></p>
	    <!-- Now we can render the completed list table -->
	   	<input type="submit" value="save" class="button-primary"/>
	   	<?php wp_nonce_field($this->_nonce_value); ?>
	</form>
	<?php
break;

case 'listedit':
	?>
	<div id="taskbar" class="lists-dashboard rounded group"> 
	<h2>Edit List</h2>
	</div>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="list-edit" method="post">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="action" value="edit-list" />
	    <input type="hidden" name="listID" value="<?php echo $_GET['listID']; ?>" />
	    <p><input type="text" name="name" value="<?php echo $listinfo[0]->name; ?>" /></p>
	    <p><input type="checkbox" class="edit-list-checkbox" name="public" value="<?php echo $listinfo[0]->public; ?>" <?php if( $listinfo[0]->public == 1 ){ echo 'checked'; } ?> /><label for="public">Allow user to sign up to this list</label></p>
	    <!-- Now we can render the completed list table -->
	   	<input type="submit" value="save" class="button-primary"/>
	   	<?php wp_nonce_field($this->_nonce_value); ?>
	</form>
	<?php
break;

default:

	

	//Create an instance of our package class...
	$testListTable = new SendPress_Lists_Table();
	//Fetch, prepare, sort, and filter our data...
	$testListTable->prepare_items();

	?>
	<div id="taskbar" class="lists-dashboard rounded group"> 
		<div id="button-area">  
			<a class="btn btn-primary btn-large" href="?page=<?php echo $_REQUEST['page']; ?>&view=listcreate">Create List</a>
		</div>
		<h2>Subscribers</h2>
	</div>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="movies-filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	    <!-- Now we can render the completed list table -->
	    <?php $testListTable->display() ?>
	    <?php wp_nonce_field($this->_nonce_value); ?>
	</form>
<?php } 
