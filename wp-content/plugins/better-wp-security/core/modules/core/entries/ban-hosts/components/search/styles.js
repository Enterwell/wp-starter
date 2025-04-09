/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { SelectControl } from '@ithemes/security-components';

export const StyledSearchControlContainer = styled.section`
	display: flex;
	align-items: center;
	gap: 0.75rem;
	padding: 1rem;
`;

export const StyledSelectControl = styled( SelectControl )`
	select.components-select-control__input {
		width: 100%
	}
`;
