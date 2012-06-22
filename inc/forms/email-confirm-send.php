<?php
$info = $this->get_option('current_send_'.$post->ID );
$subject = $this->get_option('current_send_subject_'.$post->ID ,true);
?>
<div id="styler-menu">
    <div style="float:right;" class="btn-group">
<a class="btn btn-primary btn-large " id="confirm-send" href="#"><i class="icon-white  icon-thumbs-up"></i> Confirm Send</a>
  </div>
</div>
<div id="sp-cancel-btn" style="float:right; margin-top: 5px;">
<a class="btn" href="<?php echo '?page='.$_GET['page']. '&view=send-email&emailID='. $_GET['emailID']; ?>">Cancel Send</a>&nbsp;
</div>
<h2>Confirm Send</h2>

<input type="hidden" value="save-send-confirm" name="action" />
<input type="hidden" id="user-id" name="user_ID" value="<?php //echo $current_user->ID; ?>" />
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo $post->ID; ?>" />
<div class="boxer">
<div class="boxer-inner">
<h2><strong>Subject</strong>: <?php echo stripslashes(esc_attr( htmlspecialchars( $subject ) )); ?></h2><br>
<div class="leftcol">
    
    <div class="style-unit">
<h4>Lists</h4>

<?php

if( !empty($info['listIDS']) ){
    foreach($info['listIDS'] as $list_id){
        $list = $this->get_list_details( $list_id );
        echo $list[0]->name.'<br>';      

    } 
} else {
    echo "No Lists Selected.<br>";
}


?>
</div>
<div class="style-unit">
<h4>Test Emails</h4>
<?php


if( !empty($info['testemails']) ){
    foreach($info['testemails'] as $test){
       echo $test['email'] .'<br>';   

    } 
} else {
    echo "No Test Emails added.<br>";
}


?>
</div>
</div>
<div class="widerightcol">
<iframe src="<?php echo get_permalink( $post->ID ); ?>?inline=true" width="100%" height="600px"></iframe>
</div>

 <?php wp_nonce_field($this->_nonce_value); ?><br><br>


 <?php

 /*
  $saveid = $post->ID;

                $post = get_post( $saveid );
                $info = $this->get_option('current_send');
                $this->log('ADD QUEUE');
                foreach($info['listIDS'] as $list_id){
                    $_email = $this->getSubscribers( $list_id );
                    echo '<pre>';
                   // print_r($_email);
                    echo '</pre>';
                    foreach($_email as $email){
                    	echo '<pre>';
                      // print_r($email);
                         $go = array(
                            'from_name' => 'Josh',
                            'from_email' => 'joshlyford@gmail.com',
                            'to_email' => $email->email,
                            'to_name' => $email->firstname .' '. $email->lastname,
                            'subject' => $post->post_title,
                           'emailID'=>$saveid
                            );
                       
                        $this->add_email_to_queue($go);
                        echo '</pre>';
                       

                    }


                }

              $this->log('END ADD QUEUE');
*/
           
 ?>
</div>
</div>
