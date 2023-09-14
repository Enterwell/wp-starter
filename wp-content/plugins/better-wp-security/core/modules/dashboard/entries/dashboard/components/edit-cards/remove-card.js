/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getCardTitle } from '../../utils';

function RemoveCard( { card, config, remove } ) {
	return (
		<li className="itsec-edit-cards__card-choice itsec-edit-cards__card-choice--remove">
			<Button
				className="itsec-edit-cards__action itsec-edit-cards__action--remove"
				label={ __( 'Remove', 'better-wp-security' ) }
				icon="no"
				showTooltip={ false }
				onClick={ remove }
			/>
			<span className="itsec-edit-cards__card-choice-title">
				{ getCardTitle( card, config ) }
			</span>
		</li>
	);
}

export default compose( [
	withSelect( ( select, props ) => ( {
		config: select( 'ithemes-security/dashboard' ).getAvailableCard(
			props.card.card
		),
	} ) ),
	withDispatch( ( dispatch, props ) => ( {
		remove() {
			return dispatch( 'ithemes-security/dashboard' ).removeDashboardCard(
				props.dashboardId,
				props.card
			);
		},
	} ) ),
] )( RemoveCard );
