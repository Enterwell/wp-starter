/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { MasterDetailBackButton, Surface } from '@ithemes/ui';

export const StyledSearchContainer = styled.div`
	padding: 1rem;
	max-width: 400px;
`;

export const StyledSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	height: 100%;
`;

export const BodyContainer = styled.div`
	text-align: center;
	display: flex;
	gap: 1.25rem;
	flex-direction: column;
	align-items: center;
	flex-grow: 1;
	height: 100%;
	justify-content: center;
	padding: 1rem;
`;

export const StyledSection = styled.section`
	max-width: 70ch;
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
`;

export const StyledActiveLockoutsContainer = styled.div`
	padding-bottom: 2rem;
`;

export const StyledMasterDetailBackButton = styled( MasterDetailBackButton )`
	padding: 1rem !important;
	margin-top: 1rem;
`;
