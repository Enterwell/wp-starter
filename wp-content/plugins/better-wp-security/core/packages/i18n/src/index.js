import { _x } from '@wordpress/i18n';

export function getCanonicalRoles() {
	return [
		{
			value: 'administrator',
			label: _x( 'Administrator', 'User role' ),
		},
		{
			value: 'editor',
			label: _x( 'Editor', 'User role' ),
		},
		{
			value: 'author',
			label: _x( 'Author', 'User role' ),
		},
		{
			value: 'contributor',
			label: _x( 'Contributor', 'User role' ),
		},
		{
			value: 'subscriber',
			label: _x( 'Subscriber', 'User role' ),
		},
	];
}
