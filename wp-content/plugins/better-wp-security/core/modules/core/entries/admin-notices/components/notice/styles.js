/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Surface, Text } from '@ithemes/ui';

export const StyledNotice = styled.article`
	flex-grow: 1;
	& > *:last-child {
		border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
		&:not(:first-child) {
			border-bottom-left-radius: 2px;
			border-bottom-right-radius: 2px;
		}
	}
`;

export const StyledHeader = styled.header`
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
	align-items: flex-start;
	padding: 0.5rem;
	border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-right: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-left: 4px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-radius: 2px;
`;

export const StyledMessage = styled.section`
	padding: 0.75rem;
    border-left: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-right: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledMeta = styled( Surface )`
	display: grid;
	grid-template: auto / min-content 1fr;
	gap: 1.25rem;
	margin: 0;
	padding: 0.75rem;
	border-left: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-right: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledMetaItem = styled( Text )`
	margin: 0;
`;

export const StyledFooter = styled.footer`
	padding: 0.5rem 0.75rem;
	border-left: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-right: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;
