/**
 * External dependencies
 */
import { Link, useParams } from 'react-router-dom';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useFocusOutside } from '@ithemes/security-hocs';
import { Logo } from '../';
import './style.scss';

export default function Sidebar( { className, logo = 'color', children } ) {
	const { root } = useParams();
	const [ expanded, setExpanded ] = useState( false );

	return (
		<div
			{ ...useFocusOutside( () => expanded && setExpanded( false ) ) }
			tabIndex={ -1 }
			className={ classnames(
				'itsec-settings-sidebar',
				className,
				`itsec-settings-sidebar--root-${ root }`,
				{
					'itsec-settings-sidebar--expanded': expanded,
				}
			) }
		>
			<div className="itsec-settings-sidebar__inner">
				<Link to="/" className="itsec-settings-sidebar__logo">
					<Logo style={ logo } />
				</Link>
				<Button
					icon="menu-alt2"
					label={ __( 'Toggle Sidebar', 'better-wp-security' ) }
					className="itsec-settings-sidebar__toggle"
					showTooltip={ false }
					isPressed={ expanded }
					onClick={ ( e ) => {
						e.currentTarget.focus();
						setExpanded( ! expanded );
					} }
					aria-expanded={ expanded }
				/>
				{ children }
			</div>
		</div>
	);
}
