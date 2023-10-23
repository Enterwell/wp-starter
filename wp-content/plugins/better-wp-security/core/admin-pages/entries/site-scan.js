/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { setLocaleData } from '@wordpress/i18n';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import App from './site-scan/app.js';
import store from './site-scan/store';
import { createHistory } from './settings/history';

const history = createHistory( document.location, { page: 'itsec-site-scan' } );
domReady( () => {
	const containerEl = document.getElementById( 'itsec-site-scan-root' );
	render( <App history={ history } />, containerEl );
} );

export { store };

export { SiteScanIssuesFill, SiteScanMutedIssuesFill, ProgressBarBeforeFill } from './site-scan/components/slot-fill';
export { default as SiteScanIssue } from './site-scan/components/site-scan-issue';
export { default as SiteScanIssueActions } from './site-scan/components/site-scan-issue-actions';
export {
	StyledDetailContent as ScanIssueDetailContent,
	StyledDetailColumn as ScanIssueDetailColumn,
	StyledScanIssueText as ScanIssueText,
} from './site-scan/components/site-scan-issue/styles';
export { StyledActionButtons as ScanResultActionButtons } from './site-scan/styles';
export { ScanComponentPromo } from './site-scan/components/progress-bar';
