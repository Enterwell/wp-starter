/**
 * External dependencies
 */
import {
	Switch,
	Route,
	Redirect,
	Link,
	useRouteMatch,
	useLocation,
} from 'react-router-dom';
import { isPlainObject } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	Card,
	CardHeader,
	Flex,
	FlexItem,
	Button,
	Disabled,
} from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { getQueryArg } from '@wordpress/url';

/**
 * Internal dependencies
 */
import {
	ControlledTabPanel,
	ErrorList,
	FlexSpacer,
	MessageList,
} from '@ithemes/security-components';
import { Unknown as Icon } from '@ithemes/security-style-guide';
import { withNavigate } from '@ithemes/security-hocs';
import { useNavigateTo } from '@ithemes/security.pages.settings';
import { TabEditGroup, TabSettings, SingleGroupHeader } from '../';
import './style.scss';

export default function ManageGroup( { groupId, showSave } ) {
	const { hash, search } = useLocation();
	const moduleFilter = getQueryArg( search, 'module' );
	const { url, path } = useRouteMatch();
	const { type, isDirty, isSaving, notFound } = useSelect(
		( select ) => ( {
			type: select(
				'ithemes-security/user-groups-editor'
			).getMatchableType( groupId ),
			isDirty: select( 'ithemes-security/user-groups-editor' ).isDirty(
				groupId
			),
			isSaving: select(
				'ithemes-security/user-groups-editor'
			).isSavingGroupOrSettings( groupId ),
			notFound: select( 'ithemes-security/user-groups' ).isGroupNotFound(
				groupId
			),
		} ),
		[ groupId ]
	);
	const { saveGroupAndSettings, resetEdits } = useDispatch(
		'ithemes-security/user-groups-editor'
	);
	const tabs = useMemo(
		() =>
			[
				{
					name: 'settings',
					title: __( 'Features', 'better-wp-security' ),
					className: 'itsec-manage-user-group-tabs__tab',
					Component: TabSettings,
				},
				type === 'user-group' && {
					name: 'edit',
					title: __( 'Edit Group', 'better-wp-security' ),
					className: 'itsec-manage-user-group-tabs__tab',
					Component: TabEditGroup,
				},
			].filter( isPlainObject ),
		[ type ]
	);

	const onSave = () => saveGroupAndSettings( groupId );
	const onReset = () => resetEdits( groupId );

	if ( notFound ) {
		return (
			<>
				<MessageList
					type="error"
					title={ __( 'No Group Found', 'better-wp-security' ) }
					messages={ [
						__(
							'No user group was found with the requested id.',
							'better-wp-security'
						),
					] }
				/>
				<div
					style={ {
						maxWidth: '20rem',
						margin: '0 auto',
						display: 'block',
					} }
				>
					<Icon />
				</div>
			</>
		);
	}

	return (
		<>
			<SingleGroupHeader
				groupId={ groupId }
				moduleFilter={ moduleFilter }
			/>
			<Switch>
				<Route path={ `${ path }/:tab` }>
					{ isSaving ? (
						<Disabled>
							<ManageGroupRoute
								groupId={ groupId }
								base={ url }
								tabs={ tabs }
								moduleFilter={ moduleFilter }
							/>
						</Disabled>
					) : (
						<ManageGroupRoute
							groupId={ groupId }
							base={ url }
							tabs={ tabs }
							highlight={ hash.substr( 1 ) }
							moduleFilter={ moduleFilter }
						/>
					) }
				</Route>

				<Route path={ path }>
					<Redirect
						to={ `${ url }/${ tabs[ 0 ].name }/${ search }${ hash }` }
					/>
				</Route>
			</Switch>

			{ showSave && (
				<Flex>
					<FlexSpacer />
					<FlexItem>
						<Button
							variant="secondary"
							onClick={ onReset }
							disabled={ ! isDirty }
						>
							{ __( 'Undo Changes', 'better-wp-security' ) }
						</Button>
					</FlexItem>
					<FlexItem>
						<Button
							variant="primary"
							onClick={ onSave }
							isBusy={ isSaving }
							disabled={ isSaving || ! isDirty }
						>
							{ __( 'Save', 'better-wp-security' ) }
						</Button>
					</FlexItem>
				</Flex>
			) }
		</>
	);
}

function ManageGroupRoute( { groupId, base, tabs, highlight, moduleFilter } ) {
	const {
		url,
		params: { tab },
	} = useRouteMatch();
	const navigateTo = useNavigateTo();
	const error = useSelect( ( select ) =>
		select( 'ithemes-security/user-groups-editor' ).getError( groupId )
	);
	const onSelect = ( selected ) => {
		navigateTo( `${ base }/${ selected }`, 'replace' );
	};
	if ( moduleFilter ) {
		return (
			<Card>
				<CardHeader className="itsec-user-groups-filtered-features">
					<Link
						to={ url }
						component={ withNavigate( Button ) }
						icon="arrow-left"
						text={ __( 'All Features', 'better-wp-security' ) }
						variant="link"
					/>
				</CardHeader>
				<TabSettings
					moduleFilter={ moduleFilter }
					groupId={ groupId }
					highlight={ highlight }
				>
					<ErrorList apiError={ error } />
				</TabSettings>
			</Card>
		);
	}

	return (
		<Card>
			<ControlledTabPanel
				tabs={ tabs }
				isStyled
				selected={ tab }
				onSelect={ onSelect }
			>
				{ ( { Component } ) => (
					<Component groupId={ groupId } highlight={ highlight }>
						<ErrorList apiError={ error } />
					</Component>
				) }
			</ControlledTabPanel>
		</Card>
	);
}
