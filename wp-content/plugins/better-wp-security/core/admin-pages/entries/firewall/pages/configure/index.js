/**
 * External dependencies
 */
import { NavLink, useParams } from 'react-router-dom';
import { ErrorBoundary } from 'react-error-boundary';
import { sortBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex, FlexBlock } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import {
	Button,
	PageHeader,
	SecondaryNavigation,
	SecondaryNavigationItem,
	Surface,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	ErrorRenderer,
	ModuleCard,
	useSettingsForm,
	ModuleFormInputs,
} from '@ithemes/security.pages.settings';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { withNavigate } from '@ithemes/security-hocs';
import { Page } from '../../components';
import {
	StyledButtonsContainer,
	StyledGlobalSettingsContainer, StyledModulePanelContainer,
} from './styles';

export default function Configure() {
	const { modules } = useSelect( ( select ) => ( {
		modules: select( MODULES_STORE_NAME ).getModules(),
	} ), [] );

	const firewallModules = sortBy( modules.filter( ( maybeModule ) => maybeModule.type === 'lockout' ), 'order' );

	return (
		<Page>
			<Flex gap={ 5 } align="start" >
				<SecondaryNavigation
					orientation="vertical"
				>
					<NavLink
						key="global"
						to="/configure/global"
						component={ withNavigate( SecondaryNavigationItem ) }
					>
						{ __( 'Global Settings', 'better-wp-security' ) }
					</NavLink>
					{ firewallModules.map( ( firewallModule ) => (
						<NavLink
							key={ firewallModule.id }
							to={ '/configure/' + firewallModule.id }
							component={ withNavigate( SecondaryNavigationItem ) }
						>
							{ firewallModule.title }
						</NavLink>
					) ) }
				</SecondaryNavigation>
				<ModuleSettings />
			</Flex>
		</Page>
	);
}

function ModuleSettings() {
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
						{ module.id === 'global'
							? <FirewallGlobalSettingsCard module={ module } />
							: <FirewallModuleSettingsCard module={ module } />
						}
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

const allowedFields = [
	'user_lockout_message',
	'lockout_period',
	'blacklist_period',
	'blacklist',
	'blacklist_count',
	'lockout_message',
	'user_lockout_message',
	'community_lockout_message',
	'automatic_temp_auth',
	'lockout_white_list',
];

function FirewallModuleSettingsCard( { module } ) {
	return (
		<>
			<PageHeader
				title={ module.title }
				description={ module.description }
				fullWidth
				hasBorder
			/>
			<StyledModulePanelContainer>
				<ModuleCard module={ module } persistStatus includeTitle />
			</StyledModulePanelContainer>
		</>
	);
}

function FirewallGlobalSettingsCard( { module } ) {
	const _filterFields = useCallback(
		( value, key ) => allowedFields.includes( key ),
		[]
	);

	const {
		schema,
		uiSchema,
		formData,
		setFormData,
	} = useSettingsForm( module, _filterFields );

	return (
		<Surface>
			<PageHeader
				title={ module.title }
				description={ module.description }
				fullWidth
				hasBorder
			/>
			<StyledGlobalSettingsContainer>
				<ModuleFormInputs
					module={ module }
					schema={ schema }
					uiSchema={ uiSchema }
					formData={ formData }
					setFormData={ setFormData }
				/>
			</StyledGlobalSettingsContainer>
		</Surface>
	);
}
