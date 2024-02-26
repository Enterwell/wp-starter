/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Surface, PageHeader } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { TabbedNavigation } from '@ithemes/security-ui';

export const StyledPageHeader = styled( PageHeader )`
	margin-bottom: 1rem;
`;

export const StyledFormContainer = styled( Surface )`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledSingleModuleSettingsContainer = styled.form`
	padding: 1rem 0;
`;

export const StyledSettingsActions = styled.div`
	display: flex;
	align-items: center;
	justify-content: end;
	gap: 1.5rem;
	margin-top: 2rem;
`;

export const StyledModuleList = styled.form`
	padding: 1.25rem 1.5rem;
	display: flex;
	flex-direction: column;
	gap: 1.25rem;
`;

export const StyledNavigation = styled( TabbedNavigation )`
	padding: 0 1.5rem;
`;

export const StyledOnboardWrapper = styled.div`
	flex-grow: 1;
	max-width: 830px;
	width: 100%;
`;
