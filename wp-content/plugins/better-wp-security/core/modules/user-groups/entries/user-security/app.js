/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import {
	Button,
	FiltersGroupCheckboxes,
	Text,
	TextSize,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	EditingModalActionFill,
	EditingModalActionDropdown,
	UserSecurityFilterFill,
	UserSecurityActionsFill,
} from '@ithemes/security.pages.user-security';
import {
	useSettingsDefinitions,
	SettingsForm,
	SingleSettingField,
	BulkSettingField,
	store as uiStore,
} from '@ithemes/security.user-groups.ui';
import { store as userGroupsStore } from '@ithemes/security.user-groups.api';
import { userSecurityStore } from '@ithemes/security.packages.data';
import {
	StyledButtonsContainer,
	StyledModalPillContainer,
	StyledSettingsFormContainer,
	StyledUserSecurityUserGroupActionsModal,
	StyledUserGroupPill,
	StyledUserGroupsConfirmationButton,
} from './styles';

export default function App() {
	const [ isOpen, setIsOpen ] = useState( false );

	const { matchables, queryParams } = useSelect(
		( select ) => ( {
			matchables: select( userGroupsStore ).getMatchables(),
			queryParams: select( userSecurityStore ).getQueryParams( 'main' ) || {},
		} ),
		[]
	);

	const userGroups = matchables
		.filter( ( group ) => group.type === 'user-group' )
		.map( ( match ) => ( { value: match.id, label: match.label, indeterminate: false } ) );

	const openModal = () => {
		setIsOpen( true );
	};

	const solidUserGroups = queryParams.solid_user_groups || [];
	return (
		<>
			<EditingModalActionFill>
				<EditingModalActionDropdown
					title={ __( 'Add to a User Group', 'better-wp-security' ) }
					description={ __( 'Add the selected users to new user groups.', 'better-wp-security' ) }
					dropdownTitle={ __( 'Add to User Groups', 'better-wp-security' ) }
					dropdownButtonText={ __( 'Add to User Groups', 'better-wp-security' ) }
					slug={ 'add-user-groups' }
					options={ userGroups }
					confirmationText={ __( 'Adding User Groups', 'better-wp-security' ) }
				/>
			</EditingModalActionFill>
			<UserSecurityFilterFill>
				<FiltersGroupCheckboxes
					slug="solid_user_groups"
					title={ __( 'User Groups', 'better-wp-security' ) }
					options={ userGroups }
				/>
			</UserSecurityFilterFill>
			<UserSecurityActionsFill>
				<StyledButtonsContainer>
					<Button
						text={ __( 'Edit User Group Settings', 'better-wp-security' ) }
						onClick={ openModal }
						disabled={ solidUserGroups.length === 0 }
					/>
				</StyledButtonsContainer>
				{ isOpen && (
					<UserSecurityUserGroupsActionsModal
						matchables={ matchables }
						setIsOpen={ setIsOpen }
						selectedUserGroupIds={ solidUserGroups }
					/>
				) }
			</UserSecurityActionsFill>
		</>
	);
}

export function UserSecurityUserGroupsActionsModal( { matchables, setIsOpen, selectedUserGroupIds } ) {
	const settings = useSettingsDefinitions();
	const { saveBulkEdits, saveGroupSettings } = useDispatch( uiStore );
	const { isSaving } = useSelect( ( select ) => ( {
		isSaving: selectedUserGroupIds.length > 1
			? select( uiStore ).isSavingBulkEdits( selectedUserGroupIds )
			: select( userGroupsStore ).isUpdatingSettings( selectedUserGroupIds[ 0 ] ),
	} ), [ selectedUserGroupIds ] );

	const onSave = async () => {
		if ( selectedUserGroupIds.length > 1 ) {
			await saveBulkEdits( selectedUserGroupIds );
		} else {
			await saveGroupSettings( selectedUserGroupIds[ 0 ] );
		}

		setIsOpen( false );
	};

	if ( ! selectedUserGroupIds ) {
		return null;
	}

	const selectedUserGroups = selectedUserGroupIds.map( ( userGroup ) => matchables.find( ( match ) => match.id === userGroup ) );

	const closeModal = () => {
		setIsOpen( false );
	};
	return (
		<StyledUserSecurityUserGroupActionsModal
			title={ __( 'Edit User Group Settings', 'better-wp-security' ) }
			className="itsec-apply-css-vars"
			onRequestClose={ closeModal }
		>
			<StyledSettingsFormContainer>
				<UserSecurityUserGroupPillContainer
					selectedUserGroups={ selectedUserGroups }
				/>
				<SettingsForm
					definitions={ settings }
					settingComponent={ selectedUserGroupIds.length > 1 ? BulkSettingField : SingleSettingField }
					groupIds={ selectedUserGroupIds.length > 1 ? selectedUserGroupIds : null }
					groupId={ selectedUserGroupIds.length === 1 ? selectedUserGroupIds[ 0 ] : null }
				/>
				<StyledUserGroupsConfirmationButton
					text={ __( 'Update User Group Settings', 'better-wp-security' ) }
					variant="primary"
					onClick={ onSave }
					align="right"
					isBusy={ isSaving }
				/>
			</StyledSettingsFormContainer>
		</StyledUserSecurityUserGroupActionsModal>
	);
}

export function UserSecurityUserGroupPillContainer( { selectedUserGroups } ) {
	const maxNumberOfUserGroupsToShow = 10;
	return (
		<>
			<Text
				level={ 4 }
				text={ __( 'User groups selected', 'better-wp-security' ) }
				weight={ TextWeight.HEAVY }
				size={ TextSize.NORMAL }
			/>
			<StyledModalPillContainer>
				{ selectedUserGroups
					.slice( 0, maxNumberOfUserGroupsToShow )
					.map( ( item, index ) => (
						<StyledUserGroupPill
							text={ item.label }
							weight={ TextWeight.HEAVY }
							key={ index }
						/>
					) )
				}
			</StyledModalPillContainer>
		</>
	);
}
