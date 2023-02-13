/**
 * Internal dependencies
 */
import { GroupLabel, PanelRoles, PanelUsers, TabBody } from '../';
import './style.scss';

function EditGroupFields( { groupId, disabled } ) {
	return (
		<TabBody.Row name="edit-fields">
			<GroupLabel groupId={ groupId } disabled={ disabled } />
			<PanelRoles groupId={ groupId } disabled={ disabled } />
			<PanelUsers groupId={ groupId } disabled={ disabled } />
		</TabBody.Row>
	);
}

export default EditGroupFields;
