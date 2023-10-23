/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { plus } from '@wordpress/icons';
import { StyledButton, StyledCard } from './styles';

export default function AddCard( { dashboardId, ldo } ) {
	const addingKey = `edit-cards-add-${ ldo.aboutLink }-to-${ dashboardId }`;
	const { cardAtLimit, isAdding } = useSelect( ( select ) => ( {
		cardAtLimit: select(
			'ithemes-security/dashboard'
		).isCardAtDashboardLimit( dashboardId, ldo.aboutLink ),
		isAdding: select( 'ithemes-security/dashboard' ).isAddingCard(
			addingKey
		),
	} ), [ dashboardId, ldo, addingKey ] );
	const { addDashboardCard } = useDispatch( 'ithemes-security/dashboard' );

	if ( cardAtLimit ) {
		return null;
	}

	return (
		<StyledCard>
			<StyledButton
				text={ ldo.title }
				/* translators: 1. Dashboard Card Name */
				label={ sprintf( __( 'Add %s', 'better-wp-security' ), ldo.title ) }
				icon={ plus }
				iconPosition="left"
				disabled={ isAdding }
				onClick={ () => addDashboardCard( ldo.href, {}, addingKey ) }
				showTooltip={ false }
				variant="tertiaryAccent"
			/>
		</StyledCard>
	);
}
