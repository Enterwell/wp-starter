/**
 * External dependencies
 */
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

/**
 * iThemes dependencies
 */
import { solidTheme } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	StyledMainContainer,
	StyledMain,
	StyledProLogo,
	StyledUpsellText,
	StyledUpsellGradient,
	StyledUpsellButton,
} from './style.js';

export default function App() {
	return (
		<ThemeProvider theme={ solidTheme }>
			<StyledMainContainer className="itsec-go-pro">
				<StyledProLogo />
				<StyledMain>
					<StyledUpsellText
						text={ createInterpolateElement(
							__( 'The only WordPress security plugin you need — <i>period</i>', 'better-wp-security' ), {
								i: <span />,
							} ) }
					/>
					<StyledUpsellGradient />
					<StyledUpsellButton
						variant="primary"
						text={ __( 'Get Solid Security Pro', 'better-wp-security' ) }
						href={ 'https://go.solidwp.com/basic-to-pro' }
					/>
				</StyledMain>
			</StyledMainContainer>
		</ThemeProvider>
	);
}
