/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

export const {
	Slot: BeforeCreateFirewallRuleSlot,
	Fill: BeforeCreateFirewallRuleFill,
} = createSlotFill( 'BeforeCreateFirewallRule' );

export const {
	Slot: AsideHeaderSlot,
	Fill: AsideHeaderFill,
} = createSlotFill( 'AsideHeader' );

export const {
	Slot: FirewallBannerSlot,
	Fill: FirewallBannerFill,
} = createSlotFill( 'FirewallBanner' );
