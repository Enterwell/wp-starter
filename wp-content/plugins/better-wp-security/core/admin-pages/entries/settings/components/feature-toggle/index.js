/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Flex, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Badge, Surface } from '@ithemes/ui';

export default function FeatureToggle( {
	label,
	checked,
	onChange,
	recommended,
	children,
} ) {
	return (
		<StyledFeatureToggle>
			<Flex gap={ 1 } align="center" expanded={ false }>
				<ToggleControl
					label={ label }
					checked={ checked }
					onChange={ onChange }
					__nextHasNoMarginBottom
				/>
				{ children }
			</Flex>
			{ recommended && (
				<Badge text={ __( 'Recommended', 'better-wp-security' ) } variant="infoAccent" />
			) }
		</StyledFeatureToggle>
	);
}

const StyledFeatureToggle = styled( Surface )`
	display: flex;
	justify-content: space-between;
	align-items: center;
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	padding: 1rem;
	gap: 0.5rem;
`;
