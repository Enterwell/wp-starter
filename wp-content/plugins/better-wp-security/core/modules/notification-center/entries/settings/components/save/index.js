/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Solid dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';

const StyledSave = styled( Flex )`
	margin-top: 2rem;
`;

export default function Save( { setErrors } ) {
	const { isDirty, isSaving } = useSelect(
		( select ) => ( {
			isDirty: select( MODULES_STORE_NAME ).areSettingsDirty(
				'notification-center'
			),
			isSaving: select( MODULES_STORE_NAME ).isSavingSettings(
				'notification-center'
			),
		} ),
		[]
	);
	const { saveSettings, resetSettingEdits, validateSettings } = useDispatch( MODULES_STORE_NAME );

	const maybeSubmit = async () => {
		const isValid = await validateSettings( 'notification-center' );

		if ( isValid === true ) {
			setErrors?.( [] );
			saveSettings( 'notification-center' );
		} else {
			setErrors?.( isValid.errorText );
		}
	};

	return (
		<StyledSave justify="end">
			<FlexItem>
				<Button
					onClick={ () =>
						resetSettingEdits( 'notification-center' )
					}
					disabled={ ! isDirty }
					text={ __( 'Undo Changes', 'better-wp-security' ) }
				/>
			</FlexItem>

			<FlexItem>
				<Button
					onClick={ maybeSubmit }
					disabled={ ! isDirty }
					isBusy={ isSaving }
					variant="primary"
					text={ __( 'Save All Changes', 'better-wp-security' ) }
				/>
			</FlexItem>
		</StyledSave>
	);
}
