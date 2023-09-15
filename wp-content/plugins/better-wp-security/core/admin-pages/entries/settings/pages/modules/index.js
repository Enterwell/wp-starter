/**
 * External dependencies
 */
import {
	useParams,
	useLocation,
	Link,
	Route,
	Switch,
	useRouteMatch,
	Redirect,
} from 'react-router-dom';
import { isEmpty, size, sortBy } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import {
	FormToggle,
	Card,
	CardBody,
	Flex,
	FlexItem,
	Tooltip,
	Button,
	VisuallyHidden,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	ErrorList,
	FlexSpacer,
	ControlledTabPanel,
	HelpList,
	Markup,
} from '@ithemes/security-components';
import { withNavigate } from '@ithemes/security-hocs';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import {
	Breadcrumbs,
	HelpFill,
	PageHeader,
	useHelpBreadcrumbTrail,
} from '../../components';
import { useNavigation, ChildPages } from '../../page-registration';
import {
	useModuleRequirementsValidator,
	getModuleTypes,
	useNavigateTo,
	useConfigContext,
} from '../../utils';
import './style.scss';

export default function Modules() {
	const { url, path } = useRouteMatch();
	const { root } = useParams();
	const validateModuleRequirements = useModuleRequirementsValidator();
	const { installType } = useConfigContext();
	const { modules, dirty } = useSelect(
		( select ) => ( {
			modules: select( MODULES_STORE_NAME ).getEditedModules(),
			dirty: select( MODULES_STORE_NAME ).getDirtyModules(),
		} ),
		[]
	);
	const tabs = getModuleTypes()
		.map( ( { slug, label } ) => ( {
			name: slug,
			title: label,
			modules: sortBy(
				modules.filter(
					( module ) =>
						module.type === slug &&
						module.status.default !== 'always-active' &&
						( root === 'settings' ||
							module.onboard ||
							( root === 'import' && dirty.includes( slug ) ) ) &&
						shouldShowModuleRequirementsCheck( validateModuleRequirements, module )
				),
				'order'
			),
		} ) )
		.filter(
			( tab ) =>
				tab.modules.length > 0 &&
				( root === 'settings' || tab.name !== 'advanced' )
		);
	const allModules = sortBy(
		tabs.reduce( ( acc, tab ) => acc.concat( tab.modules ), [] ),
		( { title } ) => title.toLowerCase()
	);
	if ( allModules.length > 0 ) {
		const allTab = { name: 'all', title: __( 'All', 'better-wp-security' ), modules: allModules };
		if ( installType === 'free' ) {
			tabs.unshift( allTab );
		} else {
			tabs.push( allTab );
		}
	}

	const help = __(
		'Features is the home base of iThemes Security. Enabling a security feature will unlock the related User Group, Configure, and Notification settings. Disabling a security feature will hide the related options throughout the plugin.',
		'better-wp-security'
	);

	return (
		<>
			<ChildPages
				pages={ tabs.map( ( tab ) => ( {
					title: tab.title,
					to: `${ url }/${ tab.name }`,
					id: tab.name,
					replace: true,
				} ) ) }
			/>
			{ tabs.length > 0 && (
				<Switch>
					<Route path={ `${ path }/:child` }>
						<PageHeader
							title={ __( 'Features', 'better-wp-security' ) }
							subtitle={ __(
								'Choose the security features you‘d like to enable.',
								'better-wp-security'
							) }
							help={ help }
						/>
						<ModuleTabPanel base={ url } tabs={ tabs } />
						{ root !== 'settings' && <Navigation /> }
						<HelpPage help={ help } />
					</Route>
					<Redirect to={ `${ url }/${ tabs[ 0 ].name }` } />
				</Switch>
			) }
		</>
	);
}

/**
 * Checks if a module should be shown because it passes any requirements check.
 *
 * Active modules are always shown.
 *
 * @param {Function} validator The module requirement validator.
 * @param {Object}   module    The module definition.
 * @return {boolean} True if the module should be hidden.
 */
function shouldShowModuleRequirementsCheck( validator, module ) {
	if ( module.status.selected === 'active' ) {
		return true;
	}

	const result = validator( module, 'activate' );

	if ( ! result.hasErrors() ) {
		return true;
	}

	return result.getErrorCodes().some( ( code ) => result.getErrorData( code )[ 0 ].showMessageIfUnmet );
}

function ModuleTabPanel( { base, tabs } ) {
	const { child } = useParams();
	const navigateTo = useNavigateTo();
	const onSelect = ( selected ) => {
		navigateTo( `${ base }/${ selected }`, 'replace' );
	};

	return (
		<Card>
			<ControlledTabPanel
				isStyled
				tabs={ tabs }
				selected={ child }
				onSelect={ onSelect }
			>
				{ ( tab ) => <ModuleTab modules={ tab.modules } /> }
			</ControlledTabPanel>
		</Card>
	);
}

