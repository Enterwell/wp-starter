/**
 * WordPress Dependencies
 */
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as uiStore } from '@ithemes/security.user-groups.ui';

export default function GroupLabel( { groupId, disabled = false } ) {
	const { label } = useSelect( ( select ) => ( {
		label: select( uiStore ).getEditedGroupAttribute( groupId, 'label' ) || '',
	} ), [ groupId ] );

	const { editGroup } = useDispatch( uiStore );

	return (
		<TextControl
			label={ __( 'Group Name', 'better-wp-security' ) }
			value={ label }
			maxLength={ 50 }
			disabled={ disabled }
			onChange={ ( newLabel ) => editGroup( groupId, { label: newLabel } ) }
			__nextHasNoMarginBottom
		/>
	);
}
