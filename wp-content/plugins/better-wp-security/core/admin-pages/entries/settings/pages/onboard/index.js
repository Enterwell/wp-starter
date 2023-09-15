/**
 * External dependencies
 */
import {
	Switch,
	Route,
	Redirect,
	useRouteMatch,
	useLocation,
	Link,
} from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { NoticeList } from '@ithemes/security-components';
import { useEffect, useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { useMediaQuery } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { Rocket } from '@ithemes/security-style-guide';
import WelcomePage from './welcome';
import { Sidebar, Main, Navigation } from '../../components';
import { useNavigation, usePages } from '../../page-registration';
import { ONBOARD_STORE_NAME } from '../../stores';
import './style.scss';

export default function Onboard() {
	const pages = usePages();
	const { url, path } = useRouteMatch();
	const { pathname } = useLocation();
	const { recordVisitedLocation } = useDispatch( ONBOARD_STORE_NAME );

	useEffect( () => {
		recordVisitedLocation( pathname );
	}, [ pathname ] );

	return (
		<Switch>
			{ pages.map( ( { id, render } ) => (
				<Route path={ `${ path }/:page(${ id })` } key={ id }>
					<DynamicPage render={ render } />
				</Route>
			) ) }

			<Route path={ url }>
				{ pages.length > 0 && (
					<Redirect to={ `${ url }/${ pages[ 0 ].id }` } />
				) }
				<Sidebar>
					<Navigation guided allowBack />
				</Sidebar>
				<Main />
			</Route>
		</Switch>
	);
}

function DynamicPage( { render } ) {
	const [ showWelcome, setShowWelcome ] = useState( true );
	const isLarge = useMediaQuery( '(min-width: 960px)' );
	const {
		isExact,
		params: { page },
	} = useRouteMatch();

	if ( isExact && page === 'site-type' ) {
		if ( isLarge || ! showWelcome ) {
			return (
				<>
					{ isLarge ? (
						<WelcomeSidebar />
					) : (
						<DefaultSidebar page={ page } />
					) }
					<DefaultMain render={ render } />
				</>
			);
		}

		return <WelcomePage onDismiss={ () => setShowWelcome( false ) } />;
	}

	return (
		<>
			<DefaultSidebar page={ page } />
			<DefaultMain render={ render } />
		</>
	);
}

function DefaultSidebar( { page } ) {
	return (
		<Sidebar>
			<Navigation guided allowBack />
			{ page === 'site-type' && <SkipSetup /> }
		</Sidebar>
	);
}

function DefaultMain( { render: Component } ) {
	return (
		<Main>
			<NoticeList />
			<Component />
		</Main>
	);
}

function WelcomeSidebar() {
	return (
		<Sidebar className="itsec-onboard-welcome-sidebar" logo="white">
			<p className="itsec-onboard-welcome-sidebar__lead">
				{ __(
					'Welcome to iThemes Security. You are just a few clicks away from securing your site.',
					'better-wp-security'
				) }
			</p>
			<p className="itsec-onboard-welcome-sidebar__content">
				{ __(
					'The next steps will guide you through the setup process so the most important security features are enabled for your site.',
					'better-wp-security'
				) }
			</p>
			<SkipSetup showGraphic />
		</Sidebar>
	);
}

function SkipSetup( { showGraphic } ) {
	const { next } = useNavigation();

	return (
		<div className="itsec-onboard-skip-setup">
			{ showGraphic && (
				<Rocket className="itsec-onboard-welcome-sidebar__graphic" />
			) }
			<div className="itsec-onboard-skip-setup__text">
				<Link to={ next }>{ __( 'Skip Setup', 'better-wp-security' ) }</Link>
				<p className="itsec-onboard-skip-setup__description">
					{ __( 'Proceed with default settings.', 'better-wp-security' ) }
				</p>
			</div>
		</div>
	);
}
