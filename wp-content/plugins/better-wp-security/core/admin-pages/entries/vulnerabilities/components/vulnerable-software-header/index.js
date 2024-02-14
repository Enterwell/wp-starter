/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { useSelect } from '@wordpress/data';
import { useViewportMatch } from '@wordpress/compose';

/**
 * Solid WP dependencies
 */
import { TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { siteScannerStore } from '@ithemes/security.packages.data';
import {
	ActiveUpdatesBadge,
	VirtualPatchingBadge,
} from '@ithemes/security-ui';
import {
	StyledBadgesContainer,
	StyledBrand,
	StyledLogoText,
	StyledLogoImage,
	StyledHeader,
	StyledLastScanDate,
	StyledHeading,
} from './styles';

export default function VulnerableSoftwareHeader() {
	const { scans } = useSelect( ( select ) => ( {
		scans: select( siteScannerStore ).getScans(),
	} ), [] );

	const isSmall = useViewportMatch( 'small', '<' );
	const isLarge = useViewportMatch( 'large' );

	return (
		<StyledHeader isSmall={ isSmall }>
			<div>
				<StyledHeading
					isSmall={ isSmall }
					level={ 2 }
					size={ TextSize.LARGE }
					variant={ TextVariant.DARK }
					weight={ 600 }
					text={ __( 'Vulnerable Software', 'better-wp-security' ) }
				/>
				<StyledLastScanDate hasScanDate={ scans.length > 0 } variant={ TextVariant.MUTED } text={ sprintf(
				/* translators: 1. The date and time of the last check. */
					__( 'This website was last checked against the vulnerability database on %s.', 'better-wp-security' ),
					dateI18n( 'm/d/Y (g:i A)', scans[ 0 ]?.time ) ) } />
				<StyledBadgesContainer isSmall={ isSmall }>
					<ActiveUpdatesBadge />
					<VirtualPatchingBadge />
				</StyledBadgesContainer>
			</div>
			<StyledBrand isSmall={ isSmall }>
				<StyledLogoText weight={ 600 } text={ __( 'Powered by', 'better-wp-security' ) } />
				<StyledLogoImage isLarge={ isLarge } />
			</StyledBrand>
		</StyledHeader>
	);
}
