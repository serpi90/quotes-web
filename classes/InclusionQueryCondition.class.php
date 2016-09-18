<?php
class InclusionQueryCondition extends QueryCondition {
	private $left;
	private $arguments;
	private $bindings;

	public function __construct( $left, $arguments ) {
		$this->left = $left;
		if( ! is_array( $arguments ) ) {
			throw new InvalidArgumentException( "\$arguments: $arguments should be an array but is " . gettype( $arguments ) . '.' );
		}
		$this->arguments = $arguments;
		$this->bindings = '';
		foreach ( $arguments as $argument ) {
			$this->bindings = $this->bindings . $this->bindType( $argument );
		}
	}

	public function arguments( ) {
		return $this->arguments;
	}

	public function bindings(  ) {
		return $this->bindings;
	}

	public function condition( ) {
		return $this->left . ' IN ( ' . implode( array_fill( 0, count( $this->arguments ), '?' ), ' , ') . ' )' ;
	}

	public function evaluate( $object ) {
		return count( array_intersect( array( call_user_func( array( $object, $this->left ) ) ), $this->arguments ) ) > 0;
	}
}
?>