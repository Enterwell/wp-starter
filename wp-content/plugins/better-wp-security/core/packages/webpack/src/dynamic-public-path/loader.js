module.exports = function( source ) {
	const config = this[ 'dynamic-public-path' ];
	const resource = this.resource;

	if ( config.entryFiles.includes( resource ) && resource.match( /\.js$/ ) ) {
		source = `__webpack_public_path__ = window.${ config.propertyName };\n${ source }`;
	}

	return source;
};
