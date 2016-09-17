<?php
class PersonRepository {

	private $db_connection;
	private $people;
	private $statements;

	public function __construct( $db_connection ) {
		$this->db_connection = $db_connection;
		$this->people = array( );
		$this->statements = array( );
		$this->fetchAllPeople( );
	}

	public function __destruct( ) {
		foreach( $this->statements as $s ) {
			$s->close( );
		}
	}

	public function personWithId( $id ) {
		if( !isset( $this->people[ $id ] ) ) {
			throw new OutOfBoundException( "Person with $id not found" );
		}
		return $this->people[ $id ];
	}

	public function peopleFilteredBy( $conditions ) {
		$filtered = array( );
		foreach( $this->people as $q ) {
			$valid = TRUE;
			foreach( $conditions as $condition ) {
				$valid = ( $valid and $condition->evaluate( $q ) );
				if( !$valid ){
					break;
				}
			}
			if ( $valid ) {
				array_push( $filtered, $q );
			}
		}
		return $filtered;
	}

	public function create( $name, $aliases, $active ) {
		$name = $this->validateName( $name );
		foreach( array_keys( $aliases ) as $key ){
			$aliases[ $key ] = $this->validateName( $aliases[ $key ] );
		}
		$this->db_connection->beginTransaction( );
		try {
			$id = $this->insertPerson( $name, $active );
			$this->insertPersonAliases( $id, $aliases );
			$this->db_connection->commitTransaction( );
		} catch ( Exception $e ) {
			if( $this->db_connection->inTransaction( ) ) {
				$this->db_connection->rollbackTransaction( );
			}
			throw $e;
		}
		$person = new Person( $id, $name, $aliases, $active );
		$this->people[ $person->id( ) ] = $person;
		return $person;
	}

	public function update( $person, $name, $aliases, $active ) {
		$name = $this->validateName( $name );
		foreach( array_keys( $aliases ) as $key ){
			$aliases[ $key ] = $this->validateName( $aliases[ $key ] );
		}
		$this->db_connection->beginTransaction( );
		try {
			$this->updatePerson( $person, $name, $active );
			$this->updatePersonAliases( $person, $aliases );
			$this->db_connection->commitTransaction( );
		} catch ( Exception $e ) {
			if( $this->db_connection->inTransaction( ) ) {
				$this->db_connection->rollbackTransaction( );
			}
			throw $e;
		}
		$this->people[ $person->id( ) ] = new Person( $person->id( ), $name, $aliases, $active );
		return $this->people[ $person->id( ) ];
	}

	public function delete( $person ) {

		$this->db_connection->beginTransaction( );
		try {
			if( $this->hasQuotes( $person->id( ) ) ) {
				throw new StillReferencedException;
			}
			$this->deletePersonAliases( $person->id( ) );
			$this->deletePerson( $person->id( ) );
			$this->db_connection->commitTransaction( );
		} catch ( Exception $e ) {
			if( $this->db_connection->inTransaction( ) ) {
				$this->db_connection->rollbackTransaction( );
			}
			throw $e;
		}
		unset( $this->people[ $person->id( ) ] );
	}

	private function fetchAllpeople( ) {
		$peopleAliases = array ( );
		$result = $this->db_connection->nonEscapedQuery( "SELECT * from PersonAlias" );
		while( $alias = $result->fetch_object( ) ) {
			$peopleAliases[$alias->id][] = ( $alias->alias );
		}
		$result->free( );

		$result = $this->db_connection->nonEscapedQuery( "SELECT * from Person ORDER BY name ASC" );
		while( $row = $result->fetch_object( ) ) {
			$personAliases = array_key_exists( $row->id, $peopleAliases ) ? $peopleAliases[ $row->id ] : array( );
			$person = new Person( $row->id, $row->name, $personAliases, $row->active );
			$this->people[ $person->id( ) ] = $person;
		}
		$result->free( );
	}

	private function insertPerson( $name, $active ) {
		if( !isset( $this->statements['insertPerson'] ) or $this->statements['insertPerson'] === null ) {
			$this->statements['insertPerson'] = $this->db_connection->prepare( "INSERT INTO Person ( name, active ) VALUES ( ? , ? )" );
		}
		$this->statements['insertPerson']->bind_param( 'sb', $name, $active );
		$this->statements['insertPerson']->execute( );

		return $this->statements['insertPerson']->insert_id;
	}

	private function insertPersonAliases( $id, $aliases ) {
		foreach( $aliases as $alias ) {
			if( !isset( $this->statements['insertAlias'] ) ) {
				$this->statements['insertAlias'] = $this->db_connection->prepare( "INSERT INTO PersonAlias ( idPerson, alias ) VALUES ( ? , ? )" );
			}
			$this->statements['insertAlias']->bind_param( 'ss', $id, $alias );
			$this->statements['insertAlias']->execute( );
		}
	}

	private function validateName( $name ) {
		$name = trim( $name );
		if( $name === '' ) {
			throw new InvalidArgumentException( 'Name nor alias can\'t be an empty string' );
		}
		return $name;
	}

	private function updatePerson( $person, $name, $active ) {
		if( !isset( $this->statements['updatePerson'] ) ) {
			$this->statements['updatePerson'] = $this->db_connection->prepare( "UPDATE Person SET name = ?, active = ? WHERE idPerson = ?" );
		}
		$this->statements['updatePerson']->bind_param( 'sii', $name, $active, $id );
		$this->statements['updatePerson']->execute( );
	}

	private function updatePersonAliases( $person, $aliases ) {
		$aliasesToRemove = array_diff( $person->aliases( ), $aliases );
		$aliasesToAdd = array_diff( $aliases, $person->aliases( ) );
		$this->insertPersonAliases( $person->id( ), $aliasesToAdd );
		$this->deletePersonAliases( $person->id( ), $aliasesToRemove );
	}

	private function deletePersonAliases( $id, $aliases = array( ) ) {
		$conditions[] = new BinaryQueryCondition( 'idPerson', '=', $id );
		if( !empty( $aliases ) ) {
			$conditions[] = new InclusionQueryCondition( 'alias' , $aliases );
		}
		$this->db_connection->boundQuery( 'DELETE FROM PersonAlias', $conditions );
	}

	private function deletePerson( $id ) {
		$conditions[] = new BinaryQueryCondition( 'idPerson', '=', $id );
		$this->db_connection->boundQuery( 'DELETE FROM Person', $conditions );
	}

	private function hasQuotes( $id ) {
		$result = $this->db_connection->nonEscapedQuery( "(SELECT 'Quote', COUNT(*) FROM Quote WHERE idPerson = $id) UNION (SELECT 'QuoteDraft',COUNT(*) FROM QuoteDraft WHERE idPerson = $id)" );
		$rows = $result->fetch_all( );
		$result->free( );
		return ( $rows[0][1] + $rows[1][1] > 0 );
	}
}
