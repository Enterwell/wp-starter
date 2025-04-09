/**
 * External dependencies
 */
import { ErrorBoundary } from 'react-error-boundary';
import classnames from 'classnames';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { pure } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';

/**
 * iThemes dependencies
 */
import { Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withProps } from '@ithemes/security-hocs';
import { useCardElementQueries, useCardRenderer } from '../../cards';
import CardUnknown from '../empty-states/card-unknown';
import CardCrash from '../empty-states/card-crash';
import './style.scss';

const StyledCard = styled( Surface )`
	width: 100%;
	height: 100%;
	border-radius: 2px;
	box-shadow: 0 0 5px rgba(211, 211, 211, 0.35);
`;

function Card( { id, dashboardId, className, gridWidth, children, ...rest } ) {
	const { card, config } = useSelect(
		( select ) => ( {
			card: select( 'ithemes-security/dashboard' ).getDashboardCard( id ),
			config:
				select( 'ithemes-security/dashboard' ).getDashboardCardConfig(
					id
				) || {},
		} ),
		[ id ]
	);
	const CardRender = useCardRenderer( config );
	const eqProps = useCardElementQueries( config, rest.style, gridWidth );

	if ( card.card === 'unknown' ) {
		return (
			<StyledCard
				as="article"
				className={ classnames(
					className,
					'itsec-card',
					'itsec-card--unknown'
				) }
				{ ...rest }
			>
				<CardUnknown card={ card } dashboardId={ dashboardId } />
			</StyledCard>
		);
	}

	if ( ! CardRender ) {
		return (
			<StyledCard
				as="article"
				className={ classnames(
					className,
					'itsec-card',
					'itsec-card--no-rendered'
				) }
				{ ...rest }
			>
				<CardCrash card={ card } config={ config } />
			</StyledCard>
		);
	}

	return (
		<StyledCard
			as="article"
			className={ classnames( className, 'itsec-card' ) }
			id={ `itsec-card-${ card.id }` }
			{ ...rest }
			{ ...eqProps }
		>
			<ErrorBoundary
				FallbackComponent={ withProps( { card, config } )( CardCrash ) }
			>
				<CardRender
					card={ card }
					config={ config }
					dashboardId={ dashboardId }
					eqProps={ eqProps }
				/>
			</ErrorBoundary>
			{ children }
		</StyledCard>
	);
}

export default pure( Card );
