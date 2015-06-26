<?php
	require_once('header.php');
	$result = $db->query('SELECT year,count(*) AS quotes FROM Quote GROUP BY year ORDER BY year DESC');
  $years = array( );
  while( $o = $result->fetch_object( ) ) {
    array_push( $years, array( $o->year, (int) $o->quotes ) );
  }
	echo json_encode( $years );
?>