function Navigation() {
	const { next } = useNavigation();

	return (
		<Flex>
			<FlexSpacer />
			<FlexItem>
				<Link
					component={ withNavigate( Button ) }
					variant="primary"
					to={ next }
				>
					{ __( 'Next', 'better-wp-security' ) }
				</Link>
			</FlexItem>
		</Flex>
	);
}

function ModuleTab( { modules } ) {
	return (
		<CardBody>
			<ModuleGrid modules={ modules } />
		</CardBody>
	);
}

function ModuleGrid( { modules } ) {
	const { root } = useParams();
	const statusToggle =
		root === 'settings' ? StatusToggleSettings : StatusToggleOnboard;

	return (
		<div className="itsec-modules">
			{ modules.map( ( module ) => (
				<Module
					key={ module.id }
					module={ module }
					statusToggle={
						module.side_effects
							? StatusToggleSettings
							: statusToggle
					}
				/>
			) ) }
			{ modules.length === 1 && <div aria-hidden="true" /> }
		</div>
	);
}

function Module( { module, statusToggle: StatusToggle } ) {
	const { hash } = useLocation();
	const { root } = useParams();

	const apiError = useSelect( ( select ) =>
		select( MODULES_STORE_NAME ).getError( module.id )
	);
	const validRequirements = useModuleRequirementsValidator()( module, 'run' );

	return (
		<Card
			key={ module.id }
			className={ classnames( 'itsec-module', {
				'itsec-highlighted-search-result': hash === `#${ module.id }`,
			} ) }
		>
			<CardBody className="itsec-module__body">
				<h3>{ module.title }</h3>
				{ root === 'settings' &&
					module.status.selected === 'active' &&
					! validRequirements.hasErrors() && (
					<>
						{ module.settings?.interactive.length > 0 && (
							<Tooltip text={ __( 'Edit Settings', 'better-wp-security' ) }>
								<Link
									className="itsec-module__settings"
									to={ `/settings/configure/${ module.type }/${ module.id }` }
								>
									<VisuallyHidden>
										{ __( 'Edit Settings', 'better-wp-security' ) }
									</VisuallyHidden>
								</Link>
							</Tooltip>
						) }
						{ ! isEmpty( module.user_groups ) && (
							<Link
								className="itsec-module__user-groups"
								to={ `/settings/user-groups?module=${ module.id }` }
							>
								{ sprintf(
									/* translators: 1. The number of user groups. */
									__( 'User Groups (%d)', 'better-wp-security' ),
									size( module.user_groups )
								) }
							</Link>
						) }
					</>
				) }
				<Markup
					content={ module.description }
					tagName="p"
					id={ `itsec-module-description--${ module.id }` }
				/>
				{ ( module.status.selected === 'active' || ! validRequirements.hasErrors() ) && (
					<StatusToggle module={ module } />
				) }
				<ErrorList
					apiError={ apiError }
					errors={ validRequirements.getAllErrorMessages() }
				/>
			</CardBody>
		</Card>
	);
}

function StatusToggleOnboard( { module } ) {
	const { editModule } = useDispatch( MODULES_STORE_NAME );

	return (
		<FormToggle
			checked={ module.status.selected === 'active' }
			onChange={ ( e ) =>
				editModule( module.id, {
					status: {
						selected: e.target.checked ? 'active' : 'inactive',
					},
				} )
			}
			aria-label={ sprintf(
				/* translators: 1. The module name. */
				__( 'Enable the “%s” module.', 'better-wp-security' ),
				module.title
			) }
			aria-describedby={ `itsec-module-description--${ module.id }` }
		/>
	);
}

function StatusToggleSettings( { module } ) {
	const isActive = module.status.selected === 'active';
	const [ toggling, setIsToggling ] = useState( false );
	const { activateModule, deactivateModule } = useDispatch(
		MODULES_STORE_NAME
	);

	const toggleStatus = async ( checked ) => {
		setIsToggling( true );
		if ( checked ) {
			await activateModule( module.id );
		} else {
			await deactivateModule( module.id );
		}
		setIsToggling( false );
	};

	return (
		<FormToggle
			checked={ isActive }
			onChange={ ( e ) => toggleStatus( e.target.checked ) }
			disabled={ toggling }
			aria-label={ sprintf(
				/* translators: 1. The module name. */
				__( 'Enable the “%s” module.', 'better-wp-security' ),
				module.title
			) }
			aria-describedby={ `itsec-module-description--${ module.id }` }
		/>
	);
}

function HelpPage( { help } ) {
	return (
		<HelpFill>
			<PageHeader
				title={ __( 'Features', 'better-wp-security' ) }
				description={ help }
				breadcrumbs={
					<Breadcrumbs trail={ useHelpBreadcrumbTrail() } />
				}
			/>
			<HelpList topic="modules" />
		</HelpFill>
	);
}
