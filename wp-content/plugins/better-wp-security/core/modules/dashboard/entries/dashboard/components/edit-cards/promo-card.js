/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { external } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { StyledCard, StyledButton } from './styles';

export default function PromoCard( { title } ) {
	return (
		<StyledCard>
			<StyledButton
				/* translators: 1. Dashboard Card Name. */
				text={ sprintf( __( 'Pro: %s', 'better-wp-security' ), title ) }
				href="https://go.solidwp.com/buy-solid-security"
				target="_blank"
				align="left"
				icon={ external }
				iconPosition="right"
				variant="tertiary"
			/>
		</StyledCard>
	);
}
