<?php

require_once('classes/Person.class.php');
require_once('classes/Quote.class.php');

class QuoteTest extends \PHPUnit_Framework_TestCase {
	protected $bob;
	protected $now;

	protected function setUp( ) {
		$this->bob = new Person( 1 , 'Bob', array( 'Bobby' ), TRUE );
		$this->now = new DateTime();
	}

	public function testValidCreation( ) {
		$quote =  new Quote( 1, 1, $this->now, 'test', $this->bob );

		$this->assertSame( 1, $quote->id( ) );
		$this->assertSame( 1, $quote->number( ) );
		$this->assertSame( $this->now, $quote->registrationTime( ) );
		$this->assertSame( 'test', $quote->phrase( ) );
		$this->assertSame( $this->bob, $quote->author( ) );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage id: 'z' should be of type integer but is string.
	 */
	public function testBadId( ) {
		new Quote( 'z', 1, $this->now, 'test', $this->bob );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage number: 'z' should be of type integer but is string.
	 */
	public function testBadNumber( ) {
		new Quote( 1, 'z', $this->now, 'test', $this->bob );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage registrationTime: 2 should be of type DateTime but is integer.
	 */
	public function testBadregistrationTime( ) {
		new Quote( 1, 1, 2, 'test', $this->bob );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage phrase: true should be of type string but is boolean.
	 */
	public function testBadPhrase( ) {
		new Quote( 1, 1, $this->now, true, $this->bob );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage author: 'bob' should be of type Person but is string.
	 */
	public function testBadAuthor( ) {
		new Quote( 1, 1, $this->now, 'test', 'bob' );
	}
}
?>
