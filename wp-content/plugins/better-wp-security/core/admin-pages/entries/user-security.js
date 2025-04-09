/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { setLocaleData } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import App from './user-security/app.js';
import { createHistory } from './settings/history';

dispatch( preferencesStore ).setDefaults(
	'ithemes-security/users',
	{
		howToEditUserGroups: true,
	}
);

const history = createHistory( document.location, { page: 'itsec-user-security' } );

domReady( () => {
	const containerEl = document.getElementById( 'itsec-user-security-root' );
	render( <App history={ history } />, containerEl );
} );

export { EditingModalActionFill } from './user-security/components/user-security-actions-modal/editing-modal';
export { EditingModalActionButton, EditingModalActionDropdown } from './user-security/components/user-security-actions-modal/modal-action-section';
export { UserSecurityFilterFill } from './user-security/components/user-security-table-filters/index';
export { UserSecurityActionsFill } from './user-security/components/user-security-table-filters/index';
