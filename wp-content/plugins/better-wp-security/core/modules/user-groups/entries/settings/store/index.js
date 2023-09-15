/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import controls from './controls';
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import userGroupsEditor from './reducers';

const store = registerStore( 'ithemes-security/user-groups-editor', {
	controls,
	actions,
	selectors,
	resolvers,
	reducer: userGroupsEditor,
	persist: [
		'edits',
		'settingEdits',
		'bulkSettingEdits',
		'localGroupIds',
		'markedForDelete',
	],
} );

export default store;
