<?php
  require_once('auth.php');
	if( !isset($_REQUEST['quote']) or empty($_REQUEST['quote']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'No se ingreso quote';
		$result['errorDetail'] = 'Parametro: `quote` ausente';
		die(json_encode($result));
	} else if ( !isset($_REQUEST['quoted']) or empty($_REQUEST['quoted']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Debe elegirse al menos un quoteado';
    $result['errorDetail'] = 'Parametro: `quoted` (QuotedId) ausente';
		die(json_encode($result));
	} else if ( !isset($_REQUEST['n']) or empty($_REQUEST['n']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Parametro: n (Numero de Quote) ausente';
		die(json_encode($result));
	}

  $quote = utf8_decode( $_REQUEST['quote'] );
	$unknown = array( );
	$known = array( );

	foreach( $_REQUEST['quoted'] as $id ) {
		if( (int) $id <= 0 ) {
			array_push( $unknown, $id );
		} else {
			array_push( $known, $id );
		}
	}

	if( !empty($unknown) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'QuotedId invalidos';
		$result['invalid'] = $unknown;
	}
	if( empty( $known ) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Debe elegirse al menos un quoteado valido';
		die(json_encode($result));
	}

	require_once('header.php');

	$qdr = new QuotedRepository( $db );
	$qr = new QuoteRepository( $db, $qdr );

  $number = (int)$_REQUEST['n'];
  try {
    $oldQuote = $qr->getQuoteByNumber( $number );
  } catch (OutOfRangeException $e) {
		$result['error'] = TRUE;
		$result['errorDescription'] = "Quote numero {$number} inexistente";
		die(json_encode($result));
	}
  $oldQuoted = array( );
  foreach( $oldQuote->quoted( ) as $q ) {
    array_push( $oldQuoted, (int) $q->idQuoted( ) );
  }
  sort( $oldQuoted );

	$quoted = array( );
	$quotedIds = array( );

	foreach( array_keys($known) as $key) {
		$id = $known[$key];
		try {
			$q = $qdr->getQuotedWithId( $id );
			array_push( $quoted, $q );
			array_push( $quotedIds, $id );
		} catch (OutOfRangeException $e) {
			array_push( $unknown, $id );
			unset( $known[$key] );
		}
	}
  sort( $quotedIds );
  if( $quotedIds !== $oldQuoted ) {
    $qr->removeRelationships( $oldQuote );
    $qr->editQuote( $number, $quote, $quoted );
  } else {
    $qr->editQuote( $number, $quote, array( ) );
  }
	$result['success'] = TRUE;
	echo json_encode($result);
?>

