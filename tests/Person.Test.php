<?php

require_once('classes/Person.class.php');

class PersonTest extends \PHPUnit_Framework_TestCase {
	public function testValidCreation( ) {
		$bob = new Person( 1 , 'Bob', array( 'Bobby' ), TRUE );

		$this->assertSame( 1, $bob->id( ));
		$this->assertSame( 'Bob', $bob->name( ) );
		$this->assertEquals( array( 'Bobby' ), $bob->aliases( ) );
		$this->assertSame( 'Bobby' , $bob->aliases( )[0] );
		$this->assertSame( TRUE, $bob->active( ) );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage id: 'z' should be of type integer but is string.
	 */
	public function testBadId( ) {
		new Person( 'z' , 'Bob', array( 'Bobby' ), TRUE );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage name: false should be of type string but is boolean.
	 */
	public function testBadName( ) {
		new Person( 1 , FALSE, array( 'Bobby' ), TRUE );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage aliases: 'Bobby' should be of type array but is string.
	 */
	public function testBadAliases( ) {
		new Person( 1 , 'Bob', 'Bobby', TRUE );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage alias: 0 should be of type string but is integer.
	 */
	public function testBadAlias( ) {
		new Person( 1 , 'Bob', array( 0 ), TRUE );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage active: 3 should be of type boolean but is integer.
	 */
	public function testBadActive( ) {
		new Person( 1 , 'Bob', array( 'Bobby' ), 3 );
	}
}
?>
