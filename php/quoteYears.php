<?php
  require_once('header.php');
  $qdr = new QuotedRepository( $db );
  $qr = new QuoteRepository( $db, $qdr );
  echo json_encode( $qr->getQuotesYears( ) );
?>