/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { NoData as Icon } from '@ithemes/security-style-guide';

export default function CardNoData() {
	return (
		<div className="itsec-empty-state-card itsec-empty-state-card--no-data">
			<h3>{ __( 'No data to report…', 'better-wp-security' ) }</h3>
			<Icon />
			<p>
				{ __(
					"There is no data to report yet. Don't worry, this does not mean there is an issue.",
					'better-wp-security'
				) }
			</p>
		</div>
	);
}
