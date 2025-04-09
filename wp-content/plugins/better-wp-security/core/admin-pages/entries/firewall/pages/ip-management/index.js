/**
 * External dependencies
 */
import { NavLink, Route } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import {
	Heading,
	SecondaryNavigation,
	SecondaryNavigationItem,
	Text,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { Page } from '../../components';
import BannedIPs from '../../components/banned-ips';
import AuthorizeIPs from '../../components/authorize-ips';
import ActiveLockouts from '../../components/active-lockouts';
import { modulesStore } from '@ithemes/security.packages.data';
import { StyledPageHeader } from './styles';

export default function IPManagement() {
	const { banUsersActive } = useSelect( ( select ) => ( {
		banUsersActive: select( modulesStore ).isActive( 'ban-users' ),
	} ), [] );
	return (
		<Page>
			<StyledPageHeader>
				<Heading
					level={ 1 }
					weight={ TextWeight.NORMAL }
					text={ __( 'IP Management', 'better-wp-security' ) }
				/>
				<Text
					text={ __( 'Your one-stop for all things IP management. Ban troublesome IPs from wreaking havoc, ensure everyone who needs access to your site has it by authorizing IPs and manage temporarily locked out users.', 'better-wp-security' ) }
				/>
			</StyledPageHeader>
			<Flex gap={ 5 } align="start">
				<SecondaryNavigation
					orientation="vertical"
				>
					{ banUsersActive && (
						<NavLink
							key="ban-users"
							to="/ip-management/ban-users"
							component={ withNavigate( SecondaryNavigationItem ) }
						>
							{ __( 'Banned IPs', 'better-wp-security' ) }
						</NavLink>
					) }
					<NavLink
						key="active-lockouts"
						to="/ip-management/active-lockouts"
						component={ withNavigate( SecondaryNavigationItem ) }
					>
						{ __( 'Active Lockouts', 'better-wp-security' ) }
					</NavLink>
					<NavLink
						key="global"
						to="/ip-management/authorize-ips"
						component={ withNavigate( SecondaryNavigationItem ) }
					>
						{ __( 'Authorized IPs', 'better-wp-security' ) }
					</NavLink>
				</SecondaryNavigation>
				{ banUsersActive && (
					<Route
						path="/ip-management/ban-users"
						component={ BannedIPs }
					/>
				) }
				<Route
					path="/ip-management/active-lockouts"
					component={ ActiveLockouts }
				/>
				<Route
					path="/ip-management/authorize-ips"
					component={ AuthorizeIPs }
				/>
			</Flex>
		</Page>
	);
}

