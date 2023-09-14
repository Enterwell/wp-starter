/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { PageHeader } from '@ithemes/security.pages.settings';

export default function MultiGroupHeader( { groupIds } ) {
	const label = useSelect(
		( select ) =>
			groupIds
				.map(
					select( 'ithemes-security/user-groups-editor' )
						.getEditedMatchableLabel
				)
				.join( ', ' ),
		[ groupIds ]
	);

	return <PageHeader title={ label || __( 'Select Groups', 'better-wp-security' ) } />;
}
