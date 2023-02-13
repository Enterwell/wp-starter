const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const ManifestPlugin = require( 'webpack-manifest-plugin' );
const FilterWarningsPlugin = require( 'webpack-filter-warnings-plugin' );
const CustomTemplatedPathPlugin = require( '../custom-templated-path-webpack-plugin' );
const DynamicPublicPath = require( '../dynamic-public-path' );
const StyleOnlyEntryPlugin = require( '../style-only-entry-plugin' );
const SplitChunkName = require( '../split-chunk-name' );
const { generate: generateManifest, serialize: serializeManifest } = require( '../manifest' );
const wpExternals = require( '../wp-externals' );
const debug = process.env.NODE_ENV !== 'production';
const glob = require( 'glob' );
const path = require( 'path' );
const autoprefixer = require( 'autoprefixer' );
const webpack = require( 'webpack' );

module.exports = function makeConfig( directory, pro ) {
	/*
	Convert the wildcard entry points into an entry object suitable for Webpack consumption.

	This requires an object where the key is the path to the destination file without a file extension
	and the value is the path to the source file.

	For Example:
	[ 'pro/dashboard/entries/dashboard.js' ]
	{ 'dashboard/dashboard': './pro/dashboard/entries/dashboard.js' }
	*/
	const entries = glob.sync( 'core/modules/**/entries/*.js' ).reduce( function( acc, entry ) {
		const baseName = path.basename( entry, '.js' );
		let out = path.join( entry, '..', '..', baseName );
		out = out.replace( /^core\/modules\//, '' );

		// The entry needs to be marked as relative to the current directory.
		acc[ out ] = './' + entry;

		return acc;
	}, {} );

	if ( pro ) {
		Object.assign( entries, glob.sync( 'pro/**/entries/*.js' ).reduce( function( acc, entry ) {
			const baseName = path.basename( entry, '.js' );
			let out = path.join( entry, '..', '..', baseName );
			out = out.replace( /^pro\//, '' );

			acc[ out ] = './' + entry;

			return acc;
		}, {} ) );
	}

	const config = {
		context: directory,
		devtool: debug ? 'inline-sourcemap' : false,
		mode: debug ? 'development' : 'production',
		entry: {
			...entries,
			'core/packages/components/site-scan-results/style': './core/packages/components/src/site-scan-results/style.scss',
			'packages/preload': './core/packages/preload/src/index.js',
		},
		output: {
			path: path.resolve( directory, 'dist' ),
			filename: debug ? '[name].js' : '[name].min.js',
			jsonpFunction: 'itsecWebpackJsonP',
			library: [ 'itsec', '[module]', '[entry]' ],
			libraryTarget: 'this',
		},
		externals: [
			...wpExternals,
			function( context, request, callback ) {
				if ( /^@ithemes\/security\./.test( request ) ) {
					const parts = request.split( '.' );
					const external = {
						this: [ 'itsec', parts[ 1 ], parts[ 2 ] ],
					};

					callback( null, external, 'this' );
				} else {
					callback();
				}
			},
		],
		module: {
			rules: [
				{ parser: { amd: false } },
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: [
						DynamicPublicPath.loader,
						{
							loader: 'babel-loader',
							options: {
								configFile: path.resolve( directory, './core/packages/webpack/src/babel.js' ),
							},
						},
					],
				},
				{
					test: /\.s?css$/,
					use: [
						MiniCssExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								url: false,
							},
						},
						{
							loader: 'postcss-loader',
							options: {
								plugins: [
									autoprefixer,
								],
							},
						},
						{
							loader: 'sass-loader',
							options: {
								outputStyle: debug ? 'nested' : 'compressed',
								sourceMap: debug ? 'inline' : false,
								includePaths: [
									path.resolve( directory, './core/packages/style-guide/src' ),
								],
							},
						},
					],
				},
				{
					test: /\.svg$/,
					exclude: /node_modules/,
					use: [
						{
							loader: 'svg-react-loader',
							query: {
								classIdPrefix: 'itsec-icon-[name]-[hash:5]__',
							},
						},
					],
				},
			],
		},
		plugins: [
			new MiniCssExtractPlugin( {
				filename: debug ? '[name].css' : '[name].min.css',
			} ),
			new FilterWarningsPlugin( {
				exclude: /mini-css-extract-plugin[^]*Conflicting order between:/,
			} ),
			new StyleOnlyEntryPlugin(),
			new DynamicPublicPath( 'itsecWebpackPublicPath' ),
			new CustomTemplatedPathPlugin( {
				entry( p, data ) {
					const parts = data.chunk.name.split( '/' );

					return parts[ 1 ];
				},
				module( p, data ) {
					const parts = data.chunk.name.split( '/' );

					return parts[ 0 ];
				},
			} ),
			new ManifestPlugin( {
				fileName: debug ? 'manifest-dev.php' : 'manifest.php',
				generate: generateManifest,
				serialize: serializeManifest,
			} ),
		],
		resolve: {
			modules: [
				path.resolve( directory, './' ),
				path.resolve( directory, './node_modules' ),
			],
			alias: {
				'@ithemes/security-utils': path.resolve( directory, './core/packages/utils/src/index.js' ),
				'@ithemes/security-style-guide': path.resolve( directory, './core/packages/style-guide/src/index.js' ),
				'@ithemes/security-hocs': path.resolve( directory, './core/packages/hocs/src/index.js' ),
				'@ithemes/security-components': path.resolve( directory, './core/packages/components/src/index.js' ),
				'@ithemes/security-i18n': path.resolve( directory, './core/packages/i18n/src/index.js' ),
				'@ithemes/security-data': path.resolve( directory, './core/packages/data/src/index.js' ),
				...Object.keys( entries ).reduce( function( acc, entry ) {
					const parts = entry.split( '/' );
					const alias = `@ithemes/security.${ parts[ 0 ] }.${ parts[ 1 ] }`;

					acc[ alias ] = path.resolve( directory, entries[ entry ] );

					return acc;
				}, {} ),
			},
		},
		optimization: {},
	};

	if ( ! debug ) {
		const splitChunkName = new SplitChunkName();

		config.optimization.splitChunks = {
			chunks: 'all',
			maxInitialRequests: 10,
			hidePathInfo: true,
			cacheGroups: {
				recharts: {
					test: /[\\/]node_modules[\\/](recharts)[\\/]/,
					name: 'vendors/recharts',
					enforce: true,
				},
			},
			name: splitChunkName.name.bind( splitChunkName ),
		};

		config.plugins.push( new webpack.HashedModuleIdsPlugin() );
	}

	return config;
};
