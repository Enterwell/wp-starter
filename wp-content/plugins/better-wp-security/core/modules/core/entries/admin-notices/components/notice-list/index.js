/**
 * iThemes dependencies
 */
import { List } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import Notice from '../notice';
import NoticeActions from '../notice-actions';
import { StyledNoticeItem } from './styles';

function NoticeList( { notices } ) {
	return (
		<List gap={ 4 }>
			{ notices.map( ( notice ) => (
				<StyledNoticeItem key={ notice.id }>
					<Notice notice={ notice } noticeId={ notice.id } />
					<NoticeActions notice={ notice } />
				</StyledNoticeItem>
			) ) }
		</List>
	);
}

export default NoticeList;
