/**
 * WordPress components
 */
import { CheckboxControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

export default function UserRoleList( {
	value,
	onChange,
	usersAndRoles,
	help,
} ) {
	return (
		<fieldset className="itsec-nc-user-role-list">
			{ help && <legend>{ help }</legend> }

			<ul className="itsec-nc-user-role-list__group">
				{ usersAndRoles.roles.map( ( { value: item, label } ) => (
					<li key={ item }>
						<CheckboxControl
							label={ sprintf(
								/* translators: 1. WordPress User Role */
								__( 'All “%s” Users', 'better-wp-security' ),
								label
							) }
							checked={ value.includes( item ) }
							onChange={ ( checked ) =>
								onChange(
									checked
										? [ ...value, item ]
										: value.filter(
											( maybe ) => maybe !== item
										)
								)
							}
						/>
					</li>
				) ) }
			</ul>

			<ul className="itsec-nc-user-role-list__group">
				{ usersAndRoles.users.map( ( { value: item, label } ) => (
					<li key={ item }>
						<CheckboxControl
							label={ label }
							checked={ value.includes( item ) }
							onChange={ ( checked ) =>
								onChange(
									checked
										? [ ...value, item ]
										: value.filter(
											( maybe ) => maybe !== item
										)
								)
							}
						/>
					</li>
				) ) }
			</ul>
		</fieldset>
	);
}
