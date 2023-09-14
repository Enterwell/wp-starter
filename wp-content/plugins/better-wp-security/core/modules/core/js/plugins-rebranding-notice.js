( function( $, wp, config ) {
	$( function () {
		$( document ).on( 'click', '.itsec-plugin-rebranding-notice-dismiss', function ( e ) {
			let $plugin_notice = $( '.itsec-plugin-rebranding-tr' );
			wp.ajax.post( 'itsec_dismiss_rebranding_plugins_banner', {
				notice_id   	: 'plugin-rebranding',
				itsec_action 	: 'dismiss_plugin-rebranding',
				nonce       	: config.nonce,
			} )
				.done( function () {
					if ( $plugin_notice.css( 'opacity' ) !== '1' ) {
						if ( $plugin_notice.css( 'opacity' ) === '0' ) {
							$plugin_notice.remove();
						} else {
							setTimeout( function () {
								$plugin_notice.remove();
							}, 100 );
						}
					} else {
						$plugin_notice.fadeTo( 100, 0, function () {
							$plugin_notice.slideUp( 100, function () {
								$plugin_notice.remove();
							} )
						} )
					}
				} )
				.fail( function ( response ) {
					if ( response.message ) {
						alert( response.message );
					} else {
						alert( 'An unexpected error occurred.' );
					}
				} )
		} )
	} )
} )( jQuery, wp, window[ 'ITSECAdminNotices' ] );
