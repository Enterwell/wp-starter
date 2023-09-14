/**
 * WordPress dependencies
 */
import '@wordpress/notices';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import '@ithemes/security.packages.data';
import '@ithemes/security.user-groups.api';
import { Page } from '@ithemes/security.pages.settings';
import { Layout } from './components';
import { useCompletionSteps, useSearchProviders } from './utils';
import './store';
import './hooks';
import './style.scss';

export default function App() {
	useCompletionSteps();
	useSearchProviders();

	return (
		<Page
			id="user-groups"
			title={ __( 'User Groups', 'better-wp-security' ) }
			icon="groups"
			priority={ 10 }
			roots={ [ 'onboard', 'settings', 'import' ] }
		>
			{ () => <Layout /> }
		</Page>
	);
}
