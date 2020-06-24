import { _x } from '@wordpress/i18n';

export function getCanonicalRoles() {
	return [
		{
			value: 'administrator',
			label: _x( 'Administrator', 'User role', 'default' ),
		},
		{
			value: 'editor',
			label: _x( 'Editor', 'User role', 'default' ),
		},
		{
			value: 'author',
			label: _x( 'Author', 'User role', 'default' ),
		},
		{
			value: 'contributor',
			label: _x( 'Contributor', 'User role', 'default' ),
		},
		{
			value: 'subscriber',
			label: _x( 'Subscriber', 'User role', 'default' ),
		},
	];
}
