/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import { Text, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	StyledModalActionSection,
	StyledModalActionSectionButton,
} from './styles';
import { UserSecurityModalActionsDropdown } from './dropdown';
import { UserSecurityActionsContext } from './editing-modal';

export function EditingModalActionTitleAndDescription( { title, description } ) {
	return (
		<>
			<Text
				weight={ TextWeight.HEAVY }
				text={ title }
			/>
			<Text
				text={ description }
			/>
		</>
	);
}

export function EditingModalActionButton( { title, description, buttonText, slug, confirmationText, ...props } ) {
	const { activeActions, setActiveActions, confirmationMessages, setConfirmationMessages } = useContext( UserSecurityActionsContext );
	return (
		<StyledModalActionSection>
			<EditingModalActionTitleAndDescription
				title={ title }
				description={ description }
			/>
			<StyledModalActionSectionButton
				variant="secondary"
				onClick={ () => {
					setActiveActions( {
						...activeActions,
						[ slug ]: true,
					} );
					setConfirmationMessages( {
						...confirmationMessages,
						[ slug ]: confirmationText,
					} );
				} }
				{ ...props }
			>
				{ buttonText }
			</StyledModalActionSectionButton>
		</StyledModalActionSection>
	);
}

export function EditingModalActionDropdown( {
	title,
	description,
	dropdownTitle,
	dropdownButtonText,
	slug,
	confirmationText,
	options,
	...props
} ) {
	return (
		<StyledModalActionSection>
			<EditingModalActionTitleAndDescription
				title={ title }
				description={ description }
			/>
			<UserSecurityModalActionsDropdown
				dropdownTitle={ dropdownTitle }
				dropdownButtonText={ dropdownButtonText }
				slug={ slug }
				confirmationText={ confirmationText }
				options={ options }
				{ ...props }
			/>
		</StyledModalActionSection>
	);
}
