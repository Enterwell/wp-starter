/**
 * External dependencies
 */
import { useRouteMatch, Link } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { createSlotFill } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { Heading, List, ListItem, Notice, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { SelectableCard } from '../../../components';
import { STORE_NAME } from '../../../stores/onboard';
import { useNavigateTo } from '../../../utils';
import {
	StyledHeader,
	StyledLogo,
	StyledSiteTypeChooser,
	brochure,
	blog,
	network,
	portfolio,
	ecommerce,
	nonProfit,
} from './styles';

export const {
	Slot: OnboardSiteTypeBeforeSlot,
	Fill: OnboardSiteTypeBeforeFill,
} = createSlotFill( 'OnboardSiteTypeBefore' );

export default function SiteTypeChooser() {
	const { clearVisitedLocations } = useDispatch( STORE_NAME );
	const { siteTypes, lastVisitedLocation } = useSelect( ( select ) => ( {
		siteTypes: select( STORE_NAME ).getSiteTypes(),
		lastVisitedLocation: select( STORE_NAME ).getLastVisitedLocation(),
	} ) );

	return (
		<StyledSiteTypeChooser>
			<StyledLogo />
			<Text
				as="p"
				size={ TextSize.EXTRA_LARGE }
				text={ __( 'Welcome to Solid Security! Answer a few questions to quickly enable the most important security features for this website. You can always change settings later.', 'better-wp-security' ) }
			/>

			<OnboardSiteTypeBeforeSlot />

			{ lastVisitedLocation && (
				<Notice
					onDismiss={ clearVisitedLocations }
					text={
						<Text>
							{ createInterpolateElement(
								__(
									'Already started setting up Solid Security? <a>Resume</a> from where you left off.',
									'better-wp-security'
								),
								{
									a: <Link to={ lastVisitedLocation } />,
								}
							) }
						</Text>
					}
				/>
			) }

			<StyledHeader>
				<Heading
					level={ 2 }
					size={ TextSize.LARGE }
					weight={ TextWeight.HEAVY }
					text={ __( 'What type of website is this?', 'better-wp-security' ) }
				/>
				<Text
					as="p"
					variant={ TextVariant.DARK }
					text={ __( 'Select the one that best represents your website. This will focus the rest of the setup wizard on the options most necessary to secure the site.', 'better-wp-security' ) }
				/>
			</StyledHeader>

			<List gap={ 3 }>
				{ siteTypes.map( ( siteType ) => (
					<ListItem key={ siteType.id }>
						<SiteType
							id={ siteType.id }
							title={ siteType.title }
							description={ siteType.description }
							recommended={ siteType.recommended }
						/>
					</ListItem>
				) ) }
			</List>
		</StyledSiteTypeChooser>
	);
}

const icons = {
	ecommerce,
	network,
	'non-profit': nonProfit,
	blog,
	brochure,
	portfolio,
};

function SiteType( { id, title, description, recommended } ) {
	const { clearSiteType } = useDispatch( STORE_NAME );
	const match = useRouteMatch();
	const navigateTo = useNavigateTo();
	const onClick = () => {
		clearSiteType();
		navigateTo( `${ match.url }/${ id }` );
	};

	return (
		<SelectableCard
			onClick={ onClick }
			title={ title }
			description={ description }
			icon={ icons[ id ] }
			recommended={ recommended }
		/>
	);
}
