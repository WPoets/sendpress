<?php
global $wp_version;

				

$classes = 'sp-welcome-panel';

$option = get_user_meta( get_current_user_id(), 'show_sp_welcome_panel', true );
// 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner
$hide = 0 == $option || ( 2 == $option && wp_get_current_user()->user_email != get_option( 'admin_email' ) );
//if ( $hide )
//	$classes .= ' hidden';

list( $display_version ) = explode( '-', $wp_version );
?>
<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
<?php wp_nonce_field( 'welcome-panel-nonce', 'welcomepanelnonce', false ); ?>
<?php wp_nonce_field( $this->_nonce_value ); ?>
<!--<a class="welcome-panel-close" href="<?php echo esc_url( admin_url( '/admin.php?page=sp&welcome=0' ) ); ?>"><?php _e('Dismiss'); ?></a>-->
<div class="sp-badge"><?php printf( __( 'Version %s' ), SENDPRESS_VERSION ); ?></div>

<div class="welcome-panel-content">
<h3><?php _e( 'Welcome to SendPress Beta! ' ); ?></h3>
<p class="about-description">Thanks for trying out SendPress. Their is a little bit of setup to do before you can start sending emails out. If you follow the steps below you will be up and running in no time.</p>
<!--
<p class="about-description"><?php _e( 'If you need help getting started, check out our documentation on <a href="http://docs.sendpress.com/">First Steps with SendPress</a>. If you&#8217;d rather dive right in, here are a few things most people do first set up SendPress. If you need help, use the Help tab in the upper right corner to get information on how to use your current screen and where to go for more assistance.' ); ?></p>
-->
<div class="welcome-panel-column-container">
<div class="welcome-panel-column">
	<h4><span class="icon16 icon-settings"></span> <?php _e( 'Basic Settings' ); ?></h4>
	<p><?php _e( 'Here are a few easy things you can do to get your feet wet. Make sure to click Save on each Settings screen.' ); ?></p>
	<ul>
	<li><?php echo sprintf(	__( '<a href="%s">Choose your sending method</a>' ), esc_url( admin_url('admin.php?page=sp-templates&view=account') ) ); ?></li>
	<li><?php echo sprintf( __( '<a href="%s">Set your CAN-SPAM information</a>' ), esc_url( admin_url('admin.php?page=sp-templates&view=information') ) ); ?></li>
	<li><?php echo sprintf( __( '<a href="%s">Style your default template</a>' ), esc_url( admin_url('admin.php?page=sp-templates') ) ); ?></li>
	</ul>
</div>
<div class="welcome-panel-column">
	<h4><span class="icon16 icon-page"></span> <?php _e( 'Add Real Content' ); ?></h4>
	<p><?php _e( 'Check out eac setion to see how it all works, then add some content and start sending emails!' ); ?></p>
	<ul>
	<li><?php echo sprintf( __( '<a href="%s">Create your subscription list</a>' ), esc_url( admin_url('admin.php?page=sp-subscribers') ) ); ?></li>
	<li><?php echo sprintf( __( '<a href="%s">Create your first email</a>' ), esc_url( admin_url('admin.php?page=sp-emails&view=create-email') ) ); ?></li>
	</ul>
</div>
<div class="welcome-panel-column welcome-panel-last">
	<h4><span class="icon16 icon-appearance"></span> <?php _e( 'Looking for more?' ); ?></h4>
	
	<ul>
	<li><?php echo sprintf(	__( '<a href="%s">Setup the SendPress widget</a>' ), esc_url( admin_url('widgets.php') ) ); ?></li>
	<li><?php echo sprintf( __( '<a href="%s">View your reports</a>' ), esc_url( admin_url('admin.php?page=sp-sends') ) ); ?></li>
	<li><?php echo sprintf( __( '<a href="%s">Provide us with some feedback</a>' ),  'http://sendpress.com' ); ?></li>
	</ul>
</div>
</div>
<!--
<p class="welcome-panel-dismiss"><?php printf( __( 'Already know what you&#8217;re doing? <a href="%s">Dismiss this message</a>.' ), esc_url( admin_url( '?welcome=0' ) ) ); ?></p>
-->
</div>
</div>
