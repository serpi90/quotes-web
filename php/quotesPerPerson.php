<?php
	require_once('header.php');
	$result = $db->query('SELECT name,count(*) AS quotes FROM R_Quoted_Quote JOIN Quoted ON R_Quoted_Quote.idQuoted = Quoted.idQuoted WHERE Quoted.display = 1 GROUP BY R_Quoted_Quote.idQuoted ORDER BY quotes DESC LIMIT 10');
  $years = array( );
  while( $o = $result->fetch_object( ) ) {
    array_push( $years, array( utf8_encode($o->name), (int) $o->quotes ) );
  }
	echo json_encode( $years );
?>

