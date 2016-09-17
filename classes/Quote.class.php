<?php
class Quote {
	private $id;
	private $number;
	private $date;
	private $phrase;
	private $author;
	
	public function __construct( $id, $number, $date, $phrase, $author ) {
		$this->id = $this->validateType( 'integer', $id, 'id' );
		$this->number = $this->validateType( 'integer', $number, 'number' );
		$this->registrationTime = $this->validateType( 'DateTime', $date, 'registrationTime' );
		$this->phrase = $this->validateType( 'string', $phrase, 'phrase' );
		$this->author = $this->validateType( 'Person', $author, 'author' );
	}

	public function id( ) {
		return $this->id;
	}
	
	public function number( ) {
		return $this->number;
	}
	
	public function registrationTime( ) {
		return $this->registrationTime;
	}
	
	public function phrase( ) {
		return $this->phrase;
	}

	public function author( ) {
		return $this->author;
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
