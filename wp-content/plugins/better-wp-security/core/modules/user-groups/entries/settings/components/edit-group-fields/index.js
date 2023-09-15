/**
 * Internal dependencies
 */
import { GroupLabel, PanelRoles, PanelUsers } from '../';
import './style.scss';

function EditGroupFields( { groupId, disabled } ) {
	return (
		<>
			<GroupLabel groupId={ groupId } disabled={ disabled } />
			<PanelRoles groupId={ groupId } disabled={ disabled } />
			<PanelUsers groupId={ groupId } disabled={ disabled } />
		</>
	);
}

export default EditGroupFields;
