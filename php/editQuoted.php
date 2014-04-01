<?php
  //require_once('auth.php');
	if( !isset($_REQUEST['id']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Id de quoteado ausente';
		$result['errorDetail'] = 'Parametro: `id` ausente';
		die(json_encode($result));
	} else if ( !isset($_REQUEST['name']) or empty($_REQUEST['name']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'No se ingreso nombre';
		$result['errorDetail'] = 'Parametro: `name` ausente';
		die(json_encode($result));
	} else if( !isset($_REQUEST['active']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Indicador de actividad ausente';
		$result['errorDetail'] = 'Parametro: `active` ausente';
		die(json_encode($result));
	}

  $active = strtolower($_REQUEST['active']) === 'true';
  $id = (int) $_REQUEST['id'];
  $name = utf8_decode( $_REQUEST['name'] );
  $aliases = array( );
  if ( isset($_REQUEST['aliases']) and  is_array($_REQUEST['aliases']) ) {
    foreach( $_REQUEST['aliases'] as $alias ) {
      array_push( $aliases, trim( utf8_decode( $alias ) ) );
    }
  }
  sort( $aliases );

	require_once('header.php');
	$qdr = new QuotedRepository( $db );
  try {
    $oldQuoted = $qdr->getQuotedWithId( $id );
  } catch ( OutOfRangeException $e ) {
    $result['error'] = TRUE;
		$result['errorDescription'] = 'Id de quoteado inexistente: '.$e->getMessage( );
		die(json_encode($result));
  }
  $oldAliases = $oldQuoted->alias();
  sort( $oldAliases );
  try {
    if( $oldQuoted->name( ) !== $name || $oldQuoted->display() != $active ) {
      $qdr->editQuoted( $id, $name , $active);
    }
    if ( $oldAliases !== $aliases ) {
      $qdr->removeAliasesOf( $id );
      $qdr->editQuotedAliases( $id, $aliases );
    }
  } catch ( DomainException $e ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Nombres duplicados: '.$e->getMessage( );
		die(json_encode($result));
  }
	$result['success'] = TRUE;
	echo json_encode($result);
?>

