<?php

class SendPressAjaxProcessor{
	
	function &init() {
		static $instance = false;

		if ( !$instance ) {
			$instance = new SendPressAjaxProcessor;
		}

		return $instance;
	}

	function save_list(){
		global $wpdb;

		// Create the response array
		$response = array(
			'success' => false
		);

		if($_POST) {
			$s = NEW SendPress;

			// get the credit card details submitted by the form
			$listid = $_POST['id'];
			$name = $_POST['name'];
			$public = ( $_POST['public'] === '1' ) ? 1 : 0;

			$list = $s->updateList($listid, array( 'name'=>$name, 'public'=>$public ) );

			if( false !== $list ){
				$response['success'] = true;
			}else{
				$response['error'] = $list;
			}
			
		}
		// Add additional processing here
		if($response['success']) {
			// Succeess
		} else {
			// Failed
		}
		
		// Serialize the response back as JSON
		echo json_encode($response);
		die();
	}

	function subscribe_to_list(){
		global $wpdb;

		// Create the response array
		$response = array(
			'success' => false
		);

		if($_POST) {
			$s = NEW SendPress;

			// get the credit card details submitted by the form
			$first = isset($_POST['first']) ? $_POST['first'] : '';
			$last = isset($_POST['last']) ? $_POST['last'] : '';
			$email = isset($_POST['email']) ? $_POST['email'] : '';
			$listid = isset($_POST['listid']) ? $_POST['listid'] : '';

			$subscriberID = $s->addSubscriber(array('firstname' => $first,'lastname' => $last,'email' => $email));

			$listids = explode(',', $listid);

			$lists = $s->getData($s->lists_table());
			foreach($lists as $list){
				if( $list->public == 1 && in_array($list->listID, $listids) ){
					$success = $s->linkListSubscriber($list->listID, $subscriberID, 2);
				}
			}

			if( false !== $success ){
				$response['success'] = true;
			}else{
				$response['error'] = 'User was not subscribed to the list.';
			}
			
		}
		// Add additional processing here
		if($response['success']) {
			// Succeess
		} else {
			// Failed
		}
		
		// Serialize the response back as JSON
		echo json_encode($response);
		die();
	}

}

add_action( 'init', array( 'SendPressAjaxProcessor', 'init' ) );

// register the ajax process function with wordpress
add_action("wp_ajax_sendpress_save_list", array('SendPressAjaxProcessor','save_list') );
add_action("wp_ajax_nopriv_sendpress_save_list", array('SendPressAjaxProcessor','save_list') );

add_action("wp_ajax_sendpress_subscribe_to_list", array('SendPressAjaxProcessor','subscribe_to_list') );
add_action("wp_ajax_nopriv_sendpress_subscribe_to_list", array('SendPressAjaxProcessor','subscribe_to_list') );







