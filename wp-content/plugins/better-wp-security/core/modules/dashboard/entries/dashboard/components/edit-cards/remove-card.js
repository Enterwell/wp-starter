/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { closeSmall } from '@wordpress/icons';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getCardTitle } from '../../utils';
import { StyledCard, StyledTertiaryButton } from './styles';

export default function RemoveCard( { dashboardId, card } ) {
	const { config } = useSelect( ( select ) => ( {
		config: select( 'ithemes-security/dashboard' ).getAvailableCard( card.card ),
	} ), [ card.card ] );
	const { removeDashboardCard } = useDispatch( 'ithemes-security/dashboard' );

	const title = getCardTitle( card, config );

	return (
		<StyledCard>
			<StyledTertiaryButton
				text={ title }
				/* translators: 1. Dashboard Card Name */
				label={ sprintf( __( 'Remove %s', 'better-wp-security' ), title ) }
				icon={ closeSmall }
				iconPosition="right"
				onClick={ () => removeDashboardCard( dashboardId, card ) }
				showTooltip={ false }
				variant="tertiary"
			/>
		</StyledCard>
	);
}
