/**
 * External dependencies
 */
import { Link } from 'react-router-dom';
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useViewportMatch } from '@wordpress/compose';
import {
	chevronLeftSmall,
	chevronRightSmall,
	settings as filterIcon,
} from '@wordpress/icons';
import { Dropdown } from '@wordpress/components';

/**
 * iTheme dependencies
 */
import {
	Button,
	FiltersGroupCheckboxes,
	Surface,
} from '@ithemes/ui';
import { siteScannerStore, vulnerabilitiesStore } from '@ithemes/security.packages.data';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import VulnerableSoftwareHeader from '../../components/vulnerable-software-header';
import VulnerabilityTable from '../../components/vulnerability-table';
import { StyledPageContainer, StyledPageHeader } from '../../components/styles';
import { BeforeHeaderSlot } from '../../components/before-header';
import {
	StyledFilters,
	StyledFilterTools,
	StyledButtonsContainer,
	StyledPagination, StyledSearchDivider,
} from './styles';
import '../../style.scss';

const QUERY_ARGS = {
	per_page: 100,
};

export default function Active() {
	const initialFilter = { resolution: [ 'unresolved', 'patched', 'deactivated' ] };
	const [ filters, setFilters ] = useState( initialFilter );
	const { query, fetchQueryNextPage, fetchQueryPrevPage } = useDispatch( vulnerabilitiesStore );
	const { items, isQuerying, hasResolved, getScans, queryHasNextPage, queryHasPrevPage } = useSelect( ( select ) => ( {
		items: select( vulnerabilitiesStore ).getVulnerabilities(),
		isQuerying: select( vulnerabilitiesStore ).isQuerying( 'main' ),
		hasResolved: select( vulnerabilitiesStore ).hasFinishedResolution( 'getVulnerabilities' ),
		queryHasNextPage: select( vulnerabilitiesStore ).queryHasNextPage( 'main' ),
		queryHasPrevPage: select( vulnerabilitiesStore ).queryHasPrevPage( 'main' ),
		getScans: select( siteScannerStore ).getScans(),
	} ), [] );

	const isSmall = useViewportMatch( 'small', '<' );

	const onApply = ( nextFilters ) => {
		setFilters( nextFilters );
		query( 'main', { ...nextFilters, ...QUERY_ARGS } );
	};

	const onReset = ( ) => {
		setFilters( initialFilter );
		query( 'main', { ...initialFilter, ...QUERY_ARGS } );
		onApply( initialFilter );
	};

	const getPrev = () => {
		fetchQueryPrevPage( 'main', 'replace' );
	};

	const getNext = () => {
		fetchQueryNextPage( 'main', 'replace' );
	};

	const filterLength = Object.keys( filters ).filter( ( key ) => ! isEmpty( filters[ key ] ) ).length;

	return (
		<StyledPageContainer>
			<BeforeHeaderSlot />
			<StyledPageHeader isSmall={ isSmall }>
				<StyledFilterTools>
					<Dropdown
						popoverProps={ { focusOnMount: 'container' } }
						renderToggle={ ( { isOpen, onToggle } ) => (
							<Button
								icon={ filterIcon }
								onClick={ onToggle }
								aria-expanded={ isOpen }
								variant="tertiary"
								text={ sprintf(
									/* translators: 1. Number of filters. */
									__( 'Filter (%d)', 'better-wp-security' ),
									filterLength
								) }
							/>
						) }
						renderContent={ () => (
							<StyledFilters
								initialValue={ filters }
								initialOpen={ [ 'software_type' ] }
								expandSingle
								isBusy={ isQuerying }
								onApply={ onApply }
							>
								<FiltersGroupCheckboxes
									slug="software_type"
									title={ __( 'Types', 'better-wp-security' ) }
									options={ [
										{ value: 'theme', label: __( 'Themes', 'better-wp-security' ) },
										{ value: 'plugin', label: __( 'Plugins', 'better-wp-security' ) },
										{ value: 'wordpress', label: __( 'Core', 'better-wp-security' ) },
									] }
								/>
								<FiltersGroupCheckboxes
									slug="resolution"
									title={ __( 'Status', 'better-wp-security' ) }
									options={ [
										{ value: 'unresolved', label: __( 'Unresolved', 'better-wp-security' ) },
										{ value: 'patched', label: __( 'Mitigated', 'better-wp-security' ) },
										{ value: 'auto-updated', label: __( 'Auto-Updated', 'better-wp-security' ) },
										{ value: 'updated', label: __( 'Updated', 'better-wp-security' ) },
										{ value: 'muted', label: __( 'Muted', 'better-wp-security' ) },
										{ value: 'deactivated', label: __( 'Deactivated', 'better-wp-security' ) },
										{ value: 'deleted', label: __( 'Deleted', 'better-wp-security' ) },
									] }
								/>
							</StyledFilters>
						) }
					/>
					{ filterLength > 0 &&
						<>
							<StyledSearchDivider>&#124;</StyledSearchDivider>
							<Button
								onClick={ onReset }
								variant="tertiary"
								text={ __( 'Reset all', 'better-wp-security' ) }
							/>
						</>
					}
				</StyledFilterTools>
				<StyledButtonsContainer isSmall={ isSmall }>
					<Link to="/database" component={ withNavigate( Button ) } text={ __( 'Browse Vulnerability Database', 'better-wp-security' ) } />
					<Link to="/scan" replace component={ withNavigate( Button ) } onClick={ onReset } variant="primary" text={ __( 'Scan for Vulnerabilities', 'better-wp-security' ) } />
				</StyledButtonsContainer>
			</StyledPageHeader>

			<Surface as="section">
				<VulnerableSoftwareHeader />
				{ hasResolved && <VulnerabilityTable getScans={ getScans } items={ items } filters={ filters } /> }
			</Surface>
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
		</StyledPageContainer>
	);
}
