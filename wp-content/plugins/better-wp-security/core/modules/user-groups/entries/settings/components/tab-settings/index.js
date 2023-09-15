/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { CardBody, Disabled } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { MultiGroupSelector, SettingsForm } from '../';
import { useSettingsDefinitions } from '../../utils';
import Field from './field';
import './style.scss';

export default function TabSettings( {
	groupId,
	highlight,
	moduleFilter,
	children,
} ) {
	const settings = useSettingsDefinitions( { module: moduleFilter } );
	const { isLoading } = useSelect(
		( select ) => {
			const isLocal = select(
				'ithemes-security/user-groups-editor'
			).isLocalGroup( groupId );
			let _isLoading = false;

			if ( ! isLocal ) {
				const groupSettings = select(
					'ithemes-security/user-groups'
				).getGroupSettings( groupId );
				const isResolving = select(
					'ithemes-security/user-groups'
				).isResolving( 'getGroupSettings', [ groupId ] );

				_isLoading = ! groupSettings && isResolving;
			}

			return {
				isLoading: _isLoading,
			};
		},
		[ groupId ]
	);

	let body = (
		<CardBody>
			{ children }
			<SettingsForm
				definitions={ settings }
				settingComponent={ Field }
				groupId={ groupId }
				disabled={ isLoading }
				highlight={ highlight }
			/>
		</CardBody>
	);

	if ( isLoading ) {
		body = <Disabled>{ body }</Disabled>;
	}

	return (
		<>
			{ ! moduleFilter && <MultiGroupSelector /> }
			{ body }
		</>
	);
}
