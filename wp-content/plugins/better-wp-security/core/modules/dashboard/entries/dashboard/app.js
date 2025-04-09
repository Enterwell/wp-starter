/**
 * External dependencies
 */
import { ThemeProvider } from '@emotion/react';
import { Responsive } from 'react-grid-layout';

/**
 * WordPress dependencies
 */
import { NoticeList, SlotFillProvider, Popover } from '@wordpress/components';
import { pure, usePrevious, useResizeObserver } from '@wordpress/compose';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { PluginArea } from '@wordpress/plugins';
import '@wordpress/notices';

/**
 * iThemes dependencies
 */
import { solidTheme } from '@ithemes/ui';

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
import { BREAKPOINTS, ConfigContext } from './utils';
import './style.scss';

const Page = pure( ( { page, dashboardId, width, breakpoint } ) => {
	useRegisterCards();

	switch ( page ) {
		case 'view-dashboard':
		case 'create-dashboard':
			return dashboardId > 0 && <CardGrid dashboardId={ dashboardId } width={ width } breakpoint={ breakpoint } />;
		case 'help':
			return <Help />;
		default:
			return null;
	}
} );

export default function App( { context } ) {
	const [ resizeListener, size ] = useResizeObserver();
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
	}, [ primaryDashboard, prevPrimaryDashboard, viewDashboard, viewCreateDashboard ] );

	if ( primaryDashboard === undefined ) {
		return null;
	}

	return (
		<ThemeProvider theme={ solidTheme }>
			<SlotFillProvider>
				<ConfigContext.Provider value={ context }>
					<div className={ `itsec-dashboard itsec-app-page--${ page }` }>
						{ resizeListener }
						<Popover.Slot />
						<NoticeList
							notices={ notices }
							onRemove={ ( noticeId ) =>
								removeNotice( noticeId, 'ithemes-security' )
							}
						/>
						<Toolbar dashboardId={ dashboardId } />
						<BelowToolbarSlot fillProps={ { page, dashboardId } } />
						<AdminBar dashboardId={ dashboardId } width={ size.width } />
						{ page === 'create-dashboard' && (
							<CreateDashboard />
						) }
						<Page
							page={ page }
							dashboardId={ dashboardId }
							width={ size.width }
							breakpoint={ Responsive.utils.getBreakpointFromWidth( BREAKPOINTS, size.width ) }
						/>
					</div>
					<PluginArea />
				</ConfigContext.Provider>
			</SlotFillProvider>
		</ThemeProvider>
	);
}
