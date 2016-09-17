<?php
class QuoteDraft {
	private $phrase;
	private $author;
	
	public function __construct( $phrase, $author ) {
		$this->phrase = $this->validateType( 'string', $phrase, 'phrase' );
		if( $this->phrase === '' ) {
			throw new InvalidArgumentException( 'phrase can not be empty.' );
		}
		$this->author = $this->validateType( 'Person', $author, 'author' );
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