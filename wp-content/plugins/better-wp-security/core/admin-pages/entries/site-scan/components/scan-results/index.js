/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * iThemes dependencies
 */
import { TabPanel } from '@ithemes/ui';
import store from '../../store';

/**
 * Internal dependencies
 */
import ScanResultsTab from './scan-results-tab';

export default function ScanResults( { issues } ) {
	const { hasScanRun } = useSelect( ( select ) => ( {
		hasScanRun: select( store ).hasCompletedScan(),
	} ), [] );
	const issuesActive = issues.filter( ( result ) => ! result.muted ).length;
	const issuesMuted = issues.filter( ( result ) => result.muted ).length;

	const tabs = useMemo( () => ( [
		{
			name: 'scan-results',
			title:
				hasScanRun
					? sprintf(
						/* translators: The number of results*/
						__( 'Scan Results (%d)', 'better-wp-security' ),
						issuesActive )
					: __( 'Scan Results', 'better-wp-security' ),
			render() {
				return ( <ScanResultsTab hasIssues={ issuesActive > 0 } type="active" /> );
			},
		},
		{
			name: 'ignored-results',
			title:
				hasScanRun
					? sprintf(
						/* translators: The number of muted results*/
						__( 'Muted Results (%d)', 'better-wp-security' ),
						issuesMuted )
					: __( 'Muted Results', 'better-wp-security' ),
			render() {
				return ( <ScanResultsTab hasIssues={ issuesMuted > 0 } type="muted" /> );
			},
		},
	] ), [ hasScanRun, issuesActive, issuesMuted ] );

	return (
		<TabPanel tabs={ tabs }>
			{ ( { render: Component } ) => <Component /> }
		</TabPanel>
	);
}
