/**
 * External dependencies
 */
import { Router, Switch, Route, Redirect } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ErrorBoundary } from 'react-error-boundary';
import { ThemeProvider } from '@emotion/react';

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
import { defaultTheme } from '@ithemes/ui';

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

export default function App( {
	history,
	serverType,
	installType,
	onboardComplete,
} ) {
	useSearchProviders();
	const redirect = onboardComplete ? '/settings' : '/onboard';

	return (
		<ThemeProvider theme={ defaultTheme }>
			<div className="itsec-settings">
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
			</div>
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
