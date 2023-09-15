/**
 * WordPress dependencies
 */
import { CardBody, Disabled } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { SettingsForm, MultiGroupSelector } from '../';
import { useSettingsDefinitions } from '../../utils';
import Field from './field';

export default function TabSettingsBulk( { groupIds, children } ) {
	const settings = useSettingsDefinitions();

	let body = (
		<CardBody>
			{ children }
			<SettingsForm
				definitions={ settings }
				settingComponent={ Field }
				groupIds={ groupIds }
			/>
		</CardBody>
	);

	if ( ! groupIds.length ) {
		body = <Disabled>{ body }</Disabled>;
	}

	return (
		<>
			<MultiGroupSelector />
			{ body }
		</>
	);
}
