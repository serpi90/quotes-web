<?php
class QuoteRepository {
  private $quotedRepository;
  private $db_connection;
  private $quoted;
  private $statements;

  public function __construct( $db_connection, $quotedRepository ) {
    $this->db_connection = $db_connection;
    $this->quotedRepository = $quotedRepository;
    $this->quoted = array( );
    $this->statements = array( );
  }

  public function __destruct( ) {
    foreach( $this->statements as $s ) {
      $s->close( );
    }
  }

  public function addQuote( $quote, $quoted ) {
    $idsQuoted = array( );
    foreach ( $quoted  as $q ) {
      array_push( $idsQuoted, $q->idQuoted( ) );
    }
    $idQuote = $this->persistQuote( $quote );
    $this->persistRelationships( $idQuote, $idsQuoted );
  }

  public function delete( $number ) {
    $quote = $this->getQuoteByNumber( $number );
    $this->removeRelationships( $quote );
    $this->deleteQuote( $quote );
    $id = $quote->idQuote( );
    $this->updateQuoteNumbers( $id );
  }

  public function editQuote( $number, $quote, $quoted ) {
    $idsQuoted = array( );
    foreach ( $quoted  as $q ) {
      array_push( $idsQuoted, (int)$q->idQuoted( ) );
    }
    $this->updateQuote( $number, $quote );
    $old = $this->getQuoteByNumber( $number );
    $idQuote = $old->idQuote( );
    $this->persistRelationships( $idQuote, $idsQuoted );
  }

  public function getDailyQuote( ) {
    $result = $this->db_connection->query("SELECT Quote.* FROM (Quote JOIN R_Quoted_Quote USING (idQuote) ) JOIN Quoted USING (idQuoted) WHERE Quoted.display = TRUE AND Quote.year = (SELECT MAX(year) FROM Quote) GROUP BY idQuote");
    $dailyQuote;
    for( $n = mt_rand( 0 ,$result->num_rows ) ; $n >= 0 ; $n--) {
      $quote = $result->fetch_object( 'Quote' );
      if( !$n ) {
        $dailyQuote = $quote;
      }
    }
    $result->free( );
    return $dailyQuote;
  }

  public function getLatestQuotes( $offset, $amount ) {
    $offset = (int)$offset;
    $amount = (int)$amount;
    $result = $this->db_connection->query("SELECT * from Quote ORDER BY number DESC LIMIT {$offset},{$amount}");
    $quotes = array( );
    while( $quote = $result->fetch_object( 'Quote' ) ) {
      $this->fetchQuotedIn( $quote );
      array_push( $quotes, $quote );
    }
    return $quotes;
  }

  public function getQuoteByNumber( $number ) {
    $result = $this->db_connection->query("SELECT * from Quote WHERE number = {$number}");
    $quote = $result->fetch_object( 'Quote' );
    if( !$quote ) {
      throw new OutOfRangeException();
    }
    $this->fetchQuotedIn( $quote );
    return $quote;
  }

  public function getQuotesFor( $quoted ) {
    $result = $this->db_connection->query("SELECT Quote.* from Quote JOIN R_Quoted_Quote USING (idQuote) WHERE idQuoted = {$quoted->idQuoted( )} ORDER BY number DESC");
    while( $quote = $result->fetch_object( 'Quote' ) ) {
      $quote->addQuoted( $quoted );
      $quoted->addQuote( $quote );
    }
  }

  public function getQuotesForYear( $year ) {
    $year = (int)$year;
    $quotes = array( );
    $result = $this->db_connection->query("SELECT * FROM Quote WHERE year = {$year} ORDER BY number DESC ");
    while( $q = $result->fetch_object( 'Quote' ) ) {
      array_push( $quotes, $q );
    }
    return $quotes;
  }

  public function getQuotesYears( ) {
    $years = array( );
    $result = $this->db_connection->query("SELECT DISTINCT year FROM Quote ORDER BY year DESC");
    while( $r = $result->fetch_object( ) ) {
      array_push( $years, $r->year );
    }
    return $years;
  }

  public function quoteAmount( ) {
    $result = $this->db_connection->query("SELECT COUNT(*) FROM Quote");
    $amount = $result->fetch_row( );
    $amount = $amount[0];
    $result->free( );
    return $amount;
  }

  public function removeRelationships( $quote ) {
    $idQuote = $quote->idQuote( );
    if( !isset($this->statements['deleteRelationStatement']) or $this->statements['deleteRelationStatement'] === null ) {
      $this->statements['deleteRelationStatement'] = $this->db_connection->prepare("DELETE FROM `R_Quoted_Quote` WHERE `R_Quoted_Quote`.`idQuote` = ?");;
    }
    $this->statements['deleteRelationStatement']->bind_param( 'i', $idQuote ) or die( $this->statements['deleteRelationStatement']->error );
    $this->statements['deleteRelationStatement']->execute( ) or die( $this->statements['deleteRelationStatement']->error );
  }

