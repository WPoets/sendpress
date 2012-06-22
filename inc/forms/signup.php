<?php

class SendPress_Signup_Shortcode {
	
	function &init() {
			static $instance = false;

			if ( !$instance ) {
				$instance = new SendPress_Signup_Shortcode;
			}

			return $instance;
	}

	function SendPress_Signup_Shortcode() {
		add_shortcode('sendpress-signup', array($this, 'signup' ) );
		// add_action('wp_footer', array($this , 'add_script'));
		// add_filter('espresso_shortcode_box',array($this,'support_box_create'));
		// add_image_size('es-cycle', 460, 9999, false );
	}
	
	function support_box_create($boxes){
		$box = array(
			'id'=>'es-cycle',
			'title'=> 'Content Cycle Slider',
			'code'=>'[es-cycle]',
			'content'=> array($this,'box_content')
		);
		
		$boxes[] = $box;
		
		return $boxes;
	}
	
	function box_content(){
		?>
			<p>This slider is a full page slider so it is 940 pixels wide. You will need to set the content layout of the page or post detail to be <i>'One-column, no Sidebars'</i> for the slider to fit.</p>
			<h5>Creating a Slider</h5>
			<p>	
				The first thing you will want to do is make some slides in using the <a href="<?php echo admin_url(); ?>edit.php?post_type=slider_content">Slider Content</a> post type. See the <a href="#slider_content">creating slides help section</a> above for more about this.
			</p>
			<p>The Cycle Slider supports several options.
				<ul>
					<li>slider: What set of slide's to load. [es-cycle slider="Homepage"]
					<p>default: Cycle Slider Default
					</li>
					<li>time: The time between each slide. Set to 0 to stop auto rotation. [es-cycle time="5"]
					<p>default: 7</p>
					</li>
					</li>
					<li>max: Maximum number of slides to show. [es-cycle max="3"]
					<p>default: 5</p>
					</li>
				</ul>
			</p>
			<h5>Basic Layout</h5>
			<p><img src="<?php echo PARENT_URL; ?>/hopper/images/cycle-slider.png" /></p>
			<h5>Example Code: Loads the Cycle Slider Default slides</h5>
			<p><b>[es-cycle]</b></p>
			<p>For more info check out: <a href="#">Using the Cylce Slider Shortcode</a>.
		
		
		<?php
	}

	/**
	* The sendpress-signup shortcode.
	 *
	 * @since 1.0
	 *
	 * @param array $attr Attributes attributed to the shortcode.
	 * @return string HTML content to display SendPress signup form.
	 */
	function cycle_shortcode($attr) {

		extract(shortcode_atts(array(
			// 'slider'         => 'Content Cycle Slider Default',
			// 'max' => 5,
			// 'time' => 7,
		), $attr));

		if ( is_feed() ) {
			$output = "\n";
			return $output;
		}
		
		
		
		return $output;
	}
	
	function add_script() {
		if ( ! self::$add_script )
			return;
			wp_enqueue_script('espresso_cycle', PARENT_URL . '/hopper/js/jquery.cycle.min.js', array('jquery'), '1.0.0', true);
			wp_print_scripts('espresso_cycle');
			$sliders = self::$cycle_sliders;
			$output = "<script>";
			$output .=	"jQuery.noConflict();";
			$output .=	"jQuery(document).ready(function() {";
			foreach($sliders as $slider){
				$selector = $slider['selector'];
				$timeout = $slider['timeout'] * 1000;
				$output .="    jQuery('#$selector .cycle-slides').cycle({ 
					    fx:     'fade', 
				    	speed:   500, 
				    	timeout: $timeout,  
				    	pause:   1,   
					   	pager: '#$selector .cycle-slider-pager',
						pagerAnchorBuilder: function(idx, slide) {
						        return '<li><a><span>'+(idx+1)+'</span></a></li>';
						    },
						onPagerEvent: function(slideIndex,slideElement){
							jQuery('#$selector .cycle-slides').cycle('pause');
						}		
					});";
			}
			$output .=	"}); </script>";
			echo 	$output;
	}
	
}

add_action( 'init', array( 'SendPress_Signup_Shortcode', 'init' ) );
