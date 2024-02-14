/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';
import { ErrorBoundary } from 'react-error-boundary';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { FlexBlock } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Button, Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { ErrorRenderer } from '@ithemes/security.pages.settings';
import FirewallGlobalSettingsCard from '../firewall-configure-global-settings-card';
import SettingsCard from '../settings-card';
import { StyledButtonsContainer } from './styles';

export default function ModuleSettings() {
	const { tab } = useParams();
	const { module, config, isDirty, isSaving } = useSelect( ( select ) => ( {
		module: select( MODULES_STORE_NAME ).getModule( tab ),
		config: select( MODULES_STORE_NAME ).getEditedModule( tab ),
		isDirty: select( MODULES_STORE_NAME ).areSettingsDirty( tab ),
		isSaving: select( MODULES_STORE_NAME ).isSavingSettings( tab ),
	} ), [ tab ] );
	const { saveSettings, resetSettingEdits } = useDispatch( MODULES_STORE_NAME );

	const onSave = ( e ) => {
		e.preventDefault();
		saveSettings( config.id, true );
	};
	return (
		<FlexBlock>
			<form onSubmit={ onSave }>
				<ErrorBoundary FallbackComponent={ ErrorRenderer }>
					<Surface variant="primary">
						<ModuleSettingsCards
							module={ module }
						/>
						<StyledButtonsContainer justify="end">
							<Button
								text={ __( 'Undo Changes', 'better-wp-security' ) }
								variant="secondary"
								onClick={ () => resetSettingEdits( module.id ) }
								disabled={ isSaving || ! isDirty }
								align="right"
							/>
							<Button
								type="submit"
								text={ __( 'Save Settings', 'better-wp-security' ) }
								variant="primary"
								isBusy={ isSaving }
								disabled={ isSaving || ! isDirty }
								align="right"
							/>
						</StyledButtonsContainer>
					</Surface>
				</ErrorBoundary>
			</form>
		</FlexBlock>
	);
}

function ModuleSettingsCards( { module } ) {
	if ( module.id === 'global' ) {
		return <FirewallGlobalSettingsCard module={ module } />;
	}

	return <SettingsCard module={ module } />;
}

