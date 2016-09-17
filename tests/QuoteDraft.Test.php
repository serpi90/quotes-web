<?php

require_once('classes/Person.class.php');
require_once('classes/QuoteDraft.class.php');

class QuoteDraftTest extends \PHPUnit_Framework_TestCase {
	private $bob;

	protected function setUp( ) {
		$this->bob = new Person( 1 , 'Bob', array( 'Bobby' ), TRUE );
	}

	public function testValidCreation( ) {
		$draft =  new QuoteDraft( 'test', $this->bob );
		$this->assertSame( 'test', $draft->phrase( ) );
		$this->assertSame( $this->bob, $draft->author( ) );
	}
	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage phrase: true should be of type string but is boolean.
	 */
	public function testBadPhrase( ) {
		new QuoteDraft( true, $this->bob );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage phrase can not be empty.
	 */
	public function testEmptyPhrase( ) {
		new QuoteDraft( '', $this->bob );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage author: 'bob' should be of type Person but is string.
	 */
	public function testBadAuthor( ) {
		new QuoteDraft( 'test', 'bob' );
	}
}
?>
