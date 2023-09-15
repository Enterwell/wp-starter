/**
 * External dependencies
 */
import {
	Redirect,
	Route,
	Switch,
	useParams,
	useRouteMatch,
	useLocation,
	generatePath,
	Link,
} from 'react-router-dom';
import { isEmpty, every, cloneDeep, size, sortBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { useDispatch, useRegistry, useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { useMemo, useState } from '@wordpress/element';
import { Card, CardHeader, CardBody, Button } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import {
	ControlledTabPanel,
	ErrorList,
	HelpList,
} from '@ithemes/security-components';
import { withNavigate } from '@ithemes/security-hocs';
import {
	CORE_STORE_NAME,
	MODULES_STORE_NAME,
} from '@ithemes/security.packages.data';
import {
	PageHeader,
	PrimarySchemaFormInputs,
	PrimarySchemaFormActions,
	HelpFill,
	SelectableCard,
	Breadcrumbs,
	useHelpBreadcrumbTrail,
} from '../../components';
import {
	useConfigContext,
	useNavigateTo,
	useSettingsForm,
	makeConditionalSettingsSchema,
	getModuleTypes,
	appendClassNameAtPath,
	useModuleRequirementsValidator,
} from '../../utils';
import { useNavigation, ChildPages } from '../../page-registration';
import './style.scss';

function useTypes() {
	const { root } = useParams();
	const { serverType, installType } = useConfigContext();
	const registry = useRegistry();
	const { editedModules, activeModules, featureFlags } = useSelect(
		( select ) => ( {
			editedModules: select( MODULES_STORE_NAME ).getEditedModules(),
			activeModules: select( MODULES_STORE_NAME ).getActiveModules(),
			featureFlags: select( CORE_STORE_NAME ).getFeatureFlags(),
		} )
	);
	const validateModuleRequirements = useModuleRequirementsValidator();

	const getModules = () =>
		editedModules.filter( ( module ) => {
			if ( module.status.selected !== 'active' ) {
				return false;
			}

			if ( ! module.settings?.show_ui ) {
				return false;
			}

			if ( ! module.settings?.interactive.length ) {
				return false;
			}

			if ( root === 'onboard' && ! module.settings?.onboard.length ) {
				return false;
			}

			if (
				root === 'import' &&
				! module.settings?.onboard.length &&
				! module.settings?.import.length
			) {
				return false;
			}

			if ( validateModuleRequirements( module, 'run' ).hasErrors() ) {
				return false;
			}

			if ( module.settings?.conditional ) {
				const schema = makeConditionalSettingsSchema( module, {
					serverType,
					installType,
					activeModules,
					featureFlags,
					registry,
					settings: registry
						.select( MODULES_STORE_NAME )
						.getEditedSettings( module.id ),
				} );

				if ( isEmpty( schema.properties ) ) {
					return false;
				}

				const allEmpty = every(
					schema.properties,
					( propSchema ) =>
						propSchema.type === 'object' &&
						isEmpty( propSchema.properties )
				);

				if ( allEmpty ) {
					return false;
				}

				if (
					root === 'onboard' &&
					! module.settings.onboard.some(
						( setting ) => !! schema.properties[ setting ]
					)
				) {
					return false;
				}

				if (
					root === 'import' &&
					! module.settings.onboard.some(
						( setting ) => !! schema.properties[ setting ]
					) &&
					! module.settings.import.some(
						( setting ) => !! schema.properties[ setting ]
					)
				) {
					return false;
				}
			}

			return true;
		} );

	const modules = sortBy( getModules(), 'order' );
	const types = getModuleTypes()
		.map( ( type ) => ( {
			...type,
			modules: modules.filter( ( module ) => module.type === type.slug ),
		} ) )
		.filter( ( type ) => type.modules.length > 0 );

	return { types, modules };
}

export default function Configure() {
	const {
		url,
		path,
		isExact,
		params: { root },
	} = useRouteMatch();
	const { types, modules } = useTypes();
	const recommended = modules.filter(
		( module ) => module.type === 'recommended'
	);
	const recommendedIds = recommended
		.map( ( module ) => module.id )
		.join( '|' );
	const nav = [
		...recommended.map( ( module ) => ( {
			slug: module.id,
			label: module.title,
		} ) ),
		...types,
	];

	return (
		<>
			{ ! isExact && (
				<ChildPages
					pages={ nav
						.filter( ( { slug } ) => slug !== 'advanced' )
						.map( ( { slug, label } ) => ( {
							title: label,
							to: `${ url }/${ slug }`,
							id: slug,
						} ) ) }
				/>
			) }

			<Switch>
				<Route
					path={ `${ path }/:child(${ recommendedIds })` }
					render={ ( { match } ) => {
						const module = modules.find(
							( maybe ) => maybe.id === match.params.child
						);

						if ( ! module ) {
							return null;
						}

						return <ModulePage module={ module } />;
					} }
				/>

				<Route
					path={ [ `${ path }/:child/:tab`, `${ path }/:child` ] }
					render={ ( { match } ) => {
						const activeType = types.find(
							( type ) => type.slug === match.params.child
						);

						if ( ! activeType ) {
							return null;
						}

						return <TabPanel modules={ activeType.modules } />;
					} }
				/>

				<Route path={ path }>
					{ nav.length > 0 &&
						( root !== 'settings' ? (
							<Intro to={ `${ url }/${ nav[ 0 ].slug }` } />
						) : (
							<Redirect to={ `${ url }/${ nav[ 0 ].slug }` } />
						) ) }
				</Route>
			</Switch>
		</>
	);
}

function Intro( { to } ) {
	return (
		<>
			<PageHeader
				title={ __( 'Configure', 'better-wp-security' ) }
				subtitle={ __(
					'Based on the Security Features you’ve enabled while settings up iThemes Security, we’ve selected the most important settings for you to configure.',
					'better-wp-security'
				) }
			/>

			<div className="itsec-configure-intro">
				<SelectableCard
					title={ __( 'Recommended', 'better-wp-security' ) }
					description={ __( 'Configure Site', 'better-wp-security' ) }
					icon="star-filled"
					fillIcon
					to={ to }
					direction="vertical"
				/>
			</div>
		</>
	);
}

function TabPanel( { modules } ) {
	const { url, path } = useRouteMatch();
	const { child: type, tab: moduleId, root, ...params } = useParams();
	const navigateTo = useNavigateTo();
	const tabs = useMemo(
		() =>
			modules.map( ( module ) => ( {
				name: module.id,
				title: module.title,
				module,
			} ) ),
		[ type, modules ]
	);

	const activeModule = modules.find( ( module ) => module.id === moduleId );

	if ( ! activeModule ) {
		const first = modules.find( ( module ) => module.type === type );

		return <Redirect to={ first ? `${ url }/${ first.id }` : url } />;
	}

	const onSelect = ( selected ) =>
		navigateTo(
			generatePath( path, {
				...params,
				root,
				child: type,
				tab: selected,
			} )
		);

	return (
		<ModulePage
			module={ activeModule }
			tabs={ tabs }
			onSelect={ onSelect }
		/>
	);
}

function ModulePage( { module, tabs, onSelect } ) {
	const { root } = useParams();

	const Concrete =
		root === 'settings' ? ConfigureModuleSettings : ConfigureModuleOnboard;

	return (
		<>
			<PageHeader
				title={ module.title }
				subtitle={ module.description }
				help={ module.help }
				breadcrumbs={ module.type !== 'advanced' }
			/>
			<Concrete tabs={ tabs } module={ module } onSelect={ onSelect } />
		</>
	);
}

function ConfigureModuleOnboard( { tabs, module, onSelect } ) {
	const { root } = useParams();
	const { previous, goNext } = useNavigation(
		tabs?.map( ( tab ) => tab.name )
	);

	if ( ! module ) {
		return null;
	}

	return (
		<ConfigureModule
			tabs={ tabs }
			module={ module }
			onSelect={ onSelect }
			onSave={ goNext }
			saveLabel={ __( 'Next', 'better-wp-security' ) }
			saveDisabled={ false }
			cancelLabel={ __( 'Back', 'better-wp-security' ) }
			cancelRoute={ previous }
			filterFields={ ( _, setting ) =>
				module.settings.onboard.includes( setting ) ||
				( root === 'import' &&
					module.settings.import.includes( setting ) )
			}
		/>
	);
}

function ConfigureModuleSettings( { tabs, module, onSelect } ) {
	const { saveSettings } = useDispatch( MODULES_STORE_NAME );

	if ( ! module ) {
		return null;
	}

	const onSave = () => saveSettings( module.id );

	return (
		<ConfigureModule
			tabs={ tabs }
			module={ module }
			onSelect={ onSelect }
			onSave={ onSave }
		/>
	);
}

function ConfigureModule( {
	tabs,
	module,
	onSelect,
	onSave,
	saveDisabled,
	filterFields,
	...rest
} ) {
	const id = useInstanceId(
		ConfigureModule,
		`itsec-configure-${ module.id }`
	);
	const { hash } = useLocation();

	const { isSaving, isDirty, apiError } = useSelect(
		( select ) => ( {
			isSaving: select( MODULES_STORE_NAME ).isSavingSettings(
				module.id
			),
			isDirty: select( MODULES_STORE_NAME ).areSettingsDirty( module.id ),
			apiError: select( MODULES_STORE_NAME ).getError( module.id ),
		} ),
		[ module.id ]
	);
	const { resetSettingEdits } = useDispatch( MODULES_STORE_NAME );
	const {
		schema,
		uiSchema: uiSchemaRaw,
		formData,
		setFormData,
	} = useSettingsForm( module, filterFields );
	const uiSchema = useMemo( () => {
		if ( ! hash ) {
			return uiSchemaRaw;
		}

		return appendClassNameAtPath(
			uiSchemaRaw ? cloneDeep( uiSchemaRaw ) : {},
			[ hash.substr( 1 ), 'classNames' ],
			'itsec-highlighted-search-result'
		);
	}, [ uiSchemaRaw, hash ] );

	const [ schemaError, setSchemaError ] = useState( [] );
	const formContext = useMemo(
		() => ( {
			module: module.id,
			disableInlineErrors: true,
		} ),
		[ module.id ]
	);

	const onSubmit = ( e ) => {
		setSchemaError( [] );
		onSave( e );
	};

	if ( ! module ) {
		return null;
	}

	const renderModule = () => (
		<>
			<ModuleLinks module={ module } />
			<CardBody>
				<ErrorList apiError={ apiError } schemaError={ schemaError } />
				<PrimarySchemaFormInputs
					id={ id }
					onSubmit={ onSubmit }
					schema={ schema }
					uiSchema={ uiSchema }
					formData={ formData }
					onChange={ setFormData }
					idPrefix={ `itsec_${ module.id }` }
					formContext={ formContext }
					onError={ setSchemaError }
					showErrorList={ false }
				/>
			</CardBody>
		</>
	);

	return (
		<>
			<HelpPage module={ module } />
			<Card>
				{ tabs ? (
					<ControlledTabPanel
						tabs={ tabs }
						selected={ module.id }
						onSelect={ onSelect }
						isStyled
					>
						{ renderModule }
					</ControlledTabPanel>
				) : (
					renderModule()
				) }
			</Card>
			<PrimarySchemaFormActions
				id={ id }
				isSaving={ isSaving }
				saveDisabled={
					saveDisabled === undefined ? ! isDirty : saveDisabled
				}
				undoDisabled={ ! isDirty }
				onUndo={ () => resetSettingEdits( module.id ) }
				{ ...rest }
			/>
		</>
	);
}

function HelpPage( { module } ) {
	return (
		<HelpFill>
			<PageHeader
				title={ module.title }
				description={ module.help }
				breadcrumbs={
					<Breadcrumbs
						trail={ useHelpBreadcrumbTrail( module.title ) }
					/>
				}
			/>
			<HelpList topic={ module.id } />
		</HelpFill>
	);
}

function ModuleLinks( { module } ) {
	const links = [];

	if (
		! isEmpty( module.user_groups ) ||
		module.id === 'password-requirements'
	) {
		const text =
			module.id === 'password-requirements'
				? __( 'User Groups', 'better-wp-security' )
				: sprintf(
					/* translators: 1. The number of user groups. */
					__( 'User Groups (%d)', 'better-wp-security' ),
					size( module.user_groups )
				);

		links.push(
			<Link
				to={ `/settings/user-groups?module=${ module.id }` }
				component={ withNavigate( Button ) }
				variant="link"
				text={ text }
				icon="groups"
			/>
		);
	}

	if ( ! links.length ) {
		return null;
	}

	return (
		<CardHeader className="itsec-configure-module-links">
			<ul>
				{ links.map( ( link, i ) => (
					<li key={ i }>{ link }</li>
				) ) }
			</ul>
		</CardHeader>
	);
}
