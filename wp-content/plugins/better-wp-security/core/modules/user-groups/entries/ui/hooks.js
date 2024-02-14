/**
 * External dependencies
 */
import Ajv from 'ajv';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { store as userGroupsStore } from '@ithemes/security.user-groups.api';

function getAjv() {
	if ( ! getAjv.instance ) {
		getAjv.instance = new Ajv( { schemaId: 'id' } );
		getAjv.instance.addMetaSchema(
			require( 'ajv/lib/refs/json-schema-draft-04.json' )
		);
	}

	return getAjv.instance;
}

export function useSettingsDefinitions( filters = {} ) {
	const ajv = getAjv();

	return useSelect(
		( select ) =>
			select( userGroupsStore ).getSettingDefinitions(
				ajv,
				filters
			),
		[ ajv, filters ]
	);
}
