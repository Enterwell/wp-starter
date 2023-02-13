/**
 * External dependencies
 */
import { filter } from 'lodash';

/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { NoticeList, SnackbarList } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

function CompositeNoticeList( { notices, onRemove } ) {
	const dismissibleNotices = filter( notices, ( notice ) => notice.isDismissible && ( ! notice.type || notice.type === 'default' ) );
	const nonDismissibleNotices = filter( notices, ( notice ) => ! notice.isDismissible && ( ! notice.type || notice.type === 'default' ) );
	const snackbarNotices = SnackbarList ? filter( notices, {
		type: 'snackbar',
	} ) : [];

	return (
		<Fragment>
			<NoticeList
				notices={ nonDismissibleNotices }
				className="components-editor-notices__pinned" />
			<NoticeList
				notices={ dismissibleNotices }
				className="components-editor-notices__dismissible"
				onRemove={ onRemove } />
			{ SnackbarList && <SnackbarList
				notices={ snackbarNotices }
				className="components-editor-notices__snackbar"
				onRemove={ onRemove } />
			}
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
] )( CompositeNoticeList );
