/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

export { default as EditGroupFields } from './edit-group-fields';
export { default as GroupLabel } from './group-label';
export { default as GroupsNavigation } from './groups-navigation';
export { default as ImportPage } from './import-page';
export { default as Layout } from './layout';
export { default as ManageGroup } from './manage-group';
export { default as ManageMultipleGroups } from './manage-multiple-groups';
export { default as MultiGroupSelector } from './multi-group-selector';
export { default as OnboardChooser } from './onboard-chooser';
export { default as OnboardPage } from './onboard-page';
export { default as PanelRoles } from './panel-roles';
export { default as PanelUsers } from './panel-users';
export { default as SaveGroup } from './save-group';
export { default as SaveMultipleGroups } from './save-multiple-groups';
export { default as TabEditGroup } from './tab-edit-group';
export { default as TabSettings } from './tab-settings';
export { default as TabSettingsBulk } from './tab-settings-bulk';

export const {
	Slot: PageHeaderActionSlot,
	Fill: PageHeaderActionFill,
} = createSlotFill( 'UserGroupsPageHeaderAction' );

export const {
	Slot: PageHeaderSlot,
	Fill: PageHeaderFill,
} = createSlotFill( 'UserGroupsPageHeader' );
