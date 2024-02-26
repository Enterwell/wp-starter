/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import App from './vulnerabilities/app';
import { createHistory } from './settings/history';

const history = createHistory( document.location, { page: 'itsec-vulnerabilities' } );

domReady( () => {
	const containerEl = document.getElementById( 'itsec-vulnerabilities-root' );

	render(
		<App history={ history } />,
		containerEl
	);
} );

export { BeforeHeaderFill } from './vulnerabilities/components/before-header/index';
export { vulnerabilityIcon, severityColor } from './vulnerabilities/components/vulnerability-table';
