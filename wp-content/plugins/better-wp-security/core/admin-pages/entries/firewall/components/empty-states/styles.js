/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { Link } from 'react-router-dom';

export const StyledEmptyState = styled.div`
	display: flex;
	align-items: center;
	justify-content: center;
	max-width: 1680px;
	min-height: 400px;
`;

export const StyledContent = styled.div`
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 1.25rem;
	max-width: 400px;
	padding-top: 70px;
	padding-bottom: 70px;
`;

export const StyledLink = styled( Link )`
	padding-right: 14px !important;
`;
