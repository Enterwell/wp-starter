/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { Button, Popover } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

export default function IconPopover( { icon, className, label, children } ) {
	const [ isShowing, show ] = useState( false );

	return (
		<Button
			icon={ icon }
			label={ label }
			onClick={ () => show( ! isShowing ) }
			aria-expanded={ isShowing }
			aria-haspopup
			className={ classnames(
				'itsec-icon-popover__trigger',
				className && `${ className }__trigger`
			) }
		>
			{ isShowing && (
				<Popover
					noArrow={ false }
					position="bottom center"
					className={ classnames( 'itsec-icon-popover', className ) }
					focusOnMount="container"
					onFocusOutside={ () => show( false ) }
				>
					{ children }
				</Popover>
			) }
		</Button>
	);
}
