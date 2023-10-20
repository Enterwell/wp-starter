/**
 * External dependencies
 */
import styled from '@emotion/styled';

const StyledFooter = styled.footer`
	display: flex;
	flex-wrap: wrap;
	flex-shrink: 0;
	align-items: center;
	gap: 0.5rem;
	padding: 0.5rem 1.25rem;
	margin-top: auto;
	border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };

	&:empty {
		display: none;
	}
`;

function Footer( { children } ) {
	return <StyledFooter>{ children }</StyledFooter>;
}

export default Footer;
export { default as FooterSchemaActions } from './schema-actions';
