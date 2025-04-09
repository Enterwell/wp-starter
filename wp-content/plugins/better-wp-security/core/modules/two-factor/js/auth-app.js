wp.domReady( function() {
	let permission = document.getElementById( 'itsec-app-password-rest_api_permissions' );

	wp.hooks.addFilter( 'wp_application_passwords_approve_app_request', 'ithemes-security/application-passwords/new-password', function( request ) {
		request.rest_api_permissions = permission.checked ? 'read' : 'write';

		return request;
	} );
} );
