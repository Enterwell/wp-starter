/**
 * WordPress dependencies
 */
import { Toolbar, ToolbarButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { help as helpIcon, settings as settingsIcon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { AdminBarSlot } from '@ithemes/security.dashboard.api';
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import { useCanWrite, useConfigContext } from '../../utils';
import { Logo } from '@ithemes/security-ui';
import './style.scss';

export default function( { dashboardId } ) {
	const canWrite = useCanWrite();
	const settingsUrl = useGlobalNavigationUrl( 'settings' );
	const { canManage } = useConfigContext();
	const { canCreate, canEdit } = useSelect(
		( select ) => ( {
			canCreate: select(
				'ithemes-security/dashboard'
			).canCreateDashboards(),
			canEdit: select( 'ithemes-security/dashboard' ).canEditDashboard(
				dashboardId
			),
		} ),
		[ dashboardId ]
	);

	if ( ! canWrite && ! canManage ) {
		return null;
	}

	return (
		<div className="itsec-dashboard-toolbar">
			<Logo />
			<Toolbar label={ __( 'Dashboard Toolbar', 'better-wp-security' ) }>
				{ canManage && (
					<ToolbarButton
						text={ __( 'Settings', 'better-wp-security' ) }
						icon={ settingsIcon }
						href={ settingsUrl }
					/>
				) }
				<AdminBarSlot />
				{ ( canEdit || canCreate ) && (
					<ToolbarButton
						icon={ helpIcon }
						className="itsec-admin-bar__help"
						href="https://go.solidwp.com/security-help-center"
						text={ __( 'Help', 'better-wp-security' ) }
						target="_blank"
					/>
				) }
			</Toolbar>
		</div>
	);
}
