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
			<NavigationTab to="/logs" title={ __( 'Logs', 'better-wp-security' ) } />
			{ firewallRulesActive && (
				<NavigationTab to="/rules" title={ __( 'Rules', 'better-wp-security' ) } />
			) }
			<NavigationTab to="/ip-management" title={ __( 'IP Management', 'better-wp-security' ) } />
			<NavigationTab to="/configure" title={ __( 'Configure', 'better-wp-security' ) } />
			<NavigationTab to="/automated" title={ __( 'Automated', 'better-wp-security' ) } />
		</TabbedNavigation>
	);
}

