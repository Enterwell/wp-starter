/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { TabPanel } from '@ithemes/ui';
import { ErrorList } from '@ithemes/security-ui';

export const StyledTabPanel = styled( TabPanel )`
	.components-tab-panel__tabs {
		padding: 0 1.5rem;
	}

	.components-tab-panel__tab-content {
		padding: 1rem 1.5rem;
	}
`;

export const StyledErrorList = styled( ErrorList )`
	margin-bottom: 1rem;
`;
