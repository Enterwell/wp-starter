/**
 * External dependencies
 */
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { useMemo, createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * iThemes dependencies
 */
import { defaultTheme, Text, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { TabPanel } from '@ithemes/security-components';
import { LogoProColor } from '@ithemes/security-style-guide';
import { useAsync } from '@ithemes/security-hocs';
import { Features, Pricing, Integrations } from './pages';
import './style.scss';

export default function App() {
	const { value: pricing } = useAsync( fetchPricing );
	const { value: features } = useAsync( fetchFeatures );
	const { value: integrations } = useAsync( fetchIntegrations );
	const tabs = useMemo( () => ( [
		{
			name: 'features',
			title: __( 'Enhanced Website Security Features', 'better-wp-security' ),
			render() {
				return <Features features={ features } />;
			},
		},
		{
			name: 'pricing',
			title: __( 'View Pricing & Plans', 'better-wp-security' ),
			render() {
				return <Pricing pricing={ pricing } />;
			},
		},
		{
			name: 'integrations',
			title: __( 'Additional Security Integrations', 'better-wp-security' ),
			render() {
				return <Integrations integrations={ integrations } />;
			},
		},
	] ), [ pricing, features, integrations ] );

	return (
		<ThemeProvider theme={ defaultTheme }>
			<div className="itsec-go-pro">
				<header>
					<LogoProColor />
					<Text variant="dark" weight={ TextWeight.HEAVY }>
						{ createInterpolateElement(
							__( 'This page is loaded from <a>iThemes.com/Security</a>', 'better-wp-security' ),
							{
							// eslint-disable-next-line jsx-a11y/anchor-has-content
								a: <a href="https://ithem.es/security/" />,
							}
						) }
					</Text>
				</header>
				<TabPanel isStyled tabs={ tabs } className="itsec-go-pro-tab-panel">
					{ ( { render: Component } ) => <Component /> }
				</TabPanel>
			</div>
		</ThemeProvider>
	);
}

const fetchPricing = () => window.fetch( 'https://ithemes.com/api/itsec-go-pro-pricing.json' )
	.then( ( response ) => response.json() );
const fetchFeatures = () => window.fetch( 'https://ithemes.com/api/itsec-go-pro-features.json' )
	.then( ( response ) => response.json() );
const fetchIntegrations = () => window.fetch( 'https://ithemes.com/api/itsec-go-pro-integrations.json' )
	.then( ( response ) => response.json() );
