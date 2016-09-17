<?php

require_once('tests/EvaluateDummy.class.php');
require_once('classes/QueryCondition.class.php');
require_once('classes/InclusionQueryCondition.class.php');

class InclusionQueryConditionTest extends \PHPUnit_Framework_TestCase {
	public function testValidCreation( ) {
		new InclusionQueryCondition( 'spam' , array( 'eggs' ) );
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage $arguments: eggs should be an array but is string.
	 */
	public function testBadArguments( ) {
		new InclusionQueryCondition( 'spam' , 'eggs' );
	}

	public function testArguments( ) {
		$condition = new InclusionQueryCondition( 'spam' , array( 'eggs', 'spam' ) );
		$this->assertSame( array( 'eggs', 'spam' ), $condition->arguments( ) );
	}

	public function testBindings( ) {
		$condition = new InclusionQueryCondition( 'spam' , array( 'eggs', 'spam' ) );
		$this->assertSame( $condition->bindings( ), 'ss');

		$condition = new InclusionQueryCondition( 'spam' , array( 1, 2 ) );
		$this->assertSame( $condition->bindings( ), 'ii');

		$condition = new InclusionQueryCondition( 'spam' , array( FALSE, TRUE ) );
		$this->assertSame( $condition->bindings( ), 'ii');

		$condition = new InclusionQueryCondition( 'spam' , array( 1.5, 2 ) );
		$this->assertSame( $condition->bindings( ), 'di');
	}

	public function testCondition( ) {
		$condition = new InclusionQueryCondition( 'spam' , array( 'eggs', 'spam' ) );
		$this->assertSame( 'spam IN ( ? , ? )', $condition->condition( ) );
	}

	public function testEvaluate( ) {
		$condition = new InclusionQueryCondition( 'spam' , array( 'eggs', 'spam' ) );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( 'eggs' ) ) );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( 'spam' ) ) );
		$this->assertFalse( $condition->evaluate( new EvaluateDummy( 'broccoli' ) ) );

		$condition = new InclusionQueryCondition( 'spam' , array( 1, 2, 3 ) );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( 1 ) ) );
		$this->assertFalse( $condition->evaluate( new EvaluateDummy( 4 ) ) );

		$condition = new InclusionQueryCondition( 'spam' , array( TRUE ) );
		$this->assertTrue( $condition->evaluate( new EvaluateDummy( TRUE ) ) );
		$this->assertFalse( $condition->evaluate( new EvaluateDummy( FALSE ) ) );
	}
}
?>
