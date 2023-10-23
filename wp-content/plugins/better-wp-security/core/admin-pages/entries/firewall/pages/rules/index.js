/**
 * Solid dependencies
 */
import { Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Page, RulesTable, RulesTableHeader, RulesTablePagination } from '../../components';

export default function Rules() {
	return (
		<Page>
			<Surface>
				<RulesTableHeader />
				<RulesTable />
			</Surface>
			<RulesTablePagination />
		</Page>
	);
}
