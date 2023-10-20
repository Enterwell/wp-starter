/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { help as helpIcon } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { Button, Text, TextSize } from '@ithemes/ui';

const StyledSettingField = styled.div`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

const StyledSettingFieldHeader = styled.div`
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 1.25rem 1rem;
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledSettingFieldDescription = styled.div`
	padding: 1rem;
`;

export default function SettingField( { definition, children } ) {
	return (
		<StyledSettingField>
			<StyledSettingFieldHeader>
				{ children }
				<Button
					href="https://go.solidwp.com/security-basic-help-docs"
					target="_blank"
					label={ __( 'View external documentation', 'better-wp-security' ) }
					variant="tertiary"
					icon={ helpIcon }
					isSmall
				/>
			</StyledSettingFieldHeader>
			<StyledSettingFieldDescription>
				<Text text={ definition.description } size={ TextSize.SMALL } />
			</StyledSettingFieldDescription>
		</StyledSettingField>
	);
}
