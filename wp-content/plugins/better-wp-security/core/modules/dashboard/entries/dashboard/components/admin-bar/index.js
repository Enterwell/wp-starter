/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { getAuthority, getPath } from '@wordpress/url';
import { Dropdown } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { brush } from '@wordpress/icons';
import { useEffect } from '@wordpress/element';

/**
 * iThemes dependencies
 */
import { Button, Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { AdminBarSlot } from '@ithemes/security.dashboard.api';
import EditCards from '../edit-cards';
import { getMaxWidthForGrid } from '../../utils';
import { StyledAdminBar, StyledSectionPrimary, StyledSectionSecondary } from './styles';

export default function AdminBar( { dashboardId, width } ) {
	const { requesting, siteInfo, canEdit, editingCards } = useSelect( ( select ) => ( {
		siteInfo: select( 'ithemes-security/core' ).getSiteInfo(),
		dashboards: select(
			'ithemes-security/dashboard'
		).getAvailableDashboards(),
		requesting: select(
			'ithemes-security/dashboard'
		).isRequestingDashboards(),
		canEdit: dashboardId && select( 'ithemes-security/dashboard' ).canEditDashboard(
			dashboardId
		),
		editingCards: select(
			'ithemes-security/dashboard'
		).isEditingCards(),
	} ), [ dashboardId ] );
	const { openEditCards, closeEditCards } = useDispatch(
		'ithemes-security/dashboard'
	);

	let prettyUrl;

	if ( siteInfo ) {
		const path = getPath( siteInfo.url );
		prettyUrl = getAuthority( siteInfo.url );

		if ( path ) {
			prettyUrl += '/' + path;
		}
	}

	if ( requesting ) {
		return null;
	}

	const maxWidth = getMaxWidthForGrid( width );

	return (
		<StyledAdminBar className="itsec-admin-bar" maxWidth={ maxWidth }>
			<StyledSectionPrimary>
				<AdminBarSlot type="primary" />
			</StyledSectionPrimary>
			<StyledSectionSecondary>
				<Text variant={ TextVariant.DARK } text={ prettyUrl } />
				{ canEdit && (
					<Dropdown
						headerTitle={ __( 'Edit Dashboard Cards', 'better-wp-security' ) }
						expandOnMobile
						popoverProps={ { position: 'bottom left' } }
						onClose={ closeEditCards }
						renderToggle={ function Toggle( { onToggle, onClose } ) {
							// The Dropdown component can't be controlled.
							useEffect( () => {
								if ( editingCards ) {
									onToggle();
								} else {
									onClose();
								}
								// eslint-disable-next-line react-hooks/exhaustive-deps
							}, [ editingCards ] );

							return (
								<Button
									text={ __( 'Edit Dashboard Cards', 'better-wp-security' ) }
									icon={ brush }
									aria-expanded={ editingCards }
									onClick={ editingCards ? closeEditCards : openEditCards }
									variant="tertiary"
								/>
							);
						} }
						renderContent={ () => {
							return (
								<EditCards
									dashboardId={ dashboardId }
									close={ closeEditCards }
								/>
							);
						} }
					/>
				) }
			</StyledSectionSecondary>
		</StyledAdminBar>
	);
}
