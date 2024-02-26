/**
 * External dependencies
 */
import { Link } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';

/**
 * iTheme dependencies
 */
import { Button, Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledLastScanDate, StyledSuccess, StyledSuccessPanel } from './styles';
import { VulnerabilitySuccess } from '@ithemes/security-style-guide';
import { HiResIcon } from '@ithemes/security-ui';
import { withNavigate } from '@ithemes/security-hocs';

export function NoVulnerabilitiesEmptyState( { getScans } ) {
	return (
		<StyledSuccessPanel>
			<StyledSuccess>
				<HiResIcon icon={ <VulnerabilitySuccess style={ { height: '135px' } } /> } />
				<Text variant={ TextVariant.DARK } weight={ 700 } text={ __( 'No Vulnerabilities Found!', 'better-wp-security' ) } />
				<Text align="center" variant={ TextVariant.DARK } text={ __( 'Your site has been successfully checked against the Patchstack vulnerability database.', 'better-wp-security' ) } />
				<StyledLastScanDate
					hasScanDate={ getScans.length }
					variant={ TextVariant.DARK }
					weight={ 600 }
					text={
						sprintf(
							/* translators: 1. The date of the last scan. */
							__( 'Last Scan: %s', 'better-wp-security' ),
							dateI18n( 'm/d/Y', getScans[ 0 ]?.time )
						) } />
				<Link to="/scan" replace component={ withNavigate( Button ) } variant="primary" text={ __( 'Scan for Vulnerabilities', 'better-wp-security' ) } />
			</StyledSuccess>
		</StyledSuccessPanel>
	);
}
