/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { ToggleControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * iThemes dependencies
 */
import { ListItem } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledHighlightsList } from '../panel/styles';

export default function Highlights( { loaded, isConfiguring } ) {
	const { mutedHighlights, mutedHighlightUpdatesInFlight } = useSelect( ( select ) => ( {
		mutedHighlights: select(
			'ithemes-security/admin-notices'
		).getMutedHighlights(),
		mutedHighlightUpdatesInFlight: select(
			'ithemes-security/admin-notices'
		).getMutedHighlightUpdatesInFlight(),
	} ), [] );
	const { updateMutedHighlight } = useDispatch( 'ithemes-security/admin-notices' );

	if ( ! isConfiguring ) {
		return null;
	}

	return (
		<StyledHighlightsList>
			{ getAvailableHighlights().map(
				( { slug, label } ) =>
					mutedHighlights[ slug ] !== undefined && (
						<ListItem key={ slug }>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ label }
								disabled={
									! loaded ||
									mutedHighlightUpdatesInFlight[
										slug
									]
								}
								checked={
									! get(
										mutedHighlightUpdatesInFlight,
										[ slug, 'mute' ],
										mutedHighlights[ slug ]
									)
								}
								onChange={ () =>
									updateMutedHighlight(
										slug,
										! mutedHighlights[ slug ]
									)
								}
							/>
						</ListItem>
					)
			) }
		</StyledHighlightsList>
	);
}

function getAvailableHighlights() {
	return [
		{
			slug: 'file-change-report',
			label: __( 'File Change Report', 'better-wp-security' ),
		},
		{
			slug: 'notification-center-send-failed',
			label: __( 'Notification Center Errors', 'better-wp-security' ),
		},
		{
			slug: 'malware-scan-report',
			label: __( 'Malware Scan Report', 'better-wp-security' ),
		},
		{
			slug: 'malware-scan-failed',
			label: __( 'Malware Scan Failed', 'better-wp-security' ),
		},
		{
			slug: 'site-scanner-report',
			label: __( 'Site Scan Report', 'better-wp-security' ),
		},
	];
}
