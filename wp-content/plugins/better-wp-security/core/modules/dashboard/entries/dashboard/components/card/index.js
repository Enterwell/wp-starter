/**
 * External dependencies
 */
import { ErrorBoundary } from 'react-error-boundary';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { pure } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { withProps } from '@ithemes/security-hocs';
import { useCardElementQueries, useCardRenderer } from '../../cards';
import CardUnknown from '../empty-states/card-unknown';
import CardCrash from '../empty-states/card-crash';
import CardBecoming from '../empty-states/card-becoming';
import './style.scss';

function Card( { id, dashboardId, className, gridWidth, children, ...rest } ) {
	let { card, config } = useSelect(
		( select ) => ( {
			card: select( 'ithemes-security/dashboard' ).getDashboardCard( id ),
			config:
				select( 'ithemes-security/dashboard' ).getDashboardCardConfig(
					id
				) || {},
		} ),
		[ id ]
	);

	if ( id === 'ithemes-becoming-solid' ) {
		card = {
			card: 'ithemes-becoming-solid',
			dashboardId: 99999,
		};
		card.card = 'ithemes-becoming-solid';
		card.dashboardId = 99999;
	}

	const CardRender = useCardRenderer( config );
	const eqProps = useCardElementQueries( config, rest.style, gridWidth );

	if ( card.card === 'unknown' ) {
		return (
			<article
				className={ classnames(
					className,
					'itsec-card',
					'itsec-card--unknown'
				) }
				{ ...rest }
			>
				<CardUnknown card={ card } dashboardId={ dashboardId } />
			</article>
		);
	}

	if ( card.card === 'ithemes-becoming-solid' ) {
		return (
			<article
				className={ classnames(
					className,
					'itsec-card',
					'itsec-card--ithemes-becoming-solid'
				) }
				{ ...rest }
			>
				<CardBecoming card={ card } dashboardId={ dashboardId } />
			</article>
		);
	}

	if ( ! CardRender ) {
		return (
			<article
				className={ classnames(
					className,
					'itsec-card',
					'itsec-card--no-rendered'
				) }
				{ ...rest }
			>
				<CardCrash card={ card } config={ config } />
			</article>
		);
	}

	return (
		<article
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
		</article>
	);
}

export default pure( Card );
