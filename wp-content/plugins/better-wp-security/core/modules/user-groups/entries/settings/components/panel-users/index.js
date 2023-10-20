/**
 * External dependencies
 */
import { map } from 'lodash';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useMemo, useState } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';
import {
	useSelect,
	useDispatch,
} from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { AsyncSelect } from '@ithemes/security-ui';
import { store as uiStore } from '@ithemes/security.user-groups.ui';
import './style.scss';

function formatUser( user ) {
	return { value: user.id, label: user.name, user };
}

const loadUsers = ( receiveUser ) => ( search ) =>
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
				response.forEach( receiveUser );

				return response;
			} )
			.then( ( response ) => resolve( response.map( formatUser ) ) )
			.catch( reject );
	} );

export default function PanelUsers( {
	groupId,
	disabled = false,
} ) {
	const instanceId = useInstanceId( PanelUsers );
	const { editGroup } = useDispatch( uiStore );
	const { receiveUser } = useDispatch( 'ithemes-security/core' );
	const { users, loading } = useSelect( ( select ) => {
		const _userIds =
			select( uiStore ).getEditedGroupAttribute( groupId, 'users' ) || [];
		const _users = [];
		let _loading = false;

		_userIds.forEach( ( userId ) => {
			const user = select( 'ithemes-security/core' ).getUser( userId );

			if ( user ) {
				_users.push( user );
			} else if (
				select( 'core/data' ).isResolving(
					'ithemes-security/core',
					'getUser',
					[ userId ]
				)
			) {
				_loading = true;
			}
		} );

		return {
			users: _users,
			loading: _loading,
		};
	}, [ groupId ] );

	const [ selectSearch, setSelectSearch ] = useState( '' );
	const selectId = `itsec-user-group-panel-users__select-${ instanceId }`;
	const values = loading ? [] : users.map( formatUser );

	const loadOptions = useMemo( () => loadUsers( receiveUser ), [ receiveUser ] );

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
				loadOptions={ loadOptions }
				value={ values }
				onChange={ ( newUsers ) =>
					editGroup( groupId, { users: map( newUsers, 'value' ) } )
				}
				inputValue={ selectSearch }
				onInputChange={ ( newSelect ) =>
					setSelectSearch( newSelect )
				}
			/>
		</BaseControl>
	);
}
