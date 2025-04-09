/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PluginArea } from '@wordpress/plugins';
import { createSlotFill, SlotFillProvider } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';
/**
 * SolidWP dependencies
 */
import { Heading, ShadowPortal, Root, solidTheme } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { coreStore } from '@ithemes/security.packages.data';
import { useAsync } from '@ithemes/security-hocs';
import { PurpleShield } from '@ithemes/security-style-guide';
import { StyledProfileContainer, StyledTabs } from './styles';

const { Slot: UserProfileSlot, Fill: UserProfileFill } = createSlotFill( 'UserProfile' );
export { UserProfileFill };

const styleSheetIds = [ 'wp-components-css' ];

async function getAvailablePlugins( plugins, user, currentUserId, canManage ) {
	if ( ! user ) {
		return Promise.resolve( [] );
	}

	return await Promise.allSettled( plugins.map( ( plugin ) =>
		Promise.resolve( plugin.isAvailable( user, currentUserId, canManage ) )
			.then( ( isAvailable ) => {
				return isAvailable ? plugin : null;
			} )
	) ).then( ( settled ) => {
		return settled.filter(
			( maybePlugin ) => maybePlugin.status === 'fulfilled' && maybePlugin.value !== null
		).map( ( item ) => (
			item.value
		) );
	} );
}

export default function App( { plugins, canManage, userId, useShadow } ) {
	const { user, currentUserId } = useSelect( ( select ) => ( {
		user: select( coreStore ).getUser( userId ),
		currentUserId: select( coreStore ).getCurrentUserId(),
	} ), [ userId ] );

	const { value: availablePlugins } = useAsync(
		useCallback( () =>
			getAvailablePlugins( plugins, user, currentUserId, canManage ),
		[ plugins, user, currentUserId, canManage ]
		),
	);

	if ( ! availablePlugins?.length ) {
		return null;
	}

	const tabs = availablePlugins
		.map( ( plugin ) => (
			{
				title: plugin.label,
				name: plugin.name,
				order: plugin.order,
			} )
		)
		.sort( ( a, b ) => a.order - b.order );

	const children = (
		<StyledProfileContainer>
			<Heading
				level={ 2 }
				icon={ canManage && PurpleShield }
				iconSize="32"
				text={ __( 'Security', 'better-wp-security' ) }
			/>
			<SlotFillProvider>
				<StyledTabs tabs={ tabs }>
					{ ( tab ) =>
						<UserProfileSlot
							fillProps={ { name: tab.name, canManage, userId, user, useShadow } }
						/> }
				</StyledTabs>
				<PluginArea scope="solid-security-user-profile" />
			</SlotFillProvider>
		</StyledProfileContainer>
	);

	return (
		<Root theme={ solidTheme }>
			{ useShadow ? <ShadowPortal children={ children } styleSheetIds={ styleSheetIds } inherit /> : children }
		</Root>
	);
}
