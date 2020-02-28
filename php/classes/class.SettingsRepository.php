<?php
class SettingsRepository {

  private $db_connection;
  private $passwordHash;

  public function __construct( $db_connection ) {
    $this->db_connection = $db_connection;

  }

  public function passwordHash( ) {
    if( $this->passwordHash === NULL ) {
      $db_result = $this->db_connection->nonEscapedQuery('SELECT value FROM Settings WHERE `key` = "password_hash"');
      if( $db_result->num_rows == 0 ) {
        $this->passwordHash = -1;
      } else {
        $this->passwordHash = $db_result->fetch_object( )->value;
      }
    }
    if( $this->passwordHash == -1 ) {
      throw new Exception( 'Setting "password_hash" no establecido' );
    }
    return $this->passwordHash;
  }
}