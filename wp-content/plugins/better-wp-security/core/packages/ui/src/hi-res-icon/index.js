/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { useMediaQuery } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { PurpleShield } from '@ithemes/security-style-guide';

const StyledPurpleShield = styled( PurpleShield, {
	shouldForwardProp: ( propName ) => propName !== 'isSmall',
} )`
	height: ${ ( { isSmall } ) => isSmall ? '56px' : '120px' };
	width: ${ ( { isSmall } ) => isSmall ? '56px' : '120px' };
`;

export default function HiResIcon( { icon, isSmall = false } ) {
	const isHiRes = useMediaQuery( '(-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi)' );

	return (
		isHiRes ? icon : <StyledPurpleShield isSmall={ isSmall } />
	);
}
