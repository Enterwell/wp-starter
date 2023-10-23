/**
 * External dependencies
 */
import classnames from 'classnames';
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Popover, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * iThemes dependencies
 */
import { solidTheme } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import Panel from '../panel';
import './style.scss';

export default function Toolbar() {
	const [ isToggled, setIsToggled ] = useState( false );

	const { notices, noticesLoaded } = useSelect( ( select ) => ( {
		notices: select( 'ithemes-security/admin-notices' ).getNotices(),
		noticesLoaded: select( 'ithemes-security/admin-notices' ).areNoticesLoaded(),
	} ) );
	return (
		<ThemeProvider theme={ solidTheme }>
			<Button
				id="itsec-admin-notices-toolbar-trigger"
				className={ classnames( 'ab-item ab-empty-item', {
					'itsec-admin-notices-toolbar--has-notices':
						notices.length > 0,
				} ) }
				onClick={ () => setIsToggled( ! isToggled ) }
				aria-expanded={ isToggled }
			>
				<span className="it-icon-itsec" />
				<span className="itsec-toolbar-text">
					{ __( 'Security', 'better-wp-security' ) }
				</span>
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
					onClose={ () => setIsToggled( false ) }
					onFocusOutside={ () => setIsToggled( false ) }
				>
					<Panel
						notices={ notices }
						loaded={ noticesLoaded }
						close={ () => setIsToggled( false ) }
					/>
				</Popover>
			) }
		</ThemeProvider>
	);
}
