/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Button, CardBody, CardFooter, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { FlexSpacer } from '@ithemes/security-components';
import { EditGroupFields } from '../';

function TabCreateGroup( { hasEdits, save, isSaving } ) {
	return (
		<>
			<CardBody className="itsec-user-groups-group-tab__edit-fields">
				<EditGroupFields groupId="new" />
			</CardBody>
			<CardFooter>
				<FlexSpacer />
				<FlexItem>
					<Button
						disabled={ ! hasEdits }
						variant="primary"
						onClick={ save }
						isBusy={ isSaving }
					>
						{ __( 'Create', 'better-wp-security' ) }
					</Button>
				</FlexItem>
			</CardFooter>
		</>
	);
}

export default compose( [
	withSelect( ( select ) => ( {
		hasEdits: select( 'ithemes-security/user-groups-editor' ).hasEdits(
			'new'
		),
		isSaving: select( 'ithemes-security/user-groups-editor' ).isCreating(
			'new'
		),
	} ) ),
	withDispatch( ( dispatch ) => ( {
		save() {
			dispatch( 'ithemes-security/user-groups-editor' ).createGroup();
		},
	} ) ),
] )( TabCreateGroup );
