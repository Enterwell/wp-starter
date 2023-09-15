/**
 * External dependencies
 */
import { sortBy } from 'lodash';
import memize from 'memize';
/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { select as coreSelect, withSelect } from '@wordpress/data';
/**
 * Internal dependencies
 */
import { EditCardsSlot } from '@ithemes/security.dashboard.api';
import AddCard from './add-card';
import RemoveCard from './remove-card';
import { getCardTitle } from '../../utils';
import './style.scss';

const sorted = memize( ( cards ) =>
	sortBy( cards, [
		( card ) => {
			const config = coreSelect(
				'ithemes-security/dashboard'
			).getAvailableCard( card.card );

			return getCardTitle( card, config );
		},
	] )
);

function EditCards( { dashboardId, cards, availableCardLDOs } ) {
	return (
		<div className="itsec-edit-cards">
			<header>
				<h3>{ __( 'Edit Cards', 'better-wp-security' ) }</h3>
				<p>
					{ __( 'Add or remove cards on your dashboard.', 'better-wp-security' ) }
				</p>
			</header>
			<section>
				<ul className="itsec-edit-cards__card-choices">
					{ availableCardLDOs.map( ( ldo ) => (
						<AddCard
							ldo={ ldo }
							key={ ldo.href }
							dashboardId={ dashboardId }
						/>
					) ) }
					{ sorted( cards ).map( ( card ) => (
						<RemoveCard
							key={ card.id }
							card={ card }
							dashboardId={ dashboardId }
						/>
					) ) }

					<EditCardsSlot />
				</ul>
			</section>
		</div>
	);
}

export default compose( [
	withSelect( ( select, props ) => ( {
		cards: select( 'ithemes-security/dashboard' ).getDashboardCards(
			props.dashboardId
		),
		isAdding: select( 'ithemes-security/dashboard' ).isAddingCard(
			props.selected,
			{}
		),
		availableCardLDOs: select(
			'ithemes-security/dashboard'
		).getDashboardAddableCardLDOs( props.dashboardId ),
	} ) ),
] )( EditCards );
