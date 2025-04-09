/**
 * External dependencies
 */
import { Switch, Route, useRouteMatch } from 'react-router-dom';

/**
 * SolidWP dependencies
 */
import { RiveGraphicProvider } from '@ithemes/security-ui';

/**
 * Internal dependencies
 */
import Chooser from './chooser';
import Questions from './questions';

const graphics = [ 'onboard-two-factor', 'onboard-two-factor-reduced' ];

export default function SiteType() {
	const { path } = useRouteMatch();

	return (
		<RiveGraphicProvider preload={ graphics }>
			<Switch>
				<Route path={ `${ path }/:siteType` }>
					<Questions />
				</Route>

				<Route path={ `${ path }` }>
					<Chooser />
				</Route>
			</Switch>
		</RiveGraphicProvider>
	);
}
