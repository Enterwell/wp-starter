/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { ListItem, Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import { EmptyState } from './index';
import {
	vulnerabilityIcon,
	severityColor,
	statusIcon,
	StyledSeverity,
	StyledListHeading,
	StyledList,
	StyledTopRow,
	StyledBottomRow,
	StyledLink,
	StyledStatusResolution,
} from './styles';

export default function VulnerabilityList( { cardData } ) {
	return (
		<div>
			<StyledListHeading as="p" variant={ TextVariant.DARK } text={ __( 'Vulnerabilities Overview', 'better-wp-security' ) } />
			<StyledList>
				{ cardData.vulnerabilities.length === 0 ? <EmptyState date={ cardData.data } />
					: cardData.vulnerabilities.map( ( vulnerability ) => (
						<VulnerabilityListItem key={ vulnerability.id } vulnerability={ vulnerability } />
					) ) }
			</StyledList>
		</div>
	);
}

function VulnerabilityListItem( { vulnerability } ) {
	return (
		<ListItem>
			<StyledTopRow>
				<Text icon={ vulnerabilityIcon( vulnerability.software.type.slug ) } text={ vulnerability.software.type.label } />
				{ vulnerability.software.type.slug !== 'wordpress' && (
					<Text weight={ 500 } text={ vulnerability.software.label || vulnerability.software.slug } />
				) }
				<StyledSeverity backgroundColor={ severityColor( vulnerability.details.score ) } weight={ 600 } text={ vulnerability.details.score ?? '??' } />
				<Text
					text={ sprintf(
						/* translators: 1. Human time diff. */
						__( '%s ago', 'better-wp-security' ),
						vulnerability.last_seen_diff
					) }
				/>
			</StyledTopRow>
			<StyledBottomRow>
				<StyledStatusResolution
					icon={ statusIcon( vulnerability.resolution.slug ) }
					iconSize={ 16 }
					text={ vulnerability.resolution.label }
				/>
				<StyledLink href={ useGlobalNavigationUrl( 'vulnerabilities', `/vulnerability/${ vulnerability.id }` ) }>
					{ __( 'View Details', 'better-wp-security' ) }
				</StyledLink>
			</StyledBottomRow>
		</ListItem>
	);
}
