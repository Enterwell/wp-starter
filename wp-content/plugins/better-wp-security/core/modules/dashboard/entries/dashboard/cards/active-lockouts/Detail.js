/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useCallback } from '@wordpress/element';
import { dateI18n } from '@wordpress/date';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { useAsync } from '@ithemes/security-hocs';

function Detail( { master = {}, isVisible } ) {
	const fetchDetails = useCallback( () => {
		if ( ! master.links.item ) {
			return Promise.reject( new Error( 'No data available.' ) );
		}

		const url = master.links.item[ 0 ].href.replace(
			'{lockout_id}',
			master.id
		);

		return apiFetch( { url } ).then( ( response ) => {
			return response.detail;
		} );
	}, [ master.id, master.links.item ] );

	const { value: details } = useAsync( fetchDetails, isVisible );

	return (
		<div className="itsec-card-active-lockouts__detail-container">
			<time
				className="itsec-card-active-lockouts__start-time"
				dateTime={ master.start_gmt }
			>
				{ sprintf(
					/* translators: 1. Relative time from human_time_diff(). */
					__( '%s ago', 'better-wp-security' ),
					master.start_gmt_relative
				) }
			</time>
			<h3 className="itsec-card-active-lockouts__label">
				{ master.label }
			</h3>
			<p className="itsec-card-active-lockouts__description">
				{ master.description }
			</p>

			{ details && details.history.length > 0 && (
				<History history={ details.history } />
			) }
		</div>
	);
}

function History( { history } ) {
	return (
		<>
			<hr />

			<div className="itsec-card-active-lockouts__history">
				<h4 className="itsec-card-active-lockouts__history-title">
					{ __( 'History', 'better-wp-security' ) }
				</h4>
				<ul>
					{ history.map( ( detail ) =>
						<HistoryItem
							key={ detail.id }
							history={ detail }
						/>
					) }
				</ul>
			</div>
		</>
	);
}

function HistoryItem( { history } ) {
	if ( ! history.label ) {
		return;
	}

	const time = (
		<time
			dateTime={ history.time }
			title={ dateI18n( 'M d, Y g:s A', history.time ) }
		>
			{ sprintf(
				/* translators: 1. Relative time from human_time_diff(). */
				__( '%s ago', 'better-wp-security' ),
				history.time_relative
			) }
		</time>
	);

	return (
		<li key={ history.id }>
			<code>{ history.label }</code>
			{ ' – ' }
			{ time }
		</li>
	);
}

export default Detail;
