/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Popover, Button } from '@wordpress/components';
import { compose, withState } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { doesElementBelongToPanel } from '../../utils';
import Panel from '../panel';
import './style.scss';
import { withSelect } from '@wordpress/data';

function Toolbar( { notices, noticesLoaded, isToggled, setState } ) {
	return (
		<Fragment>
			<Button
				id="itsec-admin-notices-toolbar-trigger"
				className={ classnames( 'ab-item ab-empty-item', {
					'itsec-admin-notices-toolbar--has-notices': notices.length > 0,
				} ) }
				onClick={ () => setState( { isToggled: ! isToggled } ) }
				aria-expanded={ isToggled }>
				<span className="it-icon-itsec" />
				<span className="itsec-toolbar-text">{ __( 'Security', 'better-wp-security' ) }</span>
				{ notices.length > 0 && (
					<span className="itsec-admin-notices-toolbar-bubble">
						<span className="itsec-admin-notices-toolbar-bubble__count">
							{ notices.length }
						</span>
					</span>
				) }
			</Button>
			{ isToggled && (
				<Popover
					className="itsec-admin-notices-toolbar__popover"
					noArrow
					expandOnMobile
					focusOnMount="container"
					position="bottom center"
					headerTitle={ __( 'Security', 'better-wp-security' ) }
					onClose={ () => setState( { isToggled: false } ) }
					onClickOutside={ ( e ) => {
						if (
							! e.target || (
								e.target.id !== 'itsec-admin-notices-toolbar-trigger' &&
								e.target.parentNode.id !== 'itsec-admin-notices-toolbar-trigger' &&
								! doesElementBelongToPanel( e.target )
							)
						) {
							setState( { isToggled: false } );
						}
					} }
					onFocusOutside={ () => {
						const activeElement = document.activeElement;

						if (
							activeElement.id !== 'itsec-admin-notices-toolbar-trigger' &&
							( ! activeElement.parentNode || activeElement.parentNode.id !== 'itsec-admin-notices-toolbar-trigger' ) &&
							! doesElementBelongToPanel( activeElement )
						) {
							setState( { isToggled: false } );
						}
					} }
				>
					<Panel notices={ notices } loaded={ noticesLoaded } close={ () => setState( { isToggled: false } ) } />
				</Popover>
			) }
		</Fragment>
	);
}

export default compose( [
	withSelect( ( select ) => ( {
		notices: select( 'ithemes-security/admin-notices' ).getNotices(),
		noticesLoaded: select( 'ithemes-security/admin-notices' ).areNoticesLoaded(),
	} ) ),
	withState( { isToggled: false } ),
] )( Toolbar );
