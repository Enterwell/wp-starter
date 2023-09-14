/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex, Card, CardBody } from '@wordpress/components';

/**
 * iThemes dependencies
 */
import { Text, Surface, FeatureCard } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Header, CardGrid } from '../../components';
import './style.scss';

export default function Features( { features } ) {
	return (
		<Flex className="itsec-go-pro-features" direction="column" gap={ 8 }>
			<Header
				title={ __( 'Ready to take your site’s security to the next level?', 'better-wp-security' ) }
				subtitle={ __( 'Add more layers of security to your site with features designed to protect against known vulnerabilities, strengthen user logins, and enhance security logging.', 'better-wp-security' ) }
			/>
			<CardGrid>
				{ ( features || [] ).map( ( feature, i ) => (
					<FeatureCard
						key={ i }
						{ ...feature }
					/>
				) ) }
			</CardGrid>
			<Card size="large">
				<Surface variant="secondary">
					<CardBody>
						<Text
							variant="accent"
							size="large"
							text={ __( 'We stand by our product 100%. All iThemes Security Pro plans come with a 30-day money back guarantee.', 'better-wp-security' ) }
						/>
					</CardBody>
				</Surface>
			</Card>
		</Flex>
	);
}
