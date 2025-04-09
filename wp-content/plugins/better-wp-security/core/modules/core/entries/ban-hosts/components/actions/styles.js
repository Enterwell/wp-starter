/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledActions = styled( Surface )`
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	flex-shrink: 0;
	justify-content: flex-end;
	position: sticky;
	bottom: 0;
	padding: 0.5rem 1.25rem;
	gap: 0.5rem;
	margin-top: auto;
	border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;
