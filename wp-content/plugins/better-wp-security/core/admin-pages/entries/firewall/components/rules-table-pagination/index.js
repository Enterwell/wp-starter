/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { chevronLeftSmall, chevronRightSmall } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { firewallStore } from '@ithemes/security.packages.data';
import { StyledPagination } from './styles';

export default function RulesTablePagination() {
	const { isQuerying, hasPrev, hasNext } = useSelect( ( select ) => ( {
		isQuerying: select( firewallStore ).isQuerying( 'main' ),
		hasPrev: select( firewallStore ).queryHasPrevPage( 'main' ),
		hasNext: select( firewallStore ).queryHasNextPage( 'main' ),
	} ), [] );
	const { fetchQueryPrevPage, fetchQueryNextPage } = useDispatch( firewallStore );

	return (
		<StyledPagination>
			<Button
				disabled={ ! hasPrev || isQuerying }
				icon={ chevronLeftSmall }
				iconGap={ 0 }
				variant="tertiary"
				onClick={ () => fetchQueryPrevPage( 'main', 'replace' ) }
				text={ __( 'Prev', 'better-wp-security' ) }
			/>
			<Button
				disabled={ ! hasNext || isQuerying }
				icon={ chevronRightSmall }
				iconPosition="right"
				iconGap={ 0 }
				variant="tertiary"
				onClick={ () => fetchQueryNextPage( 'main', 'replace' ) }
				text={ __( 'Next', 'better-wp-security' ) }
			/>
		</StyledPagination>
	);
}
