


<?php $tpls = $this->get_templates();

$this->settings_menu();
$view = isset($_GET['view']) ? $_GET['view'] : '' ;


switch($view){
case 'feedback': ?>
<form method="post" id="post">

<br class="clear">
<div style="float:right;" >
	<a href="?page=sp-templates&view=feedback" class="btn btn-large" ><i class="icon-remove"></i> Cancel</a> <a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-white icon-ok"></i> Save</a>
</div>
<input type="hidden" name="action" value="feedback-setup" />
<br class="clear">
<div class="boxer form-box">
	<h2>Hi,
	<p>We are using presstrends.io to track items like adoption from month-to-month & other trends. We only collect anonymous data and nothing is sent unless you activate the option below.</p>
	
	Thanks for helping,<br>
	<b>The SendPress Team</b>
	</h2>
	<br><br>
	<h2>Feeback Opt-in</h2>
	<p><input name="feedback" type="radio"  <?php if($this->get_option('feedback') == 'yes' ) { ?>checked="checked"<?php } ?>   id="feedback" value="yes" > I would like to help out.</p>
	<p><input name="feedback" type="radio"  <?php if($this->get_option('feedback') == 'no' ) { ?>checked="checked"<?php } ?>   id="feedback" value="no" > No Thanks!</p>
	<br><br>
	<h2>Support</h2>
	If you are looking for support or would like to provide written feedback please go to <a href="http://sendpress.zendesk.com"> our support site</a> and submit a ticket.

</div>

<?php wp_nonce_field($this->_nonce_value); ?>
</form>
<?php
break;

case 'account': ?>
<form method="post" id="post">

<br class="clear">
<div style="float:right;" >
	<a href="?page=sp-templates&view=account" class="btn btn-large" ><i class="icon-remove"></i> Cancel</a> <a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-white icon-ok"></i> Save</a>
</div>
<input type="hidden" name="action" value="account-setup" />
<br class="clear">
<div class="boxer form-box">
<div class="sendpress-panel-column-container">
<div class="sendpress-panel-column">
	<h4>
		<input name="sendmethod" type="radio"  <?php if($this->get_option('sendmethod') == 'website' ) { ?>checked="checked"<?php } ?>   id="website" value="website" >
		<?php _e( 'Your Website' ); ?>
	</h4>
	<p>Although easy to setup your host may set limits on the number of emails per day.</p>
</div>
<div class="sendpress-panel-column">
	
	<h4>
		<input name="sendmethod" type="radio" id="gmail" <?php if($this->get_option('sendmethod') == 'gmail' ) { ?>checked="checked"<?php } ?> value="gmail" >
		<?php _e( 'Gmail Account' ); ?>
	</h4>
	<p>Gmail is limited to 500 emails a day. We recommend that you open a dedicated Gmail account for this purpose.</p>
	Username
	<p><input name="gmailuser" type="text" value="<?php echo $this->get_option('gmailuser'); ?>" style="width:100%;" /></p>
	Password
	<p><input name="gmailpass" type="password" value="<?php echo $this->get_option('gmailpass'); ?>" style="width:100%;" /></p>
</div>
<div class="sendpress-panel-column sendpress-panel-last">
	<h4>
		<input name="sendmethod" type="radio" id="sp" <?php if($this->get_option('sendmethod') == 'sendpress' ) { ?>checked="checked"<?php } ?>  value="sendpress" >
		<?php _e( 'SendPress Account' ); ?>
	</h4>
	<p>With a SendPress account your emails get delivered via our enterprise delivery system</p>
	<p>All Premium features are unlocked when using a <a href="http://sendpress.com">SendPress account</a> even if it is the free one.</p>
	Username
	<p><input name="sp_user" type="text" value="<?php echo $this->get_option('sp_user'); ?>" style="width:100%;" /></p>
	Password
	<p><input name="sp_pass" type="password" value="<?php echo $this->get_option('sp_pass'); ?>" style="width:100%;" /></p>

</div>
<div>

</div>	
</div>


</div>
<?php wp_nonce_field($this->_nonce_value); ?>
</form>
<?php
break;
case 'information': 
$default_styles_id = $this->template_post('user-style');
$post =  get_post( $default_styles_id );
?>
<form method="post" id="post">
	<input type="hidden" name="post_ID" id="post_ID" value="<?php echo $post->ID; ?>" />
<br class="clear">
<div style="float:right;" >
	<a href="?page=sp-templates&view=information" class="btn btn-large" ><i class="icon-remove"></i> Cancel</a> <a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-white icon-ok"></i> Save</a>
</div>
<input type="hidden" name="action" value="template-default-setup" />
<br class="clear">
<div class="boxer form-box">
<div style="float: right; width: 45%;">
	<p><label>Twitter:</label>
	<input name="twitter" type="text" id="twitter" value="<?php echo $this->get_option('twitter'); ?>" class="regular-text"></p>
<p><label>Facebook:</label>
<input name="facebook" type="text" id="facebook" value="<?php echo $this->get_option('facebook'); ?>" class="regular-text"></p>
<p><label>LinkedIn:</label>
<input name="linkedin" type="text" id="linkedin" value="<?php echo $this->get_option('linkedin'); ?>" class="regular-text"></p>
</div>	
<div style="width: 45%; margin-right: 10%">
<p><label>From Name:</label>
	<input name="fromname" type="text" id="fromname" value="<?php echo $this->get_option('fromname'); ?>" class="regular-text"></p>
<p><label>From Email:</label>
<input name="fromemail" type="text" id="fromemail" value="<?php echo $this->get_option('fromemail'); ?>" class="regular-text"></p>
<p><label>CAN-SPAM:</label>
<textarea cols="20" rows="10" class="large-text code" name="can-spam"><?php echo $this->get_option('canspam'); ?></textarea>
<p>All users (and email marketers for that matter) are required under US law to display a physical business address
 (no PO Box either) inside their outgoing emails.</p> This is dictated under the <a href="http://business.ftc.gov/documents/bus61-can-spam-act-compliance-guide-business" target="_blank">Federal CAN-SPAM Act of 2003</a>.
					</p>
</div></div>
<?php wp_nonce_field($this->_nonce_value); ?>
</form>
<?php
break;
default:
?>
<form method="post" id="post">
	<br class="clear">
<div style="float:right;" >
	<a href="?page=sp-templates" class="btn btn-large" ><i class="icon-remove"></i> Cancel</a> <a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-white icon-ok"></i> Save</a>
</div>
<br class="clear">
<?php require_once(SENDPRESS_PATH . 'inc/forms/email-style.2.0.php'); ?>
<input type="hidden" name="action" value="template-default-style" />
<?php wp_nonce_field($this->_nonce_value); ?>
<!--
<h3>Default Layout</h3>
<div class="boxer">
	<?php foreach($tpls as $tpl ){
			echo $tpl['name'];
		}
		?>
</div>
-->
</form>
<?php } 
