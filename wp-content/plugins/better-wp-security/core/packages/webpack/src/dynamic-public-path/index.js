const path = require( 'path' );
const { NormalModule } = require( 'webpack' );

function DynamicPublicPathPlugin( propertyName ) {
	this.propertyName = propertyName;
}

DynamicPublicPathPlugin.prototype.apply = function( compiler ) {
	compiler.hooks.thisCompilation.tap(
		'dynamic-public-path',
		( compilation ) => {
			NormalModule.getCompilationHooks( compilation ).loader.tap(
				'dynamic-public-path',
				( loaderContext ) => {
					const entryFiles = [];
					Object.values( compiler.options.entry ).forEach( function(
						entry
					) {
						entryFiles.push(
							path.join( compiler.options.context, entry.import[ 0 ] )
						);
					} );

					loaderContext[ 'dynamic-public-path' ] = {
						propertyName: this.propertyName,
						entryFiles,
					};
				}
			);
		}
	);
};

DynamicPublicPathPlugin.loader = require.resolve( './loader.js' );

module.exports = DynamicPublicPathPlugin;
