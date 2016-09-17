<?php
class EvaluateDummy {
	private $value;
	public function __construct( $value ) {
		$this->value = $value;
	}

	public function spam( ) {
		return $this->value;
	}
}
?>
