/**
 * External Dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * iThemes Dependencies
 */
import { Button, Heading, TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal Dependencies
 */
import { useGlobalNavigationUrl } from '@ithemes/security-utils';

const HeaderContainer = styled.div`
	display: grid;
	grid-template-areas: "heading heading" "text text" "dashboard settings";
	grid-template-columns: 1fr 1fr;
	gap: 1rem 0.625rem;

	@media (min-width: ${ ( { theme } ) => theme.breaks.medium }px ) {
		grid-template-areas: "heading heading heading" "text dashboard settings";
		grid-template-columns: 4fr 1fr 1fr;
		gap: 1rem 0.625rem;
	}
`;

const PrimaryHeading = styled( Heading )`
	grid-area: heading;
`;

const SecondaryHeading = styled( Heading )`
	grid-area: text;
`;

export default function HeadingContainer() {
	const dashboardLink = useGlobalNavigationUrl( 'dashboard' ),
		settingsLink = useGlobalNavigationUrl( 'settings' );

	return (
		<HeaderContainer>
			<PrimaryHeading
				level={ 1 }
				text={ __( 'Great Work! Your site is ready and is more secure than ever!', 'better-wp-security' ) }
				size={ TextSize.HUGE }
			/>
			<SecondaryHeading
				level={ 3 }
				text={ __( 'If you want to dig into your site’s security, check out your security dashboard, and make changes via settings.', 'better-wp-security' ) }
				size={ TextSize.LARGE }
				variant={ TextVariant.DARK }
			/>
			<Button
				variant="secondary"
				href={ dashboardLink }
				text={ __( 'Dashboard', 'better-wp-security' ) }
			/>
			<Button
				variant="secondary"
				href={ settingsLink }
				text={ __( 'Settings', 'better-wp-security' ) }
			/>
		</HeaderContainer>
	);
}
