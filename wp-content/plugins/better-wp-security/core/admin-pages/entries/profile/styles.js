/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { TabPanel } from '@ithemes/ui';

export const StyledProfileContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 1rem;
	background: #E9E7EE;
	margin: 1rem 0 1rem -0.625rem;
	padding: 0.625rem;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		margin: 1rem -1.25rem 1rem -1.25rem;
		padding: 1.25rem;
	}
`;

export const StyledTabs = styled( TabPanel )`
	.is-active:after {
		height: 0;
	}
`;
