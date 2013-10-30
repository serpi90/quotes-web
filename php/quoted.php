<?php
	require_once('header.php');

	$qdr = new QuotedRepository($db);

	if( isset( $_REQUEST['all'] ) ){
		$q = $qdr->getAllQuoted( );
	} else {
		$q = $qdr->getFilteredQuoted( );
	}

	$result = array( );
	foreach ( $q as $quoted ) {
		$aliases = implode( $quoted->alias( ), '/' );
		array_push( $result, array( "id" => $quoted->idQuoted( ), "quoted" => $quoted->name( ), "aliases" => $aliases ) );
	}
	echo json_encode( $result );
?>

