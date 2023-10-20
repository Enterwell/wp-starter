import styled from '@emotion/styled';
import { Text } from '@ithemes/ui';

export const StyledSuccessPanel = styled.div`
	display: flex;
	align-items: center;
	justify-content: center;
	max-width: 1680px;
`;

export const StyledSuccess = styled.div`
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 1.25rem;
	max-width: 300px;
	padding-top: 70px;
	padding-bottom: 70px;
`;

export const StyledLastScanDate = styled( Text, {
	shouldForwardProp: ( prop ) => prop !== 'hasScanDate' } )`
	visibility: ${ ( { hasScanDate } ) => hasScanDate ? 'visible' : 'hidden' };
`;
