/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import {
	ThreatsBlocked,
	LogsTable,
	Page,
	TopBlockedIPs,
	LogsTablePagination,
} from '../../components';
import {
	StyledColumnsContainer,
	StyledCardsContainer,
	StyledListsContainer,
} from './styles';

export default function Logs() {
	const [ period, setPeriod ] = useState( '30-days' );

	return (
		<Page>
			<StyledColumnsContainer>
				<StyledCardsContainer>
					<ThreatsBlocked period={ period } setPeriod={ setPeriod } />
					<LogsTable />
					<LogsTablePagination />
				</StyledCardsContainer>
				<StyledListsContainer>
					<TopBlockedIPs period={ period } />
				</StyledListsContainer>
			</StyledColumnsContainer>
		</Page>
	);
}
