/**
 * External dependencies
 */
import { Router, Switch, Route } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { SlotFillProvider, Popover } from '@wordpress/components';
import { PluginArea } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

/**
 * iThemes dependencies
 */
import {
	Button,
	Heading,
	solidTheme,
	SurfaceVariant,
	Text,
	TextSize,
	TextVariant,
	TextWeight,
} from '@ithemes/ui';
import store from './store';

/**
 * Internal dependencies
 */
import { TopToolbar } from '@ithemes/security-ui';
import ScanResults from './components/scan-results';
import ProgressBar from './components/progress-bar/index';
import {
	StyledApp,
	StyledPageContainer,
	StyledHeadingText,
	StyledScanSurface,
	StyledSnackbarList,
} from './styles';
import './style.scss';

export default function App( { history } ) {
	const { startScan } = useDispatch( store );
	const { removeNotice } = useDispatch( noticesStore );
	const { components, issues, snackbarNotices } = useSelect( ( select ) => ( {
		components: select( store ).getScanComponents(),
		issues: select( store ).getIssues(),
		snackbarNotices: select( noticesStore ).getNotices( 'ithemes-security' ),
	} ), [] );
	const onClick = () => {
		startScan();
	};

	return (
		<ThemeProvider theme={ solidTheme }>
			<Router history={ history }>
				<QueryParamProvider ReactRouterRoute={ Route }>
					<StyledApp className="itsec-site-scan" variant={ SurfaceVariant.UNDER_PAGE }>
						<SlotFillProvider>
							<PluginArea />
							<Popover.Slot />
							<TopToolbar />
							<Switch>
								<StyledPageContainer>
									<Heading
										level={ 1 }
										weight={ TextWeight.NORMAL }
										text={ __( 'Site Scans', 'better-wp-security' ) }
									/>
									<StyledHeadingText>
										<Text size={ TextSize.SMALL } variant={ TextVariant.MUTED } text={ __( 'Scan your site for security issues and find out how fix them.', 'better-wp-security' ) } />
										<Button
											onClick={ onClick }
											variant="primary"
											text={ __( 'Start Site Scan', 'better-wp-security' ) }
										/>
									</StyledHeadingText>

									<StyledScanSurface variant={ SurfaceVariant.PRIMARY }>
										<div>
											<Heading level={ 2 } size={ TextSize.LARGE } weight={ 600 } text={ __( 'Scan', 'better-wp-security' ) } />
										</div>
										<ProgressBar components={ components } />

										<ScanResults issues={ issues } />

									</StyledScanSurface>

								</StyledPageContainer>
							</Switch>
							<StyledSnackbarList
								notices={ snackbarNotices }
								onRemove={ ( id ) => ( removeNotice( id, 'ithemes-security' ) ) }
							/>
						</SlotFillProvider>
					</StyledApp>
				</QueryParamProvider>
			</Router>
		</ThemeProvider>
	);
}

