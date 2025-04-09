/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { create as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { CARD_MARGIN } from '../../utils';

const StyledEmptyState = styled.div`
	display: flex;
	width: ${ ( { maxWidth } ) => maxWidth - ( CARD_MARGIN * 2 ) }px;
	height: 80vh;
	margin: 20px auto;
	border: 2px dashed ${ ( { theme } ) => theme.colors.primary.base };
`;

const StyledButton = styled( Button )`
	gap: 0.5rem;
	width: 100%;
	height: auto;
	flex-direction: column;
	font-size: 1.25rem;
	color: ${ ( { theme } ) => theme.colors.text.muted };

	& svg {
		color: white;
		background: ${ ( { theme } ) => theme.colors.primary.base };
		border-radius: 2px;
		width: 20px;
		height: 20px;
	}
`;

export default function EmptyState( { maxWidth } ) {
	const { openEditCards } = useDispatch( 'ithemes-security/dashboard' );

	return (
		<StyledEmptyState maxWidth={ maxWidth }>
			<StyledButton
				onClick={ openEditCards }
				icon={ icon }
				text={ __( 'Add Security Cards', 'better-wp-security' ) }
			/>
		</StyledEmptyState>
	);
}
