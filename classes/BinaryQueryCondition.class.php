<?php
class BinaryQueryCondition extends QueryCondition {

	private $operation;
	private $left;
	private $right;
	private $rightBinding;
	
	public function __construct( $left, $operation, $right ) {
		$this->operation = $this->validatedOperation( $operation );
		$this->left = $left;
		$this->right = $right;
		$this->rightBinding = $this->bindType( $right );
	}

	public function bindings(  ) {
		return $this->rightBinding;
	}

	public function arguments( ) {
		return array( $this->right );
	}

	public function condition( ) {
		return $this->left . ' ' .  $this->operation . ' ?';
	}
	
	public function evaluate( $object ) {
		$value = call_user_func( array( $object, $this->left ) );
		switch( $this->operation ) {
			case '=':
				return $value == $this->right;
			case '<=':
				return $value <= $this->right;
			case '>=':
				return $value >= $this->right;
			case '<':
				return $value < $this->right;
			case '>':
				return $value > $this->right;
			default:
				// Should never reach: here, as this was validated on initialization.
				throw new UnexpectedValueException( "'{$this->operation}' is not a valid logic operator." );
		}
	}
	
	private function validatedOperation( $operation ) {
		switch( $operation ) {
			case '=':
			case '<=':
			case '>=':
			case '<':
			case '>':
				return $operation;
				break;
			default:
				throw new UnexpectedValueException( "'$operation' is not a valid logic operator." );
		}
	}
}
?>
