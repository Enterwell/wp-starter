const LiveReloadPlugin = require( 'webpack-livereload-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const { WebpackManifestPlugin } = require( 'webpack-manifest-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const CustomTemplatedPathPlugin = require( '../custom-templated-path-webpack-plugin' );
const DynamicPublicPath = require( '../dynamic-public-path' );
const StyleOnlyEntryPlugin = require( '../style-only-entry-plugin' );
const {
	generate: generateManifest,
	serialize: serializeManifest,
} = require( '../manifest' );
const wpExternals = require( '../wp-externals' );
const glob = require( 'glob' );
const path = require( 'path' );
const fs = require( 'fs' );
const autoprefixer = require( 'autoprefixer' );

const debug = process.env.NODE_ENV !== 'production';

module.exports = function makeConfig( directory, pro ) {
	const useMinExt = pro && ! debug;

	/*
	Convert the wildcard entry points into an entry object suitable for Webpack consumption.

	This requires an object where the key is the path to the destination file without a file extension
	and the value is the path to the source file.

	For Example:
	[ 'pro/dashboard/entries/dashboard.js' ]
	{ 'dashboard/dashboard': './pro/dashboard/entries/dashboard.js' }
	*/
	const entries = glob
		.sync( 'core/modules/**/entries/*.js' )
		.reduce( function( acc, entry ) {
			const baseName = path.basename( entry, '.js' );
			let out = path.join( entry, '..', '..', baseName );
			out = out.replace( /^core\/modules\//, '' );

			// The entry needs to be marked as relative to the current directory.
			acc[ out ] = './' + entry;

			return acc;
		}, {} );

	Object.assign(
		entries,
		glob
			.sync( 'core/admin-pages/entries/*.js' )
			.reduce( function( acc, entry ) {
				const baseName = path.basename( entry, '.js' );
				const out = 'pages/' + baseName;

				// The entry needs to be marked as relative to the current directory.
				acc[ out ] = './' + entry;

				return acc;
			}, {} )
	);

	if ( pro ) {
		Object.assign(
			entries,
			glob.sync( 'pro/**/entries/*.js' ).reduce( function( acc, entry ) {
				const baseName = path.basename( entry, '.js' );
				let out = path.join( entry, '..', '..', baseName );
				out = out.replace( /^pro\//, '' );

				acc[ out ] = './' + entry;

				return acc;
			}, {} )
		);
	}

	entries[ 'packages/data' ] = './core/packages/data/src/index.js';

	const vendors = [
		// {
		// 	import: 'react-router-dom',
		// 	name: 'ReactRouterDOM',
		// 	files: [
		// 		'react-router-dom/umd/react-router-dom.js',
		// 		'react-router-dom/umd/react-router-dom.min.js',
		// 	],
		// },
	];

	const dist = path.resolve( directory, 'dist' );
	const config = {
		context: directory,
		devtool: debug ? 'inline-source-map' : false,
		mode: debug ? 'development' : 'production',
		entry: {
			...entries,
			'core/packages/components/site-scan-results/style':
				'./core/packages/components/src/site-scan-results/style.scss',
			'packages/preload': './core/packages/preload/src/index.js',
		},
		output: {
			path: dist,
			filename: ! useMinExt ? '[name].js' : '[name].min.js',
			chunkLoadingGlobal: 'itsecWebpackJsonP',
			library: {
				name: [ 'itsec', '[module]', '[entry]' ],
				type: 'window',
			},
		},
		externals: [
			vendors.reduce(
				( acc, vendor ) => ( {
					...acc,
					[ vendor.import ]: vendor.name,
				} ),
				{}
			),
			...wpExternals,
			function( { request }, callback ) {
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
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: [
						DynamicPublicPath.loader,
						{
							loader: 'babel-loader',
							options: {
								configFile: path.resolve(
									directory,
									'./core/packages/webpack/src/babel.config.json'
								),
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
								plugins: [ autoprefixer ],
							},
						},
						{
							loader: 'sass-loader',
							options: {
								additionalData: '@import "config.scss";',
								sourceMap: debug,
								sassOptions: {
									outputStyle: debug ? 'expanded' : 'compressed',
									includePaths: [
										path.resolve(
											directory,
											'./core/packages/style-guide/src'
										),
									],
								},
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
							options: {
								query: {
									classIdPrefix: 'itsec-icon-[name]-[hash:5]__',
								},
							},
						},
					],
				},
			],
		},
		plugins: [
			new LiveReloadPlugin(),
			new MiniCssExtractPlugin( {
				filename: ! useMinExt ? '[name].css' : '[name].min.css',
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
			new WebpackManifestPlugin( {
				fileName: debug ? 'manifest-dev.php' : 'manifest.php',
				generate: generateManifest,
				serialize: serializeManifest,
			} ),
			{
				apply: ( compiler ) => {
					compiler.hooks.afterEmit.tap( 'TouchManifest', () => {
						if ( debug ) {
							return;
						}

						const time = new Date();
						const file = path.join( directory, 'dist', 'manifest.php' );

						try {
							fs.utimesSync( file, time, time );
						} catch ( err ) {
							fs.closeSync( fs.openSync( file, 'w' ) );
						}
					} );
				},
			},
			vendors.length > 0 &&
				new CopyWebpackPlugin( {
					patterns: vendors.flatMap( ( vendor ) => [
						{
							from:
								'node_modules/' + vendor.files[ debug ? 0 : 1 ],
							to: path.resolve(
								dist,
								vendor.import +
									( ! useMinExt ? '.js' : '.min.js' )
							),
						},
						{
							from:
								'node_modules/' +
								vendor.files[ debug ? 0 : 1 ] +
								'.map',
							to: path.resolve(
								dist,
								vendor.import +
									( ! useMinExt ? '.js.map' : '.min.js.map' )
							),
						},
					] ),
				} ),
		].filter( ( plugin ) => !! plugin ),
		resolve: {
			modules: [ path.resolve( directory, './' ), 'node_modules' ],
			alias: {
				// Always load the same copy of @emotion/react to prevent issues with npm linking our UI library.
				'@emotion/react': path.resolve( directory, './node_modules/@emotion/react' ),
				'@ithemes/security-utils': path.resolve(
					directory,
					'./core/packages/utils/src/index.js'
				),
				'@ithemes/security-style-guide': path.resolve(
					directory,
					'./core/packages/style-guide/src/index.js'
				),
				'@ithemes/security-hocs': path.resolve(
					directory,
					'./core/packages/hocs/src/index.js'
				),
				'@ithemes/security-components': path.resolve(
					directory,
					'./core/packages/components/src/index.js'
				),
				'@ithemes/security-i18n': path.resolve(
					directory,
					'./core/packages/i18n/src/index.js'
				),
				'@ithemes/security-rjsf-theme': path.resolve(
					directory,
					'./core/packages/rjsf-theme/src/index.js'
				),
				'@ithemes/security-search': path.resolve(
					directory,
					'./core/packages/search/src/index.js'
				),
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

	config.optimization.runtimeChunk = 'single';
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
			routing: {
				test: /[\\/]node_modules[\\/](react-router-dom|react-router|use-query-params)[\\/]/,
				name: 'vendors/routing',
				enforce: true,
			},
		},
	};

	return config;
};
