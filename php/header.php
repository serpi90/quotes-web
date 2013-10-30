<?php
require_once('config.php');

function __autoload( $class ) {
	require_once( "classes/class.{$class}.php" );
}
spl_autoload_register('__autoload');

$db = new Database( $db_server, $db_user, $db_pass, $db_name );
?>
