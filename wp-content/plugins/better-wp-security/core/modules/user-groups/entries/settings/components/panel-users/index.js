/**
 * External dependencies
 */
import { map } from 'lodash';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { compose, withInstanceId } from '@wordpress/compose';
import { useState } from '@wordpress/element';
import {
	withDispatch,
	withSelect,
	dispatch as dataDispatch,
} from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { AsyncSelect } from '@ithemes/security-components';
import './style.scss';

function formatUser( user ) {
	return { value: user.id, label: user.name, user };
}

const loadUsers = ( search ) =>
	new Promise( ( resolve, reject ) => {
		apiFetch( {
			path: addQueryArgs( '/wp/v2/users', {
				search,
				per_page: 100,
				context: 'embed',
				itsec_global: true,
			} ),
		} )
			.then( ( response ) => {
				response.forEach(
					dataDispatch( 'ithemes-security/core' ).receiveUser
				);

				return response;
			} )
			.then( ( response ) => resolve( response.map( formatUser ) ) )
			.catch( reject );
	} );

function PanelUsers( {
	instanceId,
	users,
	loading,
	onChange,
	disabled = false,
} ) {
	const [ selectSearch, setSelectSearch ] = useState( '' );
	const selectId = `itsec-user-group-panel-users__select-${ instanceId }`;
	const values = loading ? [] : users.map( formatUser );

	return (
		<BaseControl
			className="itsec-user-group-panel-users__select-control"
			label={ __( 'Select Users', 'better-wp-security' ) }
			help={ __( 'Select specific users to add to this group.', 'better-wp-security' ) }
			id={ selectId }
		>
			<AsyncSelect
				classNamePrefix="components-itsec-async-select-control"
				inputId={ selectId }
				isDisabled={ disabled || loading }
				isLoading={ loading }
				isMulti
				cacheOptions
				defaultOptions
				loadOptions={ loadUsers }
				value={ values }
				onChange={ ( newUsers ) =>
					onChange( { users: map( newUsers, 'value' ) } )
				}
				inputValue={ selectSearch }
				onInputChange={ ( newSelect ) =>
					setSelectSearch( newSelect )
				}
			/>
		</BaseControl>
	);
}

export default compose( [
	withSelect( ( select, { groupId } ) => {
		const userIds =
			select(
				'ithemes-security/user-groups-editor'
			).getEditedGroupAttribute( groupId, 'users' ) || [];
		const users = [];
		let loading = false;

		userIds.forEach( ( userId ) => {
			const user = select( 'ithemes-security/core' ).getUser( userId );

			if ( user ) {
				users.push( user );
			} else if (
				select( 'core/data' ).isResolving(
					'ithemes-security/core',
					'getUser',
					[ userId ]
				)
			) {
				loading = true;
			}
		} );

		return {
			users,
			userIds,
			loading,
		};
	} ),
	withDispatch( ( dispatch, { groupId } ) => ( {
		receiveUser: dispatch( 'ithemes-security/core' ).receiveUser,
		onChange( edit ) {
			return dispatch( 'ithemes-security/user-groups-editor' ).editGroup(
				groupId,
				edit
			);
		},
	} ) ),
	withInstanceId,
] )( PanelUsers );
