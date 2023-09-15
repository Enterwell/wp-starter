/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { ToolbarButton, Popover } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import Panel from '../panel';
import './style.scss';

export default function() {
	const [ isToggled, setIsToggled ] = useState( false );
	const { notices, noticesLoaded } = useSelect(
		( select ) => ( {
			notices: select( 'ithemes-security/admin-notices' ).getNotices(),
			noticesLoaded: select(
				'ithemes-security/admin-notices'
			).areNoticesLoaded(),
		} ),
		[]
	);

	return (
		<>
			<ToolbarButton
				aria-expanded={ isToggled }
				onClick={ () => setIsToggled( ! isToggled ) }
				icon="bell"
				text={ __( 'Alerts', 'better-wp-security' ) }
				className={ classnames(
					'itsec-admin-bar-admin-notices__trigger',
					{
						'itsec-admin-bar-admin-notices__trigger--has-notices':
							notices.length > 0,
					}
				) }
			/>
			{ isToggled && (
				<Popover
					className="itsec-admin-bar-admin-notices__content"
					expandOnMobile
					focusOnMount="container"
					position="bottom left"
					headerTitle={ __( 'Notifications', 'better-wp-security' ) }
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
		</>
	);
}
