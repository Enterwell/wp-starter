/**
 * External dependencies
 */
import { get, isArray } from 'lodash';

/**
 * Internal dependencies
 */
import WrappedSection from './wrapped-section';
import Detail from './detail';

function Entry( { results, entry } ) {
	let issues = get( results, [ '_embedded', 'ithemes-security:site-scan-issues', 0 ], [] );

	if ( ! isArray( issues ) ) {
		issues = [];
	}

	return (
		<WrappedSection type="malware" status={ entry.status } description={ entry.title }>
			{ issues.filter( ( issue ) => issue.entry === entry.slug ).map( ( issue, i ) => (
				<Detail key={ i } status={ issue.status }>
					<a href={ issue.link }>{ issue.description }</a>
				</Detail>
			) ) }
		</WrappedSection>
	);
}

export default Entry;
