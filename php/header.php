<?php
require_once('config.php');
/* Changing this would break js so it's reverted, for now */
// header('Content-Type: application/json');
function __autoload( $class ) {
  require_once( "classes/class.{$class}.php" );
}
spl_autoload_register('__autoload');

function sendMail( $message, $subject ) {

  require('config.php');

  ini_set( "SMTP", $smtp );
  ini_set( "sendmail_from", $fromMail );

  $to      = $quotesMail;

  // In case any of our lines are larger than 70 characters, we should use wordwrap()
  $message = wordwrap($message, 70, "\r\n");

  $headers = "From: {$fromMail}\r\n";
  $headers.= "Reply-To: {$fromMail}\r\n";
  $headers.= 'X-Mailer: PHP/' . phpversion() ."\r\n";
  $headers.= 'Content-Type: text/html;charset=utf-8';

  return mail($to, $subject, $message, $headers);
}

$db = new Database( $db_server, $db_user, $db_pass, $db_name );
?>