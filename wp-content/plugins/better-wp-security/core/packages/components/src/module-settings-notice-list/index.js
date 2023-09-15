/**
 * External dependencies
 */
import { filter, omit } from 'lodash';

/**
 * WordPress dependencies
 */
import { SnackbarList } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { Fragment, useEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Notice from './notice';
import './style.scss';

function usePrevious( value ) {
	const ref = useRef();
	useEffect( () => {
		ref.current = value;
	} );
	return ref.current;
}

function calculateLength( notices ) {
	if ( ! SnackbarList ) {
		return notices.length;
	}

	let length = 0;

	for ( const notice of notices ) {
		if ( notice.type !== 'snackbar' ) {
			length++;
		}
	}

	return length;
}

function ModuleSettingsNoticeList( { notices, onRemove } ) {
	const length = calculateLength( notices );
	const prevLength = usePrevious( length );
	useEffect( () => {
		if ( length > prevLength && window.itsecSettingsPage ) {
			window.itsecSettingsPage.scrollTop();
		}
	}, [ length, prevLength ] );

	const createRemoveNotice = ( id ) => () => onRemove( id );
	const snackbarNotices = SnackbarList
		? filter( notices, {
			type: 'snackbar',
		} )
		: [];

	return (
		<Fragment>
			<div className="itsec-module-settings-notice-list">
				{ notices.map( ( notice ) => {
					if ( notice.type === 'snackbar' && SnackbarList ) {
						return null;
					}

					return (
						<Notice
							{ ...omit( notice, [ 'content' ] ) }
							key={ notice.id }
							onRemove={ createRemoveNotice( notice.id ) }
						>
							{ notice.content }
						</Notice>
					);
				} ) }
			</div>
			{ SnackbarList && (
				<SnackbarList
					notices={ snackbarNotices }
					className="components-editor-notices__snackbar"
					onRemove={ onRemove }
				/>
			) }
		</Fragment>
	);
}

export default compose( [
	withSelect( ( select, { context = 'ithemes-security' } ) => ( {
		notices: select( 'core/notices' ).getNotices( context ),
	} ) ),
	withDispatch( ( dispatch, { context = 'ithemes-security' } ) => ( {
		onRemove( noticeId ) {
			return dispatch( 'core/notices' ).removeNotice( noticeId, context );
		},
	} ) ),
] )( ModuleSettingsNoticeList );
