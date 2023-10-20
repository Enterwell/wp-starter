/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { shield } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Text, TextWeight } from '@ithemes/ui';
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

function TwoFactorIssue( { issue } ) {
	return (
		<SiteScanIssue key={ issue.id } issue={ issue } icon={ shield }>
			<ScanIssueDetailContent>
				<ScanIssueDetailColumn>
					<Text text={ __( 'Action Details:', 'better-wp-security' ) } weight={ TextWeight.HEAVY } />
					<ScanIssueText text={ __( 'Send a notification to this user to remind them to set up two-factor authentication.', 'better-wp-security' ) } />
				</ScanIssueDetailColumn>
			</ScanIssueDetailContent>
			<SiteScanIssueActions issue={ issue } />
		</SiteScanIssue>
	);
}
export default function App() {
	const { issues } = useSelect( ( select ) => ( {
		issues: select( store ).getIssuesForComponent( 'two-factor' ),
	} ), [] );
	return (
		<>
			<SiteScanIssuesFill>
				{ issues.filter( ( issue ) => ! issue.muted ).map( ( issue ) => (
					<TwoFactorIssue key={ issue.id } issue={ issue } />
				) ) }
			</SiteScanIssuesFill>

			<SiteScanMutedIssuesFill>
				{ issues.filter( ( issue ) => issue.muted ).map( ( issue ) => (
					<TwoFactorIssue key={ issue.id } issue={ issue } />
				) ) }
			</SiteScanMutedIssuesFill>
		</>
	);
}
