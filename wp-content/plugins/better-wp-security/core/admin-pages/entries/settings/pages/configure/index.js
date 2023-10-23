/**
 * External dependencies
 */
import {
	Redirect,
	Route,
	Switch,
	useRouteMatch,
	useLocation,
	useParams,
} from 'react-router-dom';
import { cloneDeep, sortBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { useMemo, useState } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';
import { chevronDown as openedIcon, chevronUp as closedIcon, help as helpIcon } from '@wordpress/icons';
import { ToggleControl, createSlotFill } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { Button, Text, TextSize, SurfaceVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	ErrorList,
	NavigationTab,
	Markup,
} from '@ithemes/security-ui';
import {
	MODULES_STORE_NAME,
} from '@ithemes/security.packages.data';
import {
	useSettingsForm,
	useAllowedSettingsFields,
	getModuleTypes,
	appendClassNameAtPath,
	useModuleRequirementsValidator,
} from '../../utils';
import { OnboardHeader } from '../../components';
import { useNavigation } from '../../page-registration';
import {
	StyledModuleList,
	StyledOnboardWrapper,
	StyledPageHeader,
	StyledPrimarySchemaFormInputs,
	StyledSettingsActions,
	StyledModulePanel,
	StyledModulePanelHeader,
	StyledModulePanelTrigger,
	StyledModulePanelTitle,
	StyledModulePanelDescription,
	StyledModulePanelIcon,
	StyledModulePanelBody,
	StyledModulePanelNoSettingsDescription,
	StyledModulePanelNotices,
	StyledFormContainer,
	StyledSingleModuleSettingsContainer,
	StyledNavigation,
	StyledErrorList,
} from './styles';

function useTypes() {
	const { root } = useParams();
	const { editedModules } = useSelect(
		( select ) => ( {
			editedModules: select( MODULES_STORE_NAME ).getEditedModules(),
		} ), []
	);
	const modules = editedModules.filter( ( module ) => {
		if ( root === 'onboard' && ! module.onboard ) {
			return false;
		}

		if ( root === 'import' && ! module.settings?.import?.length > 0 ) {
			return false;
		}

		const hasSettings = module.settings?.show_ui;

		return hasSettings || module.status.default !== 'always-active';
	} );

	const sorted = sortBy( modules, 'order' );
	const types = getModuleTypes()
		.filter( ( type ) => modules.find( ( module ) => module.type === type.slug ) );

	return { types, modules: sorted };
}

const {
	Slot: PageHeaderSlot,
	Fill: PageHeaderFill,
} = createSlotFill( 'ConfigurePageHeader' );

export function SingleModulePage( { module } ) {
	const id = useInstanceId( SingleModulePage, 'itsec-configure-single-modules-page' );
	const { config } = useSelect( ( select ) => ( {
		config: select( MODULES_STORE_NAME ).getEditedModule( module ),
	} ), [ module ] );
	const { saveSettings } = useDispatch( MODULES_STORE_NAME );
	const { filterFields } = useAllowedSettingsFields( config );

	const {
		schema,
		uiSchema,
		formData,
		setFormData,
	} = useSettingsForm( config, filterFields );

	if ( ! config ) {
		return null;
	}

	const onSave = ( e ) => {
		e.preventDefault();
		saveSettings( config.id, true );
	};

	return (
		<Page title={ config.title } description={ config.description } headerHasBorder>
			<StyledFormContainer>
				<PageHeaderSlot />
				<StyledSingleModuleSettingsContainer onSubmit={ onSave } id={ id }>
					<ConfigureModule
						module={ config }
						schema={ schema }
						uiSchema={ uiSchema }
						formData={ formData }
						setFormData={ setFormData }
					/>
				</StyledSingleModuleSettingsContainer>
			</StyledFormContainer>
			<SettingsActions form={ id } modules={ [ module ] } />
		</Page>
	);
}

export function ModulesOfTypePage( { type, title, description } ) {
	const id = useInstanceId( ModulesOfTypePage, 'itsec-configure-modules-of-type-page' );
	const { modules: allModules } = useTypes();
	const modules = useMemo(
		() => allModules.filter( ( module ) => module.type === type ),
		[ allModules, type ]
	);
	const moduleIds = useMemo( () => modules.map( ( module ) => module.id ), [ modules ] );
	const { saveSettings } = useDispatch( MODULES_STORE_NAME );

	if ( ! modules ) {
		return null;
	}

	const onSubmit = ( e ) => {
		e.preventDefault();
		saveSettings( moduleIds, true );
	};

	return (
		<Page title={ title } description={ description } headerHasBorder>
			<StyledFormContainer>
				<PageHeaderSlot />
				<StyledModuleList id={ id } onSubmit={ onSubmit }>
					{ modules.map( ( module ) => <ModuleCard key={ module.id } module={ module } /> ) }
				</StyledModuleList>
			</StyledFormContainer>
			<SettingsActions form={ id } modules={ moduleIds } />
		</Page>
	);
}

export function TabbedModulesPage( { exclude } ) {
	const { types: allTypes, modules } = useTypes();
	const types = useMemo(
		() => allTypes.filter( ( type ) => ! exclude.includes( type.slug ) ),
		[ allTypes, exclude ]
	);
	const { path, url } = useRouteMatch();

	return (
		<Switch>
			<Route path={ `${ path }/:type` }>
				<TabbedModulesRoute allModules={ modules }>
					<PageHeaderSlot />
					<StyledNavigation size={ TextSize.NORMAL }>
						{ types.map( ( type ) => (
							<NavigationTab key={ type.slug } title={ type.label } to={ `${ url }/${ type.slug }` } />
						) ) }
					</StyledNavigation>
				</TabbedModulesRoute>
			</Route>
			<Route path={ path } exact>
				{ types.length > 0 && (
					<Redirect to={ `${ url }/${ types[ 0 ].slug }` } />
				) }
			</Route>
		</Switch>
	);
}

function TabbedModulesRoute( { allModules, children } ) {
	const id = useInstanceId( TabbedModulesPage, 'itsec-configure-tabbed-modules-route' );
	const { params } = useRouteMatch();

	const modules = useMemo(
		() => allModules.filter( ( module ) => module.type === params.type ),
		[ allModules, params ]
	);
	const moduleIds = useMemo( () => modules.map( ( module ) => module.id ), [ modules ] );
	const { saveSettings } = useDispatch( MODULES_STORE_NAME );

	const onSubmit = ( e ) => {
		e.preventDefault();
		saveSettings( moduleIds, true );
	};

	return (
		<Page
			title={ __( 'Features', 'better-wp-security' ) }
			description={ __( 'Choose and configure security features for your site.', 'better-wp-security' ) }
		>
			<StyledFormContainer>
				{ children }
				<StyledModuleList id={ id } onSubmit={ onSubmit }>
					{ modules.map( ( module ) => (
						<ModuleCard key={ module.id } module={ module } />
					) ) }
				</StyledModuleList>
			</StyledFormContainer>
			<SettingsActions form={ id } modules={ moduleIds } />
		</Page>
	);
}

function ModuleCard( { module } ) {
	const { root } = useParams();
	const { hash } = useLocation();
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

	const isHighlighted = hash === `#${ module.id }` || hash.startsWith( `#${ module.id },` );
	const isExpanded = isOpen || isHighlighted;

	return (
		<StyledModulePanel isHighlighted={ isHighlighted }>
			<StyledModulePanelHeader>
				{ canToggleStatus && (
					<StatusToggleSettings
						module={ module }
						setSettingsOpen={ setIsOpen }
						persist={ root === 'settings' }
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
					<ConfigureModule
						module={ module }
						schema={ schema }
						uiSchema={ uiSchema }
						formData={ formData }
						setFormData={ setFormData }
					/>
				</StyledModulePanelBody>
			) }
			{ validated.hasErrors() && (
				<StyledModulePanelNotices>
					<ErrorList errors={ validated.getAllErrorMessages() } />
				</StyledModulePanelNotices>
			) }
		</StyledModulePanel>
	);
}

function StatusToggleSettings( { module, setSettingsOpen, persist } ) {
	const isActive = module.status.selected === 'active';
	const [ toggling, setIsToggling ] = useState( false );
	const { activateModule, deactivateModule, editModule } = useDispatch(
		MODULES_STORE_NAME
	);

	const onToggleStatus = async ( checked ) => {
		setIsToggling( true );
		if ( checked ) {
			await ( persist
				? activateModule( module.id )
				: editModule( module.id, { status: { selected: 'active' } } )
			);
			setSettingsOpen( true );
		} else {
			await ( persist
				? deactivateModule( module.id )
				: editModule( module.id, { status: { selected: 'inactive' } } )
			);
		}
		setIsToggling( false );
	};

	return (
		<ToggleControl
			label={ module.title }
			checked={ isActive }
			onChange={ onToggleStatus }
			disabled={ toggling }
			aria-label={ sprintf(
				/* translators: 1. The module name. */
				__( 'Enable the “%s” module.', 'better-wp-security' ),
				module.title
			) }
			aria-describedby={ `itsec-module-description--${ module.id }` }
			__nextHasNoMarginBottom
		/>
	);
}

function ConfigureModule( { module, schema, uiSchema: uiSchemaRaw, formData, setFormData } ) {
	const id = useInstanceId(
		ConfigureModule,
		`itsec-configure-${ module.id }`
	);
	const { hash } = useLocation();

	const { apiError } = useSelect(
		( select ) => ( {
			apiError: select( MODULES_STORE_NAME ).getError( module.id ),
		} ),
		[ module.id ]
	);

	const highlightedSetting = hash.startsWith( `#${ module.id },` )
		? hash.split( ',' )[ 1 ]
		: hash.replace( '#', '' );

	const uiSchema = useMemo( () => {
		if ( ! highlightedSetting ) {
			return uiSchemaRaw;
		}

		return appendClassNameAtPath(
			uiSchemaRaw ? cloneDeep( uiSchemaRaw ) : {},
			[ highlightedSetting, 'classNames' ],
			'itsec-highlighted-search-result'
		);
	}, [ uiSchemaRaw, highlightedSetting ] );

	const formContext = useMemo(
		() => ( {
			module: module.id,
			disableInlineErrors: true,
		} ),
		[ module.id ]
	);

	return (
		<>
			<StyledErrorList apiError={ apiError } />
			<StyledPrimarySchemaFormInputs
				tagName="div"
				id={ id }
				schema={ schema }
				uiSchema={ uiSchema }
				formData={ formData }
				onChange={ setFormData }
				idPrefix={ `itsec_${ module.id }` }
				formContext={ formContext }
				showErrorList={ false }
			/>
		</>
	);
}

function SettingsActions( { modules, form } ) {
	const { isSaving, isDirty } = useSelect( ( select ) => ( {
		isDirty: select( MODULES_STORE_NAME ).getDirtySettings().some( ( module ) => modules.includes( module ) ),
		isSaving: select( MODULES_STORE_NAME ).isSavingSettings( modules ),
	} ), [ modules ] );
	const { resetSettingEdits } = useDispatch( MODULES_STORE_NAME );
	const { root } = useParams();
	const { goNext } = useNavigation();

	return (
		<StyledSettingsActions>
			<Button
				text={ __( 'Undo Changes', 'better-wp-security' ) }
				variant="secondary"
				onClick={ () => resetSettingEdits( modules ) }
				disabled={ isSaving || ! isDirty }
			/>
			{ root === 'settings' && (
				<Button
					type="submit"
					form={ form }
					text={ __( 'Save', 'better-wp-security' ) }
					variant="primary"
					isBusy={ isSaving }
					disabled={ isSaving || ! isDirty }
				/>
			) }
			{ root !== 'settings' && (
				<Button
					text={ __( 'Next', 'better-wp-security' ) }
					variant="primary"
					onClick={ goNext }
				/>
			) }
		</StyledSettingsActions>
	);
}

function Page( { title, description, headerHasBorder, children } ) {
	const { root } = useParams();

	if ( root === 'settings' ) {
		return (
			<>
				<PageHeaderFill>
					<StyledPageHeader title={ title } description={ description } hasBorder={ headerHasBorder } />
				</PageHeaderFill>
				{ children }
			</>
		);
	}

	return (
		<StyledOnboardWrapper>
			<OnboardHeader title={ title } description={ description } showIndicator showNext />
			{ children }
		</StyledOnboardWrapper>
	);
}
