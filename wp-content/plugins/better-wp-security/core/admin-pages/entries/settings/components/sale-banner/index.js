/**
 * External dependencies
 */
import { ThemeProvider, useTheme } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { close as dismissIcon } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useLocalStorage } from '@ithemes/security-hocs';
import { useConfigContext } from '../../utils';
import {
	StyledBFCMBanner,
	StyledBFCMTextContainer,
	StyledBFCMHeading,
	StyledBFCMText,
	StyledBFCMButton,
	StyledLogo,
	StyledBFCMDismiss,
} from './styles';

// November 20, 2023 UTC
const saleStart = Date.UTC( 2023, 10, 20, 0, 0, 0 );
// December 3, 2023 (inclusive of all US timezones)
const saleEnd = Date.UTC( 2023, 11, 4, 9, 59, 59 );
const now = Date.now();

export default function SolidSecuritySettingsSaleBanner() {
	const [ isDismissed, setIsDismissed ] = useLocalStorage( 'solidSecurityBFCM2023' );
	const { installType } = useConfigContext();
	const baseTheme = useTheme();
	const theme = useMemo( () => ( {
		...baseTheme,
		colors: {
			...baseTheme.colors,
			text: {
				...baseTheme.colors.text,
				white: '#F9FAF9',
			},
		},
	} ), [ baseTheme ] );

	if ( saleStart > now || saleEnd < now ) {
		return null;
	}

	if ( isDismissed ) {
		return null;
	}

	return (
		<ThemeProvider theme={ theme }>
			<StyledBFCMBanner>
				<StyledBFCMTextContainer>
					<StyledBFCMHeading
						level={ 2 }
						variant={ TextVariant.WHITE }
						weight={ TextWeight.HEAVY }
						text={ __( 'Save 40% on SolidWP', 'better-wp-security' ) }
					/>
					<StyledBFCMText
						variant={ TextVariant.WHITE }
						text={ __( 'Purchase new products during the Black Friday Sale.' ) }
					/>
					<StyledBFCMButton
						href={ installType === 'free' ? 'https://go.solidwp.com/bfcm-go-pro' : 'https://go.solidwp.com/bfcm-security-pro-solid-suite' }
						weight={ 500 }
					>
						{ __( 'Get Solid Suite', 'better-wp-security' ) }
					</StyledBFCMButton>
				</StyledBFCMTextContainer>
				<StyledBFCMDismiss
					label={ __( 'Dismiss', 'better-wp-security' ) }
					icon={ dismissIcon }
					onClick={ () => setIsDismissed( true ) }
				/>
				<StyledLogo />
			</StyledBFCMBanner>
		</ThemeProvider>
	);
}
