<?php
require_once 'header.php';

$qdr = new QuotedRepository( $db );
$qr = new QuoteRepository( $db, $qdr );

$dq = $qr->getDailyQuote( );

sendMail( '&nbsp;&nbsp;&nbsp;&nbsp;<i>'.$dq->number( ).') '.$dq->quote( ).'</i>' , $dailyQuoteSubject );
?>