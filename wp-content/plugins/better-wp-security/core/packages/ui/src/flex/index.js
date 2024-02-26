import styled from '@emotion/styled';

const StyledFlexSpacer = styled.div`
	flex-grow: 1;
`;

export default function FlexSpacer() {
	return (
		<StyledFlexSpacer aria-hidden />
	);
}
