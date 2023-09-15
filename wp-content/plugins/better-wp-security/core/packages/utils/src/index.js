/**
 * External dependencies
 */
import { get, isPlainObject, cloneDeep, pick } from 'lodash';

/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';
import { addQueryArgs, getQueryArgs, removeQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import WPError from './wp-error';
import ErrorResponse from './error-response';
import getParamHistory from './param-history';

export { WPError, getParamHistory };
export Result from './result';

export const GlobalNavigationContext = createContext( {
	getUrl( page ) {
		page = page === 'settings' ? 'itsec' : 'itsec-' + page;
		const href = removeQueryArgs(
			document.location.href,
			...Object.keys( getQueryArgs( document.location.href ) )
		);

		return addQueryArgs( href, { page } );
	},
} );

export function useGlobalNavigationUrl( page ) {
	const { getUrl } = useContext( GlobalNavigationContext );

	return getUrl( page );
}

export function makeUrlRelative( baseUrl, target ) {
	let rel = target.replace( baseUrl, '' );

	if ( rel.charAt( 0 ) !== '/' ) {
		rel = '/' + rel;
	}

	return rel;
}

export function shortenNumber( number ) {
	if ( number <= 999 ) {
		return number.toString();
	}

	if ( number <= 9999 ) {
		const dec = number / 1000,
			fixed = dec.toFixed( 1 );

		if ( fixed.charAt( fixed.length - 1 ) === '0' ) {
			return fixed.replace( '.0', 'k' );
		}

		return `${ fixed }k`;
	}

	if ( number <= 99999 ) {
		return number.toString().substring( 0, 2 ) + 'k';
	}

	if ( number <= 999999 ) {
		return number.toString().substring( 0, 3 ) + 'k';
	}

	if ( number <= 9999999 ) {
		const dec = number / 1000000,
			fixed = dec.toFixed( 1 );

		if ( fixed.charAt( fixed.length - 1 ) === '0' ) {
			return fixed.replace( '.0', 'm' );
		}

		return `${ fixed }m`;
	}

	if ( number <= 99999999 ) {
		return number.toString().substring( 0, 2 ) + 'm';
	}

	if ( number <= 999999999 ) {
		return number.toString().substring( 0, 3 ) + 'm';
	}

	if ( number <= 9999999999 ) {
		const dec = number / 1000000000,
			fixed = dec.toFixed( 1 );

		if ( fixed.charAt( fixed.length - 1 ) === '0' ) {
			return fixed.replace( '.0', 'b' );
		}

		return `${ fixed }b`;
	}

	return number;
}

/**
 * Is the given value likely a WP Error object.
 *
 * @param {*} object
 * @return {boolean} Whether it was an error.
 */
export function isWPError( object ) {
	if ( ! isPlainObject( object ) ) {
		return false;
	}

	const keys = Object.keys( object );

	if ( keys.length !== 2 ) {
		return false;
	}

	return keys.includes( 'errors' ) && keys.includes( 'error_data' );
}

export function isApiError( object ) {
	if ( ! isPlainObject( object ) ) {
		return false;
	}

	const keys = Object.keys( object );

	if ( keys.length !== 3 && keys.length !== 4 ) {
		return false;
	}

	if ( keys.length === 4 && ! keys.includes( 'additional_errors' ) ) {
		return false;
	}

	return (
		keys.includes( 'code' ) &&
		keys.includes( 'message' ) &&
		keys.includes( 'data' )
	);
}

/**
 * Cast to a WPError instance.
 *
 * @param {*} object
 * @return {WPError} WPError instance.
 */
export function castWPError( object ) {
	if ( object instanceof WPError ) {
		return object;
	}

	if ( isWPError( object ) ) {
		return WPError.fromPHPObject( object );
	}

	if ( isApiError( object ) ) {
		return WPError.fromApiError( object );
	}

	return new WPError();
}

/**
 * Convert an entries iterator to an object.
 *
 * @param {Iterable} entries
 *
 * @return {Object} Object with entry[0] as the key and entry[1] as the value.
 */
export function entriesToObject( entries ) {
	const obj = {};

	for ( const [ key, val ] of entries ) {
		obj[ key ] = val;
	}

	return obj;
}

/**
 * Splits a list into two arrays, with items that pass the filter in the first array, and ones that fail in the second.
 *
 * @param {Array}    array
 * @param {Function} filter
 * @return {Array<Array>} Split array.
 */
export function bifurcate( array, filter ) {
	const bifurcated = [ [], [] ];

	for ( const value of array ) {
		bifurcated[ filter( value ) ? 0 : 1 ].push( value );
	}

	return bifurcated;
}

/**
 * Convert a response object from @wordpress/apiFetch to an Error object.
 *
 * @param {Object} response
 */
export function responseToError( response ) {
	if ( response instanceof Error ) {
		throw response;
	}

	throw new ErrorResponse( response );
}

export const MYSTERY_MAN_AVATAR =
	'https://secure.gravatar.com/avatar/d7a973c7dab26985da5f961be7b74480?s=96&d=mm&f=y&r=g';

/**
 * Gets a targetHint from an object.
 *
 * @param {Object}  object
 * @param {string}  header
 * @param {boolean} undefinedIfEmpty
 * @return {Array<string>|undefined} The target hint value.
 */
export function getTargetHint( object, header, undefinedIfEmpty = true ) {
	return get(
		object,
		[ '_links', 'self', 0, 'targetHints', header ],
		undefinedIfEmpty ? undefined : []
	);
}

/**
 * Get the "self" link for a REST API object.
 *
 * @param {Object} object
 * @return {string|undefined} The self link.
 */
export function getSelf( object ) {
	return getLink( object, 'self' );
}

/**
 * Get the href for a link with the given relation.
 *
 * @param {Object} object
 * @param {string} rel
 * @return {string|undefined} The link.
 */
export function getLink( object, rel ) {
	return get( object, [ '_links', rel, 0, 'href' ] );
}

/**
 * Get a link from a schema document.
 *
 * @param {Object} schema
 * @param {string} rel
 *
 * @return {Object|undefined} The schema link.
 */
export function getSchemaLink( schema, rel ) {
	if ( ! schema || ! schema.links ) {
		return;
	}

	for ( const link of schema.links ) {
		if ( link.rel === rel ) {
			return link;
		}
	}
}

/**
 * Modifies a schema by its ui schema.
 *
 * This will remove any hidden fields from the actual schema document.
 *
 * @param {Object} schema
 * @param {Object} uiSchema
 * @return {Object} The modified schema.
 */
export function modifySchemaByUiSchema( schema, uiSchema ) {
	if ( schema.type !== 'object' ) {
		return schema;
	}

	let modified;

	for ( const property in uiSchema ) {
		if ( ! uiSchema.hasOwnProperty( property ) ) {
			continue;
		}

		if ( uiSchema[ property ][ 'ui:widget' ] === 'hidden' ) {
			if ( ! modified ) {
				modified = cloneDeep( schema );
			}

			delete modified.properties[ property ];
		}
	}

	return modified || schema;
}

/**
 * Transform an API error to a list of messages.
 *
 * @param {Object} error
 * @return {string[]} The list of error messages.
 */
export function transformApiErrorToList( error ) {
	let messages = [];

	if ( ! error ) {
		return messages;
	}

	const wpError =
		error instanceof WPError
			? error
			: castWPError( pick( error, [ 'code', 'message', 'data' ] ) );

	if ( wpError.getErrorCode() === 'rest_invalid_param' ) {
		messages = Object.values( wpError.getErrorData().params );
	}

	return [ ...wpError.getAllErrorMessages(), ...messages ];
}
