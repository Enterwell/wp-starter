/**
 * External dependencies
 */
import { Link, useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { Disabled } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { store as userGroupsStore } from '@ithemes/security.user-groups.api';
import {
	store as uiStore,
	useSettingsDefinitions,
	SettingsForm,
	SingleSettingField,
} from '@ithemes/security.user-groups.ui';
import { PageHeaderActionFill } from '../';

export default function TabSettings( {
	groupId,
	highlight,
	moduleFilter,
	children,
} ) {
	const { root } = useParams();
	const settings = useSettingsDefinitions( { module: moduleFilter } );
	const { isLoading } = useSelect(
		( select ) => {
			const isLocal = select( uiStore	).isLocalGroup( groupId );
			let _isLoading = false;

			if ( ! isLocal ) {
				const groupSettings = select( userGroupsStore ).getGroupSettings( groupId );
				const isResolving = select( userGroupsStore	).isResolving( 'getGroupSettings', [ groupId ] );

				_isLoading = ! groupSettings && isResolving;
			}

			return {
				isLoading: _isLoading,
			};
		},
		[ groupId ]
	);

	return (
		<>
			{ root === 'settings' && (
				<PageHeaderActionFill>
					<Link
						to={ `/settings/user-groups/multi?id=${ groupId }&back=${ groupId }` }
						component={ withNavigate( Text ) }
						as="a"
						variant={ TextVariant.ACCENT }
						text={ __( 'Edit Multiple Groups', 'better-wp-security' ) }
					/>
				</PageHeaderActionFill>
			) }
			<Disabled isDisabled={ isLoading }>
				{ children }
				<SettingsForm
					definitions={ settings }
					settingComponent={ SingleSettingField }
					groupId={ groupId }
					disabled={ isLoading }
					highlight={ highlight }
				/>
			</Disabled>
		</>
	);
}
