/**
 * External dependencies
 */
import classnames from 'classnames';
import { partial, map, find, findIndex } from 'lodash';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { NavigableMenu } from '@wordpress/components';
import { withInstanceId, compose } from '@wordpress/compose';
import isShallowEqual from '@wordpress/is-shallow-equal';

/**
 * Internal dependencies
 */
import { withPressedModifierKeys } from '@ithemes/security-hocs';
import TabButton from './tab-button';

class ControlledMultiTabPanel extends Component {
	constructor() {
		super( ...arguments );

		this.handleClick = this.handleClick.bind( this );
		this.onNavigate = this.onNavigate.bind( this );
		this.onKeyDown = this.onKeyDown.bind( this );
		this.toggleTab = this.toggleTab.bind( this );
		this.getSelectedTabs = this.getSelectedTabs.bind( this );
		this.isSelected = this.isSelected.bind( this );
		this.getSelectedId = this.getSelectedId.bind( this );
		this.getLabelledBy = this.getLabelledBy.bind( this );
		this.getTabId = this.getTabId.bind( this );
		this.getTabPanelId = this.getTabPanelId.bind( this );
		this.isTabDisabled = this.isTabDisabled.bind( this );
		this.isNonMultiSelectableTabSelected = this.isNonMultiSelectableTabSelected.bind(
			this
		);
	}

	handleClick( tabKey, event ) {
		if ( event.metaKey || event.ctrlKey ) {
			this.toggleTab( tabKey );
		} else {
			this.props.onSelect( [ tabKey ] );
		}
	}

	onNavigate( childIndex, child ) {
		const event = this.event;

		if ( event ) {
			if ( event.target.getAttribute( 'role' ) === 'tab' ) {
				event.preventDefault();
			}

			if ( event.ctrlKey ) {
				return;
			}

			if ( event.shiftKey ) {
				if ( this.isTabDisabled( this.props.tabs[ childIndex ] ) ) {
					return;
				}

				const name = this.props.tabs[ childIndex ].name;
				this.toggleTab( name );

				return;
			}
		}

		child.click();
	}

	onKeyDown( event ) {
		// onKeyDown is not omitted from the NavigableContainer GB-19694
		if ( event.nativeEvent ) {
			return;
		}

		// Stores the event for use in onNavigate. We don't need to persist the event
		// since onNavigate is called during the original onKeyDown event handler.
		this.event = event;

		if (
			event.ctrlKey &&
			( event.code === 'Space' || event.keyCode === 32 )
		) {
			event.preventDefault();
			const tabName = event.target.dataset.tabname;

			if ( tabName ) {
				this.toggleTab( tabName );
			}
		}
	}

	toggleTab( name ) {
		const tab = find( this.props.tabs, { name } );

		if ( tab && tab.allowMultiple === false ) {
			return;
		}

		if ( this.props.selected.includes( name ) ) {
			this.props.onSelect(
				this.props.selected.filter(
					( maybeName ) => maybeName !== name
				)
			);
		} else {
			this.props.onSelect( [ ...this.props.selected, name ] );
		}
	}

	getSelectedTabs() {
		const selectedNames = this.props.selected;

		if ( ! selectedNames.length && this.props.initialTab ) {
			selectedNames.push( this.props.initialTab );
		}

		const tabs = [];

		this.props.tabs.forEach( ( tab ) => {
			if ( this.props.selected.includes( tab.name ) ) {
				tabs.push( tab );
			}
		} );

		return tabs;
	}

	isSelected( selectedTabs, maybeTab ) {
		return selectedTabs.some( ( tab ) => tab.name === maybeTab.name );
	}

