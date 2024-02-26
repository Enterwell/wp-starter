/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { SearchControl } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledSearchContainer } from './styles';

export default function Search( { searchTerm, setSearchTerm, isQuerying, query, queryId } ) {
	return (
		<StyledSearchContainer>
			<SearchControl
				placeholder={ __( 'Search Lockouts', 'better-wp-security' ) }
				value={ searchTerm }
				onChange={ ( next ) => {
					setSearchTerm( next );
					query( queryId, next ? { search: next } : {} );
				} }
				isSearching={ isQuerying }
				size="small"
			/>
		</StyledSearchContainer>
	);
}
