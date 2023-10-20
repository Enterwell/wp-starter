/**
 * External dependencies
 */
import { Router, Switch, Route, Redirect } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ErrorBoundary } from 'react-error-boundary';
import { ThemeProvider } from '@emotion/react';
import styled from '@emotion/styled';

/**
 * WordPress components
 */
import {
	SlotFillProvider,
	Popover,
	Flex,
	FlexBlock,
} from '@wordpress/components';
import { PluginArea } from '@wordpress/plugins';

/**
 * iThemes dependencies
 */
import { solidTheme, Surface, SurfaceVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import PageRegistration from './page-registration';
import Pages, { Import, Onboard, Settings } from './pages';
import { ConfigContext } from './utils';
import { Main, Sidebar, ErrorRenderer } from './components';
import useSearchProviders from './search';
import './stores';
import './style.scss';

const StyledApp = styled( Surface )`
	display: flex;
	flex-direction: column;
`;

export default function App( {
	history,
	serverType,
	installType,
	onboardComplete,
} ) {
	useSearchProviders();
	const redirect = onboardComplete ? '/settings' : '/onboard';

	return (
		<ThemeProvider theme={ solidTheme }>
			<StyledApp className="itsec-settings" variant={ SurfaceVariant.UNDER_PAGE }>
				<ConfigContext.Provider
					value={ { serverType, installType, onboardComplete } }
				>
					<Router history={ history }>
						<QueryParamProvider ReactRouterRoute={ Route }>
							<SlotFillProvider>
								<ErrorBoundary
									FallbackComponent={ GlobalErrorBoundary }
								>
									<PageRegistration>
										<Pages />
										<PluginArea />
										<Popover.Slot />
										<Switch>
											<Route
												path="/:root(settings)"
												component={ Settings }
											/>
											<Route
												path="/:root(onboard)"
												component={ Onboard }
											/>
											<Route
												path="/:root(import)"
												component={ Import }
											/>

											<Route path="/">
												<Redirect to={ redirect } />
												<Sidebar />
												<Main />
											</Route>
										</Switch>
									</PageRegistration>
								</ErrorBoundary>
							</SlotFillProvider>
						</QueryParamProvider>
					</Router>
				</ConfigContext.Provider>
			</StyledApp>
		</ThemeProvider>
	);
}

function GlobalErrorBoundary( props ) {
	return (
		<Flex>
			<FlexBlock>
				<ErrorRenderer { ...props } />
			</FlexBlock>
		</Flex>
	);
}
