/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { withSelect, withDispatch } from '@wordpress/data';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Unknown as Icon } from '@ithemes/security-style-guide';

function CardUnknown( { card, removing, canRemove, remove } ) {
	return (
		<div className="itsec-empty-state-card itsec-empty-state-card--unknown">
			<h3>{ __( 'Unknown Card', 'better-wp-security' ) }</h3>
			<Icon />
			<p>
				{ __(
					'Something went wrong with this card. This is most likely due to disabling an iThemes Security Module.',
					'better-wp-security'
				) }
			</p>
			{ canRemove && (
				<Button variant="secondary" isBusy={ removing } onClick={ remove }>
					{ __( 'Remove Card', 'better-wp-security' ) }
				</Button>
			) }
			<span>
				{ __( 'Card Type: ', 'better-wp-security' ) }
				<br />
				<code>{ card.original }</code>
			</span>
		</div>
	);
}

export default compose( [
	withSelect( ( select, props ) => ( {
		removing: select( 'ithemes-security/dashboard' ).isRemovingCard(
			props.card.id
		),
		canRemove: select( 'ithemes-security/dashboard' ).canEditCard(
			props.dashboardId,
			props.card.id
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
] )( CardUnknown );
