/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { css } from '@emotion/css';

/**
 * Solid dependencies
 */
import { Button } from '@ithemes/ui';

export const halfFlexBasis = css`
	flex-basis: 50%;
`;

export const StyledRuleAction = styled( Button )`
	margin-top: 23px; // Hard-coded to match label + gap.
`;
