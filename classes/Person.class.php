<?php
class Person {
	private $active;
	private $aliases;
	private $id;
	private $name;

	public function __construct( $id, $name, $aliases, $active ) {
		$this->active = $this->validateType( 'boolean', $active, 'active' );
		$this->aliases = $this->validateType( 'array', $aliases, 'aliases' );
		foreach( $aliases as $alias ) {
			$this->validateType( 'string', $alias, 'alias' );
		}
		$this->id = $this->validateType( 'integer', $id, 'id' );
		$this->name = $this->validateType( 'string', $name, 'name' );
	}

	public function active( ) {
		return $this->active;
	}

	public function aliases( ) {
		return $this->aliases;
	}

	public function id( ) {
		return $this->id;
	}

	public function name( ) {
		return $this->name;
	}

	private function validateType( $type, $value, $name ) {
		$actualType = gettype( $value );
		if( $actualType == 'object' ) {
			$actualType = get_class( $value );
		}
		if( $actualType !== $type ) {
			$stringValue = var_export($value, true);
			throw new InvalidArgumentException( "{$name}: {$stringValue} should be of type {$type} but is {$actualType}.");
		} 
		return $value;
	}
}
?>