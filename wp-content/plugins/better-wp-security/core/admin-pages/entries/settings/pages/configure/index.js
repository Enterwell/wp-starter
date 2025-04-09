/**
 * External dependencies
 */
import {
	Redirect,
	Route,
	Switch,
	useRouteMatch,
	useParams,
	useLocation,
} from 'react-router-dom';
import { sortBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';
import { createSlotFill } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { Button, TextSize } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { NavigationTab } from '@ithemes/security-ui';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import {
	useSettingsForm,
	useAllowedSettingsFields,
	getModuleTypes,
} from '../../utils';
import { OnboardHeader, ModuleCard, ModuleFormInputs } from '../../components';
import { useNavigation } from '../../page-registration';
import {
	StyledModuleList,
	StyledOnboardWrapper,
	StyledPageHeader,
	StyledSettingsActions,
	StyledFormContainer,
	StyledSingleModuleSettingsContainer,
	StyledNavigation,
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
	const { hash } = useLocation();
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

	const highlightedSetting = hash.startsWith( `#${ module.id },` )
		? hash.split( ',' )[ 1 ]
		: hash.replace( '#', '' );

	const onSave = ( e ) => {
		e.preventDefault();
		saveSettings( config.id, true );
	};

	return (
		<Page title={ config.title } description={ config.description } headerHasBorder>
			<StyledFormContainer>
				<PageHeaderSlot />
				<StyledSingleModuleSettingsContainer onSubmit={ onSave } id={ id }>
					<ModuleFormInputs
						module={ config }
						schema={ schema }
						uiSchema={ uiSchema }
						formData={ formData }
						setFormData={ setFormData }
						highlightedSetting={ highlightedSetting }
					/>
				</StyledSingleModuleSettingsContainer>
			</StyledFormContainer>
			<SettingsActions form={ id } modules={ [ module ] } />
		</Page>
	);
}

export function ModulesOfTypePage( { type, title, description } ) {
	const { root } = useParams();
	const { hash } = useLocation();
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
					{ modules.map( ( module ) => {
						const isHighlighted = hash === `#${ module.id }` || hash.startsWith( `#${ module.id },` );
						const highlightedSetting = hash.startsWith( `#${ module.id },` )
							? hash.split( ',' )[ 1 ]
							: hash.replace( '#', '' );

						return (
							<ModuleCard
								key={ module.id }
								module={ module }
								isHighlighted={ isHighlighted }
								highlightedSetting={ highlightedSetting }
								persistStatus={ root === 'settings' }
							/>
						);
					} ) }
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
	const { root } = useParams();
	const { hash } = useLocation();
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
					{ modules.map( ( module ) => {
						const isHighlighted = hash === `#${ module.id }` || hash.startsWith( `#${ module.id },` );
						const highlightedSetting = hash.startsWith( `#${ module.id },` )
							? hash.split( ',' )[ 1 ]
							: hash.replace( '#', '' );

						return (
							<ModuleCard
								key={ module.id }
								module={ module }
								isHighlighted={ isHighlighted }
								highlightedSetting={ highlightedSetting }
								persistStatus={ root === 'settings' }
							/>
						);
					} ) }
				</StyledModuleList>
			</StyledFormContainer>
			<SettingsActions form={ id } modules={ moduleIds } />
		</Page>
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
