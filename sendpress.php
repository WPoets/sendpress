<?php 
/*
Plugin Name: SendPress
Version: 0.8.3
Plugin URI: http://sendpress.com
Description: The first true all-in-one Email Markteing and Newsletter plugin for WordPress.
Author: SendPress
Author URI: http://sendpress.com/
*/

defined( 'SENDPRESS_API_BASE' ) or define( 'SENDPRESS_API_BASE', 'https://api.sendpres.com' );
define( 'SENDPRESS_API_VERSION', 1 );
define( 'SENDPRESS_MINIMUM_WP_VERSION', '3.2' );
define( 'SENDPRESS_VERSION', '0.8.3' );
define( 'SENDPRESS_URL', plugin_dir_url(__FILE__) );
define( 'SENDPRESS_PATH', plugin_dir_path(__FILE__) );
define( 'SENDPRESS_BASENAME', plugin_basename( __FILE__ ) );


/*
*
*	Supporting Classes they build out the WordPress table views.
*
*/
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( SENDPRESS_PATH . 'inc/classes/lists_table.php' );
require_once( SENDPRESS_PATH . 'inc/classes/subscribers_table.php' );
require_once( SENDPRESS_PATH . 'inc/classes/emails_table.php' );
require_once( SENDPRESS_PATH . 'inc/classes/reports.table.php' );
require_once( SENDPRESS_PATH . 'inc/classes/queue-table.php' );
require_once( SENDPRESS_PATH . 'inc/classes/sendpress.posts.php' );
require_once( SENDPRESS_PATH . 'inc/classes/sendpress_helper.php' );

require_once( SENDPRESS_PATH . 'inc/helpers/ajax.php' );
require_once( SENDPRESS_PATH . 'inc/helpers/signup-shortcode.php' );
require_once( SENDPRESS_PATH . 'inc/helpers/unsubscribe-shortcode.php' );
require_once( SENDPRESS_PATH . 'inc/widget/widget-signup.php' );
require_once( SENDPRESS_PATH . 'inc/helpers/smtp-api-sendgrid.php' );

class SendPress{

	var $prefix = 'sendpress_';
	var $ready = false;
	var $_nonce_value = 'sendpress-is-awesome';

	var $_current_action = '';
	var $_current_view = '';

	var $_email_post_type = 'sp_newsletters';

	var $_report_post_type = 'sp_report';

	var $adminpages = array('sp','sp-sends','sp-emails','sp-templates','sp-subscribers','sp-settings','sp-queue');

	var $_templates = array();
	var $_messages = array();

	var $_page = '';

	var $testmode = false;

	var $_posthelper = '';

	var $_debugAddress = 'josh@sendpress.com';

	var $_debugMode = false;

	function log($args) {
		return SP_Helper::log($args);
	}

