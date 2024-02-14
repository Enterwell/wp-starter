/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Surface, Text } from '@ithemes/ui';

export const StyledGroupSelector = styled.fieldset`
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	padding: .5rem 1.5rem 1rem;
	gap: 0.75rem;
`;

export const StyledGroupControl = styled( Surface )`
	position: relative;
	display: inline;
	border-radius: 1.5rem;
`;

export const StyledGroupControlInput = styled.input`
	opacity: 0;
	position: absolute;
	width: 100%;
	height: 100%;
`;

export const StyledGroupControlLabel = styled( Text )`
	padding: 0.75rem 1.25rem 0.75rem 0.75rem;
`;
