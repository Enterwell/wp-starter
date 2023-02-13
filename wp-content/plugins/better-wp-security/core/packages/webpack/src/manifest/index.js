const crypto = require( 'crypto' );
const path = require( 'path' );
const spawn = require( 'child_process' ).spawnSync;

function generate( seed, files ) {
	const manifest = {};
	const splitChunks = [];

	for ( const file of files ) {
		if ( ! file.chunk || ! file.chunk.name ) {
			continue;
		}

		if ( ! manifest[ file.chunk.name ] ) {
			manifest[ file.chunk.name ] = generateChunk( file.chunk );
		}

		manifest[ file.chunk.name ].files.push( file.name );

		if ( ! file.chunk.hasRuntime() ) {
			splitChunks.push( file );
		}
	}

	for ( const file of splitChunks ) {
		file.chunk.groupsIterable.forEach( ( group ) => {
			if ( manifest[ group.name ] && ! manifest[ group.name ].vendors.includes( file.chunk.name ) ) {
				manifest[ group.name ].vendors.push( file.chunk.name );
			}
		} );
	}

	return manifest;
}

/**
 * Generate a chunk manifest entry.
 *
 * @param {Chunk} chunk
 * @return {{runtime: boolean, vendors: Array, hash: string, dependencies: Array}} Manifest object.
 */
function generateChunk( chunk ) {
	const chunkManifest = {
		runtime: chunk.hasRuntime(),
		files: [],
		hash: crypto.createHash( 'md4' ).update( JSON.stringify( chunk.contentHash ) ).digest( 'hex' ),
		contentHash: chunk.contentHash,
		vendors: [],
		dependencies: [],
	};

	chunk.getModules().forEach( ( module ) => {
		if ( module.external && module.userRequest ) {
			if ( module.userRequest.includes( '@wordpress/' ) ) {
				chunkManifest.dependencies.push( `wp-${ module.userRequest.replace( '@wordpress/', '' ) }` );
			} else {
				chunkManifest.dependencies.push( module.userRequest );
			}
		}
	} );

	chunkManifest.dependencies.sort();

	return chunkManifest;
}

function serialize( data ) {
	const out = spawn( 'php', [
		path.resolve( __dirname, 'json-to-php.php' ),
		JSON.stringify( data ),
	] );

	if ( out.status !== 0 ) {
		throw Error( 'Failed to generate PHP manifest.' );
	}

	return `<?php return ${ out.stdout };`;
}

module.exports = { generate, serialize };
