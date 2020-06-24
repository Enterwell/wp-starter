( function( $, lodash, itsec, data ) {
	function getValue( obj, path ) {
		return path ? lodash.get( obj, path ) : obj;
	}

	function defaultCompare( a, b ) {
		return a === b;
	}

	// Based on redux-watch licensed MIT: https://github.com/jprichardson/redux-watch
	function watch( getState, objectPath, compare ) {
		compare = compare || defaultCompare;
		var currentValue = getValue( getState(), objectPath );
		return function w( fn ) {
			return function() {
				var newValue = getValue( getState(), objectPath );
				if ( !compare( currentValue, newValue ) ) {
					var oldValue = currentValue;
					currentValue = newValue;
					fn( newValue, oldValue, objectPath );
				}
			};
		};
	}

	$( function() {
		$( '.itsec-form-input--type-user-groups' ).multiselect( {
			selectAll: true,
		} );

		if ( itsec[ 'user-groups' ] && itsec[ 'user-groups' ][ 'api' ] && itsec[ 'user-groups' ][ 'api' ][ 'store' ] ) {
			var store = itsec[ 'user-groups' ][ 'api' ][ 'store' ];

			var watchMatchables = watch( data.select( 'ithemes-security/user-groups' ).getMatchables );
			var watchSettings = watch( function() {
				return store.getState().settings;
			} );

			store.subscribe( watchMatchables( function( currentValue, previousValue ) {
				if ( !data.select( 'core/data' ).hasFinishedResolution( 'ithemes-security/user-groups', 'getMatchables' ) ) {
					return;
				}

				$( '.itsec-form-input--type-user-groups' ).each( function() {
					var $el = $( this ),
						checked = $el.val() || [];

					var options = ( currentValue || [] )
						.sort( function( groupA, groupB ) {
							if ( groupA.type === groupB.type ) {
								return 0;
							}

							if ( groupA.type === 'user-group' ) {
								return -1;
							}

							if ( groupB.type === 'user-group' ) {
								return 1;
							}

							return 0;
						} )
						.map( function( userGroup ) {
							return {
								name   : userGroup.label,
								value  : userGroup.id,
								checked: checked.includes( userGroup.id ),
							};
						} );

					$el.multiselect( 'loadOptions', options );
				} );
			} ) );

			store.subscribe( watchSettings( lodash.debounce( function( currentValue, previousValue ) {
				const settings = data.select( 'ithemes-security/user-groups' ).getGroupsBySetting();

				for ( var module in settings ) {
					if ( !settings.hasOwnProperty( module ) ) {
						continue;
					}

					for ( var setting in settings[ module ] ) {
						if ( !settings[ module ].hasOwnProperty( setting ) ) {
							continue;
						}

						var groupIds = settings[ module ][ setting ];

						var $option = $( '.itsec-form-input--type-user-groups[data-module="' + module + '"][data-setting="' + setting + '"]' );
						var current = $option.val();

						if ( !lodash.isEqual( current, groupIds ) ) {
							$option.val( groupIds );
							$option.multiselect( 'reload' );
						}
					}
				}
			}, 100 ) ) );
		}
	} );
} )( jQuery, lodash, window[ 'itsec' ], window[ 'wp' ][ 'data' ] );
