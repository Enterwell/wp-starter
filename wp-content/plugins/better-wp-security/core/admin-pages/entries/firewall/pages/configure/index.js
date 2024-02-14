/**
 * External dependencies
 */
import { NavLink } from 'react-router-dom';
import { sortBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

/**
 * SolidWP dependencies
 */
import {
	SecondaryNavigation,
	SecondaryNavigationItem,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { withNavigate } from '@ithemes/security-hocs';
import { StyledSnackbarList } from './styles';
import { Page, ModuleSettings } from '../../components';

export default function Configure() {
	const { removeNotice } = useDispatch( noticesStore );
	const { modules, snackbarNotices } = useSelect( ( select ) => ( {
		modules: select( MODULES_STORE_NAME ).getModules(),
		snackbarNotices: select( noticesStore ).getNotices( 'ithemes-security' ),
	} ), [] );

	const firewallModules = sortBy( modules.filter( ( maybeModule ) => maybeModule.type === 'lockout' ), 'order' );

	return (
		<Page>
			<StyledSnackbarList
				notices={ snackbarNotices }
				onRemove={
					( id ) => removeNotice( id, 'ithemes-security' )
				}
			/>
			<Flex gap={ 5 } align="start" >
				<SecondaryNavigation
					orientation="vertical"
				>
					<NavLink
						key="global"
						to="/configure/global"
						component={ withNavigate( SecondaryNavigationItem ) }
					>
						{ __( 'Global Settings', 'better-wp-security' ) }
					</NavLink>
					{ firewallModules.map( ( firewallModule ) => (
						<NavLink
							key={ firewallModule.id }
							to={ '/configure/' + firewallModule.id }
							component={ withNavigate( SecondaryNavigationItem ) }
						>
							{ firewallModule.title }
						</NavLink>
					) ) }
				</SecondaryNavigation>
				<ModuleSettings />
			</Flex>
		</Page>
	);
}

