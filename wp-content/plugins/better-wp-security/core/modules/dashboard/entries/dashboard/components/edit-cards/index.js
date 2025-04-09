/**
 * External dependencies
 */
import { sortBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { useViewportMatch } from '@wordpress/compose';

/**
 * iThemes dependencies
 */
import { Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { EditCardsSlot } from '@ithemes/security.dashboard.api';
import { getCardTitle } from '../../utils';
import AddCard from './add-card';
import RemoveCard from './remove-card';
import { StyledEditCards, StyledHeader, StyledCardsList } from './styles';

export default function EditCards( { dashboardId } ) {
	const isExpanded = useViewportMatch( 'medium', '<' );

	const { cards, cardConfigs, addableCards } = useSelect( ( select ) => ( {
		cards: select( 'ithemes-security/dashboard' ).getDashboardCards( dashboardId ),
		cardConfigs: select( 'ithemes-security/dashboard' ).getAvailableCards(),
		addableCards: select( 'ithemes-security/dashboard' ).getDashboardAddableCardLDOs( dashboardId ),
	} ), [ dashboardId ] );
	const sortedCards = useMemo(
		() => sortBy(
			cards,
			( card ) => getCardTitle(
				card,
				cardConfigs.find( ( config ) => card.card === config.slug )
			)
		), [ cards, cardConfigs ] );

	return (
		<StyledEditCards isExpanded={ isExpanded }>
			<StyledHeader>
				{ ! isExpanded && (
					<Heading
						level={ 3 }
						variant={ TextVariant.DARK }
						weight={ TextWeight.HEAVY }
						size={ TextSize.NORMAL }
						text={ __( 'Edit Cards', 'better-wp-security' ) }
					/>
				) }
				<Text
					variant={ TextVariant.MUTED }
					size={ TextSize.SMALL }
					text={ __( 'Add or remove cards on your dashboard.', 'better-wp-security' ) }
				/>
			</StyledHeader>
			<StyledCardsList>
				{ addableCards.map( ( ldo ) => (
					<AddCard
						ldo={ ldo }
						key={ ldo.href }
						dashboardId={ dashboardId }
					/>
				) ) }
				{ sortedCards.map( ( card ) => (
					<RemoveCard
						key={ card.id }
						card={ card }
						dashboardId={ dashboardId }
					/>
				) ) }

				<EditCardsSlot />
			</StyledCardsList>
		</StyledEditCards>
	);
}
