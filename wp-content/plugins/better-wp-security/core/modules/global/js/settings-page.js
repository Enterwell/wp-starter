function itsec_change_show_error_codes( args ) {
	var show = args[ 0 ];

	if ( show ) {
		jQuery( 'body' ).addClass( 'itsec-show-error-codes' );
	} else {
		jQuery( 'body' ).removeClass( 'itsec-show-error-codes' );
	}
}

function itsec_change_write_files( args ) {
	var enabled = args[ 0 ];

	if ( enabled ) {
		jQuery( 'body' ).removeClass( 'itsec-write-files-disabled' ).addClass( 'itsec-write-files-enabled' );
	} else {
		jQuery( 'body' ).removeClass( 'itsec-write-files-enabled' ).addClass( 'itsec-write-files-disabled' );
	}
}

( function( $ ) {
	function logTypeChanged() {
		var type = jQuery( '#itsec-global-log_type' ).val();

		if ( 'both' === type ) {
			jQuery( '#itsec-global-log_rotation' ).parents( 'tr' ).show();
			jQuery( '#itsec-global-file_log_rotation' ).parents( 'tr' ).show();
			jQuery( '#itsec-global-log_location' ).parents( 'tr' ).show();
		} else if ( 'file' === type ) {
			jQuery( '#itsec-global-log_rotation' ).parents( 'tr' ).hide();
			jQuery( '#itsec-global-file_log_rotation' ).parents( 'tr' ).show();
			jQuery( '#itsec-global-log_location' ).parents( 'tr' ).show();
		} else {
			jQuery( '#itsec-global-log_rotation' ).parents( 'tr' ).show();
			jQuery( '#itsec-global-file_log_rotation' ).parents( 'tr' ).hide();
			jQuery( '#itsec-global-log_location' ).parents( 'tr' ).hide();
		}
	}

	function proxyHeaderChanged() {
		var proxy = $( '#itsec-global-proxy' ).val();

		if ( 'security-check' === proxy ) {
			$( '#itsec-global-ip-scan' ).show();
		} else {
			$( '#itsec-global-ip-scan' ).hide();
		}

		if ( 'manual' === proxy ) {
			$( '.itsec-global-proxy_header-container' ).show();
		} else {
			$( '.itsec-global-proxy_header-container' ).hide();
		}

		updateIp();
	}

	function updateIp() {
		$( '.itsec-global-detected-ip' ).text( itsec_global_settings_page.l10n.loading );
		var proxyType = $( '#itsec-global-proxy' ).val();
		var args = {};

		switch ( proxyType ) {
			case 'manual':
				args.header = $( '#itsec-global-proxy_header' ).val();
				break;
		}

		var data = {
			method: 'get-ip',
			proxy : proxyType,
			args  : args,
		};

		itsecUtil.sendModuleAJAXRequest( 'global', data, function( r ) {
			if ( r.errors.length > 0 ) {
				itsecSettingsPage.showErrors( r.errors, 'global', 'open', 'error' );
				itsecSettingsPage.scrollTop();
			}

			if ( r.response ) {
				$( '.itsec-global-detected-ip' ).text( r.response.ip_l10n );
			}
		} );
	}

	$( function() {
		$( document ).on( 'click', '#itsec-global-add-to-whitelist', function( e ) {
			e.preventDefault();

			var $whitelist = $( '#itsec-global-lockout_white_list' ),
				whitelist = $whitelist.val().trim();
			whitelist += '\n' + itsec_global_settings_page.ip;
			$whitelist.val( whitelist );
		} );

		$( document ).on( 'click', '#itsec-global-reset-log-location', function( e ) {
			e.preventDefault();

			$( '#itsec-global-log_location' ).val( itsec_global_settings_page.log_location );
		} );

		$( document ).on( 'click', '#itsec-global-ip-scan', function( e ) {
			e.preventDefault();

			var $btn = $( this ).attr( 'disabled', true );

			itsecUtil.sendModuleAJAXRequest( 'global', { method: 'scan-ip' }, function( r ) {
				if ( r.errors.length > 0 ) {
					itsecSettingsPage.showErrors( r.errors, 'global', 'open', 'error' );
					itsecSettingsPage.scrollTop();
				}

				if ( r.response ) {
					$( '.itsec-global-detected-ip' ).text( r.response.ip_l10n );
				}

				$btn.attr( 'disabled', false );
			} );
		} );

		$( document ).on( 'change', '#itsec-global-log_type', logTypeChanged );
		$( document ).on( 'change', '#itsec-global-proxy', proxyHeaderChanged );
		$( document ).on( 'change', '#itsec-global-proxy_header', updateIp );
		itsecSettingsPage.events.on( 'modulesReloaded', proxyHeaderChanged );
		itsecSettingsPage.events.on( 'moduleReloaded', function( e, module ) {
			if ( 'global' === module ) {
				proxyHeaderChanged();
			}
		} );

		logTypeChanged();
		proxyHeaderChanged();
	} );
} )( jQuery );
