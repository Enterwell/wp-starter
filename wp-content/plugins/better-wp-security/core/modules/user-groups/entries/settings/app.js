/**
 * WordPress dependencies
 */
import '@wordpress/notices';
import { createPortal } from '@wordpress/element';

/**
 * Internal dependencies
 */
import '@ithemes/security-data';
import '@ithemes/security.user-groups.api';
import { ModuleSettingsNoticeList } from '@ithemes/security-components';
import './store';
import { Layout } from './components';
import './style.scss';

function App( { noticeEl } ) {
	return (
		<div className="itsec-user-groups-app">
			{ createPortal( <ModuleSettingsNoticeList />, noticeEl ) }
			<Layout />
		</div>
	);
}

export default App;