	function append_log($msg, $queueid = -1) {
		return SP_Helper::append_log($msg, $queueid);
	}
		

	
	/**
	 * 	Singleton
	 * 	@static
	 */
	function &init() {
		static $instance = array();
		global $SendPress_Instance;
		if ( !$instance ) {
			load_plugin_textdomain( 'sendpress', false, SENDPRESS_PATH . 'languages/' );
			$instance[0] =& new SendPress;
			$instance[0]->add_custom_post();
			$instance[0]->maybe_upgrade();
			$instance[0]->ready_for_sending();
			$instance[0]->_init();
			add_filter( 'query_vars', array( &$instance[0], 'add_vars' ) );
			add_filter( 'cron_schedules', array(&$instance[0],'cron_schedule' ));
			add_action( 'admin_menu', array( &$instance[0], 'admin_menu' ) );
			add_action( 'admin_init', array( &$instance[0], 'admin_init' ) );
			add_action( 'admin_head', array( &$instance[0], 'admin_head' ) );
			add_action('admin_notices', array( &$instance[0],'admin_notice') );
			add_action('sendpress_notices', array( &$instance[0],'sendpress_notices') );
			add_filter( 'template_include', array( &$instance[0], 'template_include' ) );
			add_action( 'sendpress_cron_action', array( &$instance[0],'sendpress_cron_action_run') );

			//using this for now, might find a different way to include things later
			global $load_signup_js;
			$load_signup_js = false;

			add_action( 'wp_footer', array( &$instance[0], 'add_front_end_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( &$instance[0], 'add_front_end_styles' ) );

		}
		$SendPress_Instance = $instance[0];
		return $instance[0];
	}

	function _init(){
		
		add_action( 'admin_print_scripts', array($this,'editor_insidepopup') );
		add_filter( 'gettext', array($this, 'change_button_text'), null, 2 );
		add_action('wp_ajax_sendpress-sendnow', array($this, 'ajax_sendnow'));
		add_action('wp_ajax_sendpress-stopcron', array($this, 'ajax_stopcron'));
		add_action('wp_ajax_sendpress-sendcount', array($this, 'ajax_sendcount'));
		if($this->get_option('permalink_rebuild')){
			 flush_rewrite_rules( false );
			 error_log('rewrite rules');
			 $this->update_option('permalink_rebuild',false);
		}


		
	}

	function ajax_stopcron(){
		$upload_dir = wp_upload_dir();
		$filename = $upload_dir['basedir'].'/sendpress.pause';
		$Content = "Stop the cron form running\r\n";
		$handle = fopen($filename, 'w');
		fwrite($handle, $Content);
		fclose($handle);
		die();
	}

	function ajax_sendcount(){
		echo $this->countQueue();

		
		die();
	}

	function ajax_sendnow(){
		$this->update_option('no_cron_send', 'true');
		echo $this->send_single_from_queue();
		exit();
	}

	function admin_notice(){
		//This is the WordPress one shows above menu area.
		//echo 'wtf';

	}
	function sendpress_notices(){
		if( in_array('settings', $this->_messages) ){

		echo '<div class="alert alert-error">
		
	    <b>Warning!</b> Before sending any emails please setup your <a href="'.admin_url("admin.php?page=sp-templates&view=information").'">information</a>.
	    </div>';
		}
	}

	function ready_for_sending(){
		
		$ready = true;
		$message = '';

		$from = $this->get_option('fromname');
		if($from == false || $from == ''){
			$ready = false;	
			$this->show_message('settings');
		}

		$fromemail = $this->get_option('fromemail');
		if( ( $from == false || $from == '' ) && !is_email( $fromemail ) ){
			$ready = false;	
			$this->show_message('settings');
		}


		$canspam = $this->get_option('canspam');
		if($canspam == false || $canspam == ''){
			$ready = false;	
			$this->show_message('settings');
		}

		$this->ready = $ready;
	}

	function show_message($item){
		if(!in_array($item,$this->_messages) ){
			array_push($this->_messages, $item);	

			
		}	
	}
 
	// Hook into that action that'll fire weekly

	function sendpress_cron_action_run() {
		$this->fetch_mail_from_queue();
	}
 

	function cron_schedule( $schedules ) {
		    $schedules['tenminutes'] = array(
		        'interval' => 300, // 1 week in seconds
		        'display'  => __( 'Once Every Minute' ),
		    );
		 
		    return $schedules;
		}

		// Start of Presstrends Magic
		function presstrends_plugin() {

		// PressTrends Account API Key
		$api_key = 'eu1x95k67zut64gsjb5qozo7whqemtqiltzu';
		$auth = 'j0nc5cpqb2nlv8xgn0ouo7hxgac5evn0o';

		// Start of Metrics
		global $wpdb;
		$data = get_transient( 'presstrends_data' );
		if (!$data || $data == ''){
		$api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
		$url = $api_base . $auth . '/api/' . $api_key . '/';
		$data = array();
		$count_posts = wp_count_posts();
		$count_pages = wp_count_posts('page');
		$comments_count = wp_count_comments();
		$theme_data = get_theme_data(get_stylesheet_directory() . '/style.css');
		$plugin_count = count(get_option('active_plugins'));
		$all_plugins = get_plugins();
		foreach($all_plugins as $plugin_file => $plugin_data) {
		$plugin_name .= $plugin_data['Name'];
		$plugin_name .= '&';}
		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_version = $plugin_data['Version'];
		$posts_with_comments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type='post' AND comment_count > 0");
		$comments_to_posts = number_format(($posts_with_comments / $count_posts->publish) * 100, 0, '.', '');
		$pingback_result = $wpdb->get_var('SELECT COUNT(comment_ID) FROM '.$wpdb->comments.' WHERE comment_type = "pingback"');
		$data['url'] = stripslashes(str_replace(array('http://', '/', ':' ), '', site_url()));
		$data['posts'] = $count_posts->publish;
		$data['pages'] = $count_pages->publish;
		$data['comments'] = $comments_count->total_comments;
		$data['approved'] = $comments_count->approved;
		$data['spam'] = $comments_count->spam;
		$data['pingbacks'] = $pingback_result;
		$data['post_conversion'] = $comments_to_posts;
		$data['theme_version'] = $plugin_version;
		$data['theme_name'] = urlencode($theme_data['Name']);
		$data['site_name'] = str_replace( ' ', '', get_bloginfo( 'name' ));
		$data['plugins'] = $plugin_count;
		$data['plugin'] = urlencode($plugin_name);
		$data['wpversion'] = get_bloginfo('version');
		foreach ( $data as $k => $v ) {
		$url .= $k . '/' . $v . '/';}
		$response = wp_remote_get( $url );
		set_transient('presstrends_data', $data, 60*60*24);}
		}

	

	function template_include( $template ) { 
	  	global $post; 

	  	if($action = get_query_var( 'sendpress' )){
	  		
		  	$this->load_default_screen($action);
			
			die();
		}



	  	if(isset($post)){
 			if($post->post_type == $this->_email_post_type || $post->post_type == $this->_report_post_type  ) {
  				return SENDPRESS_PATH. '/template-loader.php';
    		//return dirname(__FILE__) . '/my_special_template.php'; 
			}
		}
  		return $template; 
	} 

	function load_default_screen($action){
		include_once SENDPRESS_PATH. '/inc/pages/default-public.php';
	}
	
	function render_template($post_id = false, $render = true, $inline = false){
		global $post;

		if($post_id !== false){
			$post = get_post( $post_id );

		}
		if(!isset($post)){
			echo 'Sorry we could not find your email.';
			return;
		}
		$selected_template = get_post_meta($post->ID,'_sendpress_template', true);
		if( isset($this->_templates[$selected_template]) ){
			
			ob_start();
			require_once( $this->_templates[$selected_template]['file'] );
			$HtmlCode= ob_get_clean(); 
			
			$HtmlCode =str_replace("*|SP:SUBJECT|*",$post->post_title ,$HtmlCode);


			$body_bg			=	get_post_meta( $post->ID , 'body_bg', true );
			$body_text			= 	get_post_meta( $post->ID , 'body_text', true );
			$body_link			=	get_post_meta( $post->ID , 'body_link', true );
			$header_bg			=	get_post_meta( $post->ID , 'header_bg', true );
			$active_header		=	get_post_meta( $post->ID , 'active_header', true );
			$upload_image		=	get_post_meta( $post->ID , 'upload_image', true );
			$header_text_color	=	get_post_meta( $post->ID , 'header_text_color', true );
			$header_text		=	get_post_meta( $post->ID , 'header_text', true ); //needs adding to the template
			$header_link		=	get_post_meta( $post->ID , 'header_link', true ); //needs adding to the template
			$sub_header_text	=	get_post_meta( $post->ID , 'sub_header_text', true ); //needs adding to the template
			$image_header_url	=	get_post_meta( $post->ID , 'image_header_url', true ); //needs adding to the template
			$content_bg			=	get_post_meta( $post->ID , 'content_bg', true );
			$content_text		=	get_post_meta( $post->ID , 'content_text', true );
			$content_link		=	get_post_meta( $post->ID , 'sp_content_link_color', true );
			$content_border		=	get_post_meta( $post->ID , 'content_border', true );


			$header_link_open = '';
			$header_link_close = '';
			

			if($active_header == 'image'){
				if(!empty($image_header_url)){
					$header_link_open = "<a style='color:".$header_text_color."' href='".$header_link."'>";
					$header_link_close = "</a>";

				}
				$headercontent = $header_link_open. "<img style='display:block;' src='".$upload_image."' />". $header_link_close;
				$HtmlCode =str_replace("*|SP:HEADERCONTENT|*",$headercontent ,$HtmlCode);
			} else {
				$headercontent =  "<div style='padding: 10px; text-align:center;'><h1 style='text-align:center; color: ".$header_text_color." !important;'>".$header_link_open.$header_text . $header_link_close."</h1>".$sub_header_text."</div>";
				$HtmlCode =str_replace("*|SP:HEADERCONTENT|*",$headercontent ,$HtmlCode);


			}

			$HtmlCode =str_replace("*|SP:HEADERBG|*",$header_bg ,$HtmlCode);
			$HtmlCode =str_replace("*|SP:HEADERTEXT|*",$header_text_color ,$HtmlCode);


			$HtmlCode =str_replace("*|SP:BODYBG|*",$body_bg ,$HtmlCode);
			$HtmlCode =str_replace("*|SP:BODYTEXT|*",$body_text ,$HtmlCode);
			$HtmlCode =str_replace("*|SP:BODYLINK|*",$body_link ,$HtmlCode);


			$HtmlCode =str_replace("*|SP:CONTENTBG|*",$content_bg ,$HtmlCode);
			$HtmlCode =str_replace("*|SP:CONTENTTEXT|*",$content_text ,$HtmlCode);
			$HtmlCode =str_replace("*|SP:CONTENTLINK|*",$content_link ,$HtmlCode);
			$HtmlCode =str_replace("*|SP:CONTENTBORDER|*",$content_border ,$HtmlCode);


			$canspam = wpautop( $this->get_option('canspam') );

			$HtmlCode =str_replace("*|SP:CANSPAM|*",$canspam ,$HtmlCode);

			$social = '';
			$HtmlCode =str_replace("*|SP:SOCIAL|*",$social ,$HtmlCode);

			
			if($render){
				//RENDER IN BROWSER
				if($inline){
					$link = get_permalink(  $post->ID );
				$browser = 'Is this email not displaying correctly? <a style="color: '.$body_link.';" href="'.$link.'">View it in your browser</a>.';
				$HtmlCode =str_replace("*|SP:BROWSER|*",$browser ,$HtmlCode);
				$remove_me = 'Not interested anymore? <a href="#"  style="color: '.$body_link.';" >Unsubscribe</a> Instantly.';
			
				$HtmlCode =str_replace("*|SP:UNSUBSCRIBE|*",$remove_me ,$HtmlCode);
			
				} else {
				$HtmlCode =str_replace("*|SP:BROWSER|*",'' ,$HtmlCode);
				$HtmlCode =str_replace("*|SP:UNSUBSCRIBE|*",'' ,$HtmlCode);
				}
				echo $HtmlCode;
			} else {
				//PREP FOR SENDING
				$link = get_permalink(  $post->ID );
				$browser = 'Is this email not displaying correctly? <a style="color: '.$body_link.';" href="'.$link.'">View it in your browser</a>.';
				$HtmlCode =str_replace("*|SP:BROWSER|*",$browser ,$HtmlCode);
				return $HtmlCode;
			}

		} else {
			echo 'Sorry we could not find your email template.';
			return;	
		}
	}

	function has_identity_key(){
		$key = (get_query_var('fxti')) ? get_query_var('fxti') : false;
		if(false == $key)
			return $key;
		$result =  $this->getSubscriberbyKey( $key );
		if(!empty( $result ) ) {
			return true;
		}
		return false;
	}


	function add_vars($public_query_vars) {
		$public_query_vars[] = 'fxti';
		$public_query_vars[] = 'sendpress';
		$public_query_vars[] = 'splist';
		$public_query_vars[] = 'spreport';
		$public_query_vars[] = 'spurl';
		return $public_query_vars;
	}

	function add_custom_post(){
		SendPress_Posts::email_post_type( $this->_email_post_type );
		SendPress_Posts::report_post_type( $this->_report_post_type );
		SendPress_Posts::template_post_type();
	}


	function template_post ( $_token ) {
		global $wpdb;

		$_id = 0;

		$_token = strtolower( str_replace( ' ', '_', $_token ) );


		if ( $_token ) {
			// Tell the function what to look for in a post.
			$_args = array('post_parent' => '0', 'post_type' => 'sptemplates', 'name' => 'sp-template-' . $_token, 'post_status' => 'draft', 'comment_status' => 'closed', 'ping_status' => 'closed' );

			
			// look in the database for a "silent" post that meets our criteria.
			$_posts = get_posts( $_args );
			// If we've got a post, loop through and get it's ID.

			//print_r($_posts);

			if ( count( $_posts ) ) {
				$_id = $_posts[0]->ID;
			} else {
				// If no post is present, insert one.
				// Prepare some additional data to go with the post insertion.
				$_words = explode( '_', $_token );
				$_title = join( ' ', $_words );
				$_title = ucwords( $_title );
				$_post_data = array( 'post_title' => $_title );
				$_post_data = array( 'post_name' => $_args['name'] );
				
				//$_post_data = array( 'post_name' => );
				$_post_data = array_merge( $_post_data, $_args );

				$_id = wp_insert_post( $_post_data );
			} // End IF Statement
		}

		return $_id;
	} 

	function SendPress(){
		$this->_templates = $this->get_templates();
	}

	function admin_head(){
		//wp_editor( false );
	}

	function create_color_picker( $value ) { ?>	
		<input class="cpcontroller" data-id="<?php echo $value['id']; ?>" css-id="<?php echo $value['css']; ?>" link-id="<?php echo $value['link']; ?>" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="text" value="<?php  echo isset($value['value']) ? $value['value'] : $value['std'] ; ?>" />
		<input type='hidden' value='<?php echo $value['std'];?>' id='default_<?php echo $value['id']; ?>'/>
		<a href="#" class="btn btn-mini reset-line" data-type="cp" data-id="<?php echo $value['id']; ?>" >Reset</a>
		<div id="pickholder_<?php echo $value['id']; ?>" class="colorpick clearfix" style="display:none;">
			<a class="close-picker">x</a>
			<div id="<?php echo $value['id']; ?>_colorpicker" class="colorpicker_space"></div>
		</div>
		<?php
	}


	function admin_init(){
		$this->set_template_default();
		//wp_clear_scheduled_hook( 'sendpress_cron_action' );
		// Schedule an action if it's not already scheduled
		if ( ! wp_next_scheduled( 'sendpress_cron_action' ) ) {
		    wp_schedule_event( time(), 'tenminutes',  'sendpress_cron_action' );
		}


		


		/*
		add_meta_box( 'email-status', __( 'Email Status', 'sendpress' ), array( $this, 'email_meta_box' ), $this->_email_post_type, 'side', 'low' );
			
		

		
		add_meta_box( 'sendpress_stuff2', __( 'WordPress SEO by Yoast2', 'wordpress-seo' ), array( $this, 'meta_box' ), $this->_email_post_type, 'side', 'low' );
		
		*/

		if( ( isset($_GET['page']) && $_GET['page'] == 'sp-templates' ) || (isset( $_GET['view'] ) && $_GET['view'] == 'style-email' )) {
			wp_register_script('sendpress_js_styler', SENDPRESS_URL .'js/styler.js' );
			wp_enqueue_script('sendpress_js_styler');

		
		}

		//MAKE SURE WE ARE ON AN ADMIN PAGE
		if(isset($_GET['page']) && in_array($_GET['page'], $this->adminpages)){
			$this->_page = $_GET['page'];
			wp_enqueue_style( 'farbtastic' );
	   		$this->_current_view = isset( $_GET['view'] ) ? $_GET['view'] : '' ;
			


			wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
			wp_enqueue_style('thickbox');
			wp_register_script('spfarb', SENDPRESS_URL .'js/farbtastic.js' );
			wp_enqueue_script( 'spfarb' );
			wp_register_script('sendpress_js', SENDPRESS_URL .'js/sendpress.js' );
			wp_enqueue_script('sendpress_js');
			wp_register_script('sendpress_bootstrap', SENDPRESS_URL .'bootstrap/js/bootstrap.min.js' );
			wp_enqueue_script('sendpress_bootstrap');
			wp_register_style( 'sendpress_bootstrap_css', SENDPRESS_URL . 'bootstrap/css/bootstrap.css', false, '1.0.0' );
    		wp_enqueue_style( 'sendpress_bootstrap_css' );

			wp_localize_script( 'sendpress_js', 'sendpress', array( 'ajaxurl' => admin_url( 'admin-ajax.php', 'http' ) ) );

			wp_register_style( 'sendpress_css_base', SENDPRESS_URL . 'css/style.css', false, '1.0.0' );
    		wp_enqueue_style( 'sendpress_css_base' );

	    	if ( !empty($_POST) && check_admin_referer($this->_nonce_value) ){

		    	$this->_current_action = isset( $_GET['action'] ) ? $_GET['action'] : '' ;
		    	$this->_current_action = isset( $_POST['action'] ) ? $_POST['action'] : $this->_current_action ;

		    	require_once( SENDPRESS_PATH . 'inc/helpers/sendpress-post-actions.php' );
	    	
	    	} else if ( isset( $_GET['action'] )  ){

		    	$this->_current_action = $_GET['action'];
		    	
		    	require_once( SENDPRESS_PATH . 'inc/helpers/sendpress-get-actions.php' );
	    	
	    	}
		}

   	}

   	function add_front_end_scripts(){
   		global $load_signup_js;
   		if( $load_signup_js ){
   			wp_register_script('sendpress-signup-form-js', SENDPRESS_URL .'js/sendpress.signup.js', array('jquery'), false, true );
			wp_enqueue_script( 'sendpress-signup-form-js' );
			wp_localize_script( 'sendpress-signup-form-js', 'sendpress', array( 'ajaxurl' => admin_url( 'admin-ajax.php', 'http' ) ) );
   		}

   	}

   	function add_front_end_styles(){
   		wp_enqueue_style( 'sendpress-fe-css', SENDPRESS_URL.'/css/front-end.css' );
   	}


   	function change_button_text( $translation, $original ) {

		 // We don't pass "type" in our custom upload fields, yet WordPress does, so ignore our function when WordPress has triggered the upload popup.
	    if ( isset( $_REQUEST['type'] ) ) { return $translation; }
	    
	    if( $original == 'Insert into Post' ) {
	    	$translation = __( 'Use this Image', 'sendpress' );
			if ( isset( $_REQUEST['title'] ) && $_REQUEST['title'] != '' ) { $translation = sprintf( __( 'Use as %s', 'sendpress' ), esc_attr( $_REQUEST['title'] ) ); }
	    }
	
	    return $translation;
	} 


	function save_redirect(){
	    // echo $_POST['save-type'];

	    if( isset($_POST['save-action']) ){
		    switch ( $_POST['save-action'] ) {
		    	case 'save-confirm-send':
					 wp_redirect( '?page='.$_GET['page']. '&view=send-email-confirm&emailID='. $_POST['post_ID'] );
				break; 
				case 'save-style':
					 wp_redirect( '?page='.$_GET['page']. '&view=style-email&emailID='. $_POST['post_ID'] );
				break; 
				case 'save-create':
					 wp_redirect( '?page='.$_GET['page']. '&view=style-email&emailID='. $_POST['post_ID'] );
				break;
				case 'save-send':
					 wp_redirect( '?page='.$_GET['page']. '&view=send-email&emailID='. $_POST['post_ID'] );
				break;         
				default:
					wp_redirect( $_POST['_wp_http_referer'] );
				break;
		    }
		}


	}


	function settings_menu(){ 
	?>

	<div class="subnav">
		<ul class="nav nav-pills">
		  <li <?php if($this->_current_view == ''){ ?>class="active"<?php } ?> >
		    <a href="<?php echo esc_url( admin_url('admin.php?page=sp-templates') ); ?>"><i class="icon-pencil"></i> Default Styles</a>
		  </li>
		  <li <?php if($this->_current_view == 'information'){ ?>class="active"<?php } ?> ><a href="<?php echo esc_url( admin_url('admin.php?page=sp-templates&view=information') ); ?>"><i class="icon-envelope"></i> Information</a></li>
		  <li <?php if($this->_current_view == 'account'){ ?>class="active"<?php } ?> ><a href="<?php echo esc_url( admin_url('admin.php?page=sp-templates&view=account') ); ?>"><i class="icon-user"></i> Sending Account</a></li>
			 <li <?php if($this->_current_view == 'feedback'){ ?>class="active"<?php } ?> ><a href="<?php echo esc_url( admin_url('admin.php?page=sp-templates&view=feedback') ); ?>"><i class="icon-wrench"></i> Feedback</a></li>	
		</ul>
	</div>

	<?php
	}


	function styler_menu($active){
		?>
		<div id="styler-menu">
		<div style="float:right;" class="btn-group">
			<?php if($this->_current_view == 'edit-email'){ ?>
			<a href="#" id="save-update" class="btn btn-primary btn-large "><i class="icon-white icon-ok"></i> Update</a><a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-ok icon-white"></i> Save & Next</a>
			<?php } ?>
			<?php if($this->_current_view == 'style-email'){ ?>
			<a href="#" id="save-update" class="btn btn-primary btn-large "><i class="icon-white icon-ok"></i> Update</a><a href="#" id="save-send-email" class="btn btn-primary btn-large "><i class="icon-envelope icon-white"></i> Send</a>
			<?php } ?>
			<?php if($this->_current_view == 'send-email'){ ?>
			<a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-white icon-envelope"></i> Send</a>	
			<?php } ?>
			<?php if($this->_current_view == 'create-email'){ ?>
			<a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-ok icon-white"></i> Save & Next</a>
			<?php } ?>
		</div>
		<div id="sp-cancel-btn" style="float:right; margin-top: 5px;">
			<a href="?page=<?php echo $_GET['page']; ?>" id="cancel-update" class="btn">Cancel</a>&nbsp;
		</div>
	
		
		</div>
		<?php
	}

   	function editor_insidepopup () {
   		
   		if ( isset( $_REQUEST['is_sendpress'] ) && $_REQUEST['is_sendpress'] == 'yes' ) {
			add_action( 'admin_head', array(&$this,'js_popup') );
			//dd_filter( 'media_upload_tabs', 'woothemes_mlu_modify_tabs' );
		}
	}

	function js_popup () {
		$_title = 'file';

		if ( isset( $_REQUEST['sp_title'] ) ) { $_title = $_REQUEST['sp_title']; } // End IF Statement
?>
	<script type="text/javascript">
	<!--
	jQuery(function($) {
		jQuery.noConflict();

		// Change the title of each tab to use the custom title text instead of "Media File".
		$( 'h3.media-title' ).each ( function () {
			var current_title = $( this ).html();

			var new_title = current_title.replace( 'media file', '<?php echo $_title; ?>' );

			$( this ).html( new_title )
		} );

		// Hide the "Insert Gallery" settings box on the "Gallery" tab.
		$( 'div#gallery-settings' ).hide();

		// Preserve the "is_woothemes" parameter on the "delete" confirmation button.
		$( '.savesend a.del-link' ).click ( function () {
			var continueButton = $( this ).next( '.del-attachment' ).children( 'a.button[id*="del"]' );

			var continueHref = continueButton.attr( 'href' );

			continueHref = continueHref + '&is_woothemes=yes';

			continueButton.attr( 'href', continueHref );
		} );
	});
	-->
	</script>
<?php

	}

	function my_help_tabs_to_theme_page(){
		require_once( SENDPRESS_PATH . 'inc/helpers/sendpress-help-tabs.php' );
	}

	function admin_menu() {


	    		
	    add_menu_page(__('SendPress','sendpress'), __('SendPress','sendpress'), 'manage_options','sp',  array(&$this,'page_dashboard') , SENDPRESS_URL.'/im/icon.png');
	    add_submenu_page('sp', __('Overview','sendpress'), __('Overview','sendpress'), 'manage_options', 'sp', array(&$this,'page_dashboard'));
	    $main = add_submenu_page('sp', __('Emails','sendpress'), __('Emails','sendpress'), 'manage_options', 'sp-emails', array(&$this,'page_emails'));
	    add_submenu_page('sp', __('Reports','sendpress'), __('Reports','sendpress'), 'manage_options', 'sp-sends', array(&$this,'page_sends'));
	   	add_submenu_page('sp', __('Subscribers','sendpress'), __('Subscribers','sendpress'), 'manage_options', 'sp-subscribers', array(&$this,'page_subscribers'));
	    
	   	add_submenu_page('sp', __('Settings','sendpress'), __('Settings','sendpress'), 'manage_options', 'sp-templates', array(&$this,'page_templates'));
	   	add_submenu_page('sp', __('Queue','sendpress'), __('Queue','sendpress'), 'manage_options', 'sp-queue', array(&$this,'page_queue'));
	   
	   	if($this->get_option('feedback') == 'yes'){
			$this->presstrends_plugin();
		}
	   	/*
	    add_action( 'load-toplevel_page_sp', array($this,'my_help_tabs_to_theme_page' ));
	    add_action( 'load-sendpress_page_sp-emails', array($this,'my_help_tabs_to_theme_page' ));
	    add_action( 'load-sendpress_page_sp-templates', array($this,'my_help_tabs_to_theme_page' ));
	    add_action( 'load-sendpress_page_sp-subscribers', array($this,'my_help_tabs_to_theme_page' ));
	    add_action( 'load-sendpress_page_sp-settings', array($this,'my_help_tabs_to_theme_page' ));
		*/
	    
	 //   add_meta_box('welcome', 'Welcome', array($this, 'empty_box'), $main,  $context = 'advanced', $priority = 'default');
	
	
		
		/*

		



	    /*
	    add_submenu_page('lefx_designer', __('Stats','le_theme_menu'), __('Stats','le_theme_menu'), 'manage_options', 'lefx_stats', 'build_le_stats_page');
	    add_submenu_page('lefx_designer', __('Export CSV','le_theme_menu'), __('Export CSV','le_theme_menu'), 'manage_options', 'lefx_export', 'build_le_export_page');
		add_submenu_page('lefx_designer', __('Integrations','le_theme_menu'), __('Integrations','le_theme_menu'), 'manage_options', 'lefx_integrations', 'build_le_integrations_page');
		*/
	}

	function empty_box(){}

	function tabs($currtab = ''){
		?>
	<div class="nav-sp">
		<div class="sp-icons icon32">
			<br />
		</div>
		<h2 class="nav-tab-wrapper"><a class="nav-tab <?php if($currtab == 'dashboard') { echo ' nav-tab-active'; } ?>" href="?page=sp">Overview</a><a class="nav-tab <?php if($currtab == 'emails') { echo ' nav-tab-active'; } ?>" href="?page=sp-emails">Emails</a><a class="nav-tab <?php if($currtab == 'sends') { echo ' nav-tab-active'; } ?>" href="?page=sp-sends">Reports</a><a class="nav-tab <?php if($currtab == 'subscribers') { echo ' nav-tab-active'; } ?>" href="?page=sp-subscribers">Subscribers</a><a class="nav-tab <?php if($currtab == 'templates') { echo ' nav-tab-active'; } ?>" href="?page=sp-templates">Settings</a><a class="nav-tab <?php if($currtab == 'queue') { echo ' nav-tab-active'; } ?>" href="?page=sp-queue">Queue <?php echo $this->countQueue(); ?></a></h2>
	</div>

		
		
		<?php
	
		do_action('sendpress_notices');
	}


	function page_dashboard(){
		$this->page_start('dashboard');

		$this->sp_welcome_panel();
		//echo '<h2>Overview</h2>';
		//$this->add_email_to_queue();
		//$this->fetch_mail_from_queue();
		//$datat = $this->render_template(24, false);
		
		

		$this->page_end();
		
	}

	function page_emails(){
		$this->page_start('emails');
		require_once( SENDPRESS_PATH.'inc/pages/email.php');
		$this->page_end();
	}

	function page_sends(){
		$this->page_start('sends');
		require_once( SENDPRESS_PATH.'inc/pages/sends.php');
		$this->page_end();
	}

	function page_queue(){
		$this->page_start('queue');
		require_once( SENDPRESS_PATH.'inc/pages/queue.php');
		$this->page_end();
	}

	function page_subscribers(){
		$this->page_start('subscribers');
			require_once( SENDPRESS_PATH.'inc/pages/subscribers.php');
		$this->page_end();
	}

	function page_settings(){
		$this->page_start('settings');
		require_once( SENDPRESS_PATH.'inc/pages/settings.php');
		$this->page_end();
	}

	function page_templates(){
		$this->page_start('templates');
		
			require_once( SENDPRESS_PATH.'inc/pages/template.php');
		$this->page_end();
	}

	function page_start($page){
		echo '<div class="wrap">';
		$this->tabs($page);
		echo '<div class="spwrap">';

	}

	function page_end(){
		echo '</div>';
		echo '</div>';
		//echo '<div class="clear"></div>';
		
	}

	function maybe_upgrade() {
		$current_version = $this->get_option('version', '0' );
		//$current_version = '0.8.2';
		//error_log($current_version);

		
		if ( version_compare( $current_version, SENDPRESS_VERSION, '==' ) )
			return;

		if(version_compare( $current_version, '0.6.2', '==' )){
			require_once(SENDPRESS_PATH . 'inc/db/status.table.php');
		}

		if(version_compare( $current_version, '0.6.5', '<' )){
			$this->update_option( 'install_date' , time() );
		}


		if(version_compare( $current_version, '0.7', '<' )){
			$this->update_option( 'sendmethod' , 'website' );
		}

		if(version_compare( $current_version, '0.7.5', '<' )){
			require_once(SENDPRESS_PATH . 'inc/db/open.click.table.php');
		}
		
		if(version_compare( $current_version, '0.8.1', '<' )){
			require_once(SENDPRESS_PATH . 'inc/db/alter-lists-subs.php');
		}
		if(version_compare( $current_version, '0.8.3', '<' )){
			$this->update_option( 'feedback' , 'no' );
		}

		
		

		$this->update_option( 'version' , SENDPRESS_VERSION );


	}	
	
	function set_template_default(){
		$default_style_post = $this->template_post('default-style');
		update_post_meta($default_style_post ,'body_bg', '#E8E8E8' );
		update_post_meta($default_style_post ,'body_text', '#231f20' );
		update_post_meta($default_style_post ,'body_link', '#21759B' );
		update_post_meta($default_style_post ,'header_bg', '#DDDDDD' );
		update_post_meta($default_style_post ,'header_text_color', '#333333' );

		update_post_meta($default_style_post ,'content_bg', '#FFFFFF' );
		update_post_meta($default_style_post ,'content_text', '#222222' );
		update_post_meta($default_style_post ,'sp_content_link_color', '#21759B' );
		update_post_meta($default_style_post ,'content_border', '#E3E3E3' );
	}

	function wpdbQuery($query, $type) {
		global $wpdb;
		$result = $wpdb->$type( $query );
		return $result;
	}

	function wpdbQueryArray($query) {
		global $wpdb;
		$result = $wpdb->get_results( $query , ARRAY_N);
		return $result;
	}

	// GET DATA
	function getData($table) {
		$result = $this->wpdbQuery("SELECT * FROM $table", 'get_results');
		return $result;		
	}

	// GET DETAIL (RETURN X WHERE Y = Z)
	function getDetail($table, $entry, $value) {
		$result = $this->wpdbQuery("SELECT * FROM $table WHERE $entry = '$value'", 'get_results');
		return $result;	
	}

	function getUrl($report, $url) {
		$table = $this->report_url_table();
		$result = $this->wpdbQuery("SELECT * FROM $table WHERE reportID = '$report' AND url = '$url'", 'get_results');
		return $result;	
	}

	// GET DETAIL (RETURN X WHERE Y = Z)
	function deleteList($listID) {
		$table = $this->lists_table();
		$result = $this->wpdbQuery("DELETE FROM $table WHERE listID = '$listID'", 'query');

		$table = $this->list_subcribers_table();
		$result = $this->wpdbQuery("DELETE FROM $table WHERE listID = '$listID'", 'query');

		return $result;	
	}

	// GET DETAIL (RETURN X WHERE Y = Z)
	function createList($values) {
		$table = $this->lists_table();

		
		$result = $this->wpdbQuery("INSERT INTO $table (name, created, public) VALUES( '" .$values['name'] . "', '" . date('Y-m-d H:i:s') . "','" .$values['public'] . "')", 'query');

		return $result;	
	}

	function updateList($listID, $values){
		global $wpdb;

		$table = $this->lists_table();

		$result = $wpdb->update($table,$values, array('listID'=> $listID) );

		return $result;
	}

	// GET DETAIL (RETURN X WHERE Y = Z)
	function linkListSubscriber($listID, $subscriberID, $status = 0) {
		$table = $this->list_subcribers_table();
		$result = $this->wpdbQuery("SELECT id FROM $table WHERE listID = $listID AND subscriberID = $subscriberID ", 'get_var');
		
		if($result == false){
			$result = $this->wpdbQuery("INSERT INTO $table (listID, subscriberID, status, updated) VALUES( '" . $listID . "', '" . $subscriberID . "','".$status."','".date('Y-m-d H:i:s')."')", 'query');
		}
		return $result;	
	}

	// COUNT DATA
	function countData($table) {
		$count = $this->wpdbQuery($this->wpdbQuery("SELECT COUNT(*) FROM $table", 'prepare'), 'get_var');
		return $count;
	}
	


	// COUNT DATA
	function countSubscribers($listID, $status = 2) {
		$table = $this->list_subcribers_table();
		$count = $this->wpdbQuery($this->wpdbQuery("SELECT COUNT(*) FROM $table WHERE listID = $listID AND status = $status", 'prepare'), 'get_var');
		return $count;
	}

	// COUNT DATA
	function countQueue() {
		$table = $this->queue_table();
		$count = $this->wpdbQuery($this->wpdbQuery("SELECT COUNT(*) FROM $table WHERE success = 0 AND max_attempts != attempts", 'prepare'), 'get_var');
		return $count;
	}

	function addSubscriber($values){
		$table = $this->subscriber_table();
		$email = $values['email'];

		if(!isset($values['join_date'])){
			$values['join_date'] =  date('Y-m-d H:i:s');
		}
		if(!isset($values['identity_key'])){
			$values['identity_key'] =  $this->random_code();
		}



		$result = $this->wpdbQuery("SELECT subscriberID FROM $table WHERE email = '$email' ", 'get_var');
		

		if(	$result ){ return $result; }
		global $wpdb;
		$result = $wpdb->insert($table,$values);
		//$result = $this->wpdbQuery("SELECT @lastid2 := LAST_INSERT_ID()",'query');
		return $wpdb->insert_id;
	}

	function updateSubscriber($subscriberID, $values){
		$table = $this->subscriber_table();
		$email = $values['email'];
		global $wpdb;
		$result = $this->wpdbQuery("SELECT subscriberID FROM $table WHERE email = '$email' ", 'get_var');
		if($result == false || $result == $subscriberID){ 

		$result = $wpdb->update($table,$values, array('subscriberID'=> $subscriberID) );
		//$result = $this->wpdbQuery("SELECT @lastid2 := LAST_INSERT_ID()",'query');
		 }
		//return $wpdb->insert_id;
	}

	function updateStatus($listID,$subscriberID,$status){
		$table = $this->list_subcribers_table();
		global $wpdb;
		$result = $wpdb->update($table,array('status'=>$status,'updated'=>date('Y-m-d H:i:s')), array('subscriberID'=> $subscriberID,'listID'=>$listID) );
		
	}

	function getSubscriber($subscriberID, $listID = false){
		if($listID){
        $query = "SELECT t1.*, t3.status FROM " .  $this->subscriber_table() ." as t1,". $this->list_subcribers_table()." as t2,". $this->subscriber_status_table()." as t3 " ;

        
            $query .= " WHERE (t1.subscriberID = t2.subscriberID) AND ( t3.statusid = t2.status ) AND (t2.listID =  ". $listID ." AND t2.subscriberID = ". $subscriberID .")";
        } else {
            $query = "SELECT * FROM " .  $this->subscriber_table() ." WHERE subscriberID = ". $subscriberID ;
        }

        return $this->wpdbQuery($query, 'get_row');

	}

	function getSubscriberLists( $value ) {
		$table = $this->list_subcribers_table();
		$result = $this->wpdbQueryArray("SELECT listID FROM $table WHERE subscriberID = '$value'", 'get_results');
		return $result;	
	}
	function getSubscriberListsStatus( $listID,$subscriberID ) {
		$table = $this->list_subcribers_table();
		$result = $this->wpdbQuery("SELECT status,updated FROM $table WHERE subscriberID = $subscriberID AND listID = $listID", 'get_row');
		return $result;	
	}

	function getSubscribers($listID = false){
		$query = "SELECT t1.*, t3.status FROM " .  $this->subscriber_table() ." as t1,". $this->list_subcribers_table()." as t2,". $this->subscriber_status_table()." as t3 " ;

        $query .= " WHERE (t1.subscriberID = t2.subscriberID) AND ( t3.statusid = t2.status ) AND (t2.listID =  ". $listID .")";
        
        return $this->wpdbQuery($query, 'get_results');
	}
	function get_active_subscribers($listID = false){
		$query = "SELECT t1.*, t3.status FROM " .  $this->subscriber_table() ." as t1,". $this->list_subcribers_table()." as t2,". $this->subscriber_status_table()." as t3 " ;

        $query .= " WHERE (t1.subscriberID = t2.subscriberID) AND ( t3.statusid = t2.status ) AND (t2.listID =  ". $listID .") AND (t2.status = 2)";
        
        return $this->wpdbQuery($query, 'get_results');
	}


	function getSubscriberbyKey($key){
		return $this->getDetail($this->subscriber_table(), 'identity_key', $key );
	}

	function getSubscriberKey($id){
		$subscriber = $this->getSubscriber( $id );
		if($subscriber){
			return $subscriber->identity_key;
		}
		return md5('testemailsentfromsendpress');
		
	}

	function exportList($listID = false){
		if($listID){
        $query = "SELECT t1.*, t3.status FROM " .  $this->subscriber_table() ." as t1,". $this->list_subcribers_table()." as t2,". $this->subscriber_status_table()." as t3 " ;

        
            $query .= " WHERE (t1.subscriberID = t2.subscriberID) AND ( t3.statusid = t2.status ) AND (t2.listID =  ". $listID .")";
        } else {
            $query = "SELECT * FROM " .  $this->subscriber_table();
        }

        return $this->wpdbQuery($query, 'get_results');
	}

	/**
	 * Returns the requested option.  
 	 *
	 * @param string $name    Option name
	 * @param mixed  $default (optional)
	 */
	function get_option( $name, $default = false ) {
		$options = get_option( 'sendpress_options' );
		if ( is_array( $options ) && isset( $options[$name] ) ) {
			return $options[$name];
		}

		return $default;
	}	
	/**
	 * Updates the single given option.  
 	 *
	 * @param string $name  Option name
	 * @param mixed  $value Option value
	 */
	function update_option( $name, $value ) {
		
		$options = get_option( 'sendpress_options' );
		if ( !is_array( $options ) ) {
			$options = array();
		}

		$options[$name] = $value;

		return update_option( 'sendpress_options', $options );
	}
	/**
	 * Updates the multiple given options.  
 	 *
	 * @param array $array array( option name => option value, ... )
	 */
	function update_options( $array ) {
		
		$options = get_option( 'sendpress_options' );
		if ( !is_array( $options ) ) {
			$options = array();
		}

		return update_option( 'sendpress_options', array_merge( $options, $array ) );
	}

	/**
	*
	*	Install the required tables and do some setup on activation
	*	@static
	*/
	function plugin_activation(){
		if ( version_compare( $GLOBALS['wp_version'], SENDPRESS_MINIMUM_WP_VERSION, '<' ) ) {
			deactivate_plugins( __FILE__ );
	    	wp_die( sprintf( __('SendPress requires WordPress version %s or later.', 'sendpress'), SENDPRESS_MINIMUM_WP_VERSION) );
		} else {
		    require_once(SENDPRESS_PATH .'inc/db/tables.php');
		    require_once(SENDPRESS_PATH .'inc/db/status.table.php');
		    require_once(SENDPRESS_PATH .'inc/db/open.click.table.php');
		
		    
		}
		
		
		SendPress::update_option( 'permalink_rebuild' , true );
		SendPress::update_option( 'install_date' , time() );
	}

	/**
	*
	*	Nothing going on here yet
	*	@static
	*/
	function plugin_deactivation(){
		flush_rewrite_rules( false );
		wp_clear_scheduled_hook( 'sendpress_cron_action' );
	} 

	function subscriber_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix . "subscribers";
	}

	function list_subcribers_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix  . "list_subscribers";
	}

