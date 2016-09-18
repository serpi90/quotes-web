<?php
class Quote {
	private $author;
	private $id;
	private $number;
	private $phrase;
	private $registrationTime;

	public function __construct( $id, $number, $registrationTime, $phrase, $author ) {
		$this->author = $this->validateType( 'Person', $author, 'author' );
		$this->id = $this->validateType( 'integer', $id, 'id' );
		$this->number = $this->validateType( 'integer', $number, 'number' );
		$this->phrase = $this->validateType( 'string', $phrase, 'phrase' );
		$this->registrationTime = $this->validateType( 'DateTime', $registrationTime, 'registrationTime' );
	}

	public function author( ) {
		return $this->author;
	}

	public function id( ) {
		return $this->id;
	}

	public function number( ) {
		return $this->number;
	}

	public function phrase( ) {
		return $this->phrase;
	}

	public function registrationTime( ) {
		return $this->registrationTime;
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