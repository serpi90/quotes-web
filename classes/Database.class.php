<?php
class Database {
	private $connection;
	private $cachedStatements;
	private $inTransaction;
	private $isConnected;

	public function __construct( $server, $user, $password, $database ) {
		if( !extension_loaded( 'mysqlnd' ) ) {
			throw new RuntimeException( 'php-mysqlnd is required to be installed' );
		}
		$driver = new mysqli_driver( );
		$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
		$this->inTransaction = false;
		$this->connection = new mysqli( $server, $user, $password, $database );
		$this->isConnected = true;
	}

	public function __destruct( ) {
		$this->close( );
	}

	public function beginTransaction( ) {
		if( $this->inTransaction( ) ) {
			throw new LogicException( 'Nested transaction' );
		}
		$this->inTransaction = true;
		$this->nonEscapedQuery('START TRANSACTION');
	}

	public function boundQuery( $base, $conditions, $tail = '', $limit = 0, $offset = 0 ) {
		$query = $base;
		$bindings = '';
		$arguments = array();
		if( count( $conditions ) ) {
			$query = $query . ' WHERE';
		}
		foreach( $conditions as $condition ) {
			if( substr( $query, -5 ) !== 'WHERE' ) {
				$query = $query . ' AND';
			}
			$query = $query . ' ' . $condition->condition( );
			$bindings = $bindings . $condition->bindings( );
			foreach( $condition->arguments( ) as $argument ) {
				array_push( $arguments, $argument );
			}
		}
		if( $tail != '' ) {
			$query = $query . ' ' . $tail;
		}
		if( $limit > 0 ) {
			$query = $query . ' LIMIT ?';
			if( $offset > 0 ) {
				$query = $query . ', ?';
				$bindings = $bindings .'i';
				array_push( $arguments, $offset );
			}
			$bindings = $bindings .'i';
			array_push( $arguments, $limit);
		}
		if( !isset( $this->cachedStatements[$query] ) ) {
			$this->cachedStatements[$query] = $this->connection->prepare( $query );
		}
		$statement = $this->cachedStatements[$query];
		if( $bindings !== '' ) {
			// --StartHack
			// bind_param rquires arguments by reference
			$referenceKeeper = array( );
			array_unshift( $arguments, $bindings );
			foreach ($arguments as $key => &$value) {
				$referenceKeeper[] =& $value;
			}
			call_user_func_array( array( $statement, 'bind_param' ), $referenceKeeper );
			// --EndHack
		}
		$statement->execute( );
		return $statement->get_result( );
	}

	public function close( ) {
		if( $this->isConnected ) {
			if( $this->inTransaction( ) ) {
				$this->rollbackTransaction( );
			}
			$this->connection->close( );
			$this->isConnected = FALSE;
		}
	}

	public function commitTransaction( ) {
		if( !$this->inTransaction( ) ) {
			throw new LogicException( 'Not in a transaction' );
		}
		$this->nonEscapedQuery('COMMIT');
		$this->inTransaction = false;
	}

	public function inTransaction( ) {
		return $this->inTransaction;
	}

	public function nonEscapedQuery( $query ) {
		return $this->connection->query( $query );
	}

	public function prepare( $query ) {
		return $this->connection->prepare( $query );
	}

	public function rollbackTransaction( ) {
		if( !$this->inTransaction( ) ) {
			throw new LogicException( 'Not in a transaction' );
		}
		$this->nonEscapedQuery('ROLLBACK');
		$this->inTransaction = false;
	}
}
?>