/**
 * WordPress dependencies
 */
import { Popover } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { megaphone as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Panel from '../panel';
import { StyledTrigger } from './styles';
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
			<StyledTrigger
				aria-expanded={ isToggled }
				onClick={ () => ! isToggled && setIsToggled( true ) }
				icon={ icon }
				text={ __( 'Alerts', 'better-wp-security' ) }
				noticesCount={ notices.length }
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
					/>
				</Popover>
			) }
		</>
	);
}
