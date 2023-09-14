/**
 * External dependencies
 */
import { Link } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { Markup } from '@ithemes/security-components';
import { IconPopover } from '../';
import './style.scss';

export default function HelpPopover( { help, to } ) {
	return (
		<IconPopover
			icon="info"
			className="itsec-help-popover"
			label={ __( 'Help', 'better-wp-security' ) }
		>
			<Markup content={ help } tagName="p" />
			{ to && (
				<footer>
					<Link
						component={ withNavigate( Button ) }
						text={ __( 'More', 'better-wp-security' ) }
						to={ to }
						icon="arrow-right-alt"
						iconPosition="right"
					/>
				</footer>
			) }
		</IconPopover>
	);
}
