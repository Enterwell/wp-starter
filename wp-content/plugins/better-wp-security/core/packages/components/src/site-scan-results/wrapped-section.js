/**
 * External dependencies
 */
import classnames from 'classnames';
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { compose, withState, withInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Details from './details';

function WrappedSection( { type, status, description, isShowing, setState, instanceId, children } ) {
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

	const statusEl = ( <span className={ `itsec-site-scan__status itsec-site-scan__status--${ status }` }>{ statusText }</span> );

	return (
		<div className={ classnames( 'itsec-site-scan-results-section', `itsec-site-scan-results-${ type }-section` ) }>
			{ isEmpty( children ) ? ( <p>{ statusEl } { description }</p> ) : (
				<Fragment>
					<p>
						{ statusEl }
						{ description }
						<Button isLink className="itsec-site-scan-toggle-details" onClick={ () => setState( { isShowing: ! isShowing } ) }
							aria-expanded={ isShowing } aria-controls={ `itsec-site-scan__details--${ instanceId }` }>
							{ isShowing ?
								__( 'Hide Details', 'better-wp-security' ) :
								__( 'Show Details', 'better-wp-security' )
							}
						</Button>
					</p>
					<Details id={ `itsec-site-scan__details--${ instanceId }` } isVisible={ isShowing }>
						{ children }
					</Details>
				</Fragment>
			) }
		</div>
	);
}

export default compose( [
	withState( { isShowing: false } ),
	withInstanceId,
] )( WrappedSection );
