/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { PageHeader } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { OnboardHeader } from '@ithemes/security.pages.settings';
import { Markup } from '@ithemes/security-ui';
import { PageHeaderFill } from '../';
import { StyledPageWrapper } from './styles';

export default function ImportPage( { module, children } ) {
	return (
		<StyledPageWrapper>
			<PageHeaderFill>
				<PageHeader
					title={ __( 'Imported User Groups', 'better-wp-security' ) }
					description={ __( 'Click any user group to edit its features or its members before importing.', 'better-wp-security' ) }
				/>
			</PageHeaderFill>
			<OnboardHeader
				title={ module.title }
				description={ <Markup content={ module.help } noWrap /> }
				showNext
				showIndicator
			/>
			{ children }
		</StyledPageWrapper>
	);
}
