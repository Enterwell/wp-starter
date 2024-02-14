/**
 * WordPress dependencies
 */
import { dateI18n } from '@wordpress/date';
import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import {
	Button,
	Text,
	TextSize,
	TextVariant,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	CardHeader,
	CardHeaderTitle,
} from '@ithemes/security.dashboard.dashboard';
import { HiResIcon } from '@ithemes/security-ui';
import { Patchstack } from '@ithemes/security-style-guide';
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import VulnerabilityList from './list';
import VulnerabilityTable from './table';
import {
	StyledEmptyState,
	StyledVulnerabilitySuccess,
	StyledSuccessText,
	StyledContainer,
	StyledBrand,
	StyledBrandSmall,
	StyledFooter,
} from './styles';

export function EmptyState( { date } ) {
	const siteScanUrl = useGlobalNavigationUrl( 'site-scan' );

	return (
		<StyledEmptyState>
			<HiResIcon icon={ <StyledVulnerabilitySuccess /> } isSmall />
			<Text
				variant={ TextVariant.DARK }
				weight={ TextWeight.HEAVY }
				text={ date
					? __( 'No Vulnerabilities Found!', 'better-wp-security' )
					: __( 'Waiting for scan results', 'better-wp-security' )
				}
			/>
			<StyledSuccessText
				align="center"
				size={ TextSize.SMALL }
				variant={ TextVariant.DARK }
				text={ date
					? __( 'Your site has been successfully checked against the Patchstack vulnerability database.', 'better-wp-security' )
					: createInterpolateElement(
						__( 'Your site hasn’t been scanned yet. Run your first scan from the <a>Site Scans</a> page.', 'better-wp-security' ),
						{
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							a: <a href={ siteScanUrl } />,
						}
					)
				}
			/>
			{ date && (
				<Text
					size={ TextSize.SMALL }
					variant={ TextVariant.DARK }
					text={ sprintf(
					/* translators: The most recent scan date*/
						__( 'Last Scan: %s' ), dateI18n( 'M d, Y', date ) )
					}
				/>
			) }
		</StyledEmptyState>
	);
}

export default function VulnerableSoftware( { card, config, eqProps } ) {
	const isSmall = eqProps[ 'max-width' ] && eqProps[ 'max-width' ].includes( '400px' );
	const isWide = eqProps[ 'min-width' ] && eqProps[ 'min-width' ].includes( '1220px' );
	/* translators: 1. The date of the last check. */
	const lastScan = isSmall ? __( 'Last scanned on %s.', 'better-wp-security' ) : __( 'This website was last checked against the vulnerability database on %s.', 'better-wp-security' );

	return (
		<StyledContainer>
			<CardHeader>
				<div>
					<CardHeaderTitle card={ card } config={ config } />
					{ card.data.date && (
						<Text
							size={ TextSize.SMALL }
							variant={ TextVariant.MUTED }
							text={ sprintf( lastScan, dateI18n( 'M d, Y', card.data.date ) ) }
						/>
					) }
				</div>
				{ isSmall
					? <StyledBrandSmall>
						<Text weight={ 600 } text={ __( 'Powered by', 'better-wp-security' ) } />
						<Patchstack height={ 21 } />
					</StyledBrandSmall>
					: <StyledBrand>
						<Text size={ TextSize.SMALL } weight={ 600 } text={ __( 'Powered by', 'better-wp-security' ) } />
						<Patchstack height={ 21 } alt={ __( 'Patchstack', 'better-wp-security' ) } />
					</StyledBrand>
				}
			</CardHeader>
			{ isSmall
				? <VulnerabilityList cardData={ card.data } />
				: <VulnerabilityTable cardData={ card.data } isWide={ isWide } />
			}
			<StyledFooter as="footer">
				<Button variant="primary" text={ __( 'View Vulnerabilities', 'better-wp-security' ) } href={ useGlobalNavigationUrl( 'vulnerabilities' ) } />
			</StyledFooter>
		</StyledContainer>
	);
}

export const slug = 'vulnerable-software';
export const settings = {
	render: VulnerableSoftware,
	elementQueries: [
		{
			type: 'width',
			dir: 'max',
			px: 400,
		},
		{
			type: 'width',
			dir: 'min',
			px: 1220,
		},
	],
};
