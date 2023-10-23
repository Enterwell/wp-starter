/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { TabbedNavigation, NavigationTab } from '@ithemes/security-ui';
import { modulesStore } from '@ithemes/security.packages.data';

export default function Nav() {
	const { firewallRulesActive } = useSelect( ( select ) => ( {
		firewallRulesActive: select( modulesStore ).isActive( 'firewall' ),
	} ), [] );

	return (
		<TabbedNavigation>
			<NavigationTab to="/logs" title={ __( 'Logs' ) } />
			{ firewallRulesActive && (
				<NavigationTab to="/rules" title={ __( 'Rules' ) } />
			) }
		</TabbedNavigation>
	);
}

