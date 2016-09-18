<?php
class AutomaticAuthorizationPolicy extends AuthorizationPolicy {
	public function isAuthorized( $quoteDraft ) {
		return TRUE;
	}
}
?>