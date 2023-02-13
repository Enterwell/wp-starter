/**
 * External dependencies
 */
import classnames from 'classnames';
import { get, size } from 'lodash';
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { IconButton, FormToggle } from '@wordpress/components';
import { compose, withState } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import NoticeList from '../notice-list';
import './style.scss';

function getAvailableHighlights() {
	return [
		{
			slug: 'file-change-report',
			label: __( 'File Change Report', 'better-wp-security' ),
		},
		{
			slug: 'notification-center-send-failed',
			label: __( 'Notification Center Errors', 'better-wp-security' ),
		},
		{
			slug: 'malware-scan-report',
			label: __( 'Malware Scan Report', 'better-wp-security' ),
		},
		{
			slug: 'malware-scan-failed',
			label: __( 'Malware Scan Failed', 'better-wp-security' ),
		},
		{
			slug: 'site-scanner-report',
			label: __( 'Site Scan Report', 'better-wp-security' ),
		},
	];
}

function Panel( { notices, loaded, mutedHighlights, mutedHighlightUpdatesInFlight, updateMutedHighlight, isConfiguring, setState } ) {
	return (
		<div className={ classnames( 'itsec-admin-notice-panel', {
			'itsec-admin-notice-panel--is-configuring': isConfiguring,
		} ) }>
			<IconButton icon="admin-generic" label={ __( 'Configure', 'better-wp-security' ) }
				className="itsec-admin-notice-panel__configure-trigger"
				style={ { opacity: size( mutedHighlights ) > 0 ? 1 : 0 } }
				onClick={ () => setState( { isConfiguring: ! isConfiguring } ) } />
			<header className="itsec-admin-notice-panel__header">
				<h3>{ __( 'Security Admin Messages', 'better-wp-security' ) }</h3>
				<p>{ __( 'Important notices from iThemes Security', 'better-wp-security' ) }</p>
			</header>
			{ isConfiguring && (
				<ul className="itsec-admin-notice-panel__configure-highlighted-logs">
					{ getAvailableHighlights().map( ( { slug, label } ) => (
						mutedHighlights[ slug ] !== undefined && (
							<li>
								<label htmlFor={ `itsec-mute-highlight-${ slug }` }>{ label }</label>
								<FormToggle id={ `itsec-mute-highlight-${ slug }` }
									disabled={ ! loaded || mutedHighlightUpdatesInFlight[ slug ] }
									checked={ ! get( mutedHighlightUpdatesInFlight, [ slug, 'mute' ], mutedHighlights[ slug ] ) }
									onChange={ () => updateMutedHighlight( slug, ! mutedHighlights[ slug ] ) }
								/>
							</li>
						)
					) ) }
				</ul>
			) }
			{ notices.length > 0 ?
				<NoticeList notices={ notices } /> :
				loaded && <span>{ __( 'No notices at the moment.', 'better-wp-security' ) }</span>
			}
		</div>
	);
}

export default compose( [
	withState( { isConfiguring: false, checked: {} } ),
	withSelect( ( select ) => ( {
		mutedHighlights: select( 'ithemes-security/admin-notices' ).getMutedHighlights(),
		mutedHighlightUpdatesInFlight: select( 'ithemes-security/admin-notices' ).getMutedHighlightUpdatesInFlight(),
	} ) ),
	withDispatch( ( dispatch ) => ( {
		updateMutedHighlight: dispatch( 'ithemes-security/admin-notices' ).updateMutedHighlight,
	} ) ),
] )( Panel );
