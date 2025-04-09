/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Surface, SurfaceVariant, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

export default function ProTag() {
	return (
		<StyledProSiteTag variant={ SurfaceVariant.DARK }>
			<Text
				size={ TextSize.SMALL }
				variant={ TextVariant.WHITE }
				weight={ TextWeight.HEAVY }
				text={ __( 'Pro', 'better-wp-security' ) }
			/>
		</StyledProSiteTag>
	);
}

const StyledProSiteTag = styled( Surface )`
	display: flex;
	align-items: center;

	padding: 1px 8px;

	background-image: linear-gradient(
		116deg,
		#3c3454 0%,
		#44375a 36%,
		#66457b 100%
	);

	border-radius: 5px;
`;
