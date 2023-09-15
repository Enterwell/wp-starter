/**
 * External dependencies
 */
import classnames from 'classnames';
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment, useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Details from './details';

export default function WrappedSection( {
	type,
	status,
	description,
	children,
} ) {
	const instanceId = useInstanceId( WrappedSection );
	const [ isShowing, setIsShowing ] = useState( false );

	let statusText;

	switch ( status ) {
		case 'clean':
			statusText = __( 'Clean', 'better-wp-security' );
			break;
		case 'warn':
			statusText = __( 'Warn', 'better-wp-security' );
			break;
		case 'error':
			statusText = __( 'Error', 'better-wp-security' );
			break;
		default:
			statusText = status;
			break;
	}

	const statusEl = (
		<span
			className={ `itsec-site-scan__status itsec-site-scan__status--${ status }` }
		>
			{ statusText }
		</span>
	);

	return (
		<div
			className={ classnames(
				'itsec-site-scan-results-section',
				`itsec-site-scan-results-${ type }-section`
			) }
		>
			{ isEmpty( children ) ? (
				<p>
					{ statusEl } { description }
				</p>
			) : (
				<Fragment>
					<p>
						{ statusEl }
						{ description }
						<Button
							variant="link"
							className="itsec-site-scan-toggle-details"
							onClick={ () =>
								setIsShowing( ! isShowing )
							}
							aria-expanded={ isShowing }
							aria-controls={ `itsec-site-scan__details--${ instanceId }` }
						>
							{ isShowing
								? __( 'Hide Details', 'better-wp-security' )
								: __( 'Show Details', 'better-wp-security' ) }
						</Button>
					</p>
					<Details
						id={ `itsec-site-scan__details--${ instanceId }` }
						isVisible={ isShowing }
					>
						{ children }
					</Details>
				</Fragment>
			) }
		</div>
	);
}
