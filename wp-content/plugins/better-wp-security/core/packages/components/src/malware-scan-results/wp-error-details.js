/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import WrappedSection from './wrapped-section';
import { castWPError } from '@ithemes/security-utils';

function WPErrorDetails( { results, showErrorDetails = false } ) {
	const wpError = castWPError( results );

	return (
		<WrappedSection status="error" description={ __( 'The scan failed to properly scan the site.', 'better-wp-security' ) }>
			<p>{ sprintf( __( 'Error Message: %s', 'better-wp-security' ), wpError.getErrorMessage() ) }</p>
			<p>{ sprintf( __( 'Error Code: %s', 'better-wp-security' ), wpError.getErrorCode() ) }</p>

			{ showErrorDetails && wpError.getErrorData() && (
				<Fragment>
					<p>{ __( 'If you contact support about this error, please provide the following debug details:', 'better-wp-security' ) }</p>
					<pre>
						{ JSON.stringify( { code: wpError.getErrorCode(), data: wpError.getErrorData() }, null, 2 ) }
					</pre>
				</Fragment>
			) }
		</WrappedSection>
	);
}

export default WPErrorDetails;
