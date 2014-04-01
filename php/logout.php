<?php
	session_start( );
  session_unset( );
  unset( $_SESSION['token'] ); 
  session_destroy( );
?>