/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

function AddCard( { ldo, cardAtLimit, isAdding, add } ) {
	return (
		! cardAtLimit && (
			<li className="itsec-edit-cards__card-choice itsec-edit-cards__card-choice--add">
				<span className="itsec-edit-cards__card-choice-title">
					{ ldo.title }
				</span>
				<Button
					disabled={ isAdding }
					onClick={ () => add( ldo.href ) }
					className="itsec-edit-cards__action itsec-edit-cards__action--add"
					label={ __( 'Add', 'better-wp-security' ) }
					icon="plus"
					showTooltip={ false }
				/>
			</li>
		)
	);
}

export default compose( [
	withSelect( ( select, props ) => ( {
		cardAtLimit: select(
			'ithemes-security/dashboard'
		).isCardAtDashboardLimit( props.dashboardId, props.ldo.aboutLink ),
		isAdding: select( 'ithemes-security/dashboard' ).isAddingCard(
			`edit-cards-add-${ props.ldo.aboutLink }-to-${ props.dashboardId }`
		),
	} ) ),
	withDispatch( ( dispatch, props ) => ( {
		add( ep, card = {} ) {
			return dispatch( 'ithemes-security/dashboard' ).addDashboardCard(
				ep,
				card,
				`edit-cards-add-${ props.ldo.aboutLink }-to-${ props.dashboardId }`
			);
		},
	} ) ),
] )( AddCard );
