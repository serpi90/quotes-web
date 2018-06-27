<?php
  require('auth.php');
  if( !isset($_REQUEST['n']) or empty($_REQUEST['n']) ) {
    $result['error'] = TRUE;
    $result['errorDescription'] = 'Parametro: n (Numero de Quote) ausente';
    die(json_encode($result));
  }
  require_once('header.php');

  $qdr = new QuotedRepository( $db );
  $qr = new QuoteRepository( $db, $qdr );
  try {
    $n = (int) $_REQUEST['n'];
    $qr->delete( $n );
  } catch ( OutOfRangeException $e ) {
    $result['error'] = TRUE;
    $result['errorCode'] = 2;
    $result['errorDescription'] = 'Quote inexistente';
    die(json_encode($result));
  }
  $result = array( 'deleted' => $n, 'error' => FALSE );
  echo json_encode( $result );
?>