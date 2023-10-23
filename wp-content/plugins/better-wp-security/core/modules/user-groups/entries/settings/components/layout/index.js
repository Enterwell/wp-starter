/**
 * External dependencies
 */
import {
	useRouteMatch,
	Route,
	Switch,
	useParams,
	useLocation,
	Redirect,
	Link,
} from 'react-router-dom';
import {
	ArrayParam,
	StringParam,
	useQueryParam,
	withDefault,
} from 'use-query-params';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { PageHeader, Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-ui';
import { withNavigate } from '@ithemes/security-hocs';
import { store as uiStore } from '@ithemes/security.user-groups.ui';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import {
	GroupsNavigation,
	ImportPage,
	ManageGroup,
	ManageMultipleGroups,
	MultiGroupSelector,
	OnboardChooser,
	OnboardPage,
	SaveGroup,
	SaveMultipleGroups,
	PageHeaderSlot,
	PageHeaderFill,
	PageHeaderActionSlot,
} from '../';
import { StyledContainer } from './styles';

export default function Layout() {
	const { url, path, params: { root } } = useRouteMatch();
	const { hash, search } = useLocation();

	const { navIds, module } = useSelect(
		( select ) => ( {
			navIds: select( uiStore ).getMatchableNavIds(),
			module: select( MODULES_STORE_NAME ).getModule( 'user-groups' ),
		} ),
		[]
	);

	if ( navIds === null || ! module ) {
		return null;
	}

	return (
		<Switch>
			<Route path={ `${ path }/multi` }>
				<MultiRoute module={ module } />
			</Route>

			<Route path={ `${ path }/:groupId` }>
				<SingleRoute module={ module } root={ root } />
			</Route>

			<Route path={ path }>
				{ root === 'onboard' && <OnboardChooser /> }
				{ root !== 'onboard' && navIds.length > 0 && (
					<Redirect
						to={ `${ url }/${ navIds[ 0 ] }/${ search }${ hash }` }
					/>
				) }
			</Route>
		</Switch>
	);
}

function SingleRoute( { module, root } ) {
	const { groupId } = useParams();

	return (
		<Wrapper root={ root } module={ module } groupId={ groupId }>
			<StyledContainer>
				<PageHeaderSlot />
				<GroupsNavigation />
				<ManageGroup groupId={ groupId } />
			</StyledContainer>
			<SaveGroup groupId={ groupId } />
		</Wrapper>
	);
}

function MultiRoute( { module } ) {
	const [ back ] = useQueryParam( 'back', StringParam );
	const [ selected, setSelected ] = useQueryParam(
		'id',
		withDefault( ArrayParam, [] )
	);

	return (
		<>
			<StyledContainer>
				<PageHeader
					title={ __( 'User Groups', 'better-wp-security' ) }
					description={ <Markup content={ module.help } noWrap /> }
				>
					<Link
						to={ back ? `/settings/user-groups/${ back }` : '/settings/user-groups' }
						component={ withNavigate( Text ) }
						as="a"
						decoration="none"
						variant={ TextVariant.MUTED }
						text={ __( 'Cancel Group Edit', 'better-wp-security' ) }
					/>
				</PageHeader>
				<MultiGroupSelector selected={ selected } setSelected={ setSelected } />
				<ManageMultipleGroups groupIds={ selected } />
			</StyledContainer>
			<SaveMultipleGroups groupIds={ selected } />
		</>
	);
}

function Wrapper( { root, module, groupId, children } ) {
	const fillProps = useMemo( () => ( { groupId } ), [ groupId ] );

	if ( root === 'onboard' ) {
		return <OnboardPage module={ module }>{ children }</OnboardPage>;
	}

	if ( root === 'import' ) {
		return <ImportPage module={ module }>{ children }</ImportPage>;
	}

	return (
		<>
			<PageHeaderFill>
				<PageHeader
					title={ __( 'User Groups', 'better-wp-security' ) }
					description={ <Markup content={ module.help } noWrap /> }
				>
					<PageHeaderActionSlot fillProps={ fillProps } />
				</PageHeader>
			</PageHeaderFill>
			{ children }
		</>
	);
}
