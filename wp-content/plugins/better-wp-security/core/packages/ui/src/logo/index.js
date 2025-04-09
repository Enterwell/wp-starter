/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { LogoColorPro, LogoColorBasic } from '@ithemes/security-style-guide';
import { coreStore } from '@ithemes/security.packages.data';

const StyledLogoBasic = styled( LogoColorBasic )`
	height: ${ ( { size } ) => size }px;
	width: auto;
	max-width: 100%;
`;

const StyledLogo = styled( LogoColorPro )`
	height: ${ ( { size } ) => size }px;
	width: auto;
	max-width: 100%;
`;

export default function Logo( { size = 25, className } ) {
	const { installType } = useSelect(
		( select ) => ( {
			installType: select( coreStore ).getInstallType(),
		} ),
		[]
	);

	if ( installType === 'free' ) {
		return <StyledLogoBasic size={ size } className={ className } />;
	}

	return <StyledLogo size={ size } className={ className } />;
}
