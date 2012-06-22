<?php
/* PHP email queue processing script */
require_once "Mail.php";

// defile variables
$db_url = "localhost";
$db_name = "DB_NAME";
$db_user = "DB_USER";
$db_pass = "DB_PASS";

$host = "MAIL_SERVER";
$username = "MAIL_USER";
$password = "MAIL_PASS";

$max_emails_per_batch = 50;

// connect to database
$link = mysql_connect($db_url, $db_user, $db_pass);
if (!$link) {
die('Could not receive connection: ' . mysql_error());
}
if (!mysql_select_db($db_name, $link)) {
die('Could not connect to db: ' . mysql_error());
}

// query email_queue for records where success = 0
$sql    = "SELECT * FROM email_queue WHERE success = 0 AND max_attempts != attempts LIMIT " . $max_emails_per_batch;
$result = mysql_query($sql, $link);

if (!$result) {
echo "DB Error, could not query the databasen";
echo 'MySQL Error: ' . mysql_error();
exit;
}

// check if records found
if (mysql_num_rows( $result )) {

// prepare mailer
$smtp = Mail::factory('smtp',
array ('host' => $host,
‘auth’ => true,
‘username’ => $username,
‘password’ => $password));

// loop through records to send emails
while ($queued_mail = mysql_fetch_array($result)) {
// send email

$to =              $queued_mail[’to_email’];
$subject =   $queued_mail[’subject’];
$body =      $queued_mail[’message’];
$from =      $queued_mail[’from_name’] . ‘ < ' . $queued_mail['from_email'] . '>‘;

$headers = array (’From’ => $from,
‘To’ => $to,
‘Subject’ => $subject);

$mail = $smtp->send($to, $headers, $body);

if (PEAR::isError($mail)) {
// else update attempts, last attempt
$sql = “UPDATE email_queue SET ” .
“attempts = attempts+1, ” .
“last_attempt = now() ” .
“WHERE id = ‘” . $queued_mail[’id’] . “‘”;
mysql_query($sql, $link);

echo( $mail->getMessage() );
} else {
// if successful, update attempts, success, last attempt, date_sent
$sql = “UPDATE email_queue SET ” .
“attempts = attempts+1, ” .
“success = ‘1′, ” .
“last_attempt = now(), ” .
“date_sent = now() ” .
“WHERE id = ‘” . $queued_mail[’id’] . “‘”;
mysql_query($sql, $link);

echo(”Message successfully sent!”);
}
} // end while (loop through records and sending emails)
} // no rows so quit

// release resources
mysql_free_result($result);
mysql_close($link);
?>
