/**
 * External dependencies
 */
import { pick } from 'lodash';
import { validate } from 'uuid';

/**
 * WordPress dependencies
 */
import { addAction } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import { store as uiStore } from '@ithemes/security.user-groups.ui';

addAction(
	'ithemes-security.onboard.applyAnswerResponse',
	'ithemes-security/user-groups/onboard.applyAnswerResponse',
	function( registry, answer ) {
		for ( const userGroup of answer.user_groups ) {
			const created = registry
				.dispatch( uiStore )
				.createLocalGroup( userGroup.id );

			if ( ! created ) {
				continue;
			}

			registry
				.dispatch( uiStore )
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
						.dispatch( uiStore )
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
			.dispatch( uiStore )
			.deleteLocalGroups();
		registry
			.dispatch( uiStore )
			.resetAllEdits();
	}
);
