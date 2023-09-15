wp.domReady( function() {
	let permission = document.getElementById( 'itsec-app-password-rest_api_permissions' );
	let permissionLabel = document.querySelector( 'label[for="itsec-app-password-rest_api_permissions"]' );
	let enabledFor = document.getElementById( 'itsec-app-password-enabled_for' );

	enabledFor.addEventListener( 'change', function() {
		if ( 'xml-rpc' === enabledFor.value ) {
			hidePermissions();
		} else {
			showPermissions();
		}
	} );

	wp.hooks.addFilter( 'wp_application_passwords_new_password_request', 'ithemes-security/application-passwords/new-password', function( request ) {
		request.rest_api_permissions = permission.checked ? 'read' : 'write';

		let selectedEnabledFor = enabledFor.value;

		if ( 'all' === selectedEnabledFor ) {
			request.enabled_for = [ 'rest-api', 'xml-rpc' ];
		} else {
			request.enabled_for = [ selectedEnabledFor ];
		}

		return request;
	} );

	wp.hooks.addAction( 'wp_application_passwords_created_password', 'ithemes-security/application-passwords/clear-password', function() {
		permission.checked = false;
		enabledFor.value = 'all';
		showPermissions();
	} );

	function showPermissions() {
		permission.style.display = 'block';
		permissionLabel.style.display = 'block';
	}

	function hidePermissions() {
		permission.style.display = 'none';
		permissionLabel.style.display = 'none';
	}
} );
