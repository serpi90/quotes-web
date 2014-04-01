<?php
  if( !isset($_REQUEST['phash']) or empty($_REQUEST['phash']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'No se introdujo password';
		die(json_encode($result));
	}
  $hash = $_REQUEST['phash'];
  require_once('header.php');

  $sr = new SettingsRepository( $db );
  try {
    $valid_hash = $sr->passwordHash( );
  } catch ( Exception $e ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Falta configurar el password';
		die(json_encode($result));
	}
  if( $valid_hash === $hash ) {
    $result['token'] = sha1(uniqid(mt_rand(), true));
    // Almacenar el token para consultarlo luego.
    session_start( );
    session_unset( );
    unset( $_SESSION['token'] );
    $_SESSION['token'] = $result['token'];
    $result['loggedIn'] = TRUE;
    session_commit( );
  } else {
    $result['error'] = TRUE;
		$result['errorDescription'] = 'Password Incorrecto';
  }
  echo json_encode( $result );
?>