/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { Heading, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { AsideHeaderSlot, FirewallBannerSlot } from '../slot-fill';
import {
	ActiveUpdatesBadge,
	VirtualPatchingBadge,
} from '@ithemes/security-ui';
import {
	StyledHeader,
	StyledHeaderContainer,
} from './styles';

export default function Header() {
	return (
		<>
			<FirewallBannerSlot />
			<StyledHeader>
				<StyledHeaderContainer>
					<Heading level={ 1 } weight={ TextWeight.NORMAL } text={ __( 'Firewall' ) } />
					<ActiveUpdatesBadge />
					<VirtualPatchingBadge />
				</StyledHeaderContainer>
				<AsideHeaderSlot />
			</StyledHeader>
		</>
	);
}
