<?php
class QuoteRepository {
	private $personRepository;
	private $db_connection;
	private $quoted;
	private $statements;

	public function __construct( $db_connection, $personRepository ) {
		$this->db_connection = $db_connection;
		$this->personRepository = $personRepository;
		$this->statements = array( );
	}

	public function __destruct( ) {
		foreach( $this->statements as $s ) {
			$s->close( );
		}
	}

	public function create( $quoteDraft, $registrationTime = NULL ) {
		if( $registrationTime === NULL ) {
			$registrationTime = new DateTime( );
		}
		$this->db_connection->beginTransaction( );
		try {
			$id = $this->insertQuote( $quoteDraft, $registrationTime );
			$this->db_connection->commitTransaction( );
		} catch ( Exception $e ) {
			if( $this->db_connection->inTransaction( ) ) {
				$this->db_connection->rollbackTransaction( );
			}
			throw $e;
		}
		return $this->quoteWithId( $id );
	}

	private function quoteWithId( $id ) {
		$quotes = $this->quotesFilteredBy( array( new BinaryQueryCondition( 'idQuote', '=', $id )), $amount = 1 );
		if( empty( $quotes ) ) {
			throw new OutOfBoundsException( $id );
		}
		return array_shift( $quotes );
	}

	private function nextQuoteNumber( ) {
		$result = $this->db_connection->nonEscapedQuery('SELECT MAX(number) FROM Quote');
		$number = ( (int) $result->fetch_all( )[0][0] ) + 1;
		$result->free( );
		return $number;
	}

	private function insertQuote( $quoteDraft, $registrationTime ) {
		if( !isset( $this->statements['insert'] ) ) {
			$this->statements['insert'] = $this->db_connection->prepare('INSERT INTO Quote ( number, registered, phrase, idPerson ) VALUES ( ? , ? , ? , ? )');
		}
		$registrationTimeString = $registrationTime->format( 'Y-m-d H:i:s' );
		$number = $this->nextQuoteNumber( );
		$phrase = $quoteDraft->phrase( );
		$authorId = $quoteDraft->author( )->id( );
		$this->statements['insert']->bind_param( 'issi', $number, $registrationTimeString, $phrase, $authorId);
		$this->statements['insert']->execute( );
		return $this->statements['insert']->insert_id;
	}

	public function quotesFilteredBy( $conditions, $amount = 0, $offset = 0 ) {
		$result = $this->db_connection->boundQuery( 'SELECT Quote.* from Quote', $conditions, 'ORDER BY number ASC', (int)$amount, (int)$offset );
		$quotes = array( );
		while( $row = $result->fetch_object( ) ) {
			$quotes[] = new Quote( $row->idQuote, $row->number, new DateTime( $row->registered ), $row->phrase, $this->personRepository->personWithId( $row->idPerson ) );
		}
		$result->free( );
		return $quotes;
	}

	public function update( $quote, $phrase, $author ) {
		if( !isset( $this->statements['updateQuote'] ) ) {
			$this->statements['updateQuote'] = $this->db_connection->prepare( 'UPDATE `Quote` SET `phrase` = ? ,`idPerson` = ? WHERE `idQuote` = ?' );
		}
		$id = $quote->id( );
		$authorId = $author->id( );
		$this->statements['updateQuote']->bind_param( 'sii', $phrase, $authorId, $id );
		$this->statements['updateQuote']->execute( );
		return $this->quoteWithId( $quote->id( ) );
	}

	public function delete( $quote ) {
		$this->db_connection->beginTransaction( );
		try {
			$this->db_connection->boundQuery('DELETE FROM `Quote`', array( new BinaryQueryCondition( '`idQuote`', '=' , $quote->id( ) ) ) );
			$this->db_connection->boundQuery('UPDATE `Quote` SET `number`=`number`-1', array( new BinaryQueryCondition( '`idQuote`', '>' , $quote->id( ) ) ) );
			$this->db_connection->commitTransaction( );
		} catch ( Exception $e ) {
			$this->db_connection->rollbackTransaction( );
			throw $e;
		}
	}

	public function years( ) {
		$years = array( );
		$result = $this->db_connection->nonEscapedQuery("SELECT DISTINCT year FROM Quote ORDER BY year DESC");
		while( $r = $result->fetch_object( ) ) {
			$years[] = (int)$r->year;
		}
		return $years;
	}
}
?>
