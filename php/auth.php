<?php
  session_start( );
  if( !isset($_REQUEST['t']) or empty($_REQUEST['t']) or
      !isset($_SESSION['token']) or empty($_SESSION['token']) or
      $_REQUEST['t'] != $_SESSION['token'] ) {
    $result['error'] = TRUE;
    $result['errorCode'] = 1;
    $result['errorDescription'] = 'No autenticado';
    die(json_encode($result));
  }
?>