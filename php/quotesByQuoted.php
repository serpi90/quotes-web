<?php
	if( !isset( $_REQUEST['q'] ) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Parametro: q (QuotedId) ausente';
		die( json_encode( $result ) );
	}
	$q = (int)$_REQUEST['q'];

	require_once('header.php');

	$qdr = new QuotedRepository( $db );
	$qr = new QuoteRepository( $db, $qdr );

	try{
		$quoted = $qdr->getQuotedWithId($q);
		$qr->getQuotesFor( $quoted );
	} catch ( OutOfRangeException $e ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'QuotedId invalido';
		$result['invalid'] = $q;
		die( json_encode( $result ) );
	}

	$result = array( );
	foreach( $quoted->quotes( ) as $quote ) {
		array_push( $result, array( "year" => $quote->year( ), "number" => $quote->number( ), "quote" => $quote->quote( ) ) );
	}
	echo json_encode( $result );
?>

