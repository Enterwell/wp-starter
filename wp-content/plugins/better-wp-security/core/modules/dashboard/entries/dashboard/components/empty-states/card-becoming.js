/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * iThemes dependencies
 */
import { Heading, Text, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { RebrandingLogos } from '@ithemes/security-style-guide';

const StyledContainer = styled.div`
	padding: 1.25rem;
	display: flex;
	justify-content: center;
	align-items: center;
	flex-direction: column;
	align-content: center;
	flex-wrap: nowrap;
	height: 100%;
`;

const StyledRebrandedLogo = styled( RebrandingLogos )`
	margin-bottom: 1.25rem;
`;
const StyledHeading = styled( Heading )`
	text-align: center;
	font-weight: 700 !important;
	font-size: 1rem !important;
	color: #333333 !important;
	line-height: 24px; 
	margin: 0 0 1.25rem 0 !important;
`;

const StyledText = styled( Text )`
	text-align: center;
	font-size: .8rem;
	font-weight: 400 !important;
	color: #333333 !important;
	font-style: normal !important;
	line-height: 16px;
	margin: 0 0 1.25rem 0 !important;
`;

const StyledButton = styled( Button )`
	background: #772ECB !important;
`;

const StyledA = styled.a`
	color: #FFFFFF;
	text-decoration: none;
`;

export default function CardBecoming( ) {
	return (
		<StyledContainer className="itsec-empty-state-card itsec-empty-state-card--becoming">
			<StyledRebrandedLogo />
			<StyledHeading
				level={ 3 }
				text={ __( 'iThemes Security is becoming Solid Security', 'better-wp-security' ) }
				weight={ TextWeight.HEAVY }
			/>
			<StyledText
				text={ __( 'We have been working hard for almost a year to bring you incredible new features in the form of our new and improved brand: SolidWP. Discover what’s coming very soon!', 'better-wp-security' ) }
				as={ 'p' }
			/>
			<StyledButton variant="primary">
				<StyledA
					href="https://go.solidwp.com/security-wpadmin-ithemes-becoming-solidwp"
					className="itsec-promo-pro-upgrade__details"
				>
					{ __( 'Learn More', 'better-wp-security' ) }
				</StyledA>
			</StyledButton>
		</StyledContainer>
	);
}
