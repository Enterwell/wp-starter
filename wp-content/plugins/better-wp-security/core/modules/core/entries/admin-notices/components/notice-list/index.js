/**
 * Internal dependencies
 */
import Notice from '../notice';
import NoticeActions from '../notice-actions';
import './style.scss';

function NoticeList( { notices } ) {
	return (
		<ul className="itsec-admin-notice-list">
			{ notices.map( ( notice ) => (
				<li className="itsec-admin-notice-list-item-container" key={ notice.id }>
					<NoticeActions notice={ notice } />
					<Notice notice={ notice } noticeId={ notice.id } />
				</li>
			) ) }
		</ul>
	);
}

export default NoticeList;
