<?php

function selfauthEndpoint() {

	return '
		<link rel="authorization_endpoint" href="' . rtrim( site()->url(), '/' ) . '/' . option( 'sgkirby.selfauth.endpoint', 'auth' ) . '" />
	';

}
