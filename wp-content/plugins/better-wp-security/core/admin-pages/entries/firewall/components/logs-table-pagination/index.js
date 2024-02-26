/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { chevronLeftSmall, chevronRightSmall } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { logsStore } from '@ithemes/security.packages.data';
import { StyledPagination } from './styles';

export default function LogsTablePagination() {
	const { isQuerying, hasPrev, hasNext } = useSelect( ( select ) => ( {
		isQuerying: select( logsStore ).isQuerying( 'firewall' ),
		hasPrev: select( logsStore ).queryHasPrevPage( 'firewall' ),
		hasNext: select( logsStore ).queryHasNextPage( 'firewall' ),
	} ), [] );
	const { fetchQueryPrevPage, fetchQueryNextPage } = useDispatch( logsStore );

	return (
		<StyledPagination>
			<Button
				disabled={ ! hasPrev || isQuerying }
				icon={ chevronLeftSmall }
				iconGap={ 0 }
				variant="tertiary"
				onClick={ () => fetchQueryPrevPage( 'firewall', 'replace' ) }
				text={ __( 'Prev', 'better-wp-security' ) }
			/>
			<Button
				disabled={ ! hasNext || isQuerying }
				icon={ chevronRightSmall }
				iconPosition="right"
				iconGap={ 0 }
				variant="tertiary"
				onClick={ () => fetchQueryNextPage( 'firewall', 'replace' ) }
				text={ __( 'Next', 'better-wp-security' ) }
			/>
		</StyledPagination>
	);
}
