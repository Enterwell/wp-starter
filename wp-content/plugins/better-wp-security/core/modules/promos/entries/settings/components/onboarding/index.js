/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * iThemes dependencies
 */
import {
	Heading,
	Button,
	Surface,
	List,
	ListItem,
	TextSize,
} from '@ithemes/ui';

/**
 * Internal Dependencies
 */
import {
	DevicesIcon,
	GoogleIcon,
	KeyIcon,
	UpdateIcon,
	UserIcon,
} from '@ithemes/security-style-guide';
import ProPromotionProgressBar from './animations.js';

const StyledSurface = styled( Surface )`
	padding: 2rem;
	color: #fff;
	max-width: 888px;
	margin: 0px auto;
	
	@media (min-width: ${ ( { theme } ) => theme.breaks.large }px) {
		margin: 0px 0px;
	}
`;

const StyledList = styled( List )`
	display: grid;
	grid-template-columns: repeat( 1, 1fr );
	gap: 0rem 1.25rem;
	margin: 1.5rem 0rem;
	
	@media ( min-width: ${ ( { theme } ) => theme.breaks.medium }px ) {
		grid-template-columns: repeat( 2, 1fr );
	}
	
	li span {
		line-height: 1rem;
	}
`;

const StyledButton = styled( Button )`
	margin-top: 1.5rem;
	width: 100%;
	display: block;
	padding: 1.5rem 0px !important;
	text-align: center;
	
	@media ( min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		width: 35%;
	}
`;

const StyledSurfaceSubHeading = styled( Heading )`
	margin-top: 1rem;
`;

export default function OnboardingProPromotion() {
	return (
		<StyledSurface variant="dark">
			<Heading
				level={ 2 }
				text={ __( 'Add Even More Security To Your Site with iThemes Security Pro', 'better-wp-security' ) }
				size={ TextSize.EXTRA_LARGE }
				variant="white"
			/>
			<ProPromotionProgressBar />
			<StyledSurfaceSubHeading
				level={ 3 }
				text={ __( 'Upgrade to iThemes Security Pro and optimize your security even further with…', 'better-wp-security' ) }
				size={ TextSize.NORMAL }
				variant="white"
			/>
			<StyledList>
				<ListItem
					icon={ UpdateIcon }
					iconSize={ 30 }
					text={ __( 'Automatically update plugins, themes, and WordPress versions with known vulnerabilities in 5 minutes or less', 'better-wp-security' ) }
				/>
				<ListItem
					icon={ KeyIcon }
					iconSize={ 30 }
					text={ __( 'Easier, secure logins with passwordless authentication', 'better-wp-security' ) }
				/>
				<ListItem
					icon={ DevicesIcon }
					iconSize={ 30 }
					text={ __( 'Limit logins based on trusted devices', 'better-wp-security' ) }
				/>
				<ListItem
					icon={ GoogleIcon }
					iconSize={ 30 }
					text={ __( 'Block automated attacks, spam, and harmful bot activity with reCAPTCHA', 'better-wp-security' ) }
				/>
				<ListItem
					icon={ UserIcon }
					iconSize={ 30 }
					text={ __( 'Log users actions such as logging in, saving content, and making changes to the site', 'better-wp-security' ) }
				/>
			</StyledList>
			<StyledButton
				variant="primary"
				className="itsec-promo-pro-upgrade__button"
				href="https://ithem.es/go-security-pro-now"
				text={ __( 'Save 25% off Now', 'better-wp-security' ) }
				isRounded
				textSize={ TextSize.LARGE }
			>
			</StyledButton>
		</StyledSurface>
	);
}
