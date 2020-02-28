<?php
class Quote {

  private $idQuote;
  private $number;
  private $year;
  private $quote;
  private $quoted;

  public function __construct( ) {
    $this->idQuote = (int) $this->idQuote;
    $this->number = (int) $this->number;
    $this->year = (int) $this->year;
    $this->quote = utf8_encode( $this->quote );
    $this->quoted = array( );
  }

  public function idQuote ( ) {
    return $this->idQuote;
  }

  public function number ( ) {
    return $this->number;
  }

  public function year ( ) {
    return $this->year;
  }

  public function quote ( ) {
    return $this->quote;
  }

  public function quoted ( ) {
    return $this->quoted;
  }

  public function addQuoted( $quoted ) {
    $ids = array_map(function ($q) { return $q->idQuoted( ); }, $this->quoted);
    if( !in_array($quoted->idQuoted( ), $ids) ) {
      array_push( $this->quoted, $quoted );
    }
  }
}