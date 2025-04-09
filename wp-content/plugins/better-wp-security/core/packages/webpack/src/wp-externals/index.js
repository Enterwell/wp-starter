function camelCaseDash( string ) {
	return string.replace( /-([a-z])/g, ( match, letter ) =>
		letter.toUpperCase()
	);
}

const formatRequest = ( request ) => {
	// '@wordpress/apiFetch' -> [ '@wordpress', 'api-fetch' ]
	const [ , name ] = request.split( '/' );

	// { this: [ 'wp', 'apiFetch' ] }
	return {
		this: [ 'wp', camelCaseDash( name ) ],
	};
};

const wpPackages = ( { request }, callback ) => {
	if ( /^@wordpress\//.test( request ) && request !== '@wordpress/icons' ) {
		callback( null, formatRequest( request ), 'this' );
	} else {
		callback();
	}
};

const externals = Object.freeze( [
	{
		react: 'React',
		'react-dom': 'ReactDOM',
		tinymce: 'tinymce',
		moment: 'moment',
		jquery: 'jQuery',
		lodash: 'lodash',
		'lodash-es': 'lodash',
	},
	wpPackages,
] );

module.exports = externals;
