/**
 * External dependencies
 */
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { Dropdown } from '@wordpress/components';
import { settings as filterIcon } from '@wordpress/icons';

/**
 * Solid dependencies
 */
import {
	Button,
	Text,
	Heading,
	FiltersGroupCheckboxes,
	FiltersGroupDropdown,
	TextSize,
	TextVariant,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { firewallStore } from '@ithemes/security.packages.data';
import { StyledRulesTableHeader, StyledSearchContainer, StyledSearchControl, StyledFilters, StyledSearchDivider } from './styles';

const QUERY_ARGS = {
	per_page: 100,
};
const INITIAL_FILTER = { paused: 'false' };

export default function RulesTableHeader() {
	const { isQuerying } = useSelect( ( select ) => ( {
		isQuerying: select( firewallStore ).isQuerying( 'main' ),
	} ), [] );
	const { query } = useDispatch( firewallStore );

	const [ search, setSearch ] = useState( '' );

	const onSearch = () => {
		query( 'main', { search, ...filters, ...QUERY_ARGS } );
	};
	const onSubmit = ( e ) => {
		e.preventDefault();
		onSearch();
	};

	const [ filters, setFilters ] = useState( INITIAL_FILTER );
	const onApplyFilters = ( nextFilters ) => {
		setFilters( nextFilters );
		query( 'main', { ...nextFilters, search, ...QUERY_ARGS } );
	};

	const filterLength = Object.keys( filters ).filter( ( key ) => ! isEmpty( filters[ key ] ) ).length;

	const onReset = () => {
		setSearch( '' );
		setFilters( INITIAL_FILTER );
		query( 'main', { ...INITIAL_FILTER, ...QUERY_ARGS } );
	};

	return (
		<StyledRulesTableHeader onSubmit={ onSubmit }>
			<Heading
				level={ 2 }
				size={ TextSize.LARGE }
				variant={ TextVariant.DARK }
				weight={ TextWeight.HEAVY }
				text={ __( 'Firewall Rules', 'better-wp-security' ) }
			/>
			<Text
				text={ __( 'Firewall rules block requests based on patterns.', 'better-wp-security' ) }
				variant={ TextVariant.MUTED }
				size={ TextSize.SMALL }
			/>
			<StyledSearchContainer role="search">
				<StyledSearchControl
					label={ __( 'Search firewall rules', 'better-wp-security' ) }
					value={ search }
					onChange={ setSearch }
					isSearching={ isQuerying }
					size="medium"
					placeholder={ __( 'Search by title', 'better-wp-security' ) }
					onSubmit={ onSearch }
				/>
				<Dropdown
					popoverProps={ { focusOnMount: 'container' } }
					renderToggle={ ( { isOpen, onToggle } ) => (
						<Button
							icon={ filterIcon }
							onClick={ onToggle }
							aria-expanded={ isOpen }
							variant="tertiary"
							text={ sprintf(
							/* translators: 1. Number of filters */
								__( 'Filter (%d)', 'better-wp-security' ),
								filterLength
							) }
						/>
					) }
					renderContent={ () => (
						<StyledFilters
							initialValue={ filters }
							initialOpen="paused"
							expandSingle
							isBusy={ isQuerying }
							onApply={ onApplyFilters }
						>
							<FiltersGroupDropdown slug="paused" title={ __( 'Status', 'better-wp-security' ) } options={ [
								{ value: 'false', label: __( 'Active', 'better-wp-security' ), summary: __( 'Active Rules', 'better-wp-security' ) },
								{ value: 'true', label: __( 'Inactive', 'better-wp-security' ), summary: __( 'Inactive Rules', 'better-wp-security' ) },
							] } />
							<FiltersGroupCheckboxes slug="provider" title={ __( 'Source', 'better-wp-security' ) } options={ [
								{ value: 'patchstack', label: __( 'Patchstack', 'better-wp-security' ) },
								{ value: 'solid', label: __( 'Solid Security', 'better-wp-security' ) },
								{ value: 'user', label: __( 'Custom Rules', 'better-wp-security' ) },
							] } />
						</StyledFilters>
					) }
				/>
				<StyledSearchDivider>&#124;</StyledSearchDivider>
				<Button
					onClick={ onReset }
					variant="tertiary"
					text={ __( 'Reset all', 'better-wp-security' ) }
				/>
			</StyledSearchContainer>
		</StyledRulesTableHeader>
	);
}
