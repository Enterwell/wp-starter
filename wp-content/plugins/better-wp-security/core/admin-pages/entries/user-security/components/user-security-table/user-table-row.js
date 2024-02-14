/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import {
	check as checkIcon,
	chevronDown,
	chevronUp,
	closeSmall as closeIcon,
} from '@wordpress/icons';
import { __, _x, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import { Button, Text, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	CORE_STORE_NAME as coreStore,
	userSecurityStore,
} from '@ithemes/security.packages.data';
import { timeSince } from '@ithemes/security-utils';
import {
	StyledButton,
	StyledCheckboxControl,
	StyledRowDetailsContainer,
	StyledStatusCheck,
	StyledStatusRedCircle,
	StyledTable,
	StyledUserAvatar,
	StyledUserDetailsCaption,
	StyledUserDetailTableBody,
	StyledUserDetailTDContainer,
	StyledUserDetailText,
	StyledUserDetailTH,
	StyledUserDetailTR,
	StyledUser,
} from './styles';

function statusIcon( status ) {
	switch ( status ) {
		case null:
		case '':
		case 'not-enabled':
			return <StyledStatusRedCircle icon={ closeIcon } style={ { fill: '#D75A4B' } } />;
		case 'enforced-not-configured':
		case 'enabled':
			return <StyledStatusCheck icon={ checkIcon } style={ { fill: '#FFFFFF' } } />;
		default:
	}
}

function statusText( status ) {
	switch ( status ) {
		case null:
		case 'not-enabled':
			return __( 'Disabled', 'better-wp-security' );
		case 'enforced-not-configured':
			return __( 'Enforced', 'better-wp-security' );
		case 'enabled':
			return __( 'Enabled', 'better-wp-security' );
		default:
	}
}

export function getPasswordStrength( strength ) {
	switch ( strength ) {
		case 0:
		case 1:
			return { indicator: '#C10000', text: _x( 'Very Weak', 'password strength', 'better-wp-security' ) };
		case 2:
			return { indicator: '#f78b53', text: _x( 'Weak', 'password strength', 'better-wp-security' ) };
		case 3:
			return { indicator: '#F4C520', text: _x( 'Medium', 'password strength', 'better-wp-security' ) };
		case 4:
			return { indicator: '#00BA37', text: _x( 'Strong', 'password strength', 'better-wp-security' ) };
		default:
			return { indicator: '#545454', text: _x( 'Unknown', 'password strength', 'better-wp-security' ) };
	}
}

export function UserTableRow( { user, isMedium, isLarge } ) {
	const { isChecked, roles } = useSelect(
		( select ) => ( {
			isChecked: select( userSecurityStore ).isUserSelected( user.id ),
			roles: select( coreStore ).getRoles(),
		} ), [ user.id ] );
	const { toggleSelectedUser, openQuickEdit, removeSelectedUsers } = useDispatch( userSecurityStore );
	const [ isExpanded, setIsExpanded ] = useState( false );

	if ( ! roles ) {
		return null;
	}

	return (
		<>
			<tr key={ user.id }>
				<td>
					<StyledUser>
						<StyledCheckboxControl
							checked={ isChecked }
							onChange={ ( ) => {
								toggleSelectedUser( user );
							} }
							label={
								sprintf(
									/* translators: 1. Select User in Table. */
									__( 'Select %s in Table', 'better-wp-security' ),
									user.name
								)
							}
							hideLabelFromVision
						/>
						{ user?.avatar_urls?.[ 48 ] && (
							<StyledUserAvatar
								src={ user?.avatar_urls?.[ 48 ] }
								alt=""
							/>
						) }
						<StyledButton
							text={ user.name }
							href={ user.solid_edit_user_link }
							variant="link"
							align="left"
						/>
					</StyledUser>
				</td>
				{ isMedium && (
					<td>
						<Text
							text={ user.roles?.map( ( role ) => roles[ role ]?.label ?? role ).join( ', ' ) }
							textTransform="capitalize"
						/>
					</td>
				) }
				{ isLarge && (
					<td>
						{ user.solid_last_seen
							? <Text text={ timeSince( new Date( user.solid_last_seen ) ) } />
							: <Text text={ __( 'No Login Info', 'better-wp-security' ) } />
						}

					</td>
				) }
				{ isMedium && (
					<td>
						<Text weight={ TextWeight.HEAVY } { ...getPasswordStrength( user.solid_password_strength ) } />
					</td>
				) }
				{ isLarge && (
					<td>
						<Text text={ timeSince( new Date( user.solid_password_changed ) ) } />
					</td>
				) }
				{ isMedium && (
					<td>
						<Text icon={ statusIcon( user.solid_2fa ) } iconSize={ 16 } text={ statusText( user.solid_2fa ) } />
					</td>
				) }
				<td>
					<Button
						variant="secondary"
						text={ __( 'Edit User', 'better-wp-security' ) }
						align="right"
						onClick={ () => {
							openQuickEdit();
							if ( ! isChecked ) {
								removeSelectedUsers();
								toggleSelectedUser( user );
							}
						} }
					/>
				</td>
				{ ! isLarge && (
					<td>
						<Button
							aria-controls={ `solid-user-details-${ user.id }` }
							aria-expanded={ isExpanded }
							icon={ isExpanded ? chevronUp : chevronDown }
							iconPosition="right"
							onClick={ () => setIsExpanded( ! isExpanded ) }
							variant="tertiary"
							label={ __( 'View Details', 'better-wp-security' ) }
						/>
					</td>
				) }
			</tr>
			{ ! isLarge && (
				<TableRowDetails
					user={ user }
					isExpanded={ isExpanded }
				/>
			) }
		</>
	);
}

function TableRowDetails( { user, isExpanded } ) {
	const { roles } = useSelect( ( select ) => ( {
		roles: select( coreStore ).getRoles(),
	} ), [] );
	return (
		<StyledRowDetailsContainer
			as="tr"
			id={ `solid-user-details-${ user.id }` }
			isExpanded={ isExpanded }
			variant="tertiary"
		>
			<StyledUserDetailTDContainer colSpan={ 100 }>
				<StyledTable>
					<StyledUserDetailsCaption
						as="caption"
						text={ __( 'Additional User Details', 'better-wp-security' ) }
						weight={ TextWeight.HEAVY }
					/>
					<StyledUserDetailTableBody className="solid-user-details">
						<StyledUserDetailTR>
							<StyledUserDetailTH
								as="th"
								text={ __( 'Role:', 'better-wp-security' ) }
								variant={ TextVariant.MUTED }
							/>
							<StyledUserDetailText
								as="td"
								text={ user.roles?.map( ( role ) => roles[ role ]?.label ?? role ).join( ', ' ) }
								weight={ TextWeight.HEAVY }
								variant={ TextVariant.MUTED }
							/>
						</StyledUserDetailTR>
						<StyledUserDetailTR>
							<StyledUserDetailTH
								as="th"
								text={ __( 'Last Seen:', 'better-wp-security' ) }
								variant={ TextVariant.MUTED }
							/>
							{ user.solid_last_seen
								? (
									<StyledUserDetailText
										as="td"
										text={ timeSince( new Date( user.solid_last_seen ) ) }
										weight={ TextWeight.HEAVY }
										variant={ TextVariant.MUTED }
									/>
								)
								: (
									<StyledUserDetailText
										as="td"
										text={ __( 'No Login Info', 'better-wp-security' ) }
										weight={ TextWeight.HEAVY }
									/>
								)
							}
						</StyledUserDetailTR>
						<StyledUserDetailTR>
							<StyledUserDetailTH
								as="th"
								text={ __( 'PW Strength:', 'better-wp-security' ) }
								variant={ TextVariant.MUTED }
							/>
							<StyledUserDetailText
								as="td"
								weight={ TextWeight.HEAVY }
								{ ...getPasswordStrength( user.solid_password_strength ) }
								variant={ TextVariant.MUTED }
							/>
						</StyledUserDetailTR>
						<StyledUserDetailTR>
							<StyledUserDetailTH
								as="th"
								text={ __( 'PW Age:', 'better-wp-security' ) }
								variant={ TextVariant.MUTED }
							/>
							<StyledUserDetailText
								as="td"
								weight={ TextWeight.HEAVY }
								text={ timeSince( new Date( user.solid_password_changed ) ) }
								variant={ TextVariant.MUTED }
							/>
						</StyledUserDetailTR>
						<StyledUserDetailTR>
							<StyledUserDetailTH
								as="th"
								text={ __( 'Two-Factor:', 'better-wp-security' ) }
								variant={ TextVariant.MUTED }
							/>
							<StyledUserDetailText
								as="td"
								icon={ statusIcon( user.solid_2fa ) }
								iconSize={ 16 }
								text={ statusText( user.solid_2fa ) }
								weight={ TextWeight.HEAVY }
								variant={ TextVariant.MUTED }
							/>
						</StyledUserDetailTR>
					</StyledUserDetailTableBody>
				</StyledTable>
			</StyledUserDetailTDContainer>
		</StyledRowDetailsContainer>
	);
}
