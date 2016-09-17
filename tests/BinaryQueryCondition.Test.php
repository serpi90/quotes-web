<?php

require_once('tests/EvaluateDummy.class.php');
require_once('classes/QueryCondition.class.php');
require_once('classes/BinaryQueryCondition.class.php');

class BinaryQueryConditionTest extends \PHPUnit_Framework_TestCase {
	public function testValidCreation( ) {
		new BinaryQueryCondition( 'spam' , '=', 'eggs' );
	}

	/**
	 * @expectedException        UnexpectedValueException
	 * @expectedExceptionMessage '<>' is not a valid logic operator.
	 */
	public function testBadOperation( ) {
		new BinaryQueryCondition( 'spam' , '<>', 'eggs' );
	}

	public function testArguments( ) {
		$condition = new BinaryQueryCondition( 'spam' , '=', 'eggs' );
		$this->assertSame( array( 'eggs' ), $condition->arguments( ) );

		$condition = new BinaryQueryCondition( 'spam' , '=', TRUE );
		$this->assertSame( array( TRUE ), $condition->arguments( ) );
	}

	public function testBindings( ) {
		$condition = new BinaryQueryCondition( 'spam' , '=', 'eggs' );
		$this->assertSame( $condition->bindings( ), 's');

		$condition = new BinaryQueryCondition( 'spam' , '=', 1 );
		$this->assertSame( $condition->bindings( ), 'i');

		$condition = new BinaryQueryCondition( 'spam' , '=', TRUE );
		$this->assertSame( $condition->bindings( ), 'i');

		$condition = new BinaryQueryCondition( 'spam' , '=', 1.5 );
		$this->assertSame( $condition->bindings( ), 'd');
	}

	public function testCondition( ) {
		$condition = new BinaryQueryCondition( 'eggs' , '<=', 1.5 );
		$this->assertSame( 'eggs <= ?', $condition->condition( ) );
	}

	public function testEvaluate( ) {
		$condition = new BinaryQueryCondition( 'spam' , '<=', 1.5 );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( 1 ) ) );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( 1.5 ) ) );
		$this->assertFalse( $condition->evaluate( new EvaluateDummy( 2 ) ) );

		$condition = new BinaryQueryCondition( 'spam' , '=', FALSE );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( FALSE ) ) );
		$this->assertFalse( $condition->evaluate( new EvaluateDummy( TRUE ) ) );

		$condition = new BinaryQueryCondition( 'spam' , '=', TRUE );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( TRUE ) ) );
		$this->assertFalse( $condition->evaluate( new EvaluateDummy( FALSE ) ) );

		$condition = new BinaryQueryCondition( 'spam' , '>', 'spam' );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( 'zpam' ) ) );
		$this->assertFalse( $condition->evaluate( new EvaluateDummy( 'eggs' ) ) );
	}
}
?>
