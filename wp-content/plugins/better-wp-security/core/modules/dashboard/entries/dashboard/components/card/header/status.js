/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

export default function Status( { status = 'protected' } ) {
	switch ( status ) {
		case 'protected':
			status = __( 'Protected', 'better-wp-security' );
			break;
	}

	return (
		<span className="itsec-card-header-status">
			<span className="itsec-card-header-status__label">
				{ __( 'Status', 'better-wp-security' ) }
			</span>
			<span className="itsec-card-header-status__status">{ status }</span>
		</span>
	);
}
