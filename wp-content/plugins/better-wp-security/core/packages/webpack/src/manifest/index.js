const crypto = require( 'crypto' );
const path = require( 'path' );
const spawn = require( 'child_process' ).spawnSync;

function generate( seed, files ) {
	const manifest = {};
	const splitChunks = [];

	for ( const file of files ) {
		if ( ! file.isChunk ) {
			continue;
		}

		if ( ! file.chunk.hasRuntime() ) {
			splitChunks.push( file );
		}

		const name = file.chunk.name || file.chunk.id.toString();

		if ( ! manifest[ name ] ) {
			manifest[ name ] = generateChunk( file.chunk );
		}

		manifest[ name ].files.push( file.name );
	}

	for ( const file of splitChunks ) {
		file.chunk.groupsIterable.forEach( ( group ) => {
			const name = file.chunk.name || file.chunk.id.toString();

			if (
				manifest[ group.name ] &&
				! manifest[ group.name ].vendors.includes( name )
			) {
				manifest[ group.name ].vendors.push( name );
			}
		} );
	}

	return manifest;
}

/**
 * Generate a chunk manifest entry.
 *
 * @param {Object} chunk The webpack chunk.
 * @return {{runtime: boolean, vendors: Array, hash: string, dependencies: Array}} Manifest object.
 */
function generateChunk( chunk ) {
	const chunkManifest = {
		runtime: chunk.hasRuntime(),
		files: [],
		hash: crypto
			.createHash( 'md4' )
			.update( JSON.stringify( chunk.contentHash ) )
			.digest( 'hex' ),
		contentHash: chunk.contentHash,
		vendors: [],
		dependencies: [],
	};

	chunk.getModules().forEach( ( module ) => {
		if ( module.userRequest && module.externalType ) {
			if ( module.userRequest.includes( '@wordpress/' ) ) {
				chunkManifest.dependencies.push(
					`wp-${ module.userRequest.replace( '@wordpress/', '' ) }`
				);
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
