/**
 * External dependencies
 */
import { Router, Switch, Route } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import {
	SlotFillProvider,
	Popover,
} from '@wordpress/components';
import { PluginArea } from '@wordpress/plugins';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

/**
 * SolidWP dependencies
 */
import {
	solidTheme,
	Surface,
	SurfaceVariant,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { TopToolbar } from '@ithemes/security-ui';
import UserGroupNotice from './components/user-groups-notice';
import UserSecurityHeader from './components/user-security-header';
import UserSecurityTableHeader from './components/user-security-table-header';
import UserSecurityTable from './components/user-security-table';
import {
	UserSecurityTableFilters,
} from './components/user-security-table-filters';
import {
	UserSecurityActionsModal,
} from './components/user-security-actions-modal';
import { UserSecurityPagination } from './components/user-security-pagination';
import {
	StyledSnackbarList,
	StyledApp,
	StyledPageContainer,
} from './components/styles';
import './style.scss';

export default function App( { history } ) {
	const { removeNotice } = useDispatch( noticesStore );
	const { snackbarNotices } = useSelect( ( select ) => ( {
		snackbarNotices: select( noticesStore ).getNotices( 'ithemes-security' ),
	} ), [] );

	return (
		<ThemeProvider theme={ solidTheme }>
			<Router history={ history }>
				<QueryParamProvider ReactRouterRoute={ Route }>
					<StyledApp className="itsec-user-security" variant={ SurfaceVariant.UNDER_PAGE }>
						<SlotFillProvider>
							<PluginArea />
							<Popover.Slot />
							<TopToolbar />
							<Switch>
							</Switch>
							<StyledSnackbarList
								notices={ snackbarNotices }
								onRemove={ ( id ) => ( removeNotice( id, 'ithemes-security' ) ) }
							/>
							<StyledPageContainer>
								<UserGroupNotice />
								<UserSecurityHeader />
								<UserSecurityTableFilters />
								<Surface as="section">
									<UserSecurityTableHeader />
									<UserSecurityTable />
								</Surface>
								<UserSecurityPagination />
								<UserSecurityActionsModal />
							</StyledPageContainer>
						</SlotFillProvider>
					</StyledApp>
				</QueryParamProvider>
			</Router>
		</ThemeProvider>
	);
}

