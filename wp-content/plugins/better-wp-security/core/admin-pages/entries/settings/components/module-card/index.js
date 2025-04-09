/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { chevronDown as openedIcon, chevronUp as closedIcon, help as helpIcon } from '@wordpress/icons';
import { __, sprintf } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, SurfaceVariant, Text, TextSize } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { ErrorList, Markup } from '@ithemes/security-ui';
import { useAllowedSettingsFields, useModuleRequirementsValidator, useSettingsForm } from '../../utils';
import StatusToggleSettings from '../status-toggle-settings';
import ModuleFormInputs from '../module-form-inputs';
import {
	StyledModulePanel,
	StyledModulePanelBody,
	StyledModulePanelDescription,
	StyledModulePanelHeader,
	StyledModulePanelIcon,
	StyledModulePanelNoSettingsDescription,
	StyledModulePanelNotices,
	StyledModulePanelTitle,
	StyledModulePanelTrigger,
} from './styles';

export default function ModuleCard( { module, isHighlighted, highlightedSetting, persistStatus } ) {
	const [ isOpen, setIsOpen ] = useState( false );
	const isActive = module.status.selected === 'active';
	const validate = useModuleRequirementsValidator();
	const validated = validate( module, isActive ? 'run' : 'activate' );
	const { allowedFields, filterFields } = useAllowedSettingsFields( module );
	const {
		schema,
		uiSchema,
		hasSettings,
		formData,
		setFormData,
	} = useSettingsForm( module, filterFields );

	if ( ! isActive ) {
		if (
			validated.hasErrors() &&
			! validated.getErrorCodes().some( ( code ) => validated.getErrorData( code )[ 0 ].showMessageIfUnmet )
		) {
			return null;
		}
	}

	if ( module.status.default === 'always-active' && ! hasSettings ) {
		return null;
	}

	const canToggleStatus = module.status.default !== 'always-active' && ! validated.hasErrors();
	const showSettings = ( () => {
		if ( ! module.settings?.show_ui ) {
			return false;
		}

		if ( validated.hasErrors() ) {
			return false;
		}

		if ( Array.isArray( allowedFields ) && ! allowedFields.length ) {
			return false;
		}

		return true;
	} )();

	const isExpanded = isOpen || isHighlighted;

	return (
		<StyledModulePanel isHighlighted={ isHighlighted }>
			<StyledModulePanelHeader>
				{ canToggleStatus && (
					<StatusToggleSettings
						module={ module }
						setSettingsOpen={ setIsOpen }
						persist={ persistStatus }
					/>
				) }
				{ ! canToggleStatus && (
					<Text text={ module.title } />
				) }
				<Button
					icon={ helpIcon }
					label={ __( 'View external documentation.', 'better-wp-security' ) }
					href="https://go.solidwp.com/security-basic-help-docs"
					target="_blank"
					variant="tertiary"
					isSmall
				/>
			</StyledModulePanelHeader>
			{ ! showSettings && (
				<StyledModulePanelNoSettingsDescription>
					<Text size={ TextSize.SMALL }>
						<Markup noWrap content={ module.description } />
					</Text>
				</StyledModulePanelNoSettingsDescription>
			) }
			{ showSettings && (
				<StyledModulePanelTrigger
					onClick={ () => setIsOpen( ! isOpen ) }
					aria-expanded={ isExpanded }
					aria-controls={ `itsec-module-settings-${ module.id }` }
					disabled={ ! isActive }
					type="button"
				>
					<StyledModulePanelTitle text={
						sprintf(
							/* translators: 1. Module title. */
							__( '%s Settings', 'better-wp-security' ),
							module.title
						)
					} />
					<StyledModulePanelDescription text={ module.description } size={ TextSize.SMALL } />
					{ isActive && showSettings && (
						<StyledModulePanelIcon icon={ isExpanded ? closedIcon : openedIcon } />
					) }
				</StyledModulePanelTrigger>
			) }
			{ isActive && showSettings && (
				<StyledModulePanelBody
					isOpen={ isExpanded }
					variant={ SurfaceVariant.PRIMARY_CONTRAST }
					id={ `itsec-module-settings-${ module.id }` }
				>
					<ModuleFormInputs
						module={ module }
						schema={ schema }
						uiSchema={ uiSchema }
						formData={ formData }
						setFormData={ setFormData }
						highlightedSetting={ highlightedSetting }
					/>
				</StyledModulePanelBody>
			) }
			{ validated.hasErrors() && (
				<StyledModulePanelNotices>
					<ErrorList title={ __( 'Feature not available', 'better-wp-security' ) } errors={ validated.getAllErrorMessages() } />
				</StyledModulePanelNotices>
			) }
		</StyledModulePanel>
	);
}
