/**
 * External dependencies
 */
import { Link } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Rocket } from '@ithemes/security-style-guide';
import { Logo, PageHeader } from '../../../components';
import { useNavigation } from '../../../page-registration';
import './style.scss';
import { __ } from '@wordpress/i18n';

export default function WelcomePage( { onDismiss } ) {
	const { next } = useNavigation();

	return (
		<div className="itsec-onboard-welcome-page">
			<Logo style="white" className="itsec-onboard-welcome-page__logo" />

			<PageHeader
				title={ __(
					'Welcome to iThemes Security. You are just a few clicks away from securing your site.',
					'better-wp-security'
				) }
				subtitle={ __(
					'The next steps will guide you through the setup process so the most important security features are enabled for your site.',
					'better-wp-security'
				) }
				breadcrumbs={ false }
			/>

			<div className="itsec-onboard-welcome-page__actions-container">
				<Rocket className="itsec-onboard-welcome-page__graphic" />
				<div className="itsec-onboard-welcome-page__actions">
					<Button
						onClick={ onDismiss }
						icon="arrow-right-alt"
						text={ __( 'Start', 'better-wp-security' ) }
						iconPosition="right"
						variant="primary"
						className="itsec-button-icon-right"
					/>
					<Link to={ next }>{ __( 'Skip Setup', 'better-wp-security' ) }</Link>
				</div>
			</div>
		</div>
	);
}
