( function( $ ) {
	function writeToFile( type, $el ) {
		const module = type + '-config-rules';

		itsecUtil.sendModuleAJAXRequest( module, { method: 'flush' }, function( r ) {
			if ( r.messages.length > 0 ) {
				itsecSettingsPage.showMessages( r.messages, module, 'open', 'success' );
			}

			if ( r.errors.length > 0 ) {
				itsecSettingsPage.showErrors( r.errors, module, 'open', 'error' );
			}

			itsecSettingsPage.scrollTop();
			$el.removeAttr( 'disabled' );
		} );
	}

	$( document ).on( 'click', '.itsec-file-writing-flush', function() {
		const $el = $( this );
		const type = $el.data( 'type' );

		if ( type ) {
			$el.attr( 'disabled', true );
			writeToFile( type, $el );
		}
	} );
} )( jQuery );