	isTabDisabled( tab ) {
		const { pressedModifierKeys } = this.props;

		if ( this.props.selected.includes( tab.name ) ) {
			return false;
		}

		if (
			tab.allowMultiple !== false &&
			! this.isNonMultiSelectableTabSelected()
		) {
			return false;
		}

		if ( pressedModifierKeys.meta || pressedModifierKeys.ctrl ) {
			return true;
		}

		if ( pressedModifierKeys.shift ) {
			const { activeElement } = document;

			if (
				activeElement.parentElement &&
				activeElement.parentElement.id ===
					`components-tab-panel__tabs-${ this.props.instanceId }`
			) {
				return true;
			}
		}

		return false;
	}

	isNonMultiSelectableTabSelected() {
		if ( this.props.selected.length !== 1 ) {
			return false;
		}

		const selectedTab = find( this.props.tabs, {
			name: this.props.selected[ 0 ],
		} );

		return selectedTab && selectedTab.allowMultiple === false;
	}

	getSelectedId( selectedTabs ) {
		if ( selectedTabs.length === 1 ) {
			return this.getTabPanelId( selectedTabs[ 0 ].name );
		}

		return `components-tab-panel__panel-${ this.props.instanceId }-${ map(
			selectedTabs,
			'name'
		).join( '-' ) }`;
	}

	getLabelledBy( selectedTabs ) {
		return selectedTabs
			.map( ( tab ) => this.getTabId( tab.name ) )
			.join( ',' );
	}

	getTabId( tabName ) {
		return `components-tab-panel__tab-${ this.props.instanceId }-${ tabName }`;
	}

	getTabPanelId( tabName ) {
		return `components-tab-panel__panel-${ this.props.instanceId }-${ tabName }`;
	}

	componentDidUpdate( prevProps ) {
		if ( this.props.selected.length !== 1 ) {
			return;
		}

		if ( ! isShallowEqual( this.props.selected, prevProps.selected ) ) {
			return;
		}

		const selected = this.props.selected[ 0 ];

		if ( find( this.props.tabs, { name: selected } ) ) {
			return;
		}

		const removedIndex = findIndex( prevProps.tabs, { name: selected } );

		if ( removedIndex === -1 ) {
			return;
		}

		const prevIndex = Math.max( removedIndex - 1, 0 );
		const tab = this.props.tabs[ prevIndex ];

		if ( tab ) {
			this.props.onSelect( [ tab.name ] );
		}
	}

	render() {
		const {
			tabs,
			className,
			activeClass = 'is-active',
			orientation = 'horizontal',
		} = this.props;

		const selectedTabs = this.getSelectedTabs();
		const selectedId = this.getSelectedId( selectedTabs );

		return (
			<div className={ className }>
				<NavigableMenu
					role="tablist"
					aria-multiselectable
					orientation={ orientation }
					onNavigate={ this.onNavigate }
					onKeyDown={ this.onKeyDown }
					className="components-tab-panel__tabs"
					id={ `components-tab-panel__tabs-${ this.props.instanceId }` }
				>
					{ tabs.map( ( tab ) => {
						const isSelected = this.isSelected( selectedTabs, tab );
						const controls =
							isSelected && selectedTabs.length > 1
								? selectedId
								: this.getTabPanelId( tab.name );

						return (
							<TabButton
								className={ classnames( tab.className, {
									[ activeClass ]: isSelected,
								} ) }
								tabId={ this.getTabId( tab.name ) }
								aria-controls={ controls }
								selected={ isSelected }
								disabled={ this.isTabDisabled( tab ) }
								key={ tab.name }
								onClick={ partial(
									this.handleClick,
									tab.name
								) }
								data-tabname={ tab.name }
							>
								{ tab.title }
							</TabButton>
						);
					} ) }
				</NavigableMenu>
				{ selectedTabs.length > 0 && (
					<div
						aria-labelledby={ this.getLabelledBy( selectedTabs ) }
						role="tabpanel"
						id={ selectedId }
						className="components-tab-panel__tab-content"
						tabIndex="0"
					>
						{ this.props.children( selectedTabs ) }
					</div>
				) }
			</div>
		);
	}
}

export default compose( [ withInstanceId, withPressedModifierKeys ] )(
	ControlledMultiTabPanel
);
