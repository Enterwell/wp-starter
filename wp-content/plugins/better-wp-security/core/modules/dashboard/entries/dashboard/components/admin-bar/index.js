/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { getAuthority, getPath } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { AdminBarSlot } from '@ithemes/security.dashboard.api';
import './style.scss';

export default function AdminBar() {
	const { requesting, siteInfo } = useSelect( ( select ) => ( {
		siteInfo: select( 'ithemes-security/core' ).getSiteInfo(),
		dashboards: select(
			'ithemes-security/dashboard'
		).getAvailableDashboards(),
		requesting: select(
			'ithemes-security/dashboard'
		).isRequestingDashboards(),
	} ) );
	let prettyUrl;

	if ( siteInfo ) {
		const path = getPath( siteInfo.url );
		prettyUrl = getAuthority( siteInfo.url );

		if ( path ) {
			prettyUrl += '/' + path;
		}
	}

	return (
		! requesting && (
			<div className="itsec-admin-bar">
				<div className="itsec-admin-bar__primary">
					<AdminBarSlot type="primary" />
				</div>
				<div className="itsec-admin-bar__secondary">
					<span className="itsec-admin-bar__url">{ prettyUrl }</span>
				</div>
			</div>
		)
	);
}
