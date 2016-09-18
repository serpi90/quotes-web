<?php
class ManualAuthorizationPolicy extends AuthorizationPolicy {
	public function isAuthorized( $quoteDraft ) {
		return FALSE;
	}
}
?>