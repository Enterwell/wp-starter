/**
 * External dependencies
 */
import { useLocation, Link } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { createSlotFill, ToolbarButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { ToolbarFill } from '../toolbar';
import './style.scss';

export default function Help() {
	const [ hasHelp, setHasHelp ] = useState( false );
	const location = useLocation();
	const isVisible = location.hash === '#help';
	const to = { ...location, hash: isVisible ? '' : '#help' };

	return (
		<>
			<ToolbarFill>
				<Link
					component={ withNavigate( ToolbarButton ) }
					icon="editor-help"
					to={ to }
					disabled={ ! hasHelp }
					className="itsec-help-toggle-link"
					text={
						isVisible
							? __( 'Exit Help', 'better-wp-security' )
							: __( 'Help', 'better-wp-security' )
					}
				/>
			</ToolbarFill>
			<HelpSlot>
				{ ( fills ) => (
					<HelpContent
						fills={ fills }
						isVisible={ isVisible }
						setHasHelp={ setHasHelp }
					/>
				) }
			</HelpSlot>
		</>
	);
}

function HelpContent( { fills, isVisible, setHasHelp } ) {
	useEffect( () => setHasHelp( fills.length > 0 ), [ fills ] );

	if ( ! isVisible ) {
		return null;
	}

	return fills;
}

const { Slot: HelpSlot, Fill: HelpFill } = createSlotFill( 'Help' );

export { HelpFill };
