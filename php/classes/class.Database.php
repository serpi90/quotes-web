<?php
class Database {

  private $server;
  private $user;
  private $password;
  private $database;
  
  private $connection;
  
  public function __construct( $server, $user, $password, $database ) {
    $this->server = $server;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;
    
    $this->connection = new mysqli( $server, $user, $password, $database ) or die ( $this->connection->connect_error );
  }

  public function query( $query ) {
    $result = $this->connection->query( $this->connection->real_escape_string( $query ) );
    if ( $result === FALSE ) {
      die ( $this->connection->error );
    }
    return $result;
  }
  
  public function nonEscapedQuery( $query ) {
    $result = $this->connection->query( $query );
    if ( $result === FALSE ) {
      die ( $this->connection->error );
    }
    return $result;
  }
  
  public function __destruct( ) {
    $this->connection->close( );
  }
  
  public function prepare( $query ) {
    $result = $this->connection->prepare( $query );
    if( $result === FALSE ) {
      die( $this->connection->error );
    }
    return $result;
  }
}