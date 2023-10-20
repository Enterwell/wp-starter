/**
 * Internal dependencies
 */
import { GroupLabel, PanelRoles, PanelUsers } from '../';
import './style.scss';

export default function EditGroupFields( { groupId, disabled } ) {
	return (
		<>
			<GroupLabel groupId={ groupId } disabled={ disabled } />
			<PanelRoles groupId={ groupId } disabled={ disabled } />
			<PanelUsers groupId={ groupId } disabled={ disabled } />
		</>
	);
}
