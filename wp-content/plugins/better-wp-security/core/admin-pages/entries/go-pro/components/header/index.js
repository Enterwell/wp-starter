/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * iThemes dependencies
 */
import { Heading, Text } from '@ithemes/ui';

const StyledHeader = styled.header`
	max-width: 900px;
	text-align: center;
`;

export default function Header( { title, subtitle } ) {
	return (
		<StyledHeader>
			<Heading level={ 1 } text={ title } />
			<Text as="p" size="large" text={ subtitle } />
		</StyledHeader>
	);
}
