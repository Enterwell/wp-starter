( function( $, wp, config ) {
	$( function() {
		$( '.itsec-notice .notice-dismiss' ).off( 'click.wp-dismiss-notice' );

		$( document ).on( 'click', '.itsec-notice .notice-dismiss', function( e ) {
			const $this = $( this ),
				$notice = $this.parent( '.itsec-notice' );

			$notice.fadeTo( 100, 0, function() {
				$notice.slideUp( 100 );
			} );

			ajax( $notice, $notice.data( 'close' ) );
		} );

		$( document ).on( 'click', '.itsec-notice [data-action]', function( e ) {
			const $this = $( this ),
				$notice = $this.parent( '.itsec-notice' );

			$this.prop( 'disabled', true );

			ajax( $notice, action ).always( function() {
				$this.prop( 'disabled', false );
			} );
		} );
	} );

	function ajax( $notice, action ) {
		return wp.ajax.post( 'itsec-admin-notice', {
			itsec_action: action,
			notice_id   : $notice.data( 'id' ),
			nonce       : config.nonce,
		} )
			.done( function() {
				if ( $notice.css( 'opacity' ) !== '1' ) {
					if ( $notice.css( 'opacity' ) === '0' ) {
						$notice.remove();
					} else {
						setTimeout( function() {
							$notice.remove();
						}, 100 );
					}
				} else {
					$notice.fadeTo( 100, 0, function() {
						$notice.slideUp( 100, function() {
							$notice.remove();
						} );
					} );
				}
			} )
			.fail( function( response ) {
				if ( response.message ) {
					alert( response.message );
				} else if ( Array.isArray( response ) ) {
					const messages = [];

					for ( let i = 0; i < response.length; i++ ) {
						messages.push( response[ i ].message );
					}

					alert( messages.join( ' ' ) );
				} else {
					alert( 'An unexpected error occurred.' );
				}

				if ( $notice.css( 'opacity' ) !== '1' ) {
					$notice.slideDown( 100, function() {
						$notice.fadeTo( 100, 1 );
					} );
				}
			} );
	}
} )( jQuery, wp, window[ 'ITSECAdminNotices' ] );
