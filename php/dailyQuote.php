<meta http-equiv="Content-type" content="text/html" charset="UTF-8">
<?php
require_once 'header.php';

$qdr = new QuotedRepository( $db );
$qr = new QuoteRepository( $db, $qdr );

$dq = $qr->getDailyQuote( );

ini_set( "SMTP", $smtp );
ini_set( "sendmail_from", $fromMail );

$to      = $quotesMail;

$subject = 'QDD';

// In case any of our lines are larger than 70 characters, we should use wordwrap()
$message = $dq->number( ).' - '.$dq->quote( );
$message = wordwrap($message, 70, "\r\n");

$headers = "From: {$fromMail}\r\n";
$headers.= "Reply-To: {$fromMail}\r\n";
$headers.= 'X-Mailer: PHP/' . phpversion() ."\r\n";
$headers.= 'Content-Type: text/html;charset=utf-8';

echo 'SMTP: <br/>'.ini_get( "SMTP" ).'<br/><hr/>';
echo 'TO: <br/>'.$to.'<br/><hr/>';
echo 'SUBJECT: <br/>'.$subject.'<br/><hr/>';
echo 'MESSAGE: <br/>'.$message.'<br/><hr/>';
echo 'HEADERS: <br/>'.str_replace("\r\n",'<br/>',$headers).'<br/><hr/>';
//echo mail($to, $subject, $message, $headers) ? "Success" : "Fail";

?>
