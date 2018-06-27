<?php
class Quoted {

  private $idQuoted;
  private $name;
  private $display;
  private $quotes;
  private $alias;

  public function __construct( ) {
    $this->idQuote = (int) $this->idQuoted;
    $this->display = (bool) $this->display;
    $this->name = utf8_encode( $this->name );
    $this->quotes = array( );
    $this->alias = array( );
  }


  public function idQuoted( ) {
    return $this->idQuoted;
  }

  public function name( ) {
    return $this->name;
  }

  public function display( ) {
    return $this->display;
  }

  public function quotes( ) {
    return $this->quotes;
  }
  public function alias( ) {
    return $this->alias;
  }
  
  public function clearAliases( ) {
    $this->alias = array( );
  }

  public function addQuote( $quote ) {
    if( !isset( $this->quotes[ $quote->idQuote( ) ] ) ) {
      array_push( $this->quotes, $quote );
    }
  }

  public function addAlias( $alias ) {
    $alias = utf8_encode( $alias );
    if( !isset( $this->alias[ $alias ] ) ) {
      array_push( $this->alias, $alias );
    }
  }
}