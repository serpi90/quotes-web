<?php
abstract class QueryCondition {
	abstract public function arguments();
	abstract public function bindings();
	abstract public function condition();
	abstract public function evaluate( $object );

	protected function bindType( $value ) {
		switch( gettype( $value ) ) {
		case 'boolean':
		case 'integer':
			return 'i';
		case 'double':
			return 'd';
		case 'string':
			return 's';
		default:
			throw new InvalidArgumentException('Can not bind lefts of type: '.gettype( $left ));
		}
	}
}
?>