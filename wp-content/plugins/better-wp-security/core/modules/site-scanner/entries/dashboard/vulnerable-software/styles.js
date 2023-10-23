/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import {
	brush as themeIcon,
	check as checkIcon,
	closeSmall as closeIcon,
	plugins as pluginIcon,
	wordpress as coreIcon,
} from '@wordpress/icons';
import { Icon } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { List, Surface, Text } from '@ithemes/ui';
import { VulnerabilityMitigated, VulnerabilitySuccess } from '@ithemes/security-style-guide';

export function vulnerabilityIcon( type ) {
	switch ( type ) {
		case 'plugin':
			return pluginIcon;
		case 'theme':
			return themeIcon;
		case 'core':
			return coreIcon;
		default:
			return undefined;
	}
}

export function severityColor( score ) {
	switch ( true ) {
		case score === null:
			return '#CECECE';
		case score < 3:
			return '#B8E6BF';
		case score < 7:
			return '#FFC518';
		case score < 9:
			return '#FFABAF';
		default:
			return '#D63638';
	}
}

export function statusIcon( status ) {
	switch ( status ) {
		case '':
			return <StyledStatusRedCircle icon={ closeIcon } style={ { fill: '#D75A4B' } } />;
		case 'auto-updated':
		case 'deactivated':
		case 'deleted':
		case 'updated':
			return <StyledStatusCheck icon={ checkIcon } style={ { fill: '#FFFFFF' } } />;
		case 'patched':
			return <VulnerabilityMitigated />;
		default:
	}
}

export const StyledEmptyState = styled( Surface )`
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	padding: ${ ( { theme: { getSize } } ) => `${ getSize( 1.5 ) } 0` };
	gap: ${ ( { theme: { getSize } } ) => getSize( 0.75 ) };
`;

export const StyledContainer = styled( Surface )`
	display: flex;
	flex-direction: column;
	height: 100%;
	container-type: inline-size;
	overflow: auto;
`;

export const StyledBrand = styled.div`
	display: flex;
	flex-direction: column;
	align-items: flex-end;
`;

export const StyledBrandSmall = styled( StyledBrand )`
	margin-left: 1rem;
	& span {
		font-size: 0.5rem;
	}
	& svg {
		width: 100px;
	}
`;

export const StyledFooter = styled( Surface )`
	display: flex;
	justify-content: flex-end;
	position: sticky;
	bottom: 0;
	padding: 0.5rem 1.25rem;
	margin-top: auto;
	border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledVulnerabilitySuccess = styled( VulnerabilitySuccess )`
	height: 56px;
	width: 56px;
`;

export const StyledSuccessText = styled( Text )`
	padding: ${ ( { theme: { getSize } } ) => `0 ${ getSize( 0.5 ) }` };
`;

export const StyledSeverity = styled( Text )`
	padding: 1.5px 6.5px;
	background-color: ${ ( { backgroundColor } ) => backgroundColor };
	border-radius: 2px;
	width:35px;
	display: flex;
	justify-content: center;
`;

export const StyledStatusCheck = styled( Icon )`
	background-color: #438C56;
	border-radius: 2rem;
`;

export const StyledStatusRedCircle = styled( Icon )`
	background-color: #FFABAF;
	border-radius: 2rem;
`;

// Table-specific styles
export const StyledVulnerabilityName = styled( Text )`
	grid-area: name;
`;

export const StyledVulnerabilityVersion = styled( Text )`
	grid-area: version
`;

export const StyledVulnerabilityDetail = styled( Text )`
	grid-area: detail;
`;

export const StyledVulnerability = styled( Surface, {
	shouldForwardProp: ( prop ) => prop !== 'isWide',
} )`
	display: grid;
	grid-template-columns: ${ ( { isWide } ) => isWide ? '1fr 1fr 1fr' : '1fr 0.5fr 1fr' };
	grid-template-areas: ${ ( { isWide } ) => isWide ? '"name version detail"' : '"name name name" "version detail detail"' };
	align-items: center;
`;

export const StyledTableSection = styled( Surface )`
	flex-shrink: 1;
	overflow-y: auto;
	position: relative;
`;

// List-specific styles
export const StyledStatusResolution = styled( Text )`
	grid-column: 2;
`;

export const StyledListHeading = styled( Text )`
	padding: 0.875rem;
	text-transform: uppercase;
	background-color: ${ ( { theme } ) => theme.colors.surface.tertiary };
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledList = styled( List )`
	padding: 0.5rem 0.25rem 0.5rem 0.5rem;
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledTopRow = styled( Surface )`
	display: grid;
	align-items: center;
	grid-template-columns: 1fr 1.25fr 0.75fr 1fr;
	margin-bottom: 1rem;
`;

export const StyledBottomRow = styled( Surface )`
	display: grid;
	align-items: center;
	grid-template-columns: 1fr 1fr 1fr 1fr;
`;

export const StyledLink = styled.a`
	grid-column: 4;
`;
