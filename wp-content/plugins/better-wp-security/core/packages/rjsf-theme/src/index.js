/**
 * External dependencies
 */
import { isObject, mapValues } from 'lodash';

/**
 * Internal dependencies
 */
import * as widgets from './widgets';
import * as fields from './fields';
import FieldTemplate from './field-template';
import ObjectFieldTemplate from './field-template/object';
import ErrorList from './error-list';

const theme = {
	FieldTemplate,
	ObjectFieldTemplate,
	ErrorList,
	widgets,
	fields,
};

export default theme;

export { RjsfFieldFill } from './slot-fill';

export function mapApiError( error ) {
	if (
		error.code === 'rest_invalid_param' &&
		isObject( error.data.params )
	) {
		return mapValues( error.data.params, ( pError ) => ( {
			__errors: [ pError ],
		} ) );
	}

	return {
		__errors: [ error.message ],
	};
}
