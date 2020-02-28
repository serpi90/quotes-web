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
    $ids = array_map(function ($q) { return $q->idQuote( ); }, $this->quotes);
    if( !in_array($quote->idQuote( ), $ids) ) {
      array_push( $this->quotes, $quote );
    }
  }

  public function addAlias( $alias ) {
    $alias = utf8_encode( $alias );
    if( !in_array( $alias, $this->alias ) ) {
      array_push( $this->alias, $alias );
    }
  }
}