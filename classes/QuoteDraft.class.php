<?php
class QuoteDraft {
	private $author;
	private $id;
	private $phrase;
	private $registrationTime;

	public function __construct( $phrase, $author, $registrationTime = NULL, $id = 0 ) {
		$this->author = $this->validateType( 'Person', $author, 'author' );
		$this->id = $this->validateType( 'integer', $id, 'id' );
		$this->phrase = $this->validateType( 'string', $phrase, 'phrase' );
		if( $this->phrase === '' ) {
			throw new InvalidArgumentException( 'phrase can not be empty.' );
		}
		if( $registrationTime === NULL ) {
			$registrationTime = new DateTime( );
		}
		$this->registrationTime = $this->validateType( 'DateTime', $registrationTime, 'registrationTime' );
	}

	public function author( ) {
		return $this->author;
	}

	public function id( ) {
		return $this->id;
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