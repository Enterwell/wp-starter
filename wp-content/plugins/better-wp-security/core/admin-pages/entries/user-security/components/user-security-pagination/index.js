/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { chevronLeftSmall, chevronRightSmall } from '@wordpress/icons';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { userSecurityStore } from '@ithemes/security.packages.data';
import { StyledPagination } from './styles';

export function UserSecurityPagination() {
	const { queryHasNextPage, queryHasPrevPage } = useSelect(
		( select ) => ( {
			queryHasPrevPage: select( userSecurityStore ).queryHasPrevPage( 'main' ),
			queryHasNextPage: select( userSecurityStore ).queryHasNextPage( 'main' ),
		} ),
		[]
	);

	const { fetchQueryPrevPage, fetchQueryNextPage } = useDispatch( userSecurityStore );

	const getPrev = () => {
		fetchQueryPrevPage( 'main', 'replace' );
	};

	const getNext = () => {
		fetchQueryNextPage( 'main', 'replace' );
	};

	return (
		<StyledPagination>
			<Button
				disabled={ ! queryHasPrevPage }
				icon={ chevronLeftSmall }
				iconGap={ 0 }
				variant="tertiary"
				onClick={ getPrev }
				text={ __( 'Prev', 'better-wp-security' ) }
			/>
			<Button
				disabled={ ! queryHasNextPage }
				icon={ chevronRightSmall }
				iconPosition="right"
				iconGap={ 0 }
				variant="tertiary"
				onClick={ getNext }
				text={ __( 'Next', 'better-wp-security' ) }
			/>
		</StyledPagination>
	);
}
