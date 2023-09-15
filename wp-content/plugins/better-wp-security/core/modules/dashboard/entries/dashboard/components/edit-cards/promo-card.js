/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

export default function PromoCard( { title } ) {
	return (
		<li className="itsec-edit-cards__card-choice itsec-edit-cards__card-choice--promo">
			<span className="itsec-edit-cards__card-choice-title">
				<span>{ __( 'Pro: ', 'better-wp-security' ) }</span>
				{ title }
			</span>
			<Button
				className="itsec-edit-cards__action itsec-edit-cards__action--add"
				label={ __( 'Go Pro', 'better-wp-security' ) }
				href="https://ithemes.com/security/?utm_source=wordpressadmin&utm_medium=dashboardcard&utm_campaign=itsecfreecta"
				icon="external"
			/>
		</li>
	);
}
