/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Solid dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { store as uiStore } from '@ithemes/security.user-groups.ui';

const StyledSaveMultipleGroups = styled( Flex )`
	margin-top: 2rem;
`;

export default function SaveMultipleGroups( { groupIds } ) {
	const { isDirty, isSaving } = useSelect(
		( select ) => ( {
			isDirty: select( uiStore ).hasBulkSettingEdits(),
			isSaving: select( uiStore ).isSavingBulkEdits( groupIds ),
		} ),
		[ groupIds ]
	);
	const { saveBulkEdits, resetBulkGroupSettingEdits } = useDispatch( uiStore );

	return (
		<StyledSaveMultipleGroups justify="end">
			<FlexItem>
				<Button
					variant="secondary"
					onClick={ () => resetBulkGroupSettingEdits( groupIds ) }
					disabled={ ! isDirty }
				>
					{ __( 'Undo Changes', 'better-wp-security' ) }
				</Button>
			</FlexItem>
			<FlexItem>
				<Button
					variant="primary"
					onClick={ () => saveBulkEdits( groupIds ) }
					isBusy={ isSaving }
					disabled={ isSaving || ! isDirty }
				>
					{ __( 'Save', 'better-wp-security' ) }
				</Button>
			</FlexItem>
		</StyledSaveMultipleGroups>
	);
}
