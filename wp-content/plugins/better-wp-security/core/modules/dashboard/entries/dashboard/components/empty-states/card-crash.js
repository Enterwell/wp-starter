/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Crash as Icon } from '@ithemes/security-style-guide';
import Header, { Title } from '../card/header';

function CardCrash( { card, config } ) {
	return (
		<div className="itsec-empty-state-card itsec-empty-state-card--error">
			{ config && (
				<Header>
					<Title card={ card } config={ config } />
				</Header>
			) }
			<h3>{ __( 'Unexpected Error', 'better-wp-security' ) }</h3>
			<Icon />
			<p>
				{ __( 'An error occurred while rendering this card.', 'better-wp-security' ) }
			</p>
			<p>
				{ __(
					'Try refreshing your browser. If the error persists, please contact support.',
					'better-wp-security'
				) }
			</p>
		</div>
	);
}

export default CardCrash;
