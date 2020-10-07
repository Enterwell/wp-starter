'use strict';

( function( $ ) {
	var itsecSiteScanner = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			$( document ).on( 'click', '#itsec-site-scanner-start', this.startScan );
			$( document ).on( 'click', '.itsec-site-scan-toggle-details', this.toggleDetails );
		},

		toggleDetails: function( e ) {
			e.preventDefault();

			var $container = $( this ).parents( '.itsec-site-scan-results-section' );
			var $details = $container.find( '.itsec-site-scan__details' );

			if ( $details.is( ':visible' ) ) {
				$( this ).text( wp.i18n.__( 'Show Details', 'better-wp-security' ) ).attr( 'aria-expanded', false );
				$details.hide();
			} else {
				$( this ).text( wp.i18n.__( 'Hide Details', 'better-wp-security' ) ).attr( 'aria-expanded', true );
				$details.show();
			}
		},

		startScan: function( e ) {
			e.preventDefault();

			$( this ).prop( 'disabled', true ).val( wp.i18n.__( 'Scanning...', 'better-wp-security' ) );
			itsecUtil.sendWidgetAJAXRequest( 'site-scanner', { action: 'run-scan' }, itsecSiteScanner.handleResponse );
		},

		handleResponse: function( results ) {
			$( '#itsec-site-scanner-start' ).hide();
			var $wrapper = $( '.itsec-site-scanner-scan-results-wrapper' );

			if ( results.response && results.response.length ) {
				$wrapper.html( results.response );
			}

			itsecUtil.displayNotices( results, $wrapper, true );
		},
	};

	$( document ).ready( function() {
		itsecSiteScanner.init();
	} );
} )( jQuery );
