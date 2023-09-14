/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

const NO_HELP = [];

export function getHelp( state, topic ) {
	return state.byTopic[ topic ] || NO_HELP;
}

export const isEnabled = createRegistrySelector( ( select ) => () =>
	select( 'ithemes-security/modules' ).getSettings( 'global' )
		?.enable_remote_help
);
