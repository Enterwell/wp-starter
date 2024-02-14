/**
 * External dependencies
 */
import { Router, Switch, Route, Redirect } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ThemeProvider } from '@emotion/react';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { SlotFillProvider, Popover } from '@wordpress/components';
import { PluginArea } from '@wordpress/plugins';

/**
 * iThemes dependencies
 */
import { solidTheme, Surface, SurfaceVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { TopToolbar } from '@ithemes/security-ui';
import { Export, List } from './pages';
import './style.scss';

const StyledApp = styled( Surface )`
	display: flex;
	flex-direction: column;
`;

export default function App( { history } ) {
	return (
		<ThemeProvider theme={ solidTheme }>
			<Router history={ history }>
				<QueryParamProvider ReactRouterRoute={ Route }>
					<StyledApp className="itsec-tools" variant={ SurfaceVariant.UNDER_PAGE }>
						<SlotFillProvider>
							<PluginArea />
							<Popover.Slot />
							<TopToolbar />
							<Switch>
								<Route path="/export" component={ Export } />
								<Route path="/tools" component={ List } />
								<Route path="/">
									<Redirect to="/tools" />
								</Route>
							</Switch>
						</SlotFillProvider>
					</StyledApp>
				</QueryParamProvider>
			</Router>
		</ThemeProvider>
	);
}

