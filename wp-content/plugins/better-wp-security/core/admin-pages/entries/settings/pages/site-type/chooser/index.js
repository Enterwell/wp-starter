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

/**
 * Internal dependencies
 */
import { HelpList, MessageList } from '@ithemes/security-components';
import { PageHeader, SelectableCard, HelpFill } from '../../../components';
import { STORE_NAME } from '../../../stores/onboard';
import { useNavigateTo } from '../../../utils';
import './style.scss';

export default function SiteTypeChooser() {
	const { clearVisitedLocations } = useDispatch( STORE_NAME );
	const { siteTypes, lastVisitedLocation } = useSelect( ( select ) => ( {
		siteTypes: select( STORE_NAME ).getSiteTypes(),
		lastVisitedLocation: select( STORE_NAME ).getLastVisitedLocation(),
	} ) );

	return (
		<>
			<PageHeader
				title={ __( 'Choose the Type of Website', 'better-wp-security' ) }
				subtitle={ __(
					'Select one of the following that best represents your website.',
					'better-wp-security'
				) }
			/>

			{ lastVisitedLocation && (
				<MessageList
					hasBorder
					onDismiss={ clearVisitedLocations }
					messages={ [
						createInterpolateElement(
							__(
								'Already started setting up iThemes Security? <a>Resume</a> from where you left off.',
								'better-wp-security'
							),
							{
								a: <Link to={ lastVisitedLocation } />,
							}
						),
					] }
				/>
			) }

			<ul className="itsec-site-type-list">
				{ siteTypes.map( ( siteType ) => (
					<li key={ siteType.id }>
						<SiteType
							id={ siteType.id }
							title={ siteType.title }
							description={ siteType.description }
							icon={ siteType.icon }
							recommended={ siteType.recommended }
						/>
					</li>
				) ) }
			</ul>

			<HelpFill>
				<PageHeader title={ __( 'Site Type', 'better-wp-security' ) } />
				<HelpList topic="site-type" />
			</HelpFill>
		</>
	);
}

function SiteType( { id, title, description, icon, recommended } ) {
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
			icon={ icon }
			recommended={ recommended }
		/>
	);
}
