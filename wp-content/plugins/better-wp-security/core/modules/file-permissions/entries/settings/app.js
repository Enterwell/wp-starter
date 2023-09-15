/**
 * External dependencies
 */
import { map } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { TOOLS_STORE_NAME, ToolFill } from '@ithemes/security.pages.settings';
import './style.scss';

export default function App() {
	return (
		<>
			<ToolFill tool="check-file-permissions">
				<FilePermissions />
			</ToolFill>
		</>
	);
}

function FilePermissions() {
	const result = useSelect( ( select ) =>
		select( TOOLS_STORE_NAME ).getLastResult( 'check-file-permissions' )
	);

	if ( ! result || ! result.isSuccess() ) {
		return null;
	}

	const header = (
		<tr>
			<th>{ __( 'Relative Path', 'better-wp-security' ) }</th>
			<th>{ __( 'Suggestion', 'better-wp-security' ) }</th>
			<th>{ __( 'Value', 'better-wp-security' ) }</th>
			<th>{ __( 'Result', 'better-wp-security' ) }</th>
			<th>{ __( 'Status', 'better-wp-security' ) }</th>
		</tr>
	);

	return (
		<div className="itsec-check-file-permissions-results">
			<table className="widefat striped">
				<thead>{ header }</thead>
				<tbody>
					{ map( result.data, ( row, path ) => (
						<tr key={ path }>
							<th>{ row.path }</th>
							<td>{ row.suggested }</td>
							<td>{ row.actual }</td>
							<td>
								{ row.actual === row.suggested
									? __( 'Ok', 'better-wp-security' )
									: __( 'Warning', 'better-wp-security' ) }
							</td>
							<td
								aria-hidden
								className={ `itsec-check-file-permissions-status itsec-check-file-permissions-status--${
									row.actual === row.suggested
										? 'ok'
										: 'warning'
								}` }
							>
								<div />
							</td>
						</tr>
					) ) }
				</tbody>
				<tfoot>{ header }</tfoot>
			</table>
		</div>
	);
}
