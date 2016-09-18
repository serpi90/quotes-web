<?php
abstract class AuthorizationPolicy {
	public abstract function isAuthorized( $quoteDraft );
}
?>