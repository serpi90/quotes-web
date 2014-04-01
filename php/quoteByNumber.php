<?php
	if( !isset( $_REQUEST['n'] ) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Parametro: `n` (Numero de Quote) ausente';
		die( json_encode( $result ) );
	}

	$number = (int)$_REQUEST['n'];

	require_once('header.php');
	$qdr = new QuotedRepository( $db );
	$qr = new QuoteRepository( $db, $qdr );

  try {
    $quote = $qr->getQuoteByNumber( $number );
  } catch ( OutOfRangeException $e ) {
    $result['error'] = TRUE;
		$result['errorDescription'] = 'Quote inexistente';
		die( json_encode( $result ) );
  }
  $quotedIds = array( );
  foreach( $quote->quoted() as $quoted ) {
    array_push( $quotedIds, $quoted->idQuoted( ) );
  }
	$json = array(
    "number" => $quote->number( ),
    "quote" => $quote->quote( ),
    "quoted" => $quotedIds
  );
	echo json_encode( $json );
?>

