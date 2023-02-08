/**
 * External dependencies
 */
import { isEmpty, size, map } from 'lodash';

/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { autop } from '@wordpress/autop';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Notice Component.
 *
 * @param {string|number} noticeId
 * @param {Object} notice
 * @param {string} notice.severity
 * @param {string} notice.title
 * @param {string} notice.message
 * @param {Object} notice.meta
 * @param {Array.<{title: string, style: string, uri: string}>} notice.actions
 * @return {Component} Notice component.
 */
export default function Notice( { notice } ) {
	return (
		<article className={ `itsec-admin-notice itsec-admin-notice--severity-${ notice.severity }` }>
			<header className="itsec-admin-notice__header">
				<div className="itsec-admin-notice__header-inset">
					<h4 dangerouslySetInnerHTML={ { __html: notice.title || formatMessage( notice.message, notice ) } } />
					{ map( notice.actions, ( action, slug ) => ( action.style === 'primary' && (
						<Button key={ slug } href={ action.uri }>{ action.title }</Button>
					) ) ) }
				</div>
			</header>

			{ notice.title && notice.message && (
				<section className="itsec-admin-notice__message" dangerouslySetInnerHTML={ { __html: autop( formatMessage( notice.message, notice ) ) } } />
			) }

			{ hasMeta( notice ) && (
				<dl className="itsec-admin-notice__meta">
					{ map( notice.meta, ( meta, key ) => (
						key !== 'created_at' && (
							<Fragment key={ key }>
								<dt>{ meta.label }</dt>
								<dd>{ meta.formatted }</dd>
							</Fragment>
						)
					) ) }
				</dl>
			) }

			{ notice.meta.created_at && (
				<footer className="itsec-admin-notice__footer">
					<time dateTime={ notice.meta.created_at.value }>
						{ notice.meta.created_at.formatted }
					</time>
				</footer>
			) }
		</article>
	);
}

function hasMeta( notice ) {
	if ( isEmpty( notice.meta ) ) {
		return false;
	}

	if ( size( notice.meta ) === 1 && notice.meta.hasOwnProperty( 'created_at' ) ) {
		return false;
	}

	return true;
}

function formatMessage( message, notice ) {
	for ( const action in notice.actions ) {
		if ( ! notice.actions.hasOwnProperty( action ) ) {
			continue;
		}

		if ( notice.actions[ action ].uri === '' ) {
			continue;
		}

		message = message.replace( '{{ $' + action + ' }}', notice.actions[ action ].uri );
	}

	return message;
}
