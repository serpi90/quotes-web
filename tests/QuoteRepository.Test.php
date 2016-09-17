<?php
require_once('classes/Person.class.php');
require_once('classes/Quote.class.php');
require_once('classes/PersonRepository.class.php');
require_once('classes/QuoteRepository.class.php');
require_once('classes/BinaryQueryCondition.class.php');
require_once('classes/Database.class.php');

class QuoteRepositoryTest extends \PHPUnit_Framework_TestCase {
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
		$this->personRepository = new PersonRepository( self::$database );
		$this->einstein = $this->personRepository->create( 'Albert Einstein', array( 'The Father of Relativity' ), TRUE  );
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
		return new QuoteRepository( self::$database, $this->personRepository );
	}
	public function testCreateQuote( ) {
		$repository = $this->testValidCreation( );
		$draft = new QuoteDraft( 'Imagination is more important than knowledge.', $this->einstein );
		$now = new DateTime( );
		$quote = $repository->create( $draft, $now );
		$this->assertInstanceOf( 'Quote', $quote );
		$this->assertSame( 'Imagination is more important than knowledge.', $quote->phrase( ) );
		$this->assertSame( $this->einstein, $quote->author( ) );
		$this->assertEquals( $now, $quote->registrationTime( ) );
	}

	public function testFilteredQuotes( ) {
		$repository = $this->testValidCreation( );
		$draft = new QuoteDraft( 'Imagination is more important than knowledge.', $this->einstein );
		$repository->create( $draft );
		$quotes = $repository->quotesFilteredBy( array( ) );
		$this->assertCount( 1, $quotes );
		$quote = array_shift( $quotes );
		$this->assertInstanceOf( 'Quote', $quote );
		$this->assertSame( 'Imagination is more important than knowledge.', $quote->phrase( ) );
		$this->assertSame( $this->einstein, $quote->author( ) );
		// Two Quotes
		$draft = new QuoteDraft( 'The important thing is not to stop questioning. Curiosity has its own reason for existing.', $this->einstein );
		$repository->create( $draft );
		$quotes = $repository->quotesFilteredBy( array( ) );
		$this->assertCount( 2, $quotes );
		$quote = array_shift( $quotes );
		$this->assertInstanceOf( 'Quote', $quote );
		$this->assertSame( 'Imagination is more important than knowledge.', $quote->phrase( ) );
		$this->assertSame( $this->einstein, $quote->author( ) );
		$quote = array_shift( $quotes );
		$this->assertInstanceOf( 'Quote', $quote );
		$this->assertSame( 'The important thing is not to stop questioning. Curiosity has its own reason for existing.', $quote->phrase( ) );
		$this->assertSame( $this->einstein, $quote->author( ) );

	}

	public function testUpdateQuote( ) {
		$repository = $this->testValidCreation( );
		$draft = new QuoteDraft( 'Imagination is more important than knowledge.', $this->einstein );
		$quote = $repository->create( $draft );
		$this->assertCount( 1, $repository->quotesFilteredBy( array( ) ) );

		$newton = $this->personRepository->create( 'Isaac Newton', array( 'The Father of Physics' ), FALSE );
		$updatedQuote = $repository->update( $quote, 'Anyone who has never made a mistake has never tried anything new.', $newton );
		$this->assertSame( 'Anyone who has never made a mistake has never tried anything new.', $updatedQuote->phrase( ) );
		$this->assertSame( $newton, $updatedQuote->author( ) );
	}

	public function testDeleteQuote( ) {
		$repository = $this->testValidCreation( );
		$draft = new QuoteDraft( 'Imagination is more important than knowledge.', $this->einstein );
		$quote = $repository->create( $draft );
		$draft = new QuoteDraft( 'The important thing is not to stop questioning. Curiosity has its own reason for existing.', $this->einstein );
		$repository->create( $draft );
		$this->assertCount( 2, $repository->quotesFilteredBy( array( ) ) );
		$repository->delete( $quote );
		$quotes = $repository->quotesFilteredBy( array( ) );
	   	$this->assertCount( 1, $quotes );
		$quote = array_shift( $quotes );
		$this->assertSame( 'The important thing is not to stop questioning. Curiosity has its own reason for existing.', $quote->phrase( ) );
	}

	public function years( ) {
		$repository = $this->testValidCreation( );
		$draft = new QuoteDraft( 'Imagination is more important than knowledge.', $this->einstein );
		$repository->create( $draft, new DateTime( '1990-01-03 07:30:01' ) );
		$draft = new QuoteDraft( 'The important thing is not to stop questioning. Curiosity has its own reason for existing.', $this->einstein );
		$repository->create( $draft, new DateTime( '1992-01-15 10:02:12' ) );
		$this->assertSame( array( 1990, 1992 ), $repository->getYears( ) );
	}
}
?>
