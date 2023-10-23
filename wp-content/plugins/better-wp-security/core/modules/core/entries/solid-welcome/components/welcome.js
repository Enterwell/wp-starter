/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Text, TextSize } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { RebrandingLogos } from '@ithemes/security-style-guide';
import { StyledLogoBanner, StyledTextContainer } from '../styles';

function LogoBanner( { installType } ) {
	const heading = installType === 'free'
		? __( 'iThemes Security Free is now Solid Security Basic', 'better-wp-security' )
		: __( 'iThemes Security Pro is now Solid Security Pro', 'better-wp-security' );

	return (
		<StyledLogoBanner variant="dark">
			<div>
				<RebrandingLogos />
			</div>
			<StyledTextContainer>
				<Text
					size={ TextSize.HUGE }
					variant="white"
					text={ heading }
				/>
			</StyledTextContainer>
		</StyledLogoBanner>
	);
}

export function CardOne( { installType } ) {
	return (
		<LogoBanner installType={ installType } />
	);
}
