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
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import './style.scss';

export default function Notice( { notice } ) {
	return (
		<article
			className={ `itsec-admin-notice itsec-admin-notice--severity-${ notice.severity }` }
		>
			<header className="itsec-admin-notice__header">
				<div className="itsec-admin-notice__header-inset">
					<h4
						dangerouslySetInnerHTML={ {
							__html:
								notice.title ||
								formatMessage( notice.message, notice ),
						} }
					/>
					{ map(
						notice.actions,
						( action, slug ) =>
							action.style === 'primary' && (
								<PrimaryAction key={ slug } notice={ notice } action={ action } />
							)
					) }
				</div>
			</header>

			{ notice.title && notice.message && (
				<section
					className="itsec-admin-notice__message"
					dangerouslySetInnerHTML={ {
						__html: autop(
							formatMessage( notice.message, notice )
						),
					} }
				/>
			) }

			{ hasMeta( notice ) && (
				<dl className="itsec-admin-notice__meta">
					{ map(
						notice.meta,
						( meta, key ) =>
							key !== 'created_at' && (
								<Fragment key={ key }>
									<dt>{ meta.label }</dt>
									<dd>{ meta.formatted }</dd>
								</Fragment>
							)
					) }
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

function PrimaryAction( { notice, action } ) {
	const isInProgress = useSelect( ( select ) =>
		select( 'ithemes-security/admin-notices' )
			.getInProgressActions( notice.id )
			.includes( action.id ),
	[ notice.id, action.id ]
	);
	const { doNoticeAction } = useDispatch( 'ithemes-security/admin-notices' );
	// Intentionally uses string-based API because we only want to refresh modules if they are in use.
	const { fetchModules } = useDispatch( 'ithemes-security/modules' ) || {};

	const onClick = async ( e ) => {
		if ( ! action.uri ) {
			e.preventDefault();
			await doNoticeAction( notice.id, action.id );
			fetchModules?.();
		}
	};

	// We don't want to cause a dependency on the settings page entry.
	if ( action.route && window.itsec?.pages?.settings?.history ) {
		return <PrimaryRouteAction route={ action.route } title={ action.title } history={ window.itsec?.pages?.settings?.history } />;
	}

	return (
		<Button href={ action.uri } onClick={ onClick } isBusy={ isInProgress }>
			{ action.title }
		</Button>
	);
}

function PrimaryRouteAction( { title, route, history } ) {
	const onClick = () => history.push( route );

	return (
		<Button onClick={ onClick } href={ history.createHref( route ) }>
			{ title }
		</Button>
	);
}

function hasMeta( notice ) {
	if ( isEmpty( notice.meta ) ) {
		return false;
	}

	if (
		size( notice.meta ) === 1 &&
		notice.meta.hasOwnProperty( 'created_at' )
	) {
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

		message = message.replace(
			'{{ $' + action + ' }}',
			notice.actions[ action ].uri
		);
	}

	return message;
}
