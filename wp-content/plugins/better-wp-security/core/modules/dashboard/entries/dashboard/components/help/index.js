/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { HelpList } from '@ithemes/security-components';
import './style.scss';
import { useDispatch, useSelect } from '@wordpress/data';

export default function Help() {
	const { page } = useSelect( ( select ) => ( {
		page: select( 'ithemes-security/dashboard' ).getCurrentPage(),
	} ) );
	const { viewHelp, viewPrevious } = useDispatch(
		'ithemes-security/dashboard'
	);

	const onClick = () => {
		if ( page === 'help' ) {
			viewPrevious();
		} else {
			viewHelp();
		}
	};

	return (
		<div className="itsec-dashboard-help">
			<div className="itsec-help-header">
				<h1 className="itsec-help-header__title">
					{ __( 'Help', 'better-wp-security' ) }
				</h1>
				<Button
					onClick={ onClick }
					icon="arrow-left-alt"
					text={ __( 'Back', 'better-wp-security' ) }
					className="itsec-help-header__back-link"
				/>
			</div>
			<HelpList topic="dashboard" />
		</div>
	);
}
