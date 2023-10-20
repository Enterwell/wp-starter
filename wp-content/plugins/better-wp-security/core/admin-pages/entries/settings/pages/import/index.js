/**
 * External dependencies
 */
import { Redirect, Route, Switch, useRouteMatch } from 'react-router-dom';

/**
 * Internal dependencies
 */
import { OnboardMain, OnboardEmptyMain } from '../../components';
import { usePages } from '../../page-registration';

export default function Import() {
	const pages = usePages();
	const { url, path } = useRouteMatch();

	if (
		pages.length > 0 &&
		! pages.find( ( page ) => page.id === 'select-export' )
	) {
		return <Redirect to="/" />;
	}

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
