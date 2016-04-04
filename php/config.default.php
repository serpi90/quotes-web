<?php
$config = array(
	'db' => array(
		'server' => 'localhost',
		'user' => 'quotes',
		'pass' => 'quotes',
		'name' => 'quotes',
	),
	// sendmail is required for mail to function.
	'mail' => array(
		'enabled' => true,
		'to' => array('mail-to-send-quotes-to@my-mail.com'),
		'from' => 'sender-address@my-mail.com',
		'smtp' => '192.168.1.1',
		'subject' => array(
			'newQuote' => 'Quote Fresca!!!!',
			'dailyQuote' => 'La Quote del dia'
		)
	),
	'telegram' => array(
		'enabled' => true,
		'token' => '',
		'chatIds' => array( )
	)
)
?>

