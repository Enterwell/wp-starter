function StyleOnlyEntryPlugin( styleTests ) {
	let list = [];

	if ( styleTests instanceof RegExp ) {
		list.push( styleTests );
	} else if ( Array.isArray( styleTests ) && styleTests.length ) {
		list = styleTests;
	} else {
		list = [ /\.s?css$/ ];
	}

	Object.assign( this, {
		styleTests: list,
	} );
}

StyleOnlyEntryPlugin.prototype.isFileStyle = function( file ) {
	for ( const test of this.styleTests ) {
		if ( test.test( file ) ) {
			return true;
		}
	}

	return false;
};

StyleOnlyEntryPlugin.prototype.apply = function( compiler ) {
	compiler.hooks.emit.tap( 'style-only-entry-plugin', ( compilation ) => {
		for ( const chunk of compilation.chunks ) {
			if (
				chunk.entryModule &&
				this.isFileStyle( chunk.entryModule.userRequest )
			) {
				for ( const file of chunk.files ) {
					if ( ! this.isFileStyle( file ) ) {
						delete compilation.assets[ file ];
					}
				}
			}
		}
	} );
};

module.exports = StyleOnlyEntryPlugin;
