/**
 * External dependencies
 */
import { Switch, Route, useRouteMatch } from 'react-router-dom';

/**
 * Internal dependencies
 */
import Chooser from './chooser';
import Questions from './questions';

export default function SiteType() {
	const { path } = useRouteMatch();

	return (
		<Switch>
			<Route path={ `${ path }/:siteType` }>
				<Questions />
			</Route>

			<Route path={ `${ path }` }>
				<Chooser />
			</Route>
		</Switch>
	);
}
