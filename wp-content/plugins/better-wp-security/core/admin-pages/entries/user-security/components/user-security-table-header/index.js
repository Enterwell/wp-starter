/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import {
	Heading,
	TextSize,
	TextVariant,
	Button, Text,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledTableHeader } from './styles';
import { userSecurityStore } from '@ithemes/security.packages.data';

export default function UserSecurityTableHeader() {
	const { openQuickEdit } = useDispatch( userSecurityStore );
	const { selectedUsers } = useSelect( ( select ) => ( {
		selectedUsers: select( userSecurityStore ).getCurrentlySelectedUsers(),
	} ), [] );

	return (
		<StyledTableHeader>
			<div>
				<Heading
					level={ 2 }
					size={ TextSize.LARGE }
					variant={ TextVariant.DARK }
					weight={ 600 }
					text={ __( 'User Security', 'better-wp-security' ) }
				/>
				<Text
					text={ __( 'Review the security of users on your site.', 'better-wp-security' ) }
					variant={ TextVariant.MUTED }
				/>
			</div>
			{ selectedUsers.length > 0 && (
				<Button
					text={ __( 'Quick Actions - Edit Multiple Users', 'better-wp-security' ) }
					onClick={ openQuickEdit }
					variant="primary"
				/>
			) }
		</StyledTableHeader>
	);
}
