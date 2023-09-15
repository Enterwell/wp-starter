/**
 * External dependencies
 */
import { pick } from 'lodash';
import { validate } from 'uuid';

/**
 * WordPress dependencies
 */
import { addAction, addFilter } from '@wordpress/hooks';

addAction(
	'ithemes-security.onboard.applyAnswerResponse',
	'ithemes-security/user-groups/onboard.applyAnswerResponse',
	function( registry, answer ) {
		for ( const userGroup of answer.user_groups ) {
			const created = registry
				.dispatch( 'ithemes-security/user-groups-editor' )
				.createLocalGroup( userGroup.id );

			if ( ! created ) {
				continue;
			}

			registry
				.dispatch( 'ithemes-security/user-groups-editor' )
				.editGroup(
					userGroup.id,
					pick( userGroup, [
						'label',
						'users',
						'roles',
						'canonical',
					] )
				);
		}

		for ( const userGroupId in answer.user_groups_settings ) {
			if (
				! answer.user_groups_settings.hasOwnProperty( userGroupId ) ||
				! validate( userGroupId )
			) {
				continue;
			}

			const modules = answer.user_groups_settings[ userGroupId ];

			if ( ! modules ) {
				continue;
			}

			for ( const module in modules ) {
				if ( ! modules.hasOwnProperty( module ) ) {
					continue;
				}

				for ( const setting of modules[ module ] ) {
					registry
						.dispatch( 'ithemes-security/user-groups-editor' )
						.editGroupSetting( userGroupId, module, setting, true );
				}
			}
		}
	}
);

addAction(
	'ithemes-security.onboard.reset',
	'ithemes-security/user-groups/onboard.reset',
	function( registry ) {
		registry
			.dispatch( 'ithemes-security/user-groups-editor' )
			.deleteLocalGroups();
		registry
			.dispatch( 'ithemes-security/user-groups-editor' )
			.resetAllEdits();
	}
);

addFilter(
	'ithemes-security.settings.isConditionalSettingActive',
	'ithemes-security/user-groups/user-group-conditional',
	function( isActive, module, definition, context ) {
		if ( ! isActive || ! definition[ 'user-groups' ] ) {
			return isActive;
		}

		const { registry } = context;
		const groupsBySetting = registry
			.select( 'ithemes-security/user-groups-editor' )
			.getEditedGroupsBySetting();

		for ( const groupSetting of definition[ 'user-groups' ] ) {
			if (
				! ( groupsBySetting[ module.id ]?.[ groupSetting ] || [] )
					.length
			) {
				return false;
			}
		}

		return true;
	}
);
