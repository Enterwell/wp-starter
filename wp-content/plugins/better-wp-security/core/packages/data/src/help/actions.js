/**
 * Internal dependencies
 */
import { fetch, parseFetchResponse, updateSettings } from '../controls';

export function* fetchHelp( topic ) {
	const url = `https://ithemes.com/wp-json/ithemes/v1/inline-help/itsec/${ encodeURIComponent(
		topic
	) }`;
	const response = yield fetch( url, {
		credentials: 'omit',
		referrer: 'no-referrer',
	} );

	if ( ! response.ok ) {
		return;
	}

	if ( response.status >= 400 ) {
		return;
	}

	const help = yield parseFetchResponse( response );
	yield { type: RECEIVE_HELP, topic, help };
}

export function* enableHelp( enabled = true ) {
	yield updateSettings( 'global', { enable_remote_help: enabled } );
}

export const RECEIVE_HELP = 'RECEIVE_HELP';
