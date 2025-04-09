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

export default function Search( { searchTerm, setSearchTerm, isQuerying } ) {
	return (
		<StyledSearchContainer>
			<SearchControl
				placeholder={ __( 'Search Lockouts', 'better-wp-security' ) }
				value={ searchTerm }
				onChange={ ( next ) => {
					setSearchTerm( next );
				} }
				isSearching={ isQuerying }
				size="small"
			/>
		</StyledSearchContainer>
	);
}
