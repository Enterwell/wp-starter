/**
 * External dependencies
 */
import classnames from 'classnames';
import { find, noop, partial } from 'lodash';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { NavigableMenu } from '@wordpress/components';
import { withInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import TabButton from './tab-button';

class ControlledTabPanel extends Component {
	constructor() {
		super( ...arguments );

		this.handleClick = this.handleClick.bind( this );
		this.onNavigate = this.onNavigate.bind( this );
		this.onKeyDown = this.onKeyDown.bind( this );
	}

	handleClick( tabKey ) {
		const { onSelect = noop } = this.props;
		onSelect( tabKey );
	}

	onNavigate( childIndex, child ) {
		const event = this.event;

		if ( event && event.target.getAttribute( 'role' ) === 'tab' ) {
			event.preventDefault();
		}

		child.click();
	}

	onKeyDown( event ) {
		// Stores the event for use in onNavigate. We don't need to persist the event
		// since onNavigate is called during the original onKeyDown event handler.
		this.event = event;
	}

	render() {
		const {
			activeClass = 'is-active',
			className,
			instanceId,
			orientation = 'horizontal',
			tabs,
			selected,
		} = this.props;

		const selectedTab = find( tabs, { name: selected } ) || tabs[ 0 ];
		const selectedId = instanceId + '-' + selectedTab.name;

		return (
			<div className={ className }>
				<NavigableMenu
					role="tablist"
					orientation={ orientation }
					onNavigate={ this.onNavigate }
					onKeyDown={ this.onKeyDown }
					className="components-tab-panel__tabs"
				>
					{ tabs.map( ( tab ) => (
						<TabButton className={ classnames( tab.className, { [ activeClass ]: tab.name === selectedTab.name } ) }
							tabId={ instanceId + '-' + tab.name }
							aria-controls={ instanceId + '-' + tab.name + '-view' }
							selected={ tab.name === selectedTab.name }
							key={ tab.name }
							onClick={ partial( this.handleClick, tab.name ) }
						>
							{ tab.title }
						</TabButton>
					) ) }
				</NavigableMenu>
				{ selectedTab && (
					<div aria-labelledby={ selectedId }
						role="tabpanel"
						id={ selectedId + '-view' }
						className="components-tab-panel__tab-content"
						tabIndex="0"
					>
						{ this.props.children( selectedTab ) }
					</div>
				) }
			</div>
		);
	}
}

export default withInstanceId( ControlledTabPanel );
