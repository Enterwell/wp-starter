/**
 * External dependencies
 */
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { NoticeList, SlotFillProvider, Popover } from '@wordpress/components';
import { pure, usePrevious } from '@wordpress/compose';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { PluginArea } from '@wordpress/plugins';
import '@wordpress/notices';

/**
 * iThemes dependencies
 */
import { defaultTheme } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { BelowToolbarSlot } from '@ithemes/security.dashboard.api';
import '@ithemes/security.packages.data';
import { useEventListener } from '@ithemes/security-hocs';
import { useRegisterCards } from './cards';
import AdminBar from './components/admin-bar';
import CardGrid from './components/card-grid';
import CreateDashboard from './components/create-dashboard';
import Toolbar from './components/toolbar';
import Help from './components/help';
import { ConfigContext } from './utils';
import './style.scss';

const Page = pure( ( { page, dashboardId } ) => {
	useRegisterCards();

	switch ( page ) {
		case 'view-dashboard':
			return <CardGrid dashboardId={ dashboardId } />;
		case 'create-dashboard':
			return <CreateDashboard />;
		case 'help':
			return <Help />;
		default:
			return null;
	}
} );

export default function App( { context } ) {
	const {
		page,
		primaryDashboard,
		dashboardId,
		isUsingTouch,
		notices,
	} = useSelect(
		( select ) => ( {
			page: select( 'ithemes-security/dashboard' ).getCurrentPage(),
			primaryDashboard: select(
				'ithemes-security/dashboard'
			).getPrimaryDashboard(),
			dashboardId: select(
				'ithemes-security/dashboard'
			).getViewingDashboardId(),
			isUsingTouch: select( 'ithemes-security/dashboard' ).isUsingTouch(),
			notices: select( 'core/notices' ).getNotices( 'ithemes-security' ),
		} ),
		[]
	);
	const { usingTouch, viewDashboard, viewCreateDashboard } = useDispatch(
		'ithemes-security/dashboard'
	);
	const { removeNotice } = useDispatch( 'core/notices' );
	useEventListener( 'touchstart', () => isUsingTouch || usingTouch() );

	const prevPrimaryDashboard = usePrevious( primaryDashboard );
	useEffect( () => {
		if ( prevPrimaryDashboard !== undefined ) {
			return;
		}

		if ( primaryDashboard ) {
			viewDashboard( primaryDashboard );
		} else {
			viewCreateDashboard();
		}
	}, [ primaryDashboard ] );

	if ( primaryDashboard === undefined ) {
		return null;
	}

	return (
		<ThemeProvider theme={ defaultTheme }>
			<SlotFillProvider>
				<ConfigContext.Provider value={ context }>
					<div className={ `itsec-dashboard itsec-app-page--${ page }` }>
						<Popover.Slot />
						<NoticeList
							notices={ notices }
							onRemove={ ( noticeId ) =>
								removeNotice( noticeId, 'ithemes-security' )
							}
						/>
						<Toolbar dashboardId={ dashboardId } />
						<BelowToolbarSlot fillProps={ { page, dashboardId } } />
						<AdminBar dashboardId={ dashboardId } />
						<Page page={ page } dashboardId={ dashboardId } />
					</div>
					<PluginArea />
				</ConfigContext.Provider>
			</SlotFillProvider>
		</ThemeProvider>
	);
}
