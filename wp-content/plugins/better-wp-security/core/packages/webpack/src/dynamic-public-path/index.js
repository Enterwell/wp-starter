const path = require( 'path' );

function DynamicPublicPathPlugin( propertyName ) {
	this.propertyName = propertyName;
}

DynamicPublicPathPlugin.prototype.apply = function( compiler ) {
	compiler.hooks.thisCompilation.tap( 'dynamic-public-path', ( compilation ) => {
		compilation.hooks.normalModuleLoader.tap( 'dynamic-public-path', ( loaderContext ) => {
			const entryFiles = [];
			Object.values( compiler.options.entry ).forEach( function( entry ) {
				entryFiles.push( path.join( compiler.options.context, entry ) );
			} );

			loaderContext[ 'dynamic-public-path' ] = {
				propertyName: this.propertyName,
				entryFiles,
			};
		} );
	} );
};

DynamicPublicPathPlugin.loader = require.resolve( './loader.js' );

module.exports = DynamicPublicPathPlugin;
