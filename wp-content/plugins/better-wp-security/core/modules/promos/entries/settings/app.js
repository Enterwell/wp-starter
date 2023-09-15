/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useMemo, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Card, CardBody } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { AsideFill, useConfigContext, SecureSiteEndFill, BeforeSettingsFill } from '@ithemes/security.pages.settings';
import {
	MarkPro,
	VulnerabilityReport as VulnerabilityReportGraphic,
} from '@ithemes/security-style-guide';
import { ControlledTabPanel } from '@ithemes/security-components';
import { useAsync } from '@ithemes/security-hocs';
import { CORE_STORE_NAME } from '@ithemes/security.packages.data';
import Onboarding from './components/onboarding';
import BecomingSolid from './components/becoming-solid';
import './style.scss';

export default function App() {
	const { installType } = useConfigContext();

	if ( installType === 'pro' ) {
		return (
			<>
				<BeforeSettingsFill>
					<BecomingSolid />
				</BeforeSettingsFill>
			</>
		);
	}

	return (
		<>
			<AsideFill>
				<ProUpgrade />
				<VulnerabilityReport />
			</AsideFill>
			<SecureSiteEndFill>
				<OnboardingEndFill />
			</SecureSiteEndFill>
			<BeforeSettingsFill>
				<BecomingSolid />
			</BeforeSettingsFill>
		</>
	);
}

function ProUpgrade() {
	const [ currentTab, selectTab ] = useState( 'one' );
	const tabs = useMemo(
		() => [
			{
				name: 'one',
				title: __( '1 Site', 'better-wp-security' ),
				price: '99',
				link: 'https://ithem.es/security-1-site-plan',
			},
			{
				name: 'five',
				title: __( '5 Sites', 'better-wp-security' ),
				price: '199',
				link: 'https://ithem.es/security-5-site-plan',
			},
			{
				name: 'ten',
				title: __( '10 Sites', 'better-wp-security' ),
				price: '299',
				link: 'https://ithem.es/security-10-site-plan',
			},
		],
		[]
	);

	return (
		<Card size="small" className="itsec-promo itsec-promo-pro-upgrade">
			<CardBody>
				<header>
					<MarkPro />
					<h2>{ __( 'Unlock More Security Features', 'better-wp-security' ) }</h2>
				</header>
				<p>
					{ __(
						'Go beyond the basics with premium features & support.',
						'better-wp-security'
					) }
				</p>
				<ControlledTabPanel isStyled tabs={ tabs } selected={ currentTab } onSelect={ selectTab }>
					{ ( { price } ) => (
						<>
							<span className="itsec-promo-pro-upgrade__price">
								{ sprintf( '$%s', price ) }
							</span>
							<span className="itsec-promo-pro-upgrade__description">
								{ __(
									'Includes updates and support for one year.',
									'better-wp-security'
								) }
							</span>
						</>
					) }
				</ControlledTabPanel>
				<Button
					variant="primary"
					className="itsec-promo-pro-upgrade__button"
					href={ tabs.find( ( tab ) => tab.name === currentTab )?.link || 'https://ithem.es/go-security-pro-now' }
				>
					{ __( 'Go Pro Now', 'better-wp-security' ) }
				</Button>
				<a
					href="https://ithem.es/included-with-pro"
					className="itsec-promo-pro-upgrade__details"
				>
					{ __( 'What’s included with Pro?', 'better-wp-security' ) }
				</a>
			</CardBody>
		</Card>
	);
}

function VulnerabilityReport() {
	const { execute, status } = useAsync( signupToList, false );
	const email = useSelect(
		( select ) => select( CORE_STORE_NAME ).getCurrentUser()?.email
	);

	return (
		<Card
			size="small"
			className="itsec-promo itsec-promo-vulnerability-report"
		>
			<CardBody>
				<VulnerabilityReportGraphic />
				<h2>
					{ __(
						'Get the Weekly WordPress Vulnerability Report',
						'better-wp-security'
					) }
				</h2>
				<p>
					{ __(
						'Vulnerable plugins and themes are the #1 reason WordPress sites get hacked. Keep up with the latest reports of WordPress vulnerabilities each week, delivered right to your inbox.',
						'better-wp-security'
					) }
				</p>
				<Button
					variant="primary"
					className="itsec-promo itsec-promo-vulnerability-report__button"
					isBusy={ status === 'pending' }
					onClick={ () => execute( email ) }
					disabled={ ! email || status === 'success' }
				>
					{ status === 'success'
						? __( 'Subscribed!', 'better-wp-security' )
						: __( 'Get the Report', 'better-wp-security' ) }
				</Button>
			</CardBody>
		</Card>
	);
}

function signupToList( email ) {
	return window
		.fetch( 'https://api.ithemes.com/newsletter/subscribe', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify( {
				email,
				list_id: '35856077f7',
				tags: [ 'ITSEC-vuln-report-signup' ],
			} ),
		} )
		.then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( __( 'Invalid response.', 'better-wp-security' ) );
			}

			return response;
		} )
		.then( ( response ) => response.json() )
		.then( ( response ) => {
			if ( ! response.success ) {
				throw new Error(
					__(
						'Sorry, we could not subscribe you to the mailing list. Please try again later.',
						'better-wp-security'
					)
				);
			}

			return response;
		} );
}

function OnboardingEndFill() {
	const { root } = useParams();

	if ( root !== 'onboard' ) {
		return null;
	}

	return <Onboarding />;
}
