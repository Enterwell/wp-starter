/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { TextSize, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { userSecurityStore } from '@ithemes/security.packages.data';
import {
	StyledAdditionalUsersSelectedText,
	StyledUserPill,
	StyledModalPillContainer,
	StyledSubheading,
} from './styles';

export function UserSecurityPillContainer( ) {
	const maxNumberOfUsersToShow = 10;
	const { selectedUsers, userSelectionType } = useSelect(
		( select ) => ( {
			selectedUsers: select( userSecurityStore ).getCurrentlySelectedUsers(),
			userSelectionType: select( userSecurityStore ).getUserSelectionType(),
		} ),
		[]
	);
	return (
		<>
			<StyledSubheading
				level={ 4 }
				text={ userSecurityText( selectedUsers, userSelectionType ) }
				weight={ TextWeight.HEAVY }
				size={ TextSize.NORMAL }
			/>
			<StyledModalPillContainer>
				{ selectedUsers
					.slice( 0, maxNumberOfUsersToShow )
					.map( ( item, index ) => (
						<UserPill
							userId={ item }
							key={ index }
						/>
					) )
				}
				{ selectedUsers.length > maxNumberOfUsersToShow && (
					<StyledAdditionalUsersSelectedText
						text={
							sprintf(
							/* translators: 1. Number of additional users. */
								_n( '%s more user…', '%s more users…', selectedUsers.length - maxNumberOfUsersToShow, 'better-wp-security' ),
								selectedUsers.length - maxNumberOfUsersToShow
							)
						}
					/>
				) }
			</StyledModalPillContainer>
		</>
	);
}

export function UserPill( { userId } ) {
	const { user } = useSelect(
		( select ) => ( {
			user: select( userSecurityStore ).getUserById( userId ),
		} )
	);

	return (
		<StyledUserPill
			text={ user.name }
			weight={ TextWeight.HEAVY }
		/>
	);
}

function userSecurityText( selectedUsers, userSelectionType ) {
	if ( userSelectionType === 'all' ) {
		return __( 'All users Selected', 'better-wp-security' );
	}

	return sprintf(
		/* translators: 1. Number of users. */
		_n( '%s User selected', '%s Users selected', selectedUsers.length, 'better-wp-security' ),
		selectedUsers.length
	);
}
