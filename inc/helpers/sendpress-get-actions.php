<?php
/**
*
*   SENDPRESS ACTIONS 
*   
*   see @sendpress class line 101
*   Handles saving data and other user actions.
*
**/

switch ( $this->_current_action ) {
            case 'delete-list':
                $this->deleteList($_GET['listID']);
                wp_redirect( '?page='.$_GET['page'] );
            break;
            case 'delete-report':
                SendPress_Posts::delete($_GET['reportID']);
                wp_redirect( '?page='.$_GET['page'] );
            break;
            case 'delete-reports-bulk':
                $email_delete = $_GET['report'];

                foreach ($email_delete as $emailID) {
                    SendPress_Posts::delete($emailID);
                }
                wp_redirect( '?page='.$_GET['page'] );
            break;
            case 'delete-email':
                SendPress_Posts::delete($_GET['emailID']);
                wp_redirect( '?page='.$_GET['page'] );
            break;
            case 'delete-emails-bulk':
                $email_delete = $_GET['email'];

                foreach ($email_delete as $emailID) {
                    SendPress_Posts::delete($emailID);
                }
                wp_redirect( '?page='.$_GET['page'] );
            break;
          
            case 'export-list':
               
                
                $items = $this->exportList($_GET['listID']);
                    
                header("Content-type:text/octect-stream");
                header("Content-Disposition:attachment;filename=sendpress.csv");
                print "email,firstname,lastname,status \n";
                foreach($items as $user) {
                    print  $user->email . ",". $user->firstname.",". $user->lastname.",". $user->status."\n" ;
                }
                exit;

            
            break;
       

        
        }
