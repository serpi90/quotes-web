<?php
class QuotedRepository {

	private $db_connection;
	private $quoted;
	private $filteredQuoted;
	private $insertStatement;

	public function __construct( $db_connection ) {
		$this->db_connection = $db_connection;
		$this->quoted = array( );
		$this->filteredQuoted = array( );
		$this->fetchAllQuoted( );
		$this->insertStatement = null;
	}

	private function fetchAllQuoted( ) {
		$result = $this->db_connection->query("SELECT * from Quoted ORDER BY name ASC");
		while( $quoted = $result->fetch_object( 'Quoted' ) ) {
			$this->quoted[ $quoted->idQuoted( ) ] = $quoted;
			if( $quoted->display( ) ) {
				$this->filteredQuoted[ $quoted->idQuoted( ) ] = $quoted;
			}
		}
		$result->free( );
		$result = $this->db_connection->query("SELECT * from QuotedAlias");
		while( $alias = $result->fetch_object( ) ) {
			$this->quoted[ $alias->idQuoted ]->addAlias( $alias->alias );
		}

	}

	public function getQuotedWithId( $idQuoted ) {
		if( !isset( $this->quoted[ $idQuoted ] ) ) {
			throw new OutOfRangeException();
		}
		return $this->quoted[ $idQuoted ];
	}

	public function getFilteredQuoted( ) {
		return $this->filteredQuoted;
	}

	public function getAllQuoted( ) {
		return $this->quoted;
	}

	// public function addQuoted( $name ) {
		// if( !$this->existsQuotedNameOrAlias( $name ) ) {
			// $year = date( 'Y' );
			// if( !isset($this->insertStatement) or $this->insertStatement === null ) {
				// $this->insertStatement = $this->db_connection->prepare("INSERT INTO Quoted (name) VALUES (?)");
			// }
			// $this->insertStatement->bind_param( 's', $name ) or die( $this->insertStatement->error );
			// $this->insertStatement->execute( ) or die( $this->insertStatement->error );
			// return $this->insertStatement->insert_id;
		// }
		// return $this->quotedByNameOrAlias( $name )->idQuoted( );
	// }
	
	// private function existsQuotedNameOrAlias( $nameOrAlias ) {
		// foreach( $this->quoted as $q ) {
			// if( strtoupper( $q->name( ) ) == strtoupper( $nameOrAlias ) ) {
				// return true;
			// } else {
				// foreach( $q->alias( ) as $alias ) {
					// if( strtoupper( $alias ) == strtoupper( $nameOrAlias ) ) {
						// true;
					// }
				// }
			// }
		// }
		// return false;
	// }
	
	// private function quotedByNameOrAlias( $nameOrAlias ) {
		// foreach( $this->quoted as $q ) {
			// if( strtoupper( $q->name( ) ) == strtoupper( $nameOrAlias ) ) {
				// return $q;
			// } else {
				// foreach( $q->alias( ) as $alias ) {
					// if( strtoupper( $alias ) == strtoupper( $name ) ) {
						// return $q;
					// }
				// }
			// }
		// }
		// die( "$name not in quoted repository." );
	// }
	
	public function __destruct( ) {
		if( isset($this->insertStatement) and $this->insertStatement !== null ) {
			$this->insertStatement->close( );
		}
	}
}