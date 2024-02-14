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
import { useParams } from 'react-router-dom';
import { useNavigation } from '@ithemes/security.pages.settings';

const StyledSaveGroup = styled( Flex )`
	margin-top: 2rem;
`;

export default function SaveGroup( { groupId } ) {
	const { isDirty, isSaving } = useSelect(
		( select ) => ( {
			isDirty: select( uiStore ).isDirty(	groupId	),
			isSaving: select( uiStore ).isSavingGroupOrSettings( groupId ),
		} ),
		[ groupId ]
	);
	const { saveGroupAndSettings, resetEdits } = useDispatch( uiStore );
	const { root } = useParams();
	const { goNext } = useNavigation();

	return (
		<StyledSaveGroup justify="end">
			<FlexItem>
				<Button
					variant="secondary"
					onClick={ () => resetEdits( groupId ) }
					disabled={ ! isDirty }
				>
					{ __( 'Undo Changes', 'better-wp-security' ) }
				</Button>
			</FlexItem>
			{ root === 'settings' && (
				<FlexItem>
					<Button
						variant="primary"
						onClick={ () => saveGroupAndSettings( groupId ) }
						isBusy={ isSaving }
						disabled={ isSaving || ! isDirty }
					>
						{ __( 'Save', 'better-wp-security' ) }
					</Button>
				</FlexItem>
			) }
			{ root !== 'settings' && (
				<Button
					text={ __( 'Next', 'better-wp-security' ) }
					variant="primary"
					onClick={ goNext }
				/>
			) }
		</StyledSaveGroup>
	);
}
