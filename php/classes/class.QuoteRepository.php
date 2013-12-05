<?php
class QuoteRepository {

	private $quotedRepository;
	private $db_connection;
	private $quoted;
	private $insertStatement;
	private $insertRelationStatement;

	public function __construct( $db_connection, $quotedRepository ) {
		$this->db_connection = $db_connection;
		$this->quotedRepository = $quotedRepository;
		$this->quoted = array( );
		$this->insertStatement = null;
		$this->insertRelationStatement = null;
	}
	
	public function getQuotesYears( ) {
		$years = array( );
		$result = $this->db_connection->query("SELECT DISTINCT year FROM Quote ORDER BY year DESC");
		while( $r = $result->fetch_object( ) ) {
			array_push( $years, $r->year );
		}
		return $years;
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
	
	public function getQuotesFor( $quoted ) {
		$result = $this->db_connection->query("SELECT Quote.* from Quote JOIN R_Quoted_Quote USING (idQuote) WHERE idQuoted = {$quoted->idQuoted( )} ORDER BY number DESC");
		while( $quote = $result->fetch_object( 'Quote' ) ) {
			$quote->addQuoted( $quoted );
			$quoted->addQuote( $quote );
		}
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

	public function quoteAmount( ) {
		$result = $this->db_connection->query("SELECT COUNT(*) FROM Quote");
		$amount = $result->fetch_row( );
		$amount = $amount[0];
		$result->free( );
		return $amount;
	}

	public function addQuote( $quote, $quoted ) {
		$idsQuoted = array( );
		foreach ( $quoted  as $q ) {
			array_push( $idsQuoted, $q->idQuoted( ) );
		}
		$idQuote = $this->persistQuote( $quote );
		$this->persistRelationships( $idQuote, $idsQuoted );
	}

	public function __destruct( ) {
		if( $this->insertStatement !== null ) {
			$this->insertStatement->close( );
		}
	}

	private function fetchQuotedIn( $quote ) {
		$id = $quote->idQuote( );
		$result = $this->db_connection->query("SELECT * from R_Quoted_Quote WHERE idQuote = {$id}");
		while( $relationship = $result->fetch_object( ) ) {
			$quoted = $this->quotedRepository->getQuotedWithId( $relationship->idQuoted );
			$quote->addQuoted( $quoted );
		}
	}

	private function persistQuote( $quote ) {
		$number = $this->getLastQuoteNumber( ) + 1;
		$year = date( 'Y' );
		if( !isset($this->insertStatement) or $this->insertStatement === null ) {
			$this->insertStatement = $this->db_connection->prepare("INSERT INTO Quote (number,quote,year) VALUES (?,?,(SELECT value FROM Settings WHERE field = 'currentYear'))");
		}
		$this->insertStatement->bind_param( 'isi', $number, $quote, $year ) or die( $this->insertStatement->error );
		$this->insertStatement->execute( ) or die( $this->insertStatement->error );
		$idQuote = $this->insertStatement->insert_id;
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
		foreach ( $idsQuoted as $idQuoted ) {
			if( !isset($this->insertRelationStatement) or $this->insertStatement === null ) {
				$this->insertRelationStatement = $this->db_connection->prepare("INSERT INTO R_Quoted_Quote (idQuote,idQuoted) VALUES ( ? , ? )");
			}
			$this->insertRelationStatement->bind_param( 'ii', $idQuote, $idQuoted ) or die( $this->insertStatement->error );
			$this->insertRelationStatement->execute( );
		}
	}

	private function getLastQuoteNumber( ) {
		$result = $this->db_connection->query("SELECT MAX(number) FROM Quote");
		$number = $result->fetch_row( );
		$number = $number[0];
		$result->free( );
		return $number;
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
}
