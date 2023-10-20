/**
 * External dependencies
 */
import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';
import { Responsive as Grid } from 'react-grid-layout';
import { debounce } from 'lodash';

/**
 * WordPress dependencies
 */
import { compose, ifCondition, pure } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { withInterval } from '@ithemes/security-hocs';
import Card from '../card';
import EmptyState from '../card-grid/empty-state';
import {
	GRID_COLUMNS,
	BREAKPOINTS,
	CARD_MARGIN,
	getMaxWidthForGrid,
	areGridLayoutsEqual,
	transformApiLayoutToGrid,
	transformGridLayoutToApi,
	sortCardsToMatchLayout,
} from '../../utils';
import './style.scss';

class CardGrid extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			layout: transformApiLayoutToGrid(
				props.dashboardId,
				props.cards,
				props.layout
			),
			isMoving: false,
			breakpoint: props.breakpoint || 'wide',
			breakpointInitialized: !! props.breakpoint,
		};

		this.onLayoutChange = debounce( this._onLayoutChange, 1000 );
	}

	componentDidUpdate( prevProps ) {
		if (
			! this.state.breakpointInitialized &&
			this.props.breakpoint &&
			! prevProps.breakpoint
		) {
			this.setState( {
				breakpoint: this.props.breakpoint,
				breakpointInitialized: true,
			} );
		}

		if (
			this.props.layout !== prevProps.layout &&
			( this.props.dashboardId !== prevProps.dashboardId ||
				this.props.cards.length !== prevProps.cards.length )
		) {
			const transformed = transformApiLayoutToGrid(
				this.props.dashboardId,
				this.props.cards,
				this.props.layout
			);

			this.setState( { layout: transformed } );
		}

		if (
			this.props.dashboardId === prevProps.dashboardId &&
			this.props.cards.length > prevProps.cards.length
		) {
			// Converting between the two layouts allows us to detect the new card and add it to the layout. At this point in time, the card
			// is in the local state layout from RGL, but not yet in the API layout.
			const layout = transformApiLayoutToGrid(
				this.props.dashboardId,
				this.props.cards,
				transformGridLayoutToApi(
					this.props.dashboardId,
					this.state.layout
				)
			);
			this.setState( { layout } );
			this.props.saveLayout(
				this.props.dashboardId,
				transformGridLayoutToApi( this.props.dashboardId, layout )
			);
		}
	}

	componentWillUnmount() {
		this.onLayoutChange.cancel();
	}

	_onLayoutChange = ( _, newLayout ) => {
		if ( ! areGridLayoutsEqual( newLayout, this.state.layout ) ) {
			this.setState( { layout: newLayout } );
			const transformed = transformGridLayoutToApi(
				this.props.dashboardId,
				newLayout
			);
			this.props.saveLayout( this.props.dashboardId, transformed );
		}
	};

	onBreakpointChange = ( newBreakpoint ) => {
		this.setState( { breakpoint: newBreakpoint } );
	};

	onStartMove = () => {
		this.setState( { isMoving: true } );
	};

	onStopMove = () => {
		this.setState( { isMoving: false } );
	};

	render() {
		const { cards, dashboardId, usingTouch } = this.props;
		const maxWidth = getMaxWidthForGrid( this.props.width );

		if ( ! cards.length ) {
			return <EmptyState maxWidth={ maxWidth } />;
		}

		return (
			<Grid
				style={ { maxWidth } }
				breakpoints={ BREAKPOINTS }
				onBreakpointChange={ this.onBreakpointChange }
				cols={ GRID_COLUMNS }
				rowHeight={ 380 }
				width={ Math.min( this.props.width, maxWidth ) }
				layouts={ this.state.layout }
				onLayoutChange={ this.onLayoutChange }
				margin={ [ CARD_MARGIN, CARD_MARGIN ] }
				isDraggable={ ! usingTouch }
				isResizable={ ! usingTouch }
				className={
					this.state.isMoving ? 'itsec-card-grid--moving' : ''
				}
				draggableHandle=".itsec-card-header, .itsec-card--unknown, .itsec-card__drag-handle"
				onDragStart={ this.onStartMove }
				onDragStop={ this.onStopMove }
				onResizeStart={ this.onStartMove }
				onResizeStop={ this.onStopMove }
			>
				{ sortCardsToMatchLayout(
					cards,
					this.state.layout,
					this.state.breakpoint
				).map( ( card ) => (
					<Card
						id={ card.id }
						dashboardId={ dashboardId }
						key={ card.id.toString() }
						gridWidth={ this.props.width }
					/>
				) ) }
			</Grid>
		);
	}
}

export default compose( [
	withSelect( ( select, props ) => ( {
		cards: select( 'ithemes-security/dashboard' ).getDashboardCards(
			props.dashboardId
		),
		layout: select( 'ithemes-security/dashboard' ).getDashboardLayout(
			props.dashboardId
		),
		usingTouch: select( 'ithemes-security/dashboard' ).isUsingTouch(),
		cardsLoaded: select( 'ithemes-security/dashboard' ).areCardsLoaded(
			props.dashboardId
		),
		layoutLoaded: select( 'ithemes-security/dashboard' ).isLayoutLoaded(
			props.dashboardId
		),
	} ) ),
	ifCondition(
		( { cardsLoaded, layoutLoaded } ) => cardsLoaded && layoutLoaded
	),
	pure,
	withDispatch( ( dispatch, props ) => ( {
		openEditCards: dispatch( 'ithemes-security/dashboard' ).openEditCards,
		saveLayout: dispatch( 'ithemes-security/dashboard' )
			.saveDashboardLayout,
		refresh() {
			dispatch( 'ithemes-security/dashboard' ).refreshDashboardCards(
				props.dashboardId
			);
		},
	} ) ),
	withInterval( 120 * 1000, ( { refresh } ) => refresh() ),
] )( CardGrid );
