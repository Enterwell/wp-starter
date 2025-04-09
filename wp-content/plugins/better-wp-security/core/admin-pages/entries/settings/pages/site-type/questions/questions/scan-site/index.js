/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { vulnerabilitiesStore } from '@ithemes/security.packages.data';

/**
 * Internal dependencies
 */
import Intro from './intro';
import NoIssues from './no-issues';
import FoundIssues from './found-issues';

export default function SiteScan( { question, onAnswer } ) {
	const { queried, vulnerabilities } = useSelect( ( select ) => ( {
		queried: select( vulnerabilitiesStore ).hasQueried( 'onboarding' ),
		vulnerabilities: select( vulnerabilitiesStore ).getQueryResults( 'onboarding' ),
	} ), [] );

	if ( ! queried ) {
		return (
			<Intro question={ question } onAnswer={ onAnswer } />
		);
	}

	if ( ! vulnerabilities.length ) {
		return <NoIssues onAnswer={ onAnswer } />;
	}

	return <FoundIssues issues={ vulnerabilities } onAnswer={ onAnswer } />;
}