	function lists_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix . "lists";
	}

	function subscriber_status_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix . "subscribers_status";
	}

	function subscriber_event_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix . "subscribers_event";
	}

	function subscriber_click_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix . "subscribers_click";
	}

	function subscriber_open_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix . "subscribers_open";
	}
	function report_url_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix . "report_url";
	}
	

	function queue_table(){
		global $wpdb;
		return $wpdb->prefix . $this->prefix . "queue";
	}

	/**
	 * Generate Unsubscribe code
	 **/
	function random_code() {
	    $now = time();
	    $random_code = substr( $now, strlen( $now ) - 3, 3 ) . substr( md5( uniqid( rand(), true ) ), 0, 8 ) . substr( md5( $now . rand() ), 0, 4);
	    return $random_code;
	}


	function csv2array($input,$delimiter=',',$enclosure='"',$escape='\\'){ 
    	$fields=explode($enclosure.$delimiter.$enclosure,substr($input,1,-1)); 
    	foreach ($fields as $key=>$value) 
        	$fields[$key]=str_replace($escape.$enclosure,$enclosure,$value); 
    	return($fields); 
	} 

	function array2csv($input,$delimiter=',',$enclosure='"',$escape='\\'){ 
	    foreach ($input as $key=>$value) 
	        $input[$key]=str_replace($enclosure,$escape.$enclosure,$value); 
	    return $enclosure.implode($enclosure.$delimiter.$enclosure,$input).$enclosure; 
	} 

	/*
	*
	*	Creates an array from a posted textarea
	*	
	*	expects 3 fields or less: @sendpress.me, fname, lname
	*
	*/
	function subscriber_csv_post_to_array($csv, $delimiter = ',', $enclosure = '"', $escape = '\\', $terminator = "\n") { 
	    $r = array(); 
	    $rows = explode($terminator,trim($csv)); 
	    $names = array_shift($rows); 
	    $names = explode(',', $names);
		$nc = count($names);
 
	    foreach ($rows as $row) { 
	        if (trim($row)) { 
	        	$needle = substr_count($row, ',');
	        	if($needle == false){
	        		$row .=',,';
	        	} 
	        	if($needle == 1){
	        		$row .=',';
	        	} 

	            $values = explode(',' , $row);
	            if (!$values) $values = array_fill(0,$nc,null); 
	            $r[] = array_combine($names,$values); 
	        } 
	    } 
	    return $r; 
	   } 

	function get_default_templates(){
		$mainfiles = $this->glob_php( SENDPRESS_PATH . 'templates' );
		foreach ($mainfiles as $temp) {
			if($info = $this->get_template_info($temp[0]) ){
				$sp_templates[$temp[1]] = $info;
			} 
		}

		return $sp_templates;
	}

	function get_templates(){
		$mainfiles = $this->glob_php( SENDPRESS_PATH . 'templates' );
		$themmefiles = $this->glob_php( TEMPLATEPATH . '/sendpress' );
		$wordpressfiles = $this->glob_php( WP_CONTENT_DIR . '/sendpress' );
		
		$childfiles = array();
			if( is_child_theme() ){
				$childfiles = $this->glob_php( STYLESHEETPATH . '/sendpress' );
			}
		$temps =array_merge($mainfiles, $themmefiles, $childfiles, $wordpressfiles);
		$sp_templates =  array();
		foreach ($temps as $temp) {
			if($info = $this->get_template_info($temp[0]) ){
				$sp_templates[$temp[1]] = $info;
			} 
		}

		return $sp_templates;
	}

	/**
	 * Returns an array of all PHP files in the specified absolute path.
	 * Equivalent to glob( "$absolute_path/*.php" ).
	 *
	 * @param string $absolute_path The absolute path of the directory to search.
	 * @return array Array of absolute paths to the PHP files.
	 */
	function glob_php( $absolute_path ) {
		$absolute_path = untrailingslashit( $absolute_path );
		$files = array();
		if(is_dir($absolute_path)){
		if (!$dir = @opendir( $absolute_path ) ) {
			return $files;
		}
		
		while ( false !== $file = readdir( $dir ) ) {
			if ( '.' == substr( $file, 0, 1 ) || '.php' != substr( $file, -4 ) ) {
				continue;
			}

			$file2 = "$absolute_path/$file";

			if ( !is_file( $file2 ) ) {
				continue;
			}
			$basename = str_replace($absolute_path, '', $file);
			$files[] = array($file2, $basename);
		}

		closedir( $dir );
		}

		return $files;
	}

	/**
	 * Load module data from module file. Headers differ from WordPress
	 * plugin headers to avoid them being identified as standalone
	 * plugins on the WordPress plugins page.
	 */
	function get_template_info( $file ) {
		$headers = array(
			'name' => 'SendPress',
			'regions' => 'Regions',
			'description' => 'Description',
			'sort' => 'Sort Order',
		);
		$mod = get_file_data( $file, $headers );
		$mod['file'] = $file;

		if ( empty( $mod['sort'] ) )
			$mod['sort'] = 10;
		if ( !empty( $mod['name'] ) || !empty( $mod['regions'] ) )
			return $mod;
		return false;
	}

	function get_lists(){
		return	$this->getData( $this->lists_table() );
	}

	function get_list_details($id){
		return $this->getDetail( $this->lists_table(), 'listID', $id  );
	}



	function email_meta_box(){
		global $post;
		/*
		$status =  get_post_meta($post->ID,'_sendpress_status', true);
		if('' == $status){
			$status = 'public';
		}

		echo '<input type="radio" id="status" name="status" value="public" ';
		if($status =='public'){echo 'checked'; } 
		echo ' /> Public<br>';
		echo '<input type="radio" id="status" name="status" value="private" ';
		if($status =='private'){echo 'checked'; } 
		echo '/> Private';
		echo '<p><strong>Web View Only:</strong><br>Private emails can be viewed by users that are logged in or users that have an identity key in the url they use.</p>';
		*/
		?><div id="major-publishing-actions">
<div id="delete-action">
<a class="submitdelete deletion" href="">Move to Trash</a></div>

<div id="publishing-action">
<img src="http://wp.mamp/wp-admin/images/wpspin_light.gif" class="ajax-loading" id="ajax-loading" alt="">
		<input name="original_publish" type="hidden" id="original_publish" value="Update">
		<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="Save">
		<input name="save-style" type="submit" class="button-primary" id="save-style" tabindex="5" accesskey="p" value="Save & Style">
</div>


<div class="clear"></div>
</div><?php
	}

		
		function email_template_dropdown( $default = '' ) {
			$templates = $this->_templates;
			ksort( $templates );
			foreach ( $templates as $key => $template )
				: if ( $default == $key )
					$selected = " selected='selected'";
				else
					$selected = '';
					
			echo "\n\t<option value='".$key."' $selected>".$template['name'] ."</option>";
			endforeach;
		}

	









		/**
    	* Used to add Overwrite send info for testing. 
    	*
    	* @return boolean true if mail sent successfully, false if an error
    	*/
	    function sp_mail_it( $email ) {

			if ( $this->_debugMode ) {
			    $originalTo = $email->to_email;
			    $to = $this->_debugAddress;
			    $email->subject = '('.$originalTo.') '.$email->subject;
			
				$this->append_log('-- Overrode TO, instead of '.$originalTo.' used address '.$this->_debugAddress );
		   	}
		
			return  $this->send( $email );
		}

		function unique_message_id() {
			if ( isset($_SERVER['SERVER_NAME'] ) ) {
		      	$servername = $_SERVER['SERVER_NAME'];
		    } else {
		      	$servername = 'localhost.localdomain';
		    }
		    $uniq_id = md5(uniqid(time()));
		    $result = sprintf('%s@%s', $uniq_id, $servername);
		    return $result;
		}

		function get_domain_from_email($email)
		{
			$domain = substr(strrchr($email, "@"), 1);
			return $domain;
		}


		function send( $email ) {
			global $phpmailer;

			// (Re)create it, if it's gone missing
			if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				require_once ABSPATH . WPINC . '/class-smtp.php';
				$phpmailer = new PHPMailer();
			}
			
			/*
			 * Make sure the mailer thingy is clean before we start,  should not
			 * be necessary, but who knows what others are doing to our mailer
			 */
			$phpmailer->ClearAddresses();
			$phpmailer->ClearAllRecipients();
			$phpmailer->ClearAttachments();
			$phpmailer->ClearBCCs();
			$phpmailer->ClearCCs();
			$phpmailer->ClearCustomHeaders();
			$phpmailer->ClearReplyTos();

			// Get any existing copy of our transient data
			if ( false === ( $body_html = get_transient( 'sendpress_report_body_html_'. $email->emailID ) ) ) {
			    // It wasn't there, so regenerate the data and save the transient
			    $post_info = get_post( $email->emailID );
			    $body_html = $this->render_template( $post_info->ID, false );
			    set_transient( 'sendpress_report_body_html_'. $email->emailID, $body_html );
			}
			
			$subscriber_key = trim( $this->getSubscriberKey( $email->subscriberID ) );

			$tracker = "<img src='".site_url()."?sendpress=open&fxti=".$subscriber_key."&spreport=". $email->emailID ."' /></body>";
			$body_html =str_replace("</body>",$tracker , $body_html );
			$body_link			=	get_post_meta( $email->emailID , 'body_link', true );


			$pattern ="/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
			$body_html = preg_replace( $pattern , site_url() ."?sendpress=link&fxti=".$subscriber_key."&spreport=". $email->emailID ."&spurl=$0", $body_html );
			
			$remove_me = 'Not interested anymore? <a href="'.site_url().'?sendpress=manage&fxti='.$subscriber_key.'&spreport='. $email->emailID.'&splist='. $email->listID.'"  style="color: '.$body_link.';" >Unsubscribe</a> Instantly.';
			
			$body_html = str_replace("*|SP:UNSUBSCRIBE|*", $remove_me , $body_html );
			//print_r($email);

			$content_type = 'text/html';
			
			$phpmailer->ContentType = $content_type;
			// Set whether it's plaintext, depending on $content_type
			//if ( 'text/html' == $content_type )
			$phpmailer->IsHTML( true );
			//return $email;

			$phpmailer->Subject = $email->subject;
			$phpmailer->MsgHTML( $body_html );
			$phpmailer->AltBody="This is text only alternative body.";

			
			
			// If we don't have a charset from the input headers
			if ( !isset( $charset ) )
			$charset = get_bloginfo( 'charset' );
			
			// Set the content-type and charset
			$phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

			/**
			* We'll let php init mess with the message body and headers.  But then
			* we stomp all over it.  Sorry, my plug-inis more important than yours :)
			*/
			do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );
							
			
			$from_email = $this->get_option('fromemail');//$this->get_option['login'];
			$phpmailer->From = $from_email;
			$phpmailer->FromName = $this->get_option('fromname');//$from_name;
			$phpmailer->AddAddress( trim( $email->to_email ) );
	
			$sending_method  = $this->get_option('sendmethod');
			
			if($sending_method == 'sendpress'){
				// Set the mailer type as per config above, this overrides the already called isMail method
				$phpmailer->Mailer = 'smtp';
				// We are sending SMTP mail
				$phpmailer->IsSMTP();

				// Set the other options
				$phpmailer->Host = 'smtp.sendgrid.net';
				$phpmailer->Port = 25;

				// If we're using smtp auth, set the username & password
				$phpmailer->SMTPAuth = TRUE;
				$phpmailer->Username = $this->get_option('sp_user');
				$phpmailer->Password = $this->get_option('sp_pass');
		
			} elseif ( $sending_method == 'gmail' ){

				// Set the mailer type as per config above, this overrides the already called isMail method
				$phpmailer->Mailer = 'smtp';
				// We are sending SMTP mail
				$phpmailer->IsSMTP();

				// Set the other options
				$phpmailer->Host = 'smtp.gmail.com';
				$phpmailer->SMTPAuth = true;  // authentication enabled
				$phpmailer->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail

				$phpmailer->Port = 465;
				// If we're using smtp auth, set the username & password
				$phpmailer->SMTPAuth = TRUE;
				$phpmailer->Username = $this->get_option('gmailuser');
				$phpmailer->Password = $this->get_option('gmailpass');
			} 
		
			$phpmailer->MessageID = $email->messageID;
			$phpmailer->AddCustomHeader( sprintf( 'X-SP-MID: %s',$email->messageID ) );
		

			$hdr = new SmtpApiHeader();

			$hdr->addFilterSetting('dkim', 'domain', $this->get_domain_from_email($from_email) );
			$phpmailer->AddCustomHeader( sprintf( 'X-SP-MID: %s',$email->messageID ) );
			$phpmailer->AddCustomHeader(sprintf( 'X-SMTPAPI: %s', $hdr->asJSON() ) );
			// Set SMTPDebug to 2 will collect dialogue between us and the mail server
			$phpmailer->SMTPDebug = 2;

			// Start output buffering to grab smtp output
			ob_start(); 

			// Send!
			$result = true; // start with true, meaning no error
			$result = @$phpmailer->Send();
			$phpmailer->SMTPClose();

			// Grab the smtp debugging output
			$smtp_debug = ob_get_clean();

			if ( ( $result != true || true ) ) {
				$hostmsg = 'host: '.($phpmailer->Host).'  port: '.($phpmailer->Port).'  secure: '.($phpmailer->SMTPSecure) .'  auth: '.($phpmailer->SMTPAuth).'  user: '.($phpmailer->Username)."  pass: *******\n";
			    $msg = '';
				$msg .= 'The result was: '.$result."\n";
			    $msg .= 'The mailer error info: '.$phpmailer->ErrorInfo."\n";
			    $msg .= $hostmsg;
			    $msg .= "The SMTP debugging output is shown below:\n";
			    $msg .= $smtp_debug."\n";
			    $msg .= 'The full debugging output(exported mailer) is shown below:\n';
			    $msg .= var_export($phpmailer,true)."\n";
				$this->append_log($msg);								
			}

			$this->last_send_smtp_debug = $smtp_debug;
			
			return $result;
		}

		function cron_stop(){
			$upload_dir = wp_upload_dir();
			$filename = $upload_dir['basedir'].'/sendpress.pause';
			if (file_exists($filename)) {
				return true;
			} 
 
			return false;
		}
		function cron_start(){
			$upload_dir = wp_upload_dir();
			$filename = $upload_dir['basedir'].'/sendpress.pause';
			if (file_exists($filename)) {
				unlink($filename);
			} 
 
		}


		function fetch_mail_from_queue(){
			global $wpdb;




			$emails = $this->wpdbQuery("SELECT * FROM ".$this->queue_table()." WHERE success = 0 AND max_attempts != attempts LIMIT 500","get_results");
			
			foreach($emails as $email_single ){
				error_log(' Send via cron ');
				if($this->cron_stop() == false ){
					error_log(' really sent ');
					$result = $this->sp_mail_it( $email_single );
					if ($result) {
						$table = $this->queue_table();
						$wpdb->query( 
							$wpdb->prepare( 
								"DELETE FROM $table WHERE id = %d",
							    $email_single->id  
						    )
						);
						$senddata = array(
							'sendat' => date('Y-m-d H:i:s'),
							'reportID' => $email_single->emailID,
							'subscriberID' => $email_single->subscriberID
						);

						$wpdb->insert( $this->subscriber_open_table(),  $senddata);
						
					} else {
						$wpdb->update( $this->queue_table() , array('attempts'=>$email_single->attempts+1,'last_attempt'=> date('Y-m-d H:i:s') ) , array('id'=> $email_single->id ));
					}
				} else {
					error_log('Get out');
					break;
				}	
			}
		}


		function send_single_from_queue(){
			
			global $wpdb;
			$limit =  wp_rand(1, 10);
			$emails = $this->wpdbQuery("SELECT * FROM ".$this->queue_table()." WHERE success = 0 AND max_attempts != attempts LIMIT ".$limit,"get_results");
			$count = 0;
			if( empty($emails) ){
				return 'empty';
			}

			foreach($emails as $email_single ){
				$result = $this->sp_mail_it( $email_single );
				if ($result) {
					$table = $this->queue_table();
					$wpdb->query( 
						$wpdb->prepare( 
							"DELETE FROM $table WHERE id = %d",
						    $email_single->id  
					    )
					);
					$senddata = array(
						'sendat' => date('Y-m-d H:i:s'),
						'reportID' => $email_single->emailID,
						'subscriberID' => $email_single->subscriberID
					);

					$wpdb->insert( $this->subscriber_open_table(),  $senddata);
					$count++;
				} else {
					$wpdb->update( $this->queue_table() , array('attempts'=>$email_single->attempts+1,'last_attempt'=> date('Y-m-d H:i:s') ) , array('id'=> $email_single->id ));
					
				}	
			}
				return $count;
		}


		function add_email_to_queue($values){
			global $wpdb;
			$table = $this->queue_table();
			$messageid = $this->unique_message_id();
			$q ="INSERT INTO $table (subscriberID, from_name,from_email,to_email, subject, messageID, date_published, emailID, listID) VALUES( '" .$values['subscriberID'] . "','" .$values['from_name'] . "', '" .$values['from_email'] .  "', '" .$values['to_email'] . "', '" .$values['subject'] . "', '" .$messageid . "', '". date('Y-m-d H:i:s') . "', '" .$values['emailID']. "', '" .$values['listID'] ."' )";
			$result = $this->wpdbQuery($q, 'query');
		}



		function sp_welcome_panel( ) {
			require_once (SENDPRESS_PATH. 'inc/helpers/sendpress-welcome-panel.php');
		}


		function set_default_email_style( $id ){
			if( false == get_post_meta( $id , 'body_bg', true) ) {

				$default_styles_id = $this->template_post( 'user-style' );

				if(false == get_post_meta( $default_styles_id , 'body_bg', true) ){
					$default_styles_id = $this->template_post('default-style');
				}

				$default_post = get_post( $default_styles_id );

				update_post_meta( $id , 'body_bg',  get_post_meta( $default_post->ID , 'body_bg', true) );
				update_post_meta( $id , 'body_text',  get_post_meta( $default_post->ID , 'body_text', true) );
				update_post_meta( $id , 'body_link',  get_post_meta( $default_post->ID , 'body_link', true) );
				
				update_post_meta( $id , 'header_bg',  get_post_meta( $default_post->ID , 'header_bg', true) );
				update_post_meta( $id , 'header_text_color',  get_post_meta( $default_post->ID , 'header_text_color', true) );
				//update_post_meta( $id , 'header_text',  get_post_meta( $default_post->ID , 'header_text', true) );

				update_post_meta( $id, 'content_bg',  get_post_meta( $default_post->ID , 'content_bg', true) );
				update_post_meta( $id , 'content_text',  get_post_meta( $default_post->ID , 'content_text', true) );
				update_post_meta( $id , 'sp_content_link_color',  get_post_meta( $default_post->ID , 'sp_content_link_color', true) );
				update_post_meta( $id , 'content_border',  get_post_meta( $default_post->ID , 'content_border', true) );
				update_post_meta( $id , 'upload_image',  get_post_meta( $default_post->ID , 'upload_image', true) );

			} 
		}


