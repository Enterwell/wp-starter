/**
 * External dependencies
 */
import {
	Switch,
	Route,
	Redirect,
	useRouteMatch,
	useLocation,
} from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { OnboardEmptyMain, OnboardMain } from '../../components';
import { usePages } from '../../page-registration';
import { ONBOARD_STORE_NAME } from '../../stores';

export default function Onboard() {
	const pages = usePages();
	const { url, path } = useRouteMatch();
	const { pathname } = useLocation();
	const { recordVisitedLocation } = useDispatch( ONBOARD_STORE_NAME );

	useEffect( () => {
		recordVisitedLocation( pathname );
	}, [ recordVisitedLocation, pathname ] );

	return (
		<Switch>
			{ pages.map( ( { id, render } ) => (
				<Route path={ `${ path }/:page(${ id })` } key={ id }>
					<OnboardMain url={ url } render={ render } />
				</Route>
			) ) }

			<Route path={ url }>
				{ pages.length > 0 && (
					<Redirect to={ `${ url }/${ pages[ 0 ].id }` } />
				) }
				<OnboardEmptyMain />
			</Route>
		</Switch>
	);
}
