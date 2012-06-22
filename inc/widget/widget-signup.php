<?php
/**
 * SendPress Signup Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 1.0
 */
class SendPress_Signup_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function SendPress_Signup_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'sendpress', 'description' => __('Displays a signup form so your users can sign up for your public e-mail lists.', 'sendpress') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'sendpress-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'sendpress-widget', __('SendPress Signup', 'sendpress'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		/* Display name from widget settings if one was input. */

		$args = "";
		$args.= 'display_firstname="'.$instance['show_first'].'" ';
		$args.= 'display_lastname="'.$instance['show_last'].'" ';
		$args.= 'firstname_label="'.$instance['first_label'].'" ';
		$args.= 'lastname_label="'.$instance['last_label'].'" ';
		$args.= 'email_label="'.$instance['email_label'].'" ';
		$args.= 'button_text="'.$instance['button_text'].'" ';
		$args.= 'thank_you="'.$instance['thank_you'].'" ';
		$args.= 'label_display="'.$instance['label_display'].'" ';
		$args.= 'desc="'.$instance['desc'].'" ';

		$s = new SendPress;
	    $lists = $s->getData($s->lists_table());
	    $listids = array();

		foreach($lists as $list){
			if( $list->public == 1 ){

				if(isset( $instance['list_'.$list->listID] ) && filter_var($instance['list_'.$list->listID], FILTER_VALIDATE_BOOLEAN) ){
					$listids[] = $list->listID;
				}
			}
		}

		$args.= 'listids="'.implode(',',$listids).'" ';
		
		//do_shortcode goes here
		echo do_shortcode('[sendpress-signup '.$args.']');

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['desc'] = strip_tags( $new_instance['desc'] );

		( strlen($new_instance['first_label']) !== 0 ) ? $instance['first_label'] = strip_tags( $new_instance['first_label'] ) : $instance['first_label'] = 'First Name';
		( strlen($new_instance['last_label']) !== 0 ) ? $instance['last_label'] = strip_tags( $new_instance['last_label'] ) : $instance['last_label'] = 'Last Name';
		( strlen($new_instance['email_label']) !== 0 ) ? $instance['email_label'] = strip_tags( $new_instance['email_label'] ) : $instance['email_label'] = 'E-Mail';
		( strlen($new_instance['button_text']) !== 0 ) ? $instance['button_text'] = strip_tags( $new_instance['button_text'] ) : $instance['button_text'] = 'Submit';
		( strlen($new_instance['thank_you']) !== 0 ) ? $instance['thank_you'] = strip_tags( $new_instance['thank_you'] ) : $instance['thank_you'] = 'Thank you for subscribing!';

		$instance['show_first'] = $new_instance['show_first'];
		$instance['show_last'] = $new_instance['show_last'];
		$instance['label_display'] = $new_instance['label_display'];

		$s = new SendPress;
	    $lists = $s->getData($s->lists_table());

		foreach($lists as $list){
			if( $list->public == 1 ){
				$instance['list_'.$list->listID] = $new_instance['list_'.$list->listID];
			}
		}

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 
			'title' => '', 
			'show_first' => false,
			'show_last' => false,
			'label_display' => 0,
			'first_label' => __('First Name', 'sendpress'), 
			'last_label' => __('Last Name', 'sendpress'), 
			'email_label' => __('E-Mail', 'sendpress'), 
			'desc' => __('', 'sendpress'), 
			'button_text' => __('Submit', 'sendpress'),
			'thank_you' => __('Thank you for subscribing!', 'sendpress')
		);

		$s = new SendPress;
	    $lists = $s->getData($s->lists_table());

		foreach($lists as $list){
			if( $list->public == 1 ){
				$defaults['list_'.$list->listID] = false;
			}
		}

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'sendpress'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'desc' ); ?>"><?php _e('Description:', 'sendpress'); ?></label>
			<textarea rows="5" type="text" class="widefat" id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>"><?php echo $instance['desc']; ?></textarea>
			<!-- <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" value="<?php echo $instance['desc']; ?>" style="width:100%;" /> -->
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_first'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_first' ); ?>" name="<?php echo $this->get_field_name( 'show_first' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'show_first' ); ?>"><?php _e('Collect First Name', 'sendpress'); ?></label>
		</p> 

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_last'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_last' ); ?>" name="<?php echo $this->get_field_name( 'show_last' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'show_last' ); ?>"><?php _e('Collect Last Name', 'sendpress'); ?></label>
		</p> 

		<p>
			<label for="<?php echo $this->get_field_id( 'label_display' ); ?>">Display labels inside?:</label>
			<input type="radio" name="<?php echo $this->get_field_name( 'label_display' ); ?>" value="1"<?php echo $instance['label_display'] == 1 ? ' checked' : ''; ?> /> Yes
			<input type="radio" name="<?php echo $this->get_field_name( 'label_display' ); ?>" value="0"<?php echo $instance['label_display'] == 0 ? ' checked' : ''; ?> /> No
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'first_label' ); ?>"><?php _e('First Name Label:', 'sendpress'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'first_label' ); ?>" name="<?php echo $this->get_field_name( 'first_label' ); ?>" value="<?php echo $instance['first_label']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'last_label' ); ?>"><?php _e('Last Name Label:', 'sendpress'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'last_label' ); ?>" name="<?php echo $this->get_field_name( 'last_label' ); ?>" value="<?php echo $instance['last_label']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'email_label' ); ?>"><?php _e('E-Mail Label:', 'sendpress'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'email_label' ); ?>" name="<?php echo $this->get_field_name( 'email_label' ); ?>" value="<?php echo $instance['email_label']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e('Button Text:', 'sendpress'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" value="<?php echo $instance['button_text']; ?>" style="width:100%;" />
		</p>

		<p>Check off the lists you would like users to subscribe to.</p>
		<?php 
		$foundLists = false;
		foreach($lists as $list){
			if( $list->public == 1 ){
				?>
				<p>
					<input class="checkbox" type="checkbox" <?php checked( $instance['list_'.$list->listID], 'on' ); ?> id="<?php echo $this->get_field_id( 'list_'.$list->listID ); ?>" name="<?php echo $this->get_field_name( 'list_'.$list->listID ); ?>" /> 
					<label for="<?php echo $this->get_field_id( 'list_'.$list->listID ); ?>"><?php echo $list->name; ?></label>
				</p> 
				<?php
				$foundLists = true;
			}
		}
		if(!$foundLists){
			echo '<p>No public lists available</p>';
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'thank_you' ); ?>"><?php _e('Thank you message:', 'sendpress'); ?></label>
			<textarea rows="5" type="text" class="widefat" id="<?php echo $this->get_field_id( 'thank_you' ); ?>" name="<?php echo $this->get_field_name( 'thank_you' ); ?>"><?php echo $instance['thank_you']; ?></textarea>
		</p>
		<?php
		
	}
}

/**
 * Add function to widgets_init that'll load our widget.
 * @since 1.0
 */
add_action( 'widgets_init', 'sendpress_load_widgets' );

/**
 * Register our widget.
 * 'SendPress_Signup_Widget' is the widget class used below.
 *
 * @since 1.0
 */
function sendpress_load_widgets() {
	register_widget( 'SendPress_Signup_Widget' );
}