function simple_page_start(){
	?>
	<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Manage Subscription</title>
        <link rel='stylesheet' id='sendpress_bootstrap_css-css'  href='<?php echo SENDPRESS_URL; ?>bootstrap/css/bootstrap.css?ver=1.0.0' type='text/css' media='all' />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type='text/javascript' src='<?php echo SENDPRESS_URL; ?>bootstrap/js/bootstrap.min.js?ver=3.3.2'></script>
        <script>
        $(document).ready(function(){
        $(".alert").alert();
        });
        </script>
        <style type="text/css">
      
			a:link, a, a:visited{color:#666666; text-decoration: none !important;}
            a:hover { text-decoration: none !important; color:#666666;}
            h2, h3, h4, h5, h6{padding: 0px 0px 10px 0px;margin:0px;}
			input[type="text"] {
			    border: 5px solid white;
			    -webkit-box-shadow:
			      inset 0 0 8px  rgba(0,0,0,0.1),
			            0 0 16px rgba(0,0,0,0.1);
			    -moz-box-shadow:
			      inset 0 0 8px  rgba(0,0,0,0.1),
			            0 0 16px rgba(0,0,0,0.1);
			    box-shadow:
			      inset 0 0 8px  rgba(0,0,0,0.1),
			            0 0 16px rgba(0,0,0,0.1);
			    padding: 15px;
			    background: rgba(255,255,255,0.5);
			    margin: 0 0 10px 0;
			}
			.large{
				width: 100%;
			}
			input.submit{
				background: #efefef;
			}
			form th {
			color:#015C95;
			border-bottom: 1px solid #ADADAD;
			}
			form td {
			padding: 5px 0px;
			}
			td, th {
			display: table-cell;
			vertical-align: inherit;
			}
			form tr {
			font-size: 12px;
			text-align: left;
			}



        </style>
		<!-- Email Generated by WordPress using SendPress -->
    </head>
    <body marginheight="0" topmargin="0" marginwidth="0" leftmargin="0" style="background-color: #e5e5e5;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #e5e5e5; margin: 0; padding: 10px 100px 10px 100px; font-family:Georgia, Arial, Sans-serif; line-height:22px; color:#222222; font-size:14px;" >
        <tr>
            <td>
                 
                <table width="590" border="0" align="center" cellpadding="0" cellspacing="0" style="margin-top:30px; margin-bottom:10px; padding:15px; border:1px solid #cccccc; background-color:#ffffff;">
                    <tr>
                        <td valign="middle" style="border: 0; padding: 20px;">
	                        <table width="100%" border="0" cellspacing="0" cellpadding="0" >
							     <tr>
                                    <td style="border: 0; font-size:14px; line-height:22px; color:#111111; padding-bottom:10px;">                                        
						<?php
					}
					function simple_page_end(){
						?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
				
				
        	    </td>
    	    </tr>
	    </table>
    </body>
</html>
<?php
					}

		function register_open( $subscriberKey, $report ){
			global $wpdb;
			$stat = get_post_meta($report, '_open_count', true );
			$stat++;
			update_post_meta($report, '_open_count', $stat );
			$subscriber = $this->getSubscriberbyKey($subscriberKey);
			if( isset($subscriber[0]) ) {
				$wpdb->update( $this->subscriber_open_table() , array('openat'=>date('Y-m-d H:i:s') ) , array('reportID'=> $report,'subscriberID'=>$subscriber[0]->subscriberID ));
			}
		}


		function register_unsubscribe($subscriber_key, $report_id, $list_id){
			global $wpdb;
			$stat = get_post_meta($report_id, '_unsubscribe_count', true );
			$stat++;
			update_post_meta($report_id, '_unsubscribe_count', $stat );
			$subscriber = $this->getSubscriberbyKey($subscriber_key);
			if( isset($subscriber[0]) ) {
				$wpdb->update( $this->list_subcribers_table() , array('status'=> 3) , array('listID'=> $list_id,'subscriberID'=>$subscriber[0]->subscriberID ));
			}
		}






		function register_click($subscriberKey, $report, $url){
			global $wpdb;
			$stat = get_post_meta($report, '_click_count', true );
			$stat++;
			update_post_meta($report, '_click_count', $stat );

			$urlinDB = $this->getUrl($report, $url);
			$subscriber = $this->getSubscriberbyKey($subscriberKey);

			if(!isset($urlinDB[0])){
				$urlData = array(
				'url' => trim($url),
				'reportID' => $report,
				);
				$wpdb->insert( $this->report_url_table(),  $urlData);
				$urlID = $wpdb->insert_id;

			} else {
				$urlID  = $urlinDB[0]->urlID;
			}

			if(isset($subscriber[0]) && isset($urlID) ){
				$clickData = array(
				'urlID' => $urlID,
				'reportID' => $report,
				'subscriberID'=> $subscriber[0]->subscriberID,
				'clickedat'=>date('Y-m-d H:i:s')
				);
				$result = $wpdb->insert($this->subscriber_click_table() ,$clickData);
			}
			
		}

}// End SP CLASS

register_activation_hook( __FILE__, array( 'SendPress', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'SendPress', 'plugin_deactivation' ) );

add_action( 'init', array( 'SendPress', 'init' ) );
