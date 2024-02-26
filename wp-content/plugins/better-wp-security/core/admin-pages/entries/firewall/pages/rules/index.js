/**
 * External dependencies
 */
import { Link } from 'react-router-dom';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { plus as newIcon } from '@wordpress/icons';
import { Flex } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { Surface, Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { Page, RulesTable, RulesTableHeader, RulesTablePagination } from '../../components';

const StyledLink = styled( Link )`
	padding-right: 14px !important;
`;

export default function Rules() {
	return (
		<Page>
			<Flex justify="end">
				<StyledLink
					to="/rules/new"
					component={ withNavigate( Button ) }
					variant="primary"
					icon={ newIcon }
					text={ __( 'Create Rule', 'better-wp-security' ) }
				/>
			</Flex>
			<Surface>
				<RulesTableHeader />
				<RulesTable />
			</Surface>
			<RulesTablePagination />
		</Page>
	);
}
