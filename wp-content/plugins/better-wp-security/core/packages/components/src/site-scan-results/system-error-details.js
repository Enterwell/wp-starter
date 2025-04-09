/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import WrappedSection from './wrapped-section';
import Detail from './detail';

function SystemErrorDetails( { results } ) {
	return (
		results.errors.length > 0 && (
			<WrappedSection
				type="system-error"
				status="error"
				description={ __(
					'The scan failed to properly scan the site.',
					'LION'
				) }
			>
				{ results.errors.map( ( entry, i ) => (
					<Detail key={ i } status="error">
						{ entry.message }
					</Detail>
				) ) }
			</WrappedSection>
		)
	);
}

export default SystemErrorDetails;
