<?php
class Person {
	private $id;
	private $name;
	private $active;
	private $aliases;
	
	public function __construct( $id, $name, $aliases, $active ) {
		$this->id = $this->validateType( 'integer', $id, 'id' );
		$this->name = $this->validateType( 'string', $name, 'name' );
		$this->aliases = $this->validateType( 'array', $aliases, 'aliases' );
		foreach( $aliases as $alias ) {
			$this->validateType( 'string', $alias, 'alias' );
		}
		$this->active = $this->validateType( 'boolean', $active, 'active' );
	}

	public function id( ) {
		return $this->id;
	}

	public function name( ) {
		return $this->name;
	}

	public function active( ) {
		return $this->active;
	}

	public function aliases( ) {
		return $this->aliases;
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