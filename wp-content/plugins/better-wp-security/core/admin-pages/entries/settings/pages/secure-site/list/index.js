/**
 * External Dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Children } from '@wordpress/element';

/**
 * iThemes Dependencies
 */
import { List, ListItem, Text } from '@ithemes/ui';

/**
 * Internal Dependencies
 */
import { CheckMark } from '@ithemes/security-style-guide';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';

const StyledList = styled( List )`
	display: grid;
	grid-template-columns: 1fr;
	gap: 0 1rem;
	
	@media (min-width: ${ ( { theme } ) => theme.breaks.medium }px ) {
		grid-template-columns: ${ ( { children } ) => Children.count( children ) > 3 && 'repeat(2, 1fr)' };
	}
`;

const improvements = [
	{
		text: __( 'User security strengthened', 'better-wp-security' ),
		activeModules: [ 'two-factor', 'passwordless-login', 'fingerprinting' ],
	},
	{
		text: __( 'Brute force attacks blocked', 'better-wp-security' ),
		activeModules: [ 'brute-force', 'network-brute-force', 'recaptcha' ],
	},
	{
		text: __( 'Scanning for vulnerable themes, plugins, and known malware', 'better-wp-security' ),
		activeModules: [ 'malware-scheduling' ],
	},
	{
		text: __( 'Monitoring for suspicious file changes', 'better-wp-security' ),
		activeModules: [ 'file-change' ],
	},
	{
		text: __( 'Banning bad bots and user agents', 'better-wp-security' ),
		activeModules: [ 'ban-users' ],
	},
];

export default function ImprovementsList() {
	const { allActiveModules } = useSelect( ( select ) => ( {
		allActiveModules: select( MODULES_STORE_NAME ).getActiveModules(),
	} ), [] );
	const enabledImprovements = improvements
		.filter( ( { activeModules } ) => activeModules.find(
			( activeModule ) => allActiveModules.includes( activeModule )
		) );

	if ( ! enabledImprovements.length ) {
		return null;
	}

	return (
		<>
			<Text text={ __( 'Here are some notable improvements…', 'better-wp-security' ) } />
			<StyledList
				icon={ CheckMark }
				iconSize={ 20 }
				gap={ 4 }
			>
				{ enabledImprovements.map( ( { text }, i ) => <ListItem key={ i } text={ text } /> ) }
			</StyledList>
		</>
	);
}
