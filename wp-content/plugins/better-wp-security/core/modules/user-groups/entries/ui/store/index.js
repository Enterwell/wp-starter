/**
 * WordPress dependencies
 */
import { register, createReduxStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import controls from './controls';
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import userGroupsEditor from './reducers';
import { STORE_NAME } from './constant';

const store = createReduxStore( STORE_NAME, {
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

register( store );

export default store;
