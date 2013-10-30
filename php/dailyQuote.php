<?php
require_once 'header.php';

$qdr = new QuotedRepository( $db );
$qr = new QuoteRepository( $db, $qdr );

$dq = $qr->getDailyQuote( );

sendMail( $dq->number( ).' - '.$dq->quote( ), $dailyQuoteSubject );
?>

