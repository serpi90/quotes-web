<?php
  require_once('auth.php');
	if( !isset($_REQUEST['name']) or empty($_REQUEST['name']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'No se ingreso nombre';
		$result['errorDetail'] = 'Parametro: `name` ausente';
		die(json_encode($result));
	}

  $name = utf8_decode( $_REQUEST['name'] );
  $aliases = array( );
  if( isset($_REQUEST['aliases']) and  is_array($_REQUEST['aliases']) ) {
    foreach( $_REQUEST['aliases'] as $alias ) {
      array_push( $aliases, trim( utf8_decode( $alias ) ) );
    }
  }

	require_once('header.php');
	$qdr = new QuotedRepository( $db );
  
  try {
    $qdr->addQuoted( $name, $aliases );
  } catch (DomainException $e) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Nombres duplicados: '.$e->getMessage( );
		die(json_encode($result));
	}

	$result['error'] = FALSE;
	echo json_encode($result);
?>

