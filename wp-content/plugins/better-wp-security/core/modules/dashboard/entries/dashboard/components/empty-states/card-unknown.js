/**
 * External Dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress Dependencies
 */
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { withSelect, withDispatch } from '@wordpress/data';
import { createInterpolateElement } from '@wordpress/element';

/**
 * SolidWP Dependencies
 */
import {
	Button,
	Heading,
	TextSize,
	TextWeight,
	Text, Surface,
} from '@ithemes/ui';

/**
 * Internal Dependencies
 */
import { UnknownCrashCard } from '@ithemes/security-style-guide';
import { HiResIcon } from '@ithemes/security-ui';
import Header, { Title } from '../card/header';

const StyledSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	height: 100%;
`;

const BodyContainer = styled.div`
	text-align: center;
	display: flex;
	gap: ${ ( { theme: { getSize } } ) => getSize( 1.25 ) };
	flex-direction: column;
	align-items: center;
	flex-grow: 1;
	height: 100%;
	justify-content: center;
`;

const StyledSection = styled.section`
	max-width: 70ch;
	display: flex;
	flex-direction: column;
	gap: ${ ( { theme: { getSize } } ) => getSize( 0.5 ) };
`;

const StyledCardType = styled.span`
	display: flex;
	flex-direction: column;
	align-items: center;
`;

function CardUnknown( { card, removing, canRemove, remove } ) {
	return (
		<StyledSurface>
			<Header>
				<Title card={ card } />
			</Header>
			<BodyContainer className="itsec-card__util-padding">
				<StyledSection>
					<Heading
						level={ 4 }
						size={ TextSize.NORMAL }
						weight={ TextWeight.HEAVY }
						text={ __( 'Unexpected Error', 'better-wp-security' ) }
						align="center"
					/>
					<Text
						as="p"
						text={ __(
							'Something went wrong with this card. This is most likely due to disabling a Solid Security Module.',
							'better-wp-security'
						) }
						align="center"
						variant="muted"
					/>
				</StyledSection>
				<HiResIcon icon={ <UnknownCrashCard /> } isSmall />
				{ canRemove && (
					<Button
						variant="secondary"
						isBusy={ removing }
						onClick={ remove }
					>
						{ __( 'Remove Card', 'better-wp-security' ) }
					</Button>
				) }
				{ card.original && (
					<StyledCardType>
						<Text
							size={ TextSize.SMALL }>
							{ createInterpolateElement(
								__( 'Card Type: <card />', 'better-wp-security' ),
								{
									card: <code>{ card.original }</code>,
								}
							) }
						</Text>
					</StyledCardType>
				) }
			</BodyContainer>
		</StyledSurface>
	);
}

export default compose( [
	withSelect( ( select, props ) => ( {
		removing: select( 'ithemes-security/dashboard' ).isRemovingCard(
			props.card.id
		),
		canRemove: select( 'ithemes-security/dashboard' ).canEditCard(
			props.dashboardId,
			props.card.id
		),
	} ) ),
	withDispatch( ( dispatch, props ) => ( {
		remove() {
			return dispatch( 'ithemes-security/dashboard' ).removeDashboardCard(
				props.dashboardId,
				props.card
			);
		},
	} ) ),
] )( CardUnknown );
