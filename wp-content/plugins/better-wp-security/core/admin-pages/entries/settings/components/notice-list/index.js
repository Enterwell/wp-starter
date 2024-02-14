/**
 * External dependencies
 */
import { filter } from 'lodash';
import styled from '@emotion/styled';
import { useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { NoticeList, SnackbarList } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

const StyledSnackbarList = styled( SnackbarList )`
	top: ${ ( { root } ) => root === 'settings' ? '-70px' : '-10px' };

	.components-snackbar-list__notice-container {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
	}
`;

export default function CompositeNoticeList( { context = 'ithemes-security' } ) {
	const { notices } = useSelect( ( select ) => ( {
		notices: select( noticesStore ).getNotices( context ),
	} ), [ context ] );
	const { removeNotice } = useDispatch( noticesStore );
	const onRemove = ( noticeId ) => removeNotice( noticeId, context );

	const { root } = useParams();

	const dismissibleNotices = filter(
		notices,
		( notice ) =>
			notice.isDismissible &&
			( ! notice.type || notice.type === 'default' )
	);
	const nonDismissibleNotices = filter(
		notices,
		( notice ) =>
			! notice.isDismissible &&
			( ! notice.type || notice.type === 'default' )
	);
	const snackbarNotices = filter( notices, { type: 'snackbar' } );

	return (
		<>
			<NoticeList
				notices={ nonDismissibleNotices }
			/>
			<NoticeList
				notices={ dismissibleNotices }
				onRemove={ onRemove }
			/>
			<StyledSnackbarList
				root={ root }
				notices={ snackbarNotices }
				onRemove={ onRemove }
			/>
		</>
	);
}
