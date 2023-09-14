/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';
import {
	commentAuthorName,
	currencyDollar,
	starFilled,
} from '@wordpress/icons';

/**
 * iThemes dependencies
 */
import { Callout, CalloutItem, Heading, PricingCard } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Header, CardGrid } from '../../components';
import './style.scss';

export default function Pricing( { pricing } ) {
	return (
		<Flex className="itsec-go-pro-pricing" direction="column" gap={ 8 }>
			<Header
				title={ __( 'View Pricing & Plans', 'better-wp-security' ) }
				subtitle={ __( 'The iThemes Security Pro plugin adds additional layers of protection for your WordPress website with performance in mind. Plus, iThemes Security Pro pricing is perfect for those on a budget.', 'better-wp-security' ) }
			/>

			<CardGrid className="itsec-go-pro-pricing-grid">
				{ ( pricing || [] ).map( ( item, i ) => (
					<PricingCard key={ i } { ...item } />
				) ) }
			</CardGrid>

			<Heading level={ 2 } variant="dark" weight="heavy" text={ __( 'Why Buy from iThemes?', 'better-wp-security' ) } />
			<Callout variant="secondary">
				<CalloutItem
					heading={ __( 'Fast, Friendly Support', 'better-wp-security' ) }
					description={ __( 'We’ve been called “the friendliest support team in the WordPress world.” Most tickets are solved within one hour.', 'better-wp-security' ) }
					icon={ commentAuthorName }
				/>
				<CalloutItem
					heading={ __( '30-Day Money Back Guarantee', 'better-wp-security' ) }
					description={ __( 'We stand behind our products 100%. We offer a 30-day money-back guarantee with our refund policy.', 'better-wp-security' ) }
					icon={ currencyDollar }
				/>
				<CalloutItem
					heading={ __( 'We’ve Been in Business Since 2008', 'better-wp-security' ) }
					description={ __( 'Founded as one of the very first premium WordPress companies, we’re now one of the most trusted brands in WordPress.', 'better-wp-security' ) }
					icon={ starFilled }
				/>
			</Callout>
		</Flex>
	);
}
