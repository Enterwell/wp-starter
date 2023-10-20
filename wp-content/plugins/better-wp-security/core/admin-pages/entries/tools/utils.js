/**
 * External dependencies
 */
import Ajv from 'ajv';

/**
 * Grabs a global instance of Ajv.
 *
 * @return {Ajv.Ajv} The ajv instance.
 */
export function getAjv() {
	if ( ! getAjv.instance ) {
		getAjv.instance = new Ajv( { schemaId: 'id' } );
		getAjv.instance.addMetaSchema(
			require( 'ajv/lib/refs/json-schema-draft-04.json' )
		);
		getAjv.instance.addFormat( 'html', {
			type: 'string',
			validate() {
				// Validating HTML isn't something we can realistically do.
				// We accept everything and can then kses it on the server.
				return true;
			},
		} );
	}

	return getAjv.instance;
}