  private function deleteQuote ( $quote ) {
    $idQuote = $quote->idQuote( );
    if( !isset($this->statements['deleteQuoteStatement']) or $this->statements['deleteQuoteStatement'] === null ) {
      $this->statements['deleteQuoteStatement'] = $this->db_connection->prepare("DELETE FROM `Quote` WHERE `idQuote` = ?");
    }
    $this->statements['deleteQuoteStatement']->bind_param( 'i', $idQuote ) or die( $this->statements['deleteRelationStatement']->error );
    $this->statements['deleteQuoteStatement']->execute( ) or die( $this->statements['deleteRelationStatement']->error );
  }

  private function fetchQuotedIn( $quote ) {
    $id = $quote->idQuote( );
    $result = $this->db_connection->query("SELECT * from R_Quoted_Quote WHERE idQuote = {$id}");
    while( $relationship = $result->fetch_object( ) ) {
      $quoted = $this->quotedRepository->getQuotedWithId( $relationship->idQuoted );
      $quote->addQuoted( $quoted );
    }
  }

  private function getLastQuoteNumber( ) {
    $result = $this->db_connection->query("SELECT MAX(number) FROM Quote");
    $number = $result->fetch_row( );
    $number = $number[0];
    $result->free( );
    return $number;
  }

  private function persistQuote( $quote ) {
    $number = $this->getLastQuoteNumber( ) + 1;
    if( !isset($this->statements['insertStatement']) or $this->statements['insertStatement'] === null ) {
      $this->statements['insertStatement'] = $this->db_connection->prepare("INSERT INTO Quote (number,quote,year) VALUES (?,?,(SELECT value FROM Settings WHERE `key` = 'currentYear'))");
    }
    $this->statements['insertStatement']->bind_param( 'is', $number, $quote ) or die( $this->statements['insertStatement']->error );
    $this->statements['insertStatement']->execute( ) or die( $this->statements['insertStatement']->error );
    $idQuote = $this->statements['insertStatement']->insert_id;
    return $idQuote;
  }

  private function persistQuotedArray( $quotedArray ){
    $ids = array( );
    foreach( array_keys($quotedArray) as $key ) {
      $quotedArray[$key] = utf8_encode( trim( $quotedArray[$key] ) );
    }
    $quotedArray = array_unique( $quotedArray );
    foreach( $quotedArray as $quoted ) {
      $id = $this->quotedRepository->addQuoted( $quoted );
      array_push( $ids, $id );
    }
    return $ids;
  }

  private function persistRelationships( $idQuote, $idsQuoted ) {
    $idsQuoted = array_unique( $idsQuoted );
    foreach ( $idsQuoted as $idQuoted ) {
      if( !isset($this->statements['insertRelationStatement']) or $this->statements['insertRelationStatement'] === null ) {
        $this->statements['insertRelationStatement'] = $this->db_connection->prepare("INSERT INTO R_Quoted_Quote (idQuote,idQuoted) VALUES ( ? , ? )");
      }
      $this->statements['insertRelationStatement']->bind_param( 'ii', $idQuote, $idQuoted ) or die( $this->statements['insertRelationStatement']->error );
      $this->statements['insertRelationStatement']->execute( );
    }
  }

  private function updateQuote( $number, $quote ) {
    if( !isset($this->updateQuoteSatement) or $this->updateQuoteSatement === null ) {
      $this->updateQuoteSatement = $this->db_connection->prepare("UPDATE `Quote` SET `quote`=? WHERE `number` = ?");
    }
    $this->updateQuoteSatement->bind_param( 'si', $quote, $number ) or die( $this->updateQuoteSatement->error );
    $this->updateQuoteSatement->execute( ) or die( $this->updateQuoteSatement->error );
  }

  private function updateQuoteNumbers( $idQuote ) {
    if( !isset($this->statements['updateAfterDeleteQuoteStatement']) or $this->statements['updateAfterDeleteQuoteStatement'] === null ) {
        $this->statements['updateAfterDeleteQuoteStatement'] = $this->db_connection->prepare("UPDATE `Quote` SET `number`=`number`-1 WHERE `idQuote` > ?");
    }
    $this->statements['updateAfterDeleteQuoteStatement']->bind_param( 'i', $idQuote ) or die( $this->statements['deleteRelationStatement']->error );
    $this->statements['updateAfterDeleteQuoteStatement']->execute( ) or die( $this->statements['deleteRelationStatement']->error );
  }
}