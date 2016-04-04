<?php
require_once('config.php');

function __autoload( $class ) {
	require_once( "classes/class.{$class}.php" );
}
spl_autoload_register('__autoload');

function sendMail( $message, $subject ) {
	require('config.php');

	ini_set( "SMTP", $config['mail']['smtp'] );
	ini_set( "sendmail_from", $config['mail']['from'] );

	// In case any of our lines are larger than 70 characters, we should use wordwrap()
	$message = wordwrap($message, 70, "\r\n");

	$headers = "From: {$config['mail']['from']}\r\n";
	$headers.= "Reply-To: {$config['mail']['from']}\r\n";
	$headers.= 'X-Mailer: PHP/' . phpversion() ."\r\n";
	$headers.= 'Content-Type: text/html;charset=utf-8';

	$result = true;
	foreach( $config->mail->to as $to ) {
		$result = mail($to, $subject, $message, $headers) and $result;
	}
}

$db = new Database(
	$config['db']['server'],
	$config['db']['user'],
	$config['db']['pass'],
	$config['db']['name']
	);
?>

