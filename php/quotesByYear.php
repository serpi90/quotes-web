<?php
	if( !isset( $_REQUEST['y'] ) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Parametro: y (Año) ausente';
		die( json_encode( $result ) );
	}

	$year = (int)$_REQUEST['y'];

	require_once('header.php');
	$qdr = new QuotedRepository( $db );
	$qr = new QuoteRepository( $db, $qdr );

	$json = array( );
	foreach( $qr->getQuotesForYear( $year ) as $quote ) {
		array_push( $json, array( "number" => $quote->number( ), "quote" => $quote->quote( ) ) );
	}
	echo json_encode( $json );
?>
