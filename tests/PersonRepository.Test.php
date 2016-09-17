<?php

require_once('classes/Person.class.php');
require_once('classes/Quote.class.php');
require_once('classes/PersonRepository.class.php');
require_once('classes/BinaryQueryCondition.class.php');
require_once('classes/InclusionQueryCondition.class.php');
require_once('classes/Database.class.php');

class PersonRepositoryTest extends \PHPUnit_Framework_TestCase {

	protected static $database;

	public static function setUpBeforeClass ( ) {
		self::$database = new Database( 'localhost', 'quotes-test', 'quotes-test', 'quotes-test' );
	}

	public static function tearDownAfterClass( ) {
		self::$database->close( );
		self::$database = null;
	}

	protected function setUp( ) {
		$this->cleanupDatabase( );
	}

	private function cleanupDatabase( ) {
		$queries = explode( ';', trim( file_get_contents('sql/tables.sql') ) );
		foreach( $queries as $query ) {
			$query = trim( $query );
			if( $query != '' ) {
				self::$database->nonEscapedQuery( $query );
			}
		}
	}

	public function testValidCreation( ) {
		return new PersonRepository( self::$database );
	}

	public function testCreatePerson( ) {
		$repository = $this->testValidCreation( );
		$result = $repository->create( 'Albert Einstein', array( 'The Father of Relativity'), TRUE  );
		$this->assertSame( 'object', gettype( $result ) );
		$this->assertSame( 'Person', get_class( $result ) );
		$this->assertSame( 1, $result->id( ) );
		$this->assertSame( 'Albert Einstein', $result->name( ) );
		$this->assertSame( array( 'The Father of Relativity' ), $result->aliases( ) );
		$this->assertSame( TRUE, $result->active( ) );
	}

	public function testPersonWithId( ) {
		$repository = $this->testValidCreation( );
		$this->assertSame( array( ), $repository->peopleFilteredBy( array( ) ) );
		$einstein = $repository->create( 'Albert Einstein', array( 'The Father of Relativity' ), TRUE );
		$person = $repository->personWithId( $einstein->id( ) );
		$this->assertSame( $einstein , $person );
	}

	public function testFilteredPersons( ) {
		$repository = $this->testValidCreation( );
		// Nobody
		$this->assertSame( array( ), $repository->peopleFilteredBy( array( ) ) );
		// One person
		$einstein = $repository->create( 'Albert Einstein', array( 'The Father of Relativity' ), TRUE );
		$people = $repository->peopleFilteredBy( array( ) );
		$this->assertCount( 1, $people );
		$this->assertSame( array( $einstein ), $people );
		// Two people
		$newton = $repository->create( 'Isaac Newton', array( 'The Father of Physics' ), TRUE );
		$people = $repository->peopleFilteredBy( array( ) );
		$this->assertSame( array( $einstein, $newton ), $people );
		$this->assertCount( 2, $people );
		$this->assertSame( array( $einstein ), $repository->peopleFilteredBy( array( new BinaryQueryCondition( 'id', '=', $einstein->id( ) ) ) ) );
		$this->assertSame( array( $newton ), $repository->peopleFilteredBy( array( new BinaryQueryCondition( 'id', '=', $newton->id( ) ) ) ) );
		$this->assertSame( array( ), $repository->peopleFilteredBy( array( new BinaryQueryCondition( 'id', '=', $einstein->id( ) + $newton->id( ) ) ) ) );
	}

	public function testUpdatePerson( ) {
		$repository = $this->testValidCreation( );
		// Nobody
		$this->assertSame( array( ), $repository->peopleFilteredBy( array( ) ) );
		// One person
		$einstein = $repository->create( 'Albert Einstein', array( 'The Father of Relativity', 'Dr. Physics' ), TRUE );
		$newton = $repository->update( $einstein, 'Isaac Newton', array( 'The Father of Physics', 'Dr. Physics' ), FALSE );
		$people = $repository->peopleFilteredBy( array( ) );
		$this->assertSame( array( $newton ), $people );
		$this->assertCount( 1, $people );
		$this->assertSame( 1, $newton->id( ) );
		$this->assertSame( 'Isaac Newton', $newton->name( ) );
		$this->assertSame( array( 'The Father of Physics', 'Dr. Physics'  ), $newton->aliases( ) );
		$this->assertSame( FALSE, $newton->active( ) );
	}
	
	public function testDeletePerson( ) {
		$repository = $this->testValidCreation( );
		$this->assertSame( array( ), $repository->peopleFilteredBy( array( ) ) );
		$einstein = $repository->create( 'Albert Einstein', array( 'The Father of Relativity', 'Dr. Physics' ), TRUE );
		$people = $repository->peopleFilteredBy( array( ) );
		$this->assertCount( 1, $repository->peopleFilteredBy( array( ) ) );
		$repository->delete( $einstein );
		$this->assertCount( 0, $repository->peopleFilteredBy( array( ) ) );
	}
}
?>
