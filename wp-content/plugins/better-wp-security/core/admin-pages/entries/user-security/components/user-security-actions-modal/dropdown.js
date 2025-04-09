/**
 * External dependencies
 */
import { truncate } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	chevronUp as chevronUpIcon,
	chevronDown as chevronDownIcon,
} from '@wordpress/icons';
import { useContext, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	StyledCaretDropdownButton,
	StyledDropdown,
	StyledHeadingUserCheckboxOption,
	StyledUserCheckboxDropdownContent,
	StyledUserSecurityModalAddButton,
	StyledUserSecurityModalDropdownOverlay,
} from './styles';
import { CheckboxControl } from '@ithemes/security-components';
import { UserSecurityActionsContext } from './editing-modal';

export function AddCheckboxControl( { label, value, indeterminate, selectedOptions, setSelectedOptions } ) {
	const isChecked = selectedOptions.includes( value );
	return (
		<CheckboxControl
			checked={ isChecked }
			label={ label }
			onChange={ ( next ) => {
				if ( next ) {
					setSelectedOptions( [
						...selectedOptions,
						value,
					] );
				} else {
					setSelectedOptions(
						selectedOptions.filter( ( maybe ) => maybe !== value )
					);
				}
			} }
			indeterminate={ ! isChecked && indeterminate }
		/>
	);
}

export function UserSecurityModalActionsDropdown( { dropdownTitle, dropdownButtonText, slug, options, confirmationText, ...props } ) {
	const { activeActions, setActiveActions, confirmationMessages, setConfirmationMessages } = useContext( UserSecurityActionsContext );
	const [ selectedOptions, setSelectedOptions ] = useState( [] );
	const selectedText = options
		.map( ( option ) => {
			if ( selectedOptions.includes( option.value ) ) {
				return option.label;
			}
			return '';
		} )
		.filter( ( label ) => label !== '' )
		.join( ', ' );
	return (
		<StyledDropdown
			contentClassName="itsec-user-security-modal-dropdown"
			popoverProps={ { placement: 'bottom-start' } }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<StyledCaretDropdownButton
					variant="tertiary"
					onClick={ onToggle }
					aria-expanded={ isOpen }
					text={ truncate( selectedText ) || __( 'Select Options', 'better-wp-security' ) }
					icon={ isOpen ? chevronUpIcon : chevronDownIcon }
					iconPosition="right"
				/>
			) }
			renderContent={ ( { onToggle } ) =>
				<StyledUserCheckboxDropdownContent>
					<StyledHeadingUserCheckboxOption
						level={ 2 }
						size={ TextSize.LARGE }
						variant={ TextVariant.DARK }
						weight={ 600 }
						text={ dropdownTitle }
					/>
					<StyledUserSecurityModalDropdownOverlay variant="secondary">
						{ options.map( ( option ) => (
							<AddCheckboxControl
								label={ option.label }
								value={ option.value }
								selectedOptions={ selectedOptions }
								setSelectedOptions={ setSelectedOptions }
								key={ option.value }
								indeterminate={ option.indeterminate }
							/>
						) ) }
					</StyledUserSecurityModalDropdownOverlay>
					<StyledUserSecurityModalAddButton
						text={ dropdownButtonText }
						onClick={ ( ) => {
							setActiveActions( {
								...activeActions,
								[ slug ]: selectedOptions,
							} );
							setConfirmationMessages( {
								...confirmationMessages,
								[ slug ]: confirmationText,
							} );
							onToggle();
						} }
						{ ...props }
					/>
				</StyledUserCheckboxDropdownContent>
			}
		/>
	);
}
