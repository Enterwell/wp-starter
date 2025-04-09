/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { shield } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import {
	SiteScanIssue,
	SiteScanIssuesFill,
	SiteScanMutedIssuesFill,
	SiteScanIssueActions,
	ScanIssueDetailContent,
	ScanIssueDetailColumn,
	ScanIssueText,
	store,
} from '@ithemes/security.pages.site-scan';
import { Text, TextWeight } from '@ithemes/ui';

export default function App() {
	const { issues } = useSelect( ( select ) => ( {
		issues: select( store ).getIssuesForComponent( 'passwords' ),
	} ), [] );

	function StrongPasswordIssue( { issue } ) {
		return (
			<SiteScanIssue key={ issue.id } issue={ issue } icon={ shield }>
				<ScanIssueDetailContent>
					<ScanIssueDetailColumn>
						<Text text={ __( 'Action Details:', 'better-wp-security' ) } weight={ TextWeight.HEAVY } />
						<ScanIssueText text={ __( 'Passwords are the first line of defense to your site’s security. ', 'better-wp-security' ) } />
						<ScanIssueText text={ __( 'Enable strong password enforcement to require users to setup a strong password.', 'better-wp-security' ) } />
					</ScanIssueDetailColumn>
				</ScanIssueDetailContent>
				<SiteScanIssueActions issue={ issue } />
			</SiteScanIssue>
		);
	}

	return (
		<>
			<SiteScanIssuesFill>
				{ issues.filter( ( issue ) => ! issue.muted ).map( ( issue ) => (
					<StrongPasswordIssue key={ issue.id } issue={ issue } />
				) ) }
			</SiteScanIssuesFill>

			<SiteScanMutedIssuesFill>
				{ issues.filter( ( issue ) => issue.muted ).map( ( issue ) => (
					<StrongPasswordIssue key={ issue.id } issue={ issue } />
				) ) }
			</SiteScanMutedIssuesFill>
		</>
	);
}
