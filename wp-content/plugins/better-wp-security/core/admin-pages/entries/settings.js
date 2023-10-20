/**
 * WordPress dependencies
 */
import { setLocaleData } from '@wordpress/i18n';
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'better-wp-security' );

/**
 * Internal dependencies
 */
import { createHistory } from './settings/history';
import App from './settings/app.js';

const history = createHistory( document.location, { page: 'itsec' } );

domReady( () => {
	const containerEl = document.getElementById( 'itsec-settings-root' );
	const serverType = containerEl.dataset.serverType;
	const installType = containerEl.dataset.installType;
	const onboardComplete = containerEl.dataset.onboard === '1';

	return render(
		<App
			history={ history }
			serverType={ serverType }
			installType={ installType }
			onboardComplete={ onboardComplete }
		/>,
		containerEl
	);
} );

export * from './settings/components';
export { OnboardSiteTypeBeforeFill } from './settings/pages/site-type/chooser';
export {
	Page,
	ChildPages,
	useNavigation,
	useCurrentPage,
} from './settings/page-registration';
export {
	useNavigateTo,
	useConfigContext,
} from './settings/utils';
export { STORE_NAME as ONBOARD_STORE_NAME } from './settings/stores/onboard';
export { history };
